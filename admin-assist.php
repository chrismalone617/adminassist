
<?php

/**
 * Plugin Name:       AdminAssist
 * Description:       This plugin does all the admin things.
 * Requires at least: 6.3.0
 * Requires PHP:      7.4
 * Version:           0.0.1
 * Author:            admin
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       adminassist
 * Website:           
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$plugin_prefix = 'ADMINASSIST';

define($plugin_prefix . '_DIR', plugin_basename(__DIR__));
define($plugin_prefix . '_BASE', plugin_basename(__FILE__));
define($plugin_prefix . '_PATH', plugin_dir_path(__FILE__));
define($plugin_prefix . '_VER', '0.0.1');
define($plugin_prefix . '_CACHE_KEY', 'adminassist-cache-key-for-plugin');
define($plugin_prefix . '_REMOTE_URL', 'https://plugins.withchris.dev/wp-content/uploads/downloads/7/info.json');

require constant($plugin_prefix . '_PATH') . 'inc/update.php';

new DPUpdateChecker(
	constant($plugin_prefix . '_DIR'),
	constant($plugin_prefix . '_VER'),
	constant($plugin_prefix . '_CACHE_KEY'),
	constant($plugin_prefix . '_REMOTE_URL'),
	constant($plugin_prefix . '_BASE')
);

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Function to create the database table upon plugin activation
function cwpai_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adminassist';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        looker_url varchar(255) NOT NULL,
        hide_widget tinyint(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cwpai_create_table');

// Function to delete the database table upon plugin deletion
function cwpai_delete_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adminassist';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_uninstall_hook(__FILE__, 'cwpai_delete_table');

// Add settings section and fields
function cwpai_admin_settings() {
    add_settings_section(
        'cwpai_settings_section',
        '<hr>Google Analytics Report Details',
        '__return_null',
        'general'
    );

    add_settings_field(
        'cwpai_looker_url',
        'Looker Studio Report URL',
        'cwpai_looker_url_callback',
        'general',
        'cwpai_settings_section'
    );

    add_settings_field(
        'cwpai_hide_widget',
        'Hide the dashboard widget?',
        'cwpai_hide_widget_callback',
        'general',
        'cwpai_settings_section'
    );

    register_setting('general', 'cwpai_looker_url');
    register_setting('general', 'cwpai_hide_widget');
}
add_action('admin_init', 'cwpai_admin_settings');

// Callback to display Looker Studio Report URL field
function cwpai_looker_url_callback() {
    $looker_url = get_option('cwpai_looker_url');
    echo '<input type="url" id="cwpai_looker_url" name="cwpai_looker_url" value="' . esc_attr($looker_url) . '" class="regular-text" />';
}

// Callback to display Hide the dashboard widget field
function cwpai_hide_widget_callback() {
    $hide_widget = get_option('cwpai_hide_widget');
    echo '<select id="cwpai_hide_widget" name="cwpai_hide_widget">
        <option value="0"' . selected($hide_widget, '0', false) . '>No</option>
        <option value="1"' . selected($hide_widget, '1', false) . '>Yes</option>
    </select>';
}

// Save settings to custom database table
function cwpai_save_settings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adminassist';
    $looker_url = get_option('cwpai_looker_url');
    $hide_widget = get_option('cwpai_hide_widget');

    $wpdb->replace(
        $table_name,
        array(
            'looker_url' => $looker_url,
            'hide_widget' => $hide_widget
        )
    );
}
add_action('update_option_cwpai_looker_url', 'cwpai_save_settings');
add_action('update_option_cwpai_hide_widget', 'cwpai_save_settings');
