<?php
/**
 * Uninstall handler for RUS Video Embeds.
 *
 * Removes all plugin data from the database when the plugin
 * is deleted through the WordPress admin interface.
 *
 * @package RusVideoEmbeds
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('rve_settings');
