<?php
/**
 * Plugin Name: Das Falke Facebook Login Manager
 * Description: Handles Facebook login for das falke personal
 * Version: 0.0.1
 * Author: Krisztián Lakatos
 * Author URI: -
 *
 * @package eve-falke-plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

// LIVE DATA
define( 'FB_APP_ID', 663059860813363 );
define( 'FB_APP_SECRET', '8102d9a19138bdf96aaea019dcc45779' );

// DEV FOR DEBUGGING
//define( 'FB_APP_ID', 375077440013531 );
//define( 'FB_APP_SECRET', 'a57730b2b787b9a254fb5533fd537b05' );

define( 'FB_MANAGER_PLUGIN_PATH', __DIR__ );

require FB_MANAGER_PLUGIN_PATH . '/classes/FacebookLoginManager.php';
$loginManager = new FacebookLoginManager();
