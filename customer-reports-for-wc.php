<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Customer_Reports_For_Wc
 *
 * @wordpress-plugin
 * Plugin Name:       Customer Reports for WC
 * Description:       Dedicated customers repost page with customers sales information.
 * Version:           1.0.0
 * Author:            WpExpertPlugins
 * Author URI:        http://www.wpexpertplugins.com/contact-us/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       customer-reports-for-wc
 * Domain Path:       /languages
 * Tested up to:    6.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUSTOMER_REPORTS_FOR_WC_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-customer-reports-for-wc-activator.php
 */
function activate_customer_reports_for_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-customer-reports-for-wc-activator.php';
	Customer_Reports_For_Wc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-customer-reports-for-wc-deactivator.php
 */
function deactivate_customer_reports_for_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-customer-reports-for-wc-deactivator.php';
	Customer_Reports_For_Wc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_customer_reports_for_wc' );
register_deactivation_hook( __FILE__, 'deactivate_customer_reports_for_wc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-customer-reports-for-wc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_customer_reports_for_wc() {

	$plugin = new Customer_Reports_For_Wc();
	$plugin->run();
}
run_customer_reports_for_wc();
