<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://socialsquare.dk
 * @since             1.0.0
 * @package           Dac_Content_Hub
 *
 * @wordpress-plugin
 * Plugin Name:       DAC Content Hub
 * Plugin URI:        http://www.dac.dk/content-hub
 * Description:       Connecting WordPress with the DAC content hub
 * Version:           1.0.0
 * Author:            Kræn Hansen
 * Author URI:        http://socialsquare.dk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dac-content-hub
 * Domain Path:       /languages
 */

// Load the composer autoloader - if it exists
$local_autoloader_path = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
if(file_exists($local_autoloader_path)) {
  require $local_autoloader_path;
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dac-content-hub-activator.php
 */
function activate_dac_content_hub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dac-content-hub-activator.php';
	Dac_Content_Hub_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dac-content-hub-deactivator.php
 */
function deactivate_dac_content_hub() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dac-content-hub-deactivator.php';
	Dac_Content_Hub_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_dac_content_hub' );
register_deactivation_hook( __FILE__, 'deactivate_dac_content_hub' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-dac-content-hub.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dac_content_hub() {

	$plugin = new Dac_Content_Hub();
	$plugin->run();

}
run_dac_content_hub();
