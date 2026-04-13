<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Providers;

/**
 * Provider for Rutube (rutube.ru/video/*, rutube.ru/play/embed/*).
 *
 * Parses Rutube video URLs and generates embed URLs through
 * Rutube's play/embed endpoint.
 */
class RutubeProvider implements VideoProviderInterface
{
    private const URL_PATTERN = '#https?://(?:www\.)?rutube\.ru/(?:video|play/embed)/([a-f0-9]+)/?#i';

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
     * Generates a Rutube embed URL via play/embed.
     * Supports autoplay via $args['autoplay'].
     */
    public function getEmbedUrl(string $url, array $args = []): ?string
    {
        $videoId = $this->getVideoId($url);
        if ($videoId === null) {
            return null;
        }

        $embed = "https://rutube.ru/play/embed/{$videoId}";

        if (!empty($args['autoplay'])) {
            $embed .= '?autoplay=1';
        }

        return $embed;
    }

    /**
     * {@inheritDoc}
     *
     * Returns the hexadecimal video hash ID.
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
        return 'Rutube';
    }

    /**
     * {@inheritDoc}
     */
    public function getSlug(): string
    {
        return 'rutube';
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlPattern(): string
    {
        return self::URL_PATTERN;
    }
}
