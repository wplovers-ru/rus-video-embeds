<?php
declare(strict_types=1);

namespace RusVideoEmbeds;

defined('ABSPATH') || exit;

use RusVideoEmbeds\Admin\SettingsPage;
use RusVideoEmbeds\Block\VideoBlock;
use RusVideoEmbeds\Embed\EmbedRenderer;
use RusVideoEmbeds\Embed\OEmbedHandler;
use RusVideoEmbeds\Embed\ShortcodeHandler;
use RusVideoEmbeds\Integrations\IntegrationManager;
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
    private const SETTINGS_OPTION_NAME        = 'wplrve_settings';
    private const LEGACY_SETTINGS_OPTION_NAME = 'rve_settings';

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

        add_action('wp_enqueue_scripts', [self::class, 'registerFrontendAssets']);
        add_action('wp_footer', [self::class, 'maybeEnqueueFrontendAssets']);
        add_action('enqueue_block_editor_assets', [self::class, 'localizeBlockEditorData']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueEditorAssets']);
    }

    /**
     * Runs on the `init` hook: initialises providers, oEmbed, shortcodes, block.
     *
     * Registers the editor stylesheet before the block so block.json
     * can reference it by handle via editorStyle.
     *
     * @return void
     */
    public static function onInit(): void
    {
        wp_register_style(
            'rve-embed-editor-styles',
            RUS_VIDEO_EMBEDS_URL . 'assets/css/embed-editor.css',
            [],
            RUS_VIDEO_EMBEDS_VERSION
        );

        ProviderRegistry::getInstance();

        OEmbedHandler::register();
        ShortcodeHandler::register();
        VideoBlock::register();
        IntegrationManager::register();
    }

    /**
     * Registers frontend embed assets (without immediate enqueue).
     *
     * Assets are conditionally enqueued in maybeEnqueueFrontendAssets()
     * only when embed output is present in the current request.
     *
     * @return void
     */
    public static function registerFrontendAssets(): void
    {
        wp_register_style(
            'rve-embed-styles',
            RUS_VIDEO_EMBEDS_URL . 'assets/css/embed-styles.css',
            [],
            RUS_VIDEO_EMBEDS_VERSION
        );

        wp_register_script(
            'rve-embed-sandbox-fix',
            RUS_VIDEO_EMBEDS_URL . 'assets/js/embed-sandbox-fix.js',
            [],
            RUS_VIDEO_EMBEDS_VERSION,
            true
        );
    }

    /**
     * Enqueues frontend embed assets when an embed was rendered.
     *
     * @return void
     */
    public static function maybeEnqueueFrontendAssets(): void
    {
        if (!EmbedRenderer::hasEmbed()) {
            return;
        }

        wp_enqueue_style('rve-embed-styles');
        wp_enqueue_script('rve-embed-sandbox-fix');
    }

    /**
     * Enqueues embed assets on post editing screens for Classic Editor compatibility.
     *
     * Ensures oEmbed previews inside TinyMCE render without scrollbars.
     * Only loads on post.php and post-new.php screens.
     *
     * @param string $hookSuffix The current admin page hook suffix.
     * @return void
     */
    public static function enqueueEditorAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'post.php' && $hookSuffix !== 'post-new.php') {
            return;
        }

        wp_enqueue_style(
            'rve-embed-styles',
            RUS_VIDEO_EMBEDS_URL . 'assets/css/embed-styles.css',
            [],
            RUS_VIDEO_EMBEDS_VERSION
        );

        wp_enqueue_script(
            'rve-embed-sandbox-fix',
            RUS_VIDEO_EMBEDS_URL . 'assets/js/embed-sandbox-fix.js',
            [],
            RUS_VIDEO_EMBEDS_VERSION,
            true
        );
    }

    /**
     * Passes site domain and UTM link to the block editor JS for Dzen notice rendering.
     *
     * @return void
     */
    public static function localizeBlockEditorData(): void
    {
        $handle = 'rus-video-embeds-video-editor-script';

        $options = get_option(self::SETTINGS_OPTION_NAME, []);
        if (!is_array($options) || $options === []) {
            $legacyOptions = get_option(self::LEGACY_SETTINGS_OPTION_NAME, []);
            if (is_array($legacyOptions)) {
                $options = $legacyOptions;
            }
        }

        wp_localize_script($handle, 'wplrveBlockData', [
            'siteDomain'            => wp_parse_url(home_url(), PHP_URL_HOST) ?: '',
            'dzenNoticeUrl'         => EmbedRenderer::getDzenNoticeUrl(),
            'defaultVerticalMargin' => $options['default_vertical_margin'] ?? '',
        ]);
    }
}
