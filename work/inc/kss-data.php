<?php
/** 
 * KSS Data
 * 
 * This class is going to handle the gathering
 * and reading of our external data
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure the class doesn't already exist
if( ! class_exists( 'KSS_Data' ) ) {

    /** 
     * Class KSS_Data
     * 
     * This class holds the methods for managing and reading 
     * the external data that is used to populate our blockers
     * 
     * @since 7.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package Kevin's Spam Stop
     * 
    */
    class KSS_Data {

        /** 
         * refresh
         * 
         * This method is utilized for refreshing the populated data
         * it is utilized when the plugin is activated, on a weekly basis
         * and manually when the end user admin decides to run it
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        public function refresh( ) : void {

            // hold the wpdb global
            global $wpdb;

            // hold the spam data table name
            $_spam_data_tbl = $wpdb -> prefix . 'kss_spam_data';

            // hold the spam words populator
            $_spam_words_uri = KSS_PATH . '/data/blacklist.txt'; 

            // hold the spam domains populator
            $_spam_domains_uri = KSS_PATH . '/data/disposable_email_blocklist.conf'; 

            // hold the blacklisted ip populator
            $_spam_ips_uri = KSS_PATH . '/data/latest_blacklist.txt'; 

            // get the spam words from the local file
            $_spam_words = KSS::array_from_file_content( array( 'path' => $_spam_words_uri, 'max_memory_mb' => 4 ) ); // KSS::api_request( $_spam_words_uri, false );

            // get the spam domains from the local file
            $_spam_domains = KSS::array_from_file_content( array( 'path' => $_spam_domains_uri, 'max_memory_mb' => 4 ) ); // KSS::api_request( $_spam_domains_uri, false );

            // get the spam ips from the local file
            $_spam_ips = KSS::array_from_file_content( array( 'path' => $_spam_ips_uri, 'max_memory_mb' => 4 ) ); //KSS::api_request( $_spam_ips_uri, false );

            // make sure we have spam words returned
            if( ! empty( ( array ) $_spam_words ) ) {

                // convert the text returned to a line split array
                // $_arr = KSS::read_lines( $_spam_words -> scalar, ( 4 * 1024 * 1024 ) );

                // loop over this 
                foreach( $_spam_words as $_a ) {

                    // hold the data
                    $_data = array( 
                        'type' => 1, // spam words 
                        'the_data' => trim( preg_replace( '/\s+/', ' ', $_a ) ), 
                    );

                    // insert/update/replace the data
                    KSS::insert_the_data( $_spam_data_tbl, $_data );

                }

                // clean it up
                unset( $_spam_words );

            }

            // make sure we have spam domains returned
            if( ! empty( ( array ) $_spam_domains ) ) {

                // convert the text returned to a line split array
                // $_arr = KSS::read_lines( $_spam_domains -> scalar, ( 4 * 1024 * 1024 ) );

                // loop over this 
                foreach( $_spam_domains as $_a ) {

                    // hold the data
                    $_data = array( 
                        'type' => 2, // spam domains 
                        'the_data' => trim( preg_replace( '/\s+/', ' ', $_a ) ), 
                    );

                    // insert/update/replace the data
                    KSS::insert_the_data( $_spam_data_tbl, $_data );

                }

                // clean it up
                unset( $_spam_domains );

            }

            // make sure we have spam ips returned
            if( ! empty( ( array ) $_spam_ips ) ) {

                // convert the text returned to a line split array
                // $_arr = KSS::read_lines( $_spam_ips -> scalar, ( 4 * 1024 * 1024 ) );

                // loop over this 
                foreach( $_spam_ips as $_a ) {

                    // test IPv4
                    $_ipv4_regex = "/\b(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[1-9])\.)(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){2}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\b/m";
                    preg_match( $_ipv4_regex, $_a, $_match4 );

                    // test IPv6
                    $_ipv6_regex = "/\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*/";
                    preg_match( $_ipv6_regex, $_a, $_match6 );

                    // snag the IP
                    $_ip = ( $_match6[0] ) ?? ( $_match4[0] ) ?? null;

                    // make sure there is an IP
                    if( $_ip ) {

                        // hold the data
                        $_data = array( 
                            'type' => 3, // spam ips  
                            'the_data' => trim( preg_replace( '/\s+/', ' ', $_ip ) ), 
                        );

                    }

                    // insert/update/replace the data
                    KSS::insert_the_data( $_spam_data_tbl, $_data );

                }

                // clean it up
                unset( $_spam_ips );

            }

        }

        /** 
         * grab_spam_words
         * 
         * This method is utilized for grabbing our stored spam words
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return array This method returns the spam word type resultset
         * 
        */
        public function grab_spam_words( ) : array {

            // hold our cache global
            global $wp_object_cache;

            // see if we're cached first, and if so return that instead of hitting the database
            $_ret = $wp_object_cache -> get( 'kss_spam_words', 'kss_spam_words', true );

            // if we do NOT have anything here
            if( ! $_ret ) {

                // check the database
                $_ret = $this -> get_our_data( 1 );

                // set the results to cache for 1 hour
                $wp_object_cache -> set( 'kss_spam_words', $_ret, 'kss_spam_words', HOUR_IN_SECONDS );

            }

            // return
            return $_ret;            

        }

        /** 
         * grab_spam_domains
         * 
         * This method is utilized for grabbing our stored spam domains
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return array This method returns the spam domain type resultset
         * 
        */
        public function grab_spam_domains( ) : array {

            // hold our cache global
            global $wp_object_cache;

            // see if we're cached first, and if so return that instead of hitting the database
            $_ret = $wp_object_cache -> get( 'kss_spam_domains', 'kss_spam_domains', true );

            // if we do NOT have anything here
            if( ! $_ret ) {

                // check the database
                $_ret = $this -> get_our_data( 2 );

                // set the results to cache for 1 hour
                $wp_object_cache -> set( 'kss_spam_domains', $_ret, 'kss_spam_domains', HOUR_IN_SECONDS );

            }

            // return
            return $_ret;            

        }

        /** 
         * grab_spam_ips
         * 
         * This method is utilized for grabbing our stored spam ips
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return array This method returns the spam ip type resultset
         * 
        */
        public function grab_spam_ips( ) : array {

            // hold our cache global
            global $wp_object_cache;

            // see if we're cached first, and if so return that instead of hitting the database
            $_ret = $wp_object_cache -> get( 'kss_spam_ips', 'kss_spam_ips', true );

            // if we do NOT have anything here
            if( ! $_ret ) {

                // check the database
                $_ret = $this -> get_our_data( 3 );

                // set the results to cache for 1 hour
                $wp_object_cache -> set( 'kss_spam_ips', $_ret, 'kss_spam_ips', HOUR_IN_SECONDS );

            }

            // return
            return $_ret;            

        }

        /** 
         * log
         * 
         * This method is utilized for logging some actions taken with the spam blocker
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        public function log( array $_data ) : void {



        }

        /** 
         * get_our_data
         * 
         * This method is utilized for refreshing the populated data
         * it is utilized when the plugin is activated, on a weekly basis
         * and manually when the end user admin decides to run it
         * 
         * @since 7.4
         * @access private
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @param int $_type The type of data we need to return
         * @param string $_data The data to look for, defaults to an empty string
         * 
         * @return array This method returns the resultset from the queried object
         * 
        */
        private function get_our_data( int $_type, string $_data = '' ) : array {

            // hold our wpdb global
            global $wpdb;

            // setup the table to query
            $_tbl = $wpdb -> prefix . 'kss_spam_data';

            // prepare the query to run
            $_sql = "SELECT SQL_CACHE `id`, `type`, `added_on`, `updated_on`, `the_data` FROM $_tbl WHERE `type` = %d";

            // if there is data to check against
            if( ! empty( $_data ) ) {

                // append the data to look for
                $_sql .= " AND `the_data` LIKE %s";

                // setup the prepared SQL
                $_prepped_sql = $wpdb -> prepare( $_sql, intval( $_type ), '%' . $wpdb -> esc_like( $_data ) . '%' );

            // otherwise we only need it by type
            } else {

                // setup the prepared SQL
                $_prepped_sql = $wpdb -> prepare( $_sql, intval( $_type ) );

            }

            // return the queried retults object
            return $wpdb -> get_results( $_prepped_sql );

        }

    }

}
