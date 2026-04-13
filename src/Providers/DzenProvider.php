<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Providers;

/**
 * Provider for Dzen video embeds.
 *
 * Supports two URL formats with different behaviour:
 * - Watch URLs (dzen.ru/video/watch/*, zen.yandex.ru/video/watch/*) — recognised
 *   but NOT embeddable (Dzen uses separate IDs for watch vs embed).
 * - Embed URLs (dzen.ru/embed/*) — directly embeddable via iframe.
 */
class DzenProvider implements VideoProviderInterface
{
    private const WATCH_PATTERN = '#https?://(?:(?:www\.)?dzen\.ru|zen\.yandex\.ru)/video/watch/([a-zA-Z0-9]+)#i';
    private const EMBED_PATTERN = '#https?://(?:www\.)?dzen\.ru/embed/([a-zA-Z0-9]+)#i';
    private const COMBINED_PATTERN = '#https?://(?:(?:www\.)?dzen\.ru(?:/(?:video/watch|embed))|zen\.yandex\.ru/video/watch)/([a-zA-Z0-9]+)#i';

    /**
     * {@inheritDoc}
     */
    public function matches(string $url): bool
    {
        return (bool) preg_match(self::WATCH_PATTERN, $url)
            || (bool) preg_match(self::EMBED_PATTERN, $url);
    }

    /**
     * Determines whether the given URL is a Dzen watch-URL (not embeddable).
     *
     * Watch URLs use a different video ID than embed URLs, and there is no
     * public mapping between the two. These URLs are recognised but cannot
     * produce a working iframe embed.
     *
     * @param string $url The URL to check.
     * @return bool True if the URL is a Dzen watch-URL.
     */
    public function isWatchUrl(string $url): bool
    {
        return (bool) preg_match(self::WATCH_PATTERN, $url);
    }

    /**
     * {@inheritDoc}
     *
     * Returns the embed URL only for dzen.ru/embed/* URLs (without query params).
     * Returns null for watch-URLs because Dzen uses different IDs for watch vs embed.
     */
    public function getEmbedUrl(string $url, array $args = []): ?string
    {
        if (!preg_match(self::EMBED_PATTERN, $url, $matches)) {
            return null;
        }

        $embed = "https://dzen.ru/embed/{$matches[1]}";

        if (!empty($args['autoplay'])) {
            $embed .= '?autoplay=1';
        }

        return $embed;
    }

    /**
     * {@inheritDoc}
     *
     * Extracts the alphanumeric ID from both watch and embed URL formats.
     */
    public function getVideoId(string $url): ?string
    {
        if (preg_match(self::EMBED_PATTERN, $url, $matches)) {
            return $matches[1];
        }

        if (preg_match(self::WATCH_PATTERN, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Дзен';
    }

    /**
     * {@inheritDoc}
     */
    public function getSlug(): string
    {
        return 'dzen';
    }

    /**
     * {@inheritDoc}
     *
     * Returns a combined regex that matches both watch and embed URL formats.
     * Used for oEmbed handler registration via wp_embed_register_handler().
     */
    public function getUrlPattern(): string
    {
        return self::COMBINED_PATTERN;
    }
}
