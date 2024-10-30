<?php
/** 
 * Creator
 * 
 * Controls the creation of our plugins external tables
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// hook into the activation process of this plugin
add_action( 'kss_activation', function( ) : void {

    // we'll need our WP database global
    global $wpdb;

    // our custom tables
    $_tbls = array( 
        $wpdb -> prefix . 'kss_logs', 
        $wpdb -> prefix . 'kss_spam_data', 
    );

    // hold our database colation
    $_collate = $wpdb -> get_charset_collate( );

    // loop the tables and see if they need to be created
    foreach( $_tbls as $_tbl ) {

        // setup the test
        $_test = $wpdb -> get_var( $wpdb -> prepare( 'SHOW TABLES LIKE %s', $_tbl ) );

        // see if it actually exists
        if( $_test !== $_tbl ) {

            // it does not, so we need to create it.

            // different tables need different structures
            switch( $_tbl ) {
                case $wpdb -> prefix . 'kss_logs': // the logs table
                    
                    // generate the SQL for this
                    $_sql = "CREATE TABLE `$_tbl` (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `logged_at` datetime NOT NULL DEFAULT current_timestamp(),
                        `ip` varchar(64) NOT NULL,
                        `action` varchar(64) NOT NULL,
                        `details` longtext NOT NULL,
                        PRIMARY KEY (id)
                      ) $_collate;";

                    break;
                
                // our populatable spam data
                case $wpdb -> prefix . 'kss_spam_data':

                    // generate the sql for these
                    $_sql = "CREATE TABLE `$_tbl` (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `type` tinyint(1) NOT NULL DEFAULT 1,
                        `added_on` datetime NOT NULL DEFAULT current_timestamp(),
                        `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
                        `the_data` varchar(1024) NOT NULL,
                        PRIMARY KEY (id),
                        KEY the_type (`type`),
                        KEY the_data (`the_data`)
                      ) $_collate;";

                    break;

            }

            // now we can actually run the query created above
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            // execute our database modifications
	        dbDelta( $_sql );

        }

    }

    // include both common class and data class
    include KSS_PATH . '/work/inc/kss.php';
    include KSS_PATH . '/work/inc/kss-data.php';

    // fire up the data class
    $_data = new KSS_Data( );

    // run the refresh
    $_data -> refresh( );

    // clean up
    unset( $_data );

}, PHP_INT_MAX );
