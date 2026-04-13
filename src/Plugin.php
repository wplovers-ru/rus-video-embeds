<?php

declare(strict_types=1);

namespace RusVideoEmbeds;

use RusVideoEmbeds\Admin\SettingsPage;
use RusVideoEmbeds\Block\VideoBlock;
use RusVideoEmbeds\Embed\EmbedRenderer;
use RusVideoEmbeds\Embed\OEmbedHandler;
use RusVideoEmbeds\Embed\ShortcodeHandler;
use RusVideoEmbeds\Providers\ProviderRegistry;

/**
 * Main plugin bootstrap class.
 *
 * Initialises all modules and registers WordPress hooks:
 * - init: providers, oEmbed handlers, shortcodes, Gutenberg block
 * - admin_menu/admin_init: settings page
 * - wp_enqueue_scripts: conditional CSS loading
 */
class Plugin
{
    /**
     * Bootstraps the plugin by registering all hooks.
     *
     * Should be called once from the main plugin file.
     *
     * @return void
     */
    public static function init(): void
    {
        add_action('init', [self::class, 'onInit']);

        SettingsPage::register();

        add_action('wp_enqueue_scripts', [self::class, 'enqueueStyles']);
        add_action('wp_footer', [self::class, 'maybeEnqueueStyles']);
    }

    /**
     * Runs on the `init` hook: initialises providers, oEmbed, shortcodes, block.
     *
     * @return void
     */
    public static function onInit(): void
    {
        ProviderRegistry::getInstance();

        OEmbedHandler::register();
        ShortcodeHandler::register();
        VideoBlock::register();
    }

    /**
     * Registers the embed stylesheet (does not enqueue yet).
     *
     * The actual enqueue happens conditionally in maybeEnqueueStyles()
     * if at least one embed was rendered during the request.
     *
     * @return void
     */
    public static function enqueueStyles(): void
    {
        wp_register_style(
            'rve-embed-styles',
            RVE_PLUGIN_URL . 'assets/css/embed-styles.css',
            [],
            RVE_VERSION
        );
    }

    /**
     * Enqueues the CSS in the footer only if an embed was rendered.
     *
     * Avoids loading CSS on pages that don't contain any plugin embeds.
     *
     * @return void
     */
    public static function maybeEnqueueStyles(): void
    {
        if (EmbedRenderer::hasEmbed()) {
            wp_enqueue_style('rve-embed-styles');
        }
    }
}
