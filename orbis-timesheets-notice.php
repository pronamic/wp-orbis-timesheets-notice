<?php
/*
Plugin Name:   Orbis Timesheets Notice
Version:       1.0.0
Plugin URI:    https://github.com/
Description:   Orbis Timesheets Notice plugin.
Author:        Karel-Jan Tolsma
Author URI:    https://www.kareljantolsma.nl/
Text Domain:   otn
Domain Path:   /languages/
License:       GPLv3
*/

/**
 * Constants
 */
define( 'OTN_PATH', plugin_dir_path( __FILE__ ) );
define( 'OTN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Includes
 */
require OTN_PATH . 'classes/class-scripts.php';
require OTN_PATH . 'classes/class-modal.php';

$otn_scripts = new OTN_Scripts;
$otn_modal   = new OTN_Modal;

/**
 * Load text domain
 */
add_action( 'plugins_loaded', 'otn_load_plugin' );

function otn_load_plugin() {
	load_plugin_textdomain( 'otn', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
