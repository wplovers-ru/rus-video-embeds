<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Integrations;

defined('ABSPATH') || exit;

/**
 * Registers optional third-party platform integrations.
 *
 * Keeps integration bootstrapping isolated from core plugin modules,
 * so new integrations can be added without changing unrelated logic.
 */
class IntegrationManager
{
    /**
     * Registers all supported integrations.
     *
     * @return void
     */
    public static function register(): void
    {
        FluentCommunityIntegration::register();
    }
}
