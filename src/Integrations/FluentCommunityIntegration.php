<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Integrations;

defined('ABSPATH') || exit;

use RusVideoEmbeds\Providers\ProviderRegistry;

/**
 * FluentCommunity integration.
 *
 * Ensures supported video URLs are persisted into FluentCommunity's
 * `meta.media_preview` structure during feed create/update, so embeds
 * remain visible after publish.
 */
class FluentCommunityIntegration
{
    /**
     * Registers FluentCommunity hooks when the plugin is available.
     *
     * @return void
     */
    public static function register(): void
    {
        if (!self::isFluentCommunityAvailable()) {
            return;
        }

        add_filter('fluent_community/feed/new_feed_data', [self::class, 'injectMediaPreview'], 20, 2);
        add_filter('fluent_community/feed/update_feed_data', [self::class, 'injectMediaPreview'], 20, 2);
    }

    /**
     * Injects `meta.media_preview` for supported providers when missing.
     *
     * FluentCommunity often expects this structure to persist preview/embed
     * data after publishing. If missing, the post may render without video.
     *
     * @param array $data        Feed data to be persisted.
     * @param array $requestData Raw request payload from FluentCommunity editor.
     * @return array Possibly modified feed data.
     */
    public static function injectMediaPreview(array $data, array $requestData): array
    {
        $existingPreview = $data['meta']['media_preview'] ?? [];
        if (!is_array($existingPreview)) {
            $existingPreview = [];
        }

        if (!self::shouldRefreshMediaPreview($existingPreview)) {
            return $data;
        }

        $url = self::extractCandidateUrl($requestData, $data, $existingPreview);
        if ($url === null) {
            return $data;
        }

        $provider = ProviderRegistry::getInstance()->findByUrl($url);
        if ($provider === null) {
            return $data;
        }

        $embedUrl = $provider->getEmbedUrl($url);
        if ($embedUrl === null) {
            return $data;
        }

        $resolvedImage = self::resolvePreviewImage($url, $requestData, $provider->getSlug(), $embedUrl);
        if ($resolvedImage === null) {
            $existingImage = $existingPreview['image'] ?? null;
            if (is_string($existingImage) && $existingImage !== '') {
                $resolvedImage = esc_url_raw($existingImage);
            }
        }

        $data['meta']['media_preview'] = [
            'type'         => 'oembed',
            'provider'     => $provider->getSlug(),
            'content_type' => 'video',
            'url'          => esc_url_raw($url),
            'html'         => self::buildIframeHtml($embedUrl),
            'image'        => $resolvedImage,
        ];

        return $data;
    }

    /**
     * Determines whether FluentCommunity is installed/active.
     *
     * @return bool
     */
    private static function isFluentCommunityAvailable(): bool
    {
        return class_exists('FluentCommunity\\App\\Models\\Feed')
            || defined('FLUENT_COMMUNITY_PLUGIN_VERSION');
    }

    /**
     * Extracts a candidate media URL from request payload or rendered message.
     *
     * @param array $requestData     FluentCommunity request data.
     * @param array $data            Prepared feed data.
     * @param array $existingPreview Existing media_preview payload.
     * @return string|null
     */
    private static function extractCandidateUrl(array $requestData, array $data, array $existingPreview = []): ?string
    {
        $mediaUrl = $requestData['media']['url'] ?? null;
        if (is_string($mediaUrl) && filter_var($mediaUrl, FILTER_VALIDATE_URL)) {
            return $mediaUrl;
        }

        $existingUrl = $existingPreview['url'] ?? null;
        if (is_string($existingUrl) && filter_var($existingUrl, FILTER_VALIDATE_URL)) {
            return $existingUrl;
        }

        $messageRendered = $data['message_rendered'] ?? '';
        if (!is_string($messageRendered) || $messageRendered === '') {
            return null;
        }

        if (!preg_match('#https?://[^\s"\'<>]+#i', $messageRendered, $matches)) {
            return null;
        }

        return filter_var($matches[0], FILTER_VALIDATE_URL) ? $matches[0] : null;
    }

    /**
     * Determines whether media_preview needs rebuilding.
     *
     * Existing preview can miss image data for older posts or stale records.
     * In that case we re-run provider parsing and preview resolution.
     *
     * @param array $mediaPreview Existing media preview payload.
     * @return bool
     */
    private static function shouldRefreshMediaPreview(array $mediaPreview): bool
    {
        if ($mediaPreview === []) {
            return true;
        }

        $type = $mediaPreview['type'] ?? null;
        if (!is_string($type) || $type !== 'oembed') {
            return true;
        }

        $image = $mediaPreview['image'] ?? null;
        if (!is_string($image) || trim($image) === '') {
            return true;
        }

        return false;
    }

