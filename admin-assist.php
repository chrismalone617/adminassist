<?php

/**
 * Plugin Name:       AdminAssist
 * Description:       AdminAssist's plugin description
 * Requires at least: 6.3.0
 * Requires PHP:      7.4
 * Version:           0.1.0
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

// Extract the version number
$plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);

// Plugin Constants
define($plugin_prefix . '_DIR', plugin_basename(__DIR__));
define($plugin_prefix . '_BASE', plugin_basename(__FILE__));
define($plugin_prefix . '_PATH', plugin_dir_path(__FILE__));
define($plugin_prefix . '_VER', $plugin_data['Version']);
define($plugin_prefix . '_CACHE_KEY', 'adminassist-cache-key-for-plugin');
define($plugin_prefix . '_REMOTE_URL', 'https://plugins.withchris.dev/wp-content/uploads/downloads/7/info.json');

require constant($plugin_prefix . '_PATH') . 'inc/update.php';

new DPUpdateChecker(
	constant($plugin_prefix . '_BASE'),
	constant($plugin_prefix . '_VER'),
	constant($plugin_prefix . '_CACHE_KEY'),
	constant($plugin_prefix . '_REMOTE_URL'),
);

// Create the database table on plugin activation
function adminassist_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adminassist';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        looker_url varchar(255) NOT NULL,
        hide_widget varchar(3) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'adminassist_create_table' );

// Delete the database table on plugin uninstall
function adminassist_delete_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adminassist';

    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query( $sql );
}

register_uninstall_hook( __FILE__, 'adminassist_delete_table' );

// Add the settings section and fields
function adminassist_add_settings() {
    add_settings_section(
        'adminassist_settings_section',
        '',
        'adminassist_settings_section_callback',
        'general'
    );

    add_settings_field(
        'looker_url',
        'Looker Studio Report URL',
        'adminassist_looker_url_callback',
        'general',
        'adminassist_settings_section'
    );

    add_settings_field(
        'hide_widget',
        'Hide the dashboard widget?',
        'adminassist_hide_widget_callback',
        'general',
        'adminassist_settings_section'
    );

    register_setting( 'general', 'looker_url' );
    register_setting( 'general', 'hide_widget' );
}

add_action( 'admin_init', 'adminassist_add_settings' );

function adminassist_settings_section_callback() {
    echo '<hr>';
    echo '<div style="display: flex; align-items: center;">';
    echo '<img src="' . esc_url( plugins_url( 'images/ga_icon.png', __FILE__ ) ) . '" alt="Google Analytics Icon" style="height:24px; width:24px; vertical-align: middle; margin-right: 10px;">';
    echo '<h2>Google Analytics Report Details</h2>';
    echo '</div>';
}

function adminassist_looker_url_callback() {
    $looker_url = get_option( 'looker_url', '' );
    echo '<input type="url" id="looker_url" name="looker_url" value="' . esc_attr( $looker_url ) . '" class="regular-text ltr" />';
    echo '<p>Click <a href="https://lookerstudio.google.com/" target="_blank">here</a> to go to Looker Studio.</p>';
}

function adminassist_hide_widget_callback() {
    $hide_widget = get_option( 'hide_widget', 'no' );
    echo '<select id="hide_widget" name="hide_widget">
            <option value="no" ' . selected( $hide_widget, 'no', false ) . '>No</option>
            <option value="yes" ' . selected( $hide_widget, 'yes', false ) . '>Yes</option>
          </select>';
}

// Save the settings in the custom database table
function adminassist_save_settings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'adminassist';
    
    $looker_url = get_option( 'looker_url' );
    $hide_widget = get_option( 'hide_widget' );

    $wpdb->replace(
        $table_name,
        array(
            'id' => 1,
            'looker_url' => $looker_url,
            'hide_widget' => $hide_widget
        ),
        array(
            '%d',
            '%s',
            '%s'
        )
    );
}

add_action( 'update_option_looker_url', 'adminassist_save_settings' );
add_action( 'update_option_hide_widget', 'adminassist_save_settings' );

// Add the dashboard widget
function adminassist_add_dashboard_widget() {
    $hide_widget = get_option( 'hide_widget', 'no' );

    if ( $hide_widget === 'yes' ) {
        return; // Exit if the widget should be hidden
    }

    wp_add_dashboard_widget(
        'adminassist_dashboard_widget', // Widget slug
        '<div style="display: flex; align-items: center;">
            <img src="' . esc_url( plugins_url( 'images/ga_icon.png', __FILE__ ) ) . '" alt="Google Analytics Icon" style="height:24px; width:24px; vertical-align: middle; margin-right: 10px;">
            Google Analytics Report
        </div>', // Widget title
        'adminassist_dashboard_widget_content' // Callback function to display the content
    );
}

add_action( 'wp_dashboard_setup', 'adminassist_add_dashboard_widget' );

function adminassist_dashboard_widget_content() {
    $looker_url = get_option( 'looker_url', '' );

    echo '<div class="adminassist-widget-container">';
    if ( empty( $looker_url ) ) {
        echo '<p><strong>You haven\'t set your Looker Studio Report URL. <a href="' . esc_url( admin_url( 'options-general.php#looker_url' ) ) . '" style="color: #DB4437; font-weight: bold;">Click here</a> to set it now.</strong></p>';
    } else {
        echo '<p>The Google Analytics report provides detailed insights into your website\'s performance, user behavior, and traffic sources. Utilize this data to enhance your online strategy and achieve your business goals.</p>';
        echo '<a href="' . esc_url( $looker_url ) . '" class="adminassist-view-report" target="_blank">View Analytics Report</a>';
    }
    echo '</div>';
}

// Enqueue custom styles
function adminassist_enqueue_dashboard_styles() {
    wp_enqueue_style( 'adminassist_dashboard_styles', plugins_url( 'assets/style.css', __FILE__ ) );
}

add_action( 'admin_enqueue_scripts', 'adminassist_enqueue_dashboard_styles' );

// Force the widget to the top left position
function adminassist_force_dashboard_widget_position() {
    global $wp_meta_boxes;

    if (isset($wp_meta_boxes['dashboard']['normal']['core']['adminassist_dashboard_widget'])) {
        $widget = $wp_meta_boxes['dashboard']['normal']['core']['adminassist_dashboard_widget'];
        unset($wp_meta_boxes['dashboard']['normal']['core']['adminassist_dashboard_widget']);
        $wp_meta_boxes['dashboard']['normal']['high']['adminassist_dashboard_widget'] = $widget;
    }
}

add_action('wp_network_dashboard_setup', 'adminassist_force_dashboard_widget_position');
add_action('wp_user_dashboard_setup', 'adminassist_force_dashboard_widget_position');
add_action('wp_dashboard_setup', 'adminassist_force_dashboard_widget_position');
