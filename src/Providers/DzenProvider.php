<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Providers;

/**
 * Provider for Dzen (dzen.ru/video/watch/*, zen.yandex.ru/video/watch/*).
 *
 * Parses Yandex Dzen video URLs and generates embed URLs
 * through Dzen's embed endpoint.
 */
class DzenProvider implements VideoProviderInterface
{
    private const URL_PATTERN = '#https?://(?:(?:www\.)?dzen\.ru|zen\.yandex\.ru)/video/watch/([a-zA-Z0-9]+)#i';

    /**
     * {@inheritDoc}
     */
    public function matches(string $url): bool
    {
        return (bool) preg_match(self::URL_PATTERN, $url);
    }

    /**
     * {@inheritDoc}
     *
     * Generates a Dzen embed URL.
     * Supports autoplay via $args['autoplay'].
     */
    public function getEmbedUrl(string $url, array $args = []): ?string
    {
        $videoId = $this->getVideoId($url);
        if ($videoId === null) {
            return null;
        }

        $embed = "https://dzen.ru/embed/{$videoId}";

        if (!empty($args['autoplay'])) {
            $embed .= '?autoplay=1';
        }

        return $embed;
    }

    /**
     * {@inheritDoc}
     *
     * Returns the alphanumeric video ID.
     */
    public function getVideoId(string $url): ?string
    {
        $matches = [];
        if (!preg_match(self::URL_PATTERN, $url, $matches)) {
            return null;
        }

        return $matches[1];
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
     */
    public function getUrlPattern(): string
    {
        return self::URL_PATTERN;
    }
}
