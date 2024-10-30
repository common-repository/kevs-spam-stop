<?php
/** 
 * KSC
 * 
 * Primary class that controls the plugin and it's methods
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure the class doesn't already exist
if( ! class_exists( 'KSS' ) ) {

    /** 
     * Class KSS
     * 
     * This class holds some common methods, properties, and is mostly statically available
     * 
     * @since 7.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package Kevin's Spam Stop
     * 
    */
    class KSS {

        /** 
         * init
         * 
         * Initialize the plugin and it's methods
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        public function init( ) : void {

            // fire up the admin
            add_action( 'admin_init', function( ) : void {

                // fire up the admin class
                $_admin = new KSS_Admin( );

                // create the options
                $_admin -> create_settings( );

                // build out the admin pages
                $_admin -> setup_admin_pages( );

                // clean up
                unset( $_admin );


            }, PHP_INT_MAX );

            // fire up on theme setup
            add_action( 'after_setup_theme', function( ) : void {

                // create an action to create a scheduled job to update our "BAD" data
                add_action( 'kss_data_refresher', function( ) : void {

                    // fire up the data class
                    $_data = new KSS_Data( );

                    // run the refresh
                    $_data -> refresh( );

                    // clean up
                    unset( $_data );

                } );

                // make sure we're only scheduling this once
                if( ! wp_next_scheduled( 'kss_data_refresher' ) ) {

                    // schedule the event
                    wp_schedule_event( time( ), 'weekly', 'kss_data_refresher' );  
                
                }

            } );

            // fire up on wordpress fully loaded
            add_action( 'init', function( ) : void {
                
                add_filter('duplicate_comment_id', '__return_false');

                // fire up the form processor/interupter
                $_form = new KSS_Form( );

                // interupt the form posting processes to check for spam
                $_form -> interrupt( );

                // clean up
                unset( $_form );

            } );

        }

        /** 
         * get_user_ip
         * 
         * Gets the current users public IP address
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return string Returns a string containing the users public IP address
         * 
        */
        public static function get_user_ip( ) : string {

            // check if we've got a client ip header, and if it's valid
            if( rest_is_ip_address( $_SERVER['HTTP_CLIENT_IP'] ) ) {

                // return it
                return sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );

            // maybe they're proxying?
            } elseif( rest_is_ip_address( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

                // return it
                return sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );

            // if all else fails, this should exist!
            } elseif( rest_is_ip_address( $_SERVER['REMOTE_ADDR'] ) ) {

                // return it
                return sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

            }

            // default return
            return '';

        }

        /** 
         * get_user_agent
         * 
         * Gets the current users browsers User Agent
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return string Returns a string containing the users browsers User Agent
         * 
        */
        public static function get_user_agent( ) : string {

            // possible browser info
            $_browser = get_browser( );

            // let's see if the user agent header exists
            if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {

                // return the user agent
                return sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

            // let's see if we have browser data
            } elseif( $_browser ) {

                // return the browser name pattern
                return sanitize_text_field( wp_unslash( $_browser -> browser_name_pattern ) );

            }

            // default return
            return '';
                
        }
    
        /** 
         * get_user_referer
         * 
         * Gets the current users referer
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return string Returns a string containing the users referer
         * 
        */
        public static function get_user_referer( ) : string {

            // return the referer if it exists
            return isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( $_SERVER['HTTP_REFERER'], array( 'http', 'https' ) ) : '';

        }

        /** 
         * string_contains_word
         * 
         * PHP independant way to see if one string contains another
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @param string $_string The string to search
         * @param string $_word The string we're searching for
         * 
         * @return string Returns a string containing the users referer
         * 
        */
        public static function string_contains_word( string $_string, string $_word ) : bool {

            // let's see if we have str_contains available to us
            if( function_exists( 'str_contains' ) ) {

                // we do, so use it
                return str_contains( $_string, $_word );

            // mmm... nope
            } else {

                // guess we gotta go old school, but we want to match word for word, along with non-english characters
                return ( preg_match( '/(?<=[\s,.:;"\']|^)' . $_word . '(?=[\s,.:;"\']|$)/', $_string ) );

            }

            // by default
            return false;

        }

        /** 
         * api_request
         * 
         * This method is utilized for making API requests
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @param string $url What is it we need to request?
         * @param bool $should_cache Should the response be cached: Default - true
         * @param int $cache_length How long should the response be cached for: Default - 30 Minutes
         * 
         * @return object The requests response object
         * 
        */
        public static function api_request( string $url, bool $should_cache = true, int $cache_length = ( MINUTE_IN_SECONDS * 30 ) ) : object {

            // hold a return object
            $_ret = new stdClass( );

            // make sure the URL passed is clean
            $_url = esc_url_raw( $url, array( 'http', 'https' ) );

            // setup the cache key
            $_cache_key = 'kss_apir_' . $_url;

            // check if we should be caching
            if( $should_cache ) {

                // try to get a cached object
                $_ret = get_transient( $_cache_key );

            }

            // check if our returnable object is not empty
            if( $_ret && ! empty( ( array ) $_ret ) ) {

                // it is not empty, so just return it
                return $_ret;

            } else {

                // setup some headers to pass along with the request
                $_headers = array(
                    'timeout'     => 10,
                    'redirection' => 1,
                    'user-agent' => 'Kevin Pirnie ( me@kpirnie.com )' // I use this to make requests from NASA's API
                );

                // hold our response body's array
                $_body = array( );

                // perform the request
                $_req = wp_safe_remote_get( $_url, $_headers );

                // see if there's an error thrown
                if ( is_wp_error( $_req ) ) {

                    // return a null object
                    return new stdClass( );
                }

                // now... get the response body
                $_resp = wp_remote_retrieve_body( $_req );

                // make sure we actually have a response body...
                if( $_resp ) {
                
                    // try to decode the response
                    $_body = json_decode( $_resp, true );
                    
                    // let's check for an error in the decoding
                    if( json_last_error( ) !== JSON_ERROR_NONE ) {
                    
                        // there was an error, so the response is plain text
                        $_body = $_resp;
                    
                    }
                     
                }

                // build our object
                $_ret = ( object ) $_body;

                // if we need to cache it
                if( $should_cache ) {

                    // cache it for a half hour
                    set_transient( $_cache_key, $_ret, $cache_length );

                }

                // now return it
                return $_ret;

            }

        }

        /** 
         * insert_the_data
         * 
         * This method is utilized for inserting the spam checking data into our 
         * custom tables
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @param string $_tbl The table to insert the data
         * @param array $_data The data we are checking and adding
         * 
         * @return void This method returns nothing
         * 
        */
        public static function insert_the_data( string $_tbl, array $_data ) : void {

            // need the wpdb global
            global $wpdb;

            // see if the record already exists
            $_exists = $wpdb -> get_var( $wpdb -> prepare( "SELECT COUNT( `id` ) FROM `$_tbl` WHERE `type`=%d AND `the_data`=%s", $_data ) );

            // if the return is 0 we're good to insert
            if( $_exists == 0 ) {

                // insert the data
                $wpdb -> insert( $_tbl, $_data );
                
            }

        }

        /** 
         * array_from_file_content
         * 
         * This method is utilized for converting lines in a srting or a text file to an array
         * 
         * @since 7.4
         * @access public
         * @static
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @param array $_params The array containing the parameters for this method
         * 
         * @return array An array where each item represents the read line
         * 
        */
        public static function array_from_file_content( array $_params = array( ) ) : array {

            // make sure we actually have parameters in the argument
            if( ! $_params ) {

                // we don't so return an empty array
                return array( );

            }

            // setup and process the parameters
            $_path = sanitize_url( isset( $_params['path'] ) ? $_params['path'] : '' );
            $_content = sanitize_textarea_field( isset( $_params['content'] ) ? $_params['content'] : '' );
            $_max_memory = intval( ( isset( $_params['max_memory_mb'] ) ? $_params['max_memory_mb'] : 2 ) * 1024 * 1024 );

            // check if we have a file path and that the file exists
            if( ( ! empty( $_path ) ) && ( file_exists( $_path ) ) ) {

                // we're good here, so populate the string content we are going to utilize
                $_content = file_get_contents( $_path );

            // otherwise, if we do not have content in the string passed
            } elseif( empty( $_content ) ) {

                // return an empty array
                return array( );

            }

            // hold a returnable array
            $_ret = array( );

            // fire up a file descriptor object in the temporary I/O.  This will write to a temporary file if it exceeds the max memory
            $_fo = new \SplFileObject( "php://temp/maxmemory:$_max_memory", 'r+' );

            // write the content of our passed object
            $_fo -> fwrite( $_content );

            // rewind to the beginning of the stream
            $_fo -> rewind( );
            
            // while we have a valid line
            while ( $_fo -> valid( ) ) {

                // hold it in the returnable array
                $_ret[] = $_fo -> current( );

                // move to the next line
                $_fo -> next( );

            }

            // clean up the file object
            unset( $_fo );

            // return it
            return $_ret;

        }


    }

}
