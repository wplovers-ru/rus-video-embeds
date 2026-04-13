<?php
/**
 * Plugin Name: RUS Video Embeds
 * Plugin URI:  https://github.com/your-repo/rus-video-embeds
 * Description: Автоматическая вставка видео с VK Видео, Rutube и Дзен — oEmbed, шорткоды и Gutenberg-блок.
 * Version:     1.0.0
 * Author:      RUS Video Embeds
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rus-video-embeds
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('RVE_VERSION', '1.0.0');
define('RVE_PLUGIN_FILE', __FILE__);
define('RVE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RVE_PLUGIN_URL', plugin_dir_url(__FILE__));

$autoload = RVE_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

\RusVideoEmbeds\Plugin::init();
