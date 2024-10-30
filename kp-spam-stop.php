<?php

// We don't want to allow direct access to this
defined( 'ABSPATH' ) || die( 'No direct script access allowed' );

/*
Plugin Name:    Kev's Spam Stop
Plugin URI:     https://kevinpirnie.com
Description:    Plugin to attempt to stop spam form submissions.
Version:        0.1.18
Requires PHP:   7.4
Network:        false
Author:         Kevin C Pirnie
Text Domain:    kss
License:        GPLv3
License URI:    https://www.gnu.org/licenses/gpl-3.0.html
*/

// setup the full page to this plugin
define( 'KSS_PATH', dirname( __FILE__ ) );

// setup the directory name
define( 'KSS_DIRNAME', basename( dirname( __FILE__ ) ) );

// setup the primary plugin file name
define( 'KSS_FILENAME', basename( __FILE__ ) );

// setup the primary plugin URI
define( 'KSS_URI', plugin_dir_url( __FILE__ ) );

// setup the minimum allowable PHP version
define( 'KSS_MIN_PHP', '7.4' );

// Include our "work"
require KSS_PATH . '/work/common.php';
