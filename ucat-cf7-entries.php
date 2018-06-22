<?php
/*
 * Plugin Name: uCAT - Contact Form 7 Entries Manager
 * Version: 1.0
 * Plugin URI: http://ucat.biz/ucat-af7-entries/
 * Description: Save all submitted data from Contact Form 7 to database. All saved data displayed on admin as filterable table.
 * Author: Elena Zhyvohliad
 * Author URI: http://www.ucat.biz/
 * Requires at least: 4.4
 * Tested up to: 4.9.6
 *
 * Text Domain: u-cf7-entries
 * Domain Path: /lang/
 *
 * @author uCAT
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-u-cf7-entries.php' );

// Load plugin libraries
require_once( 'includes/lib/class-u-cf7-entries-admin-api.php' );
require_once( 'includes/lib/class-u-cf7-entries-post-type.php' );
require_once( 'includes/lib/class-u-cf7-entries-taxonomy.php' );

/**
 * Returns the main instance of U_CF7_Entries to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object U_CF7_Entries
 */
function U_CF7_Entries () {
	$instance = U_CF7_Entries::instance( __FILE__, '1.0.0' );

	return $instance;
}

U_CF7_Entries();
