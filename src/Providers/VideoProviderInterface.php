<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Providers;

defined('ABSPATH') || exit;

/**
 * Contract for video hosting providers.
 *
 * Each supported video platform implements this interface to handle
 * URL matching, video ID extraction, and embed URL generation.
 */
interface VideoProviderInterface
{
    /**
     * Determines whether the given URL belongs to this provider.
     *
     * @param string $url The URL to check.
     * @return bool True if this provider can handle the URL.
     */
    public function matches(string $url): bool;

    /**
     * Generates the embeddable iframe URL for the given video URL.
     *
     * @param string $url   The original video URL.
     * @param array  $args  Optional parameters (e.g. autoplay).
     * @return string|null  The embed URL, or null if the URL is invalid.
     */
    public function getEmbedUrl(string $url, array $args = []): ?string;

    /**
     * Extracts the video identifier from the URL.
     *
     * @param string $url The original video URL.
     * @return string|null The video ID, or null if extraction fails.
     */
    public function getVideoId(string $url): ?string;

    /**
     * Returns the human-readable provider name.
     *
     * @return string Provider name (e.g. "VK Видео").
     */
    public function getName(): string;

    /**
     * Returns the provider machine slug used in settings and filters.
     *
     * @return string Provider slug (e.g. "vk_video").
     */
    public function getSlug(): string;

    /**
     * Returns the regex pattern(s) that match this provider's URLs.
     * Used for oEmbed handler registration.
     *
     * @return string Regex pattern compatible with wp_embed_register_handler.
     */
    public function getUrlPattern(): string;
}
