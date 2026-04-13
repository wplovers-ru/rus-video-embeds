<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Providers;

/**
 * Registry of video providers.
 *
 * Holds all registered VideoProviderInterface implementations and provides
 * lookup by URL. Extensible via the `rve_register_providers` WordPress filter.
 */
class ProviderRegistry
{
    /** @var VideoProviderInterface[] */
    private array $providers = [];

    /** @var self|null Singleton instance. */
    private static ?self $instance = null;

    /**
     * Returns the singleton registry instance, initialised with default providers.
     *
     * Default providers (VK, Rutube, Dzen) are registered on first call,
     * then the `rve_register_providers` filter is applied so third-party
     * code can add or remove providers.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->registerDefaults();
        }

        return self::$instance;
    }

    /**
     * Registers default built-in providers and applies the WP filter.
     *
     * @return void
     */
    private function registerDefaults(): void
    {
        $this->register(new VkVideoProvider());
        $this->register(new RutubeProvider());
        $this->register(new DzenProvider());

        /** @var VideoProviderInterface[] $providers */
        $this->providers = apply_filters('rve_register_providers', $this->providers);
    }

    /**
     * Adds a provider to the registry.
     *
     * @param VideoProviderInterface $provider The provider to register.
     * @return void
     */
    public function register(VideoProviderInterface $provider): void
    {
        $this->providers[$provider->getSlug()] = $provider;
    }

    /**
     * Finds the first provider that can handle the given URL.
     *
     * @param string $url The URL to match against registered providers.
     * @return VideoProviderInterface|null The matching provider, or null.
     */
    public function findByUrl(string $url): ?VideoProviderInterface
    {
        $enabledProviders = $this->getEnabledProviders();

        foreach ($enabledProviders as $provider) {
            if ($provider->matches($url)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Returns all registered providers.
     *
     * @return VideoProviderInterface[] Associative array keyed by slug.
     */
    public function getAll(): array
    {
        return $this->providers;
    }

    /**
     * Returns only providers that are enabled in plugin settings.
     *
     * @return VideoProviderInterface[]
     */
    public function getEnabledProviders(): array
    {
        $options = get_option('rve_settings', []);
        $enabled = $options['enabled_providers'] ?? array_keys($this->providers);

        return array_filter(
            $this->providers,
            static fn(VideoProviderInterface $p): bool => in_array($p->getSlug(), $enabled, true)
        );
    }

    /**
     * Resets the singleton (useful for testing).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
