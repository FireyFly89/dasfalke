<?php
/**
 * Plugin Name: Das Falke Bundle Manager Plugin
 * Description: Handles Bundles for Das Falke
 * Version: 0.0.1
 * Author: Krisztián Lakatos
 * Author URI: -
 *
 * @package eve-falke-plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}
define( 'DAS_FALKE_BUNDLE_MANAGER_DIRNAME', basename(__DIR__) );
define( 'DAS_FALKE_BUNDLE_MANAGER_PLUGIN_PATH', __DIR__ );
define( 'DAS_FALKE_BUNDLE_CONFIG', __DIR__ . "/json/bundles_config.json" );
define( 'DAS_FALKE_QUANTITY_DISCOUNTS', __DIR__ . "/json/quantity_discounts.json" );

require DAS_FALKE_BUNDLE_MANAGER_PLUGIN_PATH . '/classes/DFPBUtilities.php';
require DAS_FALKE_BUNDLE_MANAGER_PLUGIN_PATH . '/classes/DFPBTransactionManager.php';
require DAS_FALKE_BUNDLE_MANAGER_PLUGIN_PATH . '/classes/DFPBManager.php';
$bundleManager = new DFPBManager();
