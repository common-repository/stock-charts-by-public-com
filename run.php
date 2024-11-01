<?php

// Autoload all classes
require "includes/Autoload.php";

use StockCharts_Public\Activator;
use StockCharts_Public\Deactivator;
use StockCharts_Public\PluginInit;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;


/**
 * Base plugin Path and URI
 */
define( 'STOCK_CHARTS_PUBLIC__PLUGIN_URI', plugin_dir_url( __FILE__ ) );
define( 'STOCK_CHARTS_PUBLIC__PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'STOCK_CHARTS_PUBLIC__PLUGIN_NAME', 'Stock Charts by Public.com');
define( 'STOCK_CHARTS_PUBLIC__PLUGIN_VERSION', '1.0.1');
define( 'STOCK_CHARTS_PUBLIC__PLUGIN_TEXT_DOMAIN', 'stock-charts-public');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/Activator.php
 */
register_activation_hook( __FILE__, ['StockCharts_Public\Activator', 'activate']);


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/Deactivator.php
 */
register_deactivation_hook( __FILE__, ['StockCharts_Public\Deactivator', 'deactivate']);


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
$plugin = new PluginInit();
$plugin->run();
