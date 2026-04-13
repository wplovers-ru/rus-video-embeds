<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Providers;

/**
 * Provider for VK Video (vk.com/video*, vk.com/clip*, vkvideo.ru/*).
 *
 * Parses VK video URLs in multiple formats and generates embed URLs
 * through VK's video_ext.php endpoint.
 */
class VkVideoProvider implements VideoProviderInterface
{
    private const URL_PATTERN = '#https?://(?:(?:www\.)?vk\.com/(?:video|clip)|vkvideo\.ru/video)(-?\d+)_(\d+)#i';

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
     * Generates a VK embed URL via video_ext.php.
     * Supports autoplay via $args['autoplay'].
     */
    public function getEmbedUrl(string $url, array $args = []): ?string
    {
        $matches = [];
        if (!preg_match(self::URL_PATTERN, $url, $matches)) {
            return null;
        }

        $oid = $matches[1];
        $id  = $matches[2];

        $embed = "https://vk.com/video_ext.php?oid={$oid}&id={$id}&hd=2";

        if (!empty($args['autoplay'])) {
            $embed .= '&autoplay=1';
        }

        return $embed;
    }

    /**
     * {@inheritDoc}
     *
     * Returns the combined oid_id string (e.g. "-123456_789012").
     */
    public function getVideoId(string $url): ?string
    {
        $matches = [];
        if (!preg_match(self::URL_PATTERN, $url, $matches)) {
            return null;
        }

        return $matches[1] . '_' . $matches[2];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'VK Видео';
    }

    /**
     * {@inheritDoc}
     */
    public function getSlug(): string
    {
        return 'vk_video';
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlPattern(): string
    {
        return self::URL_PATTERN;
    }
}
