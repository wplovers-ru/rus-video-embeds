<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Embed;

defined('ABSPATH') || exit;

use RusVideoEmbeds\Providers\ProviderRegistry;
use RusVideoEmbeds\Providers\VideoProviderInterface;

/**
 * Registers WordPress embed handlers for each video provider.
 *
 * When a user pastes a supported video URL on its own line in the editor,
 * WordPress automatically replaces it with a responsive iframe embed
 * via the callback registered here.
 */
class OEmbedHandler
{

    /**
     * Registers embed handlers for all enabled providers.
     *
     * Each handler is registered with wp_embed_register_handler()
     * using the provider's URL regex pattern and a rendering callback.
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
                "rve_{$slug}",
                $pattern,
                self::makeCallback($provider),
                10
            );
        }
    }

    /**
     * Creates a closure that serves as the embed handler callback.
     *
     * For providers that support isWatchUrl() (Dzen), returns an informational
     * notice instead of an empty string when the URL cannot be embedded.
     *
     * @param VideoProviderInterface $provider The provider to generate embed HTML for.
     * @return callable Callback compatible with wp_embed_register_handler.
     */
    private static function makeCallback(VideoProviderInterface $provider): callable
    {
        /**
         * @param array  $matches Regex matches from the URL pattern.
         * @param array  $attr    Shortcode-style attributes (unused).
         * @param string $url     The original URL.
         * @param array  $rawattr Raw attributes (unused).
         * @return string Rendered embed HTML.
         */
        return static function (array $matches, array $attr, string $url, array $rawattr) use ($provider): string {
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
        };
    }
}
