<?php
/** 
 * Common Functionality
 * 
 * Control and process the plugins common functionality
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// hanlde the plugin activation
register_activation_hook( KSS_PATH . '/' . KSS_FILENAME, function( $_network ) : void {

    // check the PHP version, and deny if lower than 7.4
    if ( version_compare( PHP_VERSION, KSS_MIN_PHP, '<=' ) ) {

        // it is, so throw and error message and exit
        wp_die( __( '<h1>PHP To Low</h1><p>Due to the nature of this plugin, it cannot be run on lower versions of PHP.</p><p>Please contact your hosting provider to upgrade your site to at least version ' . KSS_MIN_PHP . '</p>', 'kss' ), 
            __( 'Cannot Activate: PHP To Low', 'kss' ),
            array(
                'back_link' => true,
            ) );

    }

    // check if we tried to network activate this plugin
    if( is_multisite( ) && $_network ) {

        // we did, so... throw an error message and exit
        wp_die( 
            __( '<h1>Cannot Network Activate</h1><p>Due to the nature of this plugin, it cannot be network activated.</p><p>Please go back, and activate inside your subsites.</p>', 'kss' ), 
            __( 'Cannot Network Activate', 'kss' ),
            array(
                'back_link' => true,
            ) 
        );
    }

    // include our creator class
    include KSS_PATH . '/work/creator.php';

    // fire off our activating action
    do_action( 'kss_activation' );

} );

// handle the plugin deactivation
register_deactivation_hook( KSS_PATH . '/' . KSS_FILENAME, function( ) : void {

} );

// check if it is indeed activated
if( in_array( KSS_DIRNAME . '/' . KSS_FILENAME, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // setup our autoloader
    spl_autoload_register( function( $_cls ) : void {

        // reformat the class name to match the file name for inclusion
        $_class = strtolower( str_ireplace( '_', '-', $_cls ) );

        // we're not, so let's pull the non-cli classes
        $_path = KSS_PATH . '/work/inc/' . $_class . '.php';

        // if the file exists
        if( is_readable( $_path ) ) {

            // include it once
            include $_path;
        }

    } );

    // pull in our primary class
    $_kss = new KSS( );

    // fire it up
    $_kss -> init( );

    // clean up
    unset( $_kss );

}
