<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Embed;

/**
 * Centralised renderer for video embed iframes.
 *
 * All embed output (oEmbed, shortcodes, Gutenberg block) passes through
 * this class to ensure consistent HTML structure, security attributes,
 * and responsive behaviour.
 */
class EmbedRenderer
{
    /**
     * Renders a responsive iframe embed wrapped in a .rve-wrapper div.
     *
     * @param string $embedUrl    The embed URL for the iframe src attribute.
     * @param array  $args {
     *     Optional rendering parameters.
     *
     *     @type int    $width       Explicit width in pixels (0 = responsive).
     *     @type int    $height      Explicit height in pixels (0 = responsive).
     *     @type string $aspectRatio Aspect ratio string, e.g. "16:9", "4:3". Default "16:9".
     *     @type bool   $autoplay    Whether to enable autoplay. Default false.
     * }
     * @return string Sanitised HTML string with the embed markup.
     */
    public static function render(string $embedUrl, array $args = []): string
    {
        $defaults = self::getDefaults();

        $width       = (int) ($args['width'] ?? $defaults['width']);
        $height      = (int) ($args['height'] ?? $defaults['height']);
        $aspectRatio = $args['aspectRatio'] ?? $defaults['aspectRatio'];
        $autoplay    = !empty($args['autoplay']);

        if ($autoplay) {
            $separator = (strpos($embedUrl, '?') !== false) ? '&' : '?';
            $embedUrl .= $separator . 'autoplay=1';
        }

        $safeUrl = esc_url($embedUrl);
        if (empty($safeUrl)) {
            return '';
        }

        $ratioStyle = self::buildAspectRatioStyle($aspectRatio);
        $wrapperStyle = $ratioStyle;
        if ($width > 0) {
            $wrapperStyle .= "max-width:{$width}px;";
        }

        $iframeAttrs = [
            'src'             => $safeUrl,
            'frameborder'     => '0',
            'allowfullscreen' => 'true',
            'loading'         => 'lazy',
            'sandbox'         => 'allow-scripts allow-same-origin allow-presentation',
        ];

        if ($autoplay) {
            $iframeAttrs['allow'] = 'autoplay';
        }

        if ($width > 0) {
            $iframeAttrs['width'] = (string) $width;
        }
        if ($height > 0) {
            $iframeAttrs['height'] = (string) $height;
        }

        $iframeHtml = '<iframe';
        foreach ($iframeAttrs as $attr => $value) {
            $iframeHtml .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
        }
        $iframeHtml .= '></iframe>';

        self::$hasEmbed = true;

        return '<div class="rve-wrapper" style="' . esc_attr($wrapperStyle) . '">'
            . $iframeHtml
            . '</div>';
    }

    /**
     * Tracks whether any embed was rendered during the current request.
     *
     * @var bool
     */
    private static bool $hasEmbed = false;

    /**
     * Returns whether at least one embed was rendered in this request.
     *
     * @return bool
     */
    public static function hasEmbed(): bool
    {
        return self::$hasEmbed;
    }

    /**
     * Resets the embed flag (useful for testing).
     *
     * @return void
     */
    public static function resetFlag(): void
    {
        self::$hasEmbed = false;
    }

    /**
     * Returns default embed settings from the plugin options.
     *
     * @return array{width: int, height: int, aspectRatio: string, autoplay: bool}
     */
    private static function getDefaults(): array
    {
        $options = get_option('rve_settings', []);

        return [
            'width'       => (int) ($options['default_width'] ?? 0),
            'height'      => (int) ($options['default_height'] ?? 0),
            'aspectRatio' => '16:9',
            'autoplay'    => !empty($options['default_autoplay']),
        ];
    }

    /**
     * Converts an aspect ratio string (e.g. "16:9") to a CSS style value.
     *
     * @param string $ratio The aspect ratio in "W:H" format.
     * @return string CSS style fragment, e.g. "aspect-ratio:16/9;".
     */
    private static function buildAspectRatioStyle(string $ratio): string
    {
        $parts = explode(':', $ratio);
        if (count($parts) === 2) {
            $w = (int) $parts[0];
            $h = (int) $parts[1];
            if ($w > 0 && $h > 0) {
                return "aspect-ratio:{$w}/{$h};";
            }
        }

        return 'aspect-ratio:16/9;';
    }
}
