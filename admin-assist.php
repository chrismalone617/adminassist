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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    echo '<?xml version="1.0" ?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg height="24px" style="enable-background:new 0 0 512 512; vertical-align: middle; margin-right: 10px;" version="1.1" viewBox="0 0 512 512" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="_x31_08-analytics_x2C__google_analytics_x2C__google"><g><g><g><path d="M330.564,166.28H181.438c-8.232,0-14.912,6.699-14.912,14.956v299.06 c0,8.251,6.68,14.954,14.912,14.954h149.127c8.23,0,14.91-6.703,14.91-14.954v-299.06 C345.475,172.979,338.795,166.28,330.564,166.28L330.564,166.28z M330.564,166.28" style="fill:#FFC107;"/><path d="M181.438,315.813H32.313c-8.236,0-14.916,6.698-14.916,14.953v149.53 c0,8.251,6.68,14.954,14.916,14.954h149.125c8.234,0,14.914-6.703,14.914-14.954v-149.53 C196.352,322.511,189.672,315.813,181.438,315.813L181.438,315.813z M181.438,315.813" style="fill:#FFC107;"/><path d="M479.689,16.75H330.564c-8.236,0-14.916,6.698-14.916,14.958v448.588 c0,8.251,6.68,14.954,14.916,14.954h149.125c8.234,0,14.914-6.703,14.914-14.954V31.708 C494.604,23.448,487.924,16.75,479.689,16.75L479.689,16.75z M479.689,16.75" style="fill:#FFA000;"/></g></g></g></g><g id="Layer_1"/></svg>';
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
            <svg height="24px" style="enable-background:new 0 0 512 512; vertical-align: middle; margin-right: 10px;" version="1.1" viewBox="0 0 512 512" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="_x31_08-analytics_x2C__google_analytics_x2C__google"><g><g><g><path d="M330.564,166.28H181.438c-8.232,0-14.912,6.699-14.912,14.956v299.06 c0,8.251,6.68,14.954,14.912,14.954h149.127c8.23,0,14.91-6.703,14.91-14.954v-299.06 C345.475,172.979,338.795,166.28,330.564,166.28L330.564,166.28z M330.564,166.28" style="fill:#FFC107;"/><path d="M181.438,315.813H32.313c-8.236,0-14.916,6.698-14.916,14.953v149.53 c0,8.251,6.68,14.954,14.916,14.954h149.125c8.234,0,14.914-6.703,14.914-14.954v-149.53 C196.352,322.511,189.672,315.813,181.438,315.813L181.438,315.813z M181.438,315.813" style="fill:#FFC107;"/><path d="M479.689,16.75H330.564c-8.236,0-14.916,6.698-14.916,14.958v448.588 c0,8.251,6.68,14.954,14.916,14.954h149.125c8.234,0,14.914-6.703,14.914-14.954V31.708 C494.604,23.448,487.924,16.75,479.689,16.75L479.689,16.75z M479.689,16.75" style="fill:#FFA000;"/></g></g></g></g><g id="Layer_1"/></svg>
            Google Analytics Report
        </div>', // Widget title
        'adminassist_dashboard_widget_content' // Callback function to display the content
    );
}

add_action( 'wp_dashboard_setup', 'adminassist_add_dashboard_widget' );

function adminassist_dashboard_widget_content() {
    $looker_url = get_option( 'looker_url', '' );

    if ( empty( $looker_url ) ) {
        echo '<p><strong>You haven\'t set your Looker Studio Report URL. <a href="' . esc_url( admin_url( 'options-general.php#looker_url' ) ) . '" style="color: #DB4437; font-weight: bold;">Click here</a> to set it now.</strong></p>';
    } else {
        echo '<p>The Google Analytics report provides detailed insights into your website\'s performance, user behavior, and traffic sources. Utilize this data to enhance your online strategy and achieve your business goals.</p>';
        echo '<a href="' . esc_url( $looker_url ) . '" class="adminassist-view-report" target="_blank">View Analytics Report</a>';
    }
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
