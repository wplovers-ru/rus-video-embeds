<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Embed;

defined('ABSPATH') || exit;

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
     *     @type int    $width          Explicit width in pixels (0 = responsive).
     *     @type int    $height         Explicit height in pixels (0 = responsive).
     *     @type string $aspectRatio    Aspect ratio string, e.g. "16:9", "4:3". Default "16:9".
     *     @type bool   $autoplay       Whether to enable autoplay. Default false.
     *     @type string $verticalMargin Gutenberg spacing preset slug for top/bottom margin. Default from settings.
     *     @type bool   $skipMargin     If true, skip applying vertical margin (used by Gutenberg blocks
     *                                  where WP's block rendering pipeline handles spacing). Default false.
     * }
     * @return string Sanitised HTML string with the embed markup.
     */
    public static function render(string $embedUrl, array $args = []): string
    {
        $defaults = self::getDefaults();

        $width          = (int) ($args['width'] ?? $defaults['width']);
        $height         = (int) ($args['height'] ?? $defaults['height']);
        $aspectRatio    = $args['aspectRatio'] ?? $defaults['aspectRatio'];
        $autoplay       = !empty($args['autoplay']);
        $skipMargin     = !empty($args['skipMargin']);
        $verticalMargin = $args['verticalMargin'] ?? $defaults['verticalMargin'];

        if ($autoplay) {
            $separator = (strpos($embedUrl, '?') !== false) ? '&' : '?';
            $embedUrl .= $separator . 'autoplay=1';
        }

        $safeUrl = esc_url($embedUrl);
        if (empty($safeUrl)) {
            return '';
        }

        $ratioStyle = self::buildAspectRatioStyle($aspectRatio);
        $wrapperStyle = 'position:relative;overflow:hidden;width:100%;' . $ratioStyle;
        if ($width > 0) {
            $wrapperStyle .= "max-width:{$width}px;";
        }
        if (!$skipMargin && $verticalMargin !== '') {
            $marginCss = 'var(--wp--preset--spacing--' . esc_attr($verticalMargin) . ')';
            $wrapperStyle .= "margin-top:{$marginCss};margin-bottom:{$marginCss};";
        }

        $iframeStyle = 'display:block;width:100%;height:auto;border:0;aspect-ratio:inherit;';

        $iframeAttrs = [
            'src'             => $safeUrl,
            'style'           => $iframeStyle,
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
     * Renders an informational notice block (e.g. when a watch-URL is used instead of embed-URL).
     *
     * Used across all embed contexts (oEmbed, shortcode, Gutenberg server-side render)
     * to display a user-friendly message with an optional link.
     *
     * @param string $message  The notice message text.
     * @param string $linkUrl  The URL for the call-to-action link.
     * @param string $linkText The visible text for the link.
     * @return string HTML string with the notice markup.
     */
    public static function renderNotice(string $message, string $linkUrl, string $linkText): string
    {
        $safeUrl  = esc_url($linkUrl);
        $safeText = esc_html($linkText);
        $safeMsg  = esc_html($message);

        self::$hasEmbed = true;

        return '<div class="rve-notice">'
            . '<span class="rve-notice__icon" aria-hidden="true">&#8505;&#65039;</span>'
            . '<div class="rve-notice__content">'
            . '<p class="rve-notice__message">' . $safeMsg . '</p>'
            . '<a class="rve-notice__link" href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">'
            . $safeText . ' &rarr;</a>'
            . '</div>'
            . '</div>';
    }

    /**
     * Builds the UTM link to the Dzen embed instruction page.
     *
     * @return string Full URL with utm_source (site domain) and utm_content parameters.
     */
    public static function getDzenNoticeUrl(): string
    {
        $domain = wp_parse_url(home_url(), PHP_URL_HOST) ?: 'unknown';

        return 'https://wplovers.ru/dzen-wordpress/?'
            . http_build_query([
                'utm_source'  => $domain,
                'utm_content' => 'dzen_embed',
            ]);
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
     * @return array{width: int, height: int, aspectRatio: string, autoplay: bool, verticalMargin: string}
     */
    private static function getDefaults(): array
    {
        $options = get_option('wplrve_settings', get_option('rve_settings', []));

        return [
            'width'          => (int) ($options['default_width'] ?? 0),
            'height'         => (int) ($options['default_height'] ?? 0),
            'aspectRatio'    => '16:9',
            'autoplay'       => !empty($options['default_autoplay']),
            'verticalMargin' => $options['default_vertical_margin'] ?? '',
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
