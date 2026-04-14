<?php
/**
 * Plugin Name: RUS Video Embeds - insert VK video, Rutube, Dzen
 * Plugin URI:  https://wordpress.org/plugins/rus-video-embeds/
 * Description: Embed videos from VK Video, Rutube, and Dzen — oEmbed, shortcodes, and a Gutenberg block.
 * Version:     1.1.1
 * Author:      WPlovers
 * Author URI:  https://wplovers.ru/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rus-video-embeds
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('RUS_VIDEO_EMBEDS_VERSION', '1.1.1');
define('RUS_VIDEO_EMBEDS_FILE', __FILE__);
define('RUS_VIDEO_EMBEDS_DIR', plugin_dir_path(__FILE__));
define('RUS_VIDEO_EMBEDS_URL', plugin_dir_url(__FILE__));

if (file_exists(RUS_VIDEO_EMBEDS_DIR . 'vendor/autoload.php')) {
    require_once RUS_VIDEO_EMBEDS_DIR . 'vendor/autoload.php';
}

\RusVideoEmbeds\Plugin::init();
