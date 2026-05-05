<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Embed;

defined('ABSPATH') || exit;

use RusVideoEmbeds\Providers\ProviderRegistry;
use RusVideoEmbeds\Providers\VideoProviderInterface;

/**
 * Registers WordPress oEmbed integration for each video provider.
 *
 * Supports both core paths:
 * - `wp_embed_register_handler()` for auto-embed in content parsing.
 * - `pre_oembed_result` for contexts using `wp_oembed_get()` directly
 *   (common in third-party plugins and non-post rendering pipelines).
 */
class OEmbedHandler
{
    /**
     * Registers oEmbed handlers and hooks for all enabled providers.
     *
     * @return void
     */
    public static function register(): void
    {
        $registry = ProviderRegistry::getInstance();

        foreach ($registry->getEnabledProviders() as $provider) {
            $slug    = $provider->getSlug();
            $pattern = $provider->getUrlPattern();

            wp_embed_register_handler(
                "wplrve_{$slug}",
                $pattern,
                self::makeEmbedHandlerCallback($provider),
                10
            );
        }

        add_filter('pre_oembed_result', [self::class, 'maybeRenderPreOembedResult'], 10, 3);
    }

    /**
     * Handles the core `pre_oembed_result` filter for direct oEmbed calls.
     *
     * This enables compatibility with integrations that call `wp_oembed_get()`
     * instead of relying on post-content autoembed parsing.
     *
     * @param mixed  $result Existing result from earlier filters. False means continue resolving.
     * @param string $url    URL being resolved by WordPress oEmbed.
     * @param array  $args   Additional oEmbed arguments.
     * @return mixed HTML string for supported providers, or original $result.
     */
    public static function maybeRenderPreOembedResult($result, string $url, array $args)
    {
        if ($result !== false) {
            return $result;
        }

        $provider = ProviderRegistry::getInstance()->findByUrl($url);
        if ($provider === null) {
            return $result;
        }

        return self::renderForProvider($provider, $url);
    }

    /**
     * Creates a callback for `wp_embed_register_handler()`.
     *
     * @param VideoProviderInterface $provider The provider to generate embed HTML for.
     * @return callable Callback compatible with wp_embed_register_handler.
     */
    private static function makeEmbedHandlerCallback(VideoProviderInterface $provider): callable
    {
        /**
         * @param array  $matches Regex matches from the URL pattern.
         * @param array  $attr    Shortcode-style attributes (unused).
         * @param string $url     The original URL.
         * @param array  $rawattr Raw attributes (unused).
         * @return string Rendered embed HTML.
         */
        return static function (array $matches, array $attr, string $url, array $rawattr) use ($provider): string {
            return self::renderForProvider($provider, $url);
        };
    }

    /**
     * Renders embed output (or Dzen notice) for the given provider and URL.
     *
     * @param VideoProviderInterface $provider Provider resolved for the URL.
     * @param string                 $url      Original URL.
     * @return string Rendered HTML, or empty string when URL is unsupported.
     */
    private static function renderForProvider(VideoProviderInterface $provider, string $url): string
    {
        $embedUrl = $provider->getEmbedUrl($url);
        if ($embedUrl === null) {
            if (method_exists($provider, 'isWatchUrl') && $provider->isWatchUrl($url)) {
                return EmbedRenderer::renderNotice(
                    __('Dzen uses separate links for embedding. Click "Share" → "Embed" under the video and copy the link from the iframe code.', 'rus-video-embeds'),
                    EmbedRenderer::getDzenNoticeUrl(),
                    __('Learn more', 'rus-video-embeds')
                );
            }

            return '';
        }

        return EmbedRenderer::render($embedUrl);
    }
}
