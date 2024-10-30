<?php
/** 
 * KSS Admin
 * 
 * Primary class that controls the plugins settings and admin pages
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure the class doesn't already exist
if( ! class_exists( 'KSS_Admin' ) ) {

    /** 
     * Class KSS_Admin
     * 
     * This class holds the methods for pulling together
     * the plugins options and admin pages
     * 
     * @since 7.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package Kevin's Spam Stop
     * 
    */
    class KSS_Admin {

        /** 
         * create_settings
         * 
         * Creates the settings for the plugin
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        public function create_settings( ) : void {

            // register the settings this plugin is going to utilize and provide

        }

        /** 
         * setup_admin_pages
         * 
         * Sets up the admin pages for the plugin
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        public function setup_admin_pages( ) : void {



        }

    }

}
