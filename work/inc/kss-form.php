<?php
/** 
 * KSS Form
 * 
 * Class for interupting the sites form posts
 * 
 * @since 7.4
 * @author Kevin Pirnie <me@kpirnie.com>
 * @package Kevin's Spam Stop
 * 
*/

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

// make sure the class doesn't already exist
if( ! class_exists( 'KSS_Form' ) ) {

    /** 
     * Class KSS_Form
     * 
     * This class processes the form interuptions for the website
     * 
     * @since 7.4
     * @access public
     * @author Kevin Pirnie <me@kpirnie.com>
     * @package Kevin's Spam Stop
     * 
     * @property array $spam_domains Hold the spam domains resultset
     * @property array $spam_words Hold the spam words resultset
     * @property array $spam_ips Hold the spam IPs resultset
     * @property array $spam_blacklists Hold the spam blacklists resultset
     * 
    */
    class KSS_Form {

        // hold our internal properties
        private array $spam_domains;
        private array $spam_words;
        private array $spam_ips;
        private array $spam_blacklists;

        // fire it up
        public function __construct( ) {

            // fire up the data class
            $_data = new KSS_Data( );

            // pull the domains
            $this -> spam_domains = $_data -> grab_spam_domains( );

            // pull the spam words
            $this -> spam_words = $_data -> grab_spam_words( );

            // pull the IPs
            $this -> spam_ips = $_data -> grab_spam_ips( );

            // pull the blacklists
            $this -> spam_blacklists = array(
                'combined.mail.abusix.zone',
                'rbl.metunet.com',
                'rbl.mailspike.org',
                'dnsbl.sorbs.net',
                'zen.spamhaus.org',
                'dnsbl.justspam.org',
                'bl.spamcop.net',
                'bl.blocklist.de',
                'rbl.realtimeblacklist.com',
                'b.barracudacentral.org',
                'dnsbl.spfbl.net',
                'bl.nordspam.com',
                '0spam.fusionzero.com',
            );

            // clean up the data class
            unset( $_data );            

        }


        /** 
         * interrupt
         * 
         * Interupt the form posting, so we can check if it's spam
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        public function interrupt( ) : void {

            // check the comment posting
            $this -> check_comment_post( );

            // check WPForms


            // check Formidable Forms


            // check Gravity Forms


            // check Ninja Forms


            // check Contact Form 7


            // check Jetpack Forms


        }

        /** 
         * check_comment_post
         * 
         * Check the comment posting to see if it is spam, and mark it as such if it is
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @return void This method returns nothing
         * 
        */
        private function check_comment_post( ) : void {

            // check the comment posted
            add_action( 'comment_post', function( int $_id, int $_approved ) : void {

                // get the comment
                $_comment = get_comment( $_id );

                // get the comment author's email domain
                $_ca_email = $_comment -> comment_author_email;

                // get the comment authors IP address. if they figured a way around the default, see if we can get it from the headers
                $_ca_ip = ( $_comment -> comment_author_IP ) ?? KSS::get_user_ip( );

                // get the comment's content
                $_ca_content = $_comment -> comment_content;

                // get the comment author's referer
                $_ca_referer = $_comment -> comment_agent;

                // clean up the comment object
                unset( $_comment );

                // by default we'll mark this false
                $_is_spam = false;

                // check the email domain against the spam domains
                $_is_spam_email = $this -> is_spam( 2, $_ca_email );

                // check the content for spam words
                $_is_spam_content = $this -> is_spam( 1, $_ca_content );

                // check the IP address to see if it's considered a spammer
                $_is_spam_ip = $this -> is_spam( 3, $_ca_ip );

                // if any of these are true, mark the comment as spam
                if( $_is_spam_email || $_is_spam_content || $_is_spam_ip ) {

                    // mark this comment as spam
                    wp_spam_comment( $_id );

                    // log why it was caught
                    

                }

            }, 10, 2 );

        }

        /** 
         * is_spam
         * 
         * Run the actual check and determine if the value is spam or not
         * 
         * @since 7.4
         * @access public
         * @author Kevin Pirnie <me@kpirnie.com>
         * @package Kevin's Spam Stop
         * 
         * @param int $_type The type of value we're checking
         * @param string $_value The value to check
         * 
         * @return bool This method determines if the content is spam or not
         * 
        */
        private function is_spam( int $_type, string $_value ) : bool {

            // utilize a switch for this
            switch( $_type ) {

                case 1: // spam words

                    // loop over the data
                    foreach( $this -> spam_words as $_word ) {

                        // check the value against the word
                        $_check = KSS::string_contains_word( $_value, $_word -> the_data );

                        // if it's true
                        if( $_check ) {

                            // return because there is no need to check any further
                            return true;

                        }

                    }

                    break;

                case 2: // spam domains

                    // we're checking an email address here, so grab the domain from the value
                    $_value = substr( $_value, strpos( $_value, '@' ) + 1 );

                    // loop over the data
                    foreach( $this -> spam_domains as $_domain ) {

                        // check the value against the word
                        $_check = KSS::string_contains_word( $_value, $_domain -> the_data );

                        // if it's true
                        if( $_check ) {

                            // return because there is no need to check any further
                            return true;

                        }
                        
                    }

                    break;

                case 3: // spam ips

                    // loop over the data
                    foreach( $this -> spam_ips as $_ip ) {

                        // check the value against the word
                        $_check = KSS::string_contains_word( $_value, $_ip -> the_data );

                        // if it's true
                        if( $_check ) {

                            // return because there is no need to check any further
                            return true;

                        }

                    }

                    // reverse the IP we're checking
                    $_rip = implode( '.', array_reverse( explode( '.', $_value ) ) );

                    // loop the spam blacklists
                    foreach( $this -> spam_blacklists as $_bl ) {

                        // perform the lookup
                        $_res = checkdnsrr( $_rip . '.' . $_bl . '.', "A" );

                        // if there is a return, it means the IP was found in a blacklist
                        if( $_res ) {

                            // return because there is no need to check any further
                            return true;

                        }

                    }
                    
                    break;

            }

            // by default we'll return false
            return false;

        }

    }

}
