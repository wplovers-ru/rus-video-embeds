<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Embed;

use RusVideoEmbeds\Providers\ProviderRegistry;

/**
 * Registers and handles shortcodes for each video provider.
 *
 * Shortcodes: [vk_video], [rutube], [dzen]
 * Parameters: url (required), width, height, autoplay (0/1).
 * Defaults are sourced from plugin settings via EmbedRenderer.
 */
class ShortcodeHandler
{
    /**
     * Registers shortcodes for all enabled providers.
     *
     * Each shortcode name matches the provider slug (e.g. "vk_video").
     *
     * @return void
     */
    public static function register(): void
    {
        $registry = ProviderRegistry::getInstance();

        foreach ($registry->getEnabledProviders() as $provider) {
            $slug = $provider->getSlug();
            add_shortcode($slug, [self::class, 'handleShortcode']);
        }
    }

    /**
     * Handles any of the registered video shortcodes.
     *
     * Validates the URL against the matching provider, then delegates
     * rendering to EmbedRenderer. Returns empty string if URL is invalid
     * or doesn't match any enabled provider.
     *
     * @param array|string $atts    Shortcode attributes.
     * @param string|null  $content Shortcode content (unused).
     * @param string       $tag     The shortcode tag name (matches provider slug).
     * @return string Rendered embed HTML, or empty string on failure.
     */
    public static function handleShortcode($atts, ?string $content, string $tag): string
    {
        $atts = shortcode_atts(
            [
                'url'      => '',
                'width'    => 0,
                'height'   => 0,
                'autoplay' => 0,
            ],
            $atts,
            $tag
        );

        $url = esc_url_raw(trim($atts['url']));
        if (empty($url)) {
            return '';
        }

        $registry = ProviderRegistry::getInstance();
        $provider = $registry->findByUrl($url);

        if ($provider === null || $provider->getSlug() !== $tag) {
            return '';
        }

        $embedUrl = $provider->getEmbedUrl($url);
        if ($embedUrl === null) {
            return '';
        }

        return EmbedRenderer::render($embedUrl, [
            'width'    => (int) $atts['width'],
            'height'   => (int) $atts['height'],
            'autoplay' => (bool) $atts['autoplay'],
        ]);
    }
}