    /**
     * Builds iframe HTML expected by FluentCommunity media_preview structure.
     *
     * @param string $embedUrl Sanitized embed URL.
     * @return string
     */
    private static function buildIframeHtml(string $embedUrl): string
    {
        $safeUrl = esc_url($embedUrl);

        return sprintf(
            '<iframe width="500" height="375" src="%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>',
            $safeUrl
        );
    }

    /**
     * Resolves a thumbnail URL for FluentCommunity card view.
     *
     * FluentCommunity cards use `meta.media_preview.image` for thumbnail rendering.
     * First priority is editor payload (`media.image`), then parser fallback.
     * For VK, we additionally parse video_ext page for getVideoPreview URL.
     *
     * @param string $url Original video URL.
     * @param array  $requestData Raw FluentCommunity request payload.
     * @param string $providerSlug Resolved provider slug.
     * @param string $embedUrl Embed URL generated by provider.
     * @return string|null
     */
    private static function resolvePreviewImage(string $url, array $requestData = [], string $providerSlug = '', string $embedUrl = ''): ?string
    {
        $requestImage = $requestData['media']['image'] ?? null;
        if (is_string($requestImage) && $requestImage !== '') {
            $sanitized = esc_url_raw($requestImage);
            if ($sanitized !== '') {
                return $sanitized;
            }
        }

        if ($providerSlug === 'vk_video') {
            $vkImage = self::resolveVkPreviewFromEmbedUrl($embedUrl);
            if ($vkImage !== null) {
                return $vkImage;
            }
        }

        if (!class_exists('FluentCommunity\\App\\Services\\RemoteUrlParser')) {
            return null;
        }

        $metaData = \FluentCommunity\App\Services\RemoteUrlParser::parse($url);
        if (!is_array($metaData)) {
            return null;
        }

        $image = $metaData['image'] ?? null;
        if (!is_string($image) || $image === '') {
            return null;
        }

        return esc_url_raw($image);
    }

    /**
     * Extracts VK preview image URL from video_ext page source.
     *
     * VK often does not expose og:image for shared video pages, but embeds
     * include a getVideoPreview URL we can transform into an absolute image URL.
     *
     * @param string $embedUrl Embed URL produced by VkVideoProvider.
     * @return string|null
     */

    /**
     * Upgrades VK preview URL to a higher-quality thumbnail variant.
     *
     * VK preview endpoint uses `fn` parameter to control image size.
     * We prefer `vid_w` (wider/larger) over default `vid_s`.
     *
     * @param string $previewUrl Raw preview URL.
     * @return string
     */
    private static function upgradeVkPreviewQuality(string $previewUrl): string
    {
        $parts = wp_parse_url($previewUrl);
        if (!is_array($parts)) {
            return $previewUrl;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query['fn'] = 'vid_w';

        $rebuilt = '';
        if (!empty($parts['scheme'])) {
            $rebuilt .= $parts['scheme'] . '://';
        }
        if (!empty($parts['host'])) {
            $rebuilt .= $parts['host'];
        }
        if (!empty($parts['path'])) {
            $rebuilt .= $parts['path'];
        }

        $queryString = http_build_query($query);
        if ($queryString !== '') {
            $rebuilt .= '?' . $queryString;
        }

        return $rebuilt !== '' ? $rebuilt : $previewUrl;
    }

    private static function resolveVkPreviewFromEmbedUrl(string $embedUrl): ?string
    {
        if ($embedUrl === '') {
            return null;
        }

        $response = wp_safe_remote_get($embedUrl, [
            'timeout'             => 10,
            'limit_response_size' => 300 * KB_IN_BYTES,
            'user-agent'          => 'WP-URLDetails/' . get_bloginfo('version') . ' (+' . get_bloginfo('url') . ')',
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $html = wp_remote_retrieve_body($response);
        if (!is_string($html) || $html === '') {
            return null;
        }

        if (!preg_match('/getVideoPreview\?[^"\'\s<]+/i', $html, $matches)) {
            return null;
        }

        $previewPath = html_entity_decode($matches[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $previewUrl  = 'https://iv.okcdn.ru/' . ltrim($previewPath, '/');
        $previewUrl  = self::upgradeVkPreviewQuality($previewUrl);

        $safe = esc_url_raw($previewUrl);
        return $safe !== '' ? $safe : null;
    }
}
