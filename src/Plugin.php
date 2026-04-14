<?php
declare(strict_types=1);

namespace RusVideoEmbeds;

defined('ABSPATH') || exit;

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
        add_action('enqueue_block_editor_assets', [self::class, 'localizeBlockEditorData']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueEditorStyles']);
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
            RUS_VIDEO_EMBEDS_URL . 'assets/css/embed-styles.css',
            [],
            RUS_VIDEO_EMBEDS_VERSION
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

    /**
     * Enqueues embed styles on post editing screens for Classic Editor compatibility.
     *
     * Ensures oEmbed previews inside TinyMCE render without scrollbars.
     * Only loads on post.php and post-new.php screens.
     *
     * @param string $hookSuffix The current admin page hook suffix.
     * @return void
     */
    public static function enqueueEditorStyles(string $hookSuffix): void
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
    }

    /**
     * Passes site domain and UTM link to the block editor JS for Dzen notice rendering.
     *
     * @return void
     */
    public static function localizeBlockEditorData(): void
    {
        $handle = 'rus-video-embeds-video-editor-script';

        $options = get_option('rve_settings', []);

        wp_localize_script($handle, 'rveBlockData', [
            'siteDomain'            => wp_parse_url(home_url(), PHP_URL_HOST) ?: '',
            'dzenNoticeUrl'         => EmbedRenderer::getDzenNoticeUrl(),
            'defaultVerticalMargin' => $options['default_vertical_margin'] ?? '',
        ]);
    }
}
