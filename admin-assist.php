
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
         'Google Analytics Report Details',
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
 }
 
 function adminassist_looker_url_callback() {
     $looker_url = get_option( 'looker_url', '' );
     echo '<input type="url" id="looker_url" name="looker_url" value="' . esc_attr( $looker_url ) . '" class="regular-text ltr" />';
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
 ?>
 