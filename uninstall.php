<?php
/** 
 * Plugin Uninstaller
 * 
 * Run the plugin uninstaller.
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// make sure we're actually supposed to be doing this
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	die( 'No direct script access allowed' );
}

// we're going to need our wpdb global
global $wpdb;

// include our database functionality
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

// setup the table names
$_tbls = array( 
	$wpdb -> prefix . 'kss_logs', 
	$wpdb -> prefix . 'kss_spam_data', 
);

// loop the tables
foreach( $_tbls as $_tbl ) {

	// delete them
	dbDelta( "DROP TABLE IF EXISTS $_tbl;" );

}

// now get rid of our options

