<?php

/**
 * @link              https://public.com
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Stock Charts by Public.com
 * Description:       Embed beautiful, dynamic stock charts within a page or post with a simple line of shortcode. Plus up your content with vibrant stock charts for thousands of publicly-traded companies and ETFs. Easily view a stock chart by hovering your mouse over a ticker.
 * Version:           1.0.1
 * Author:            Public.com
 * Author URI:        https://public.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       stock-charts-public
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

// Min PHP version
define( 'STOCK_CHARTS_PUBLIC__MIN_PHP_VERSION', '7.2');

/**
 * Admin notice for user to upgrade PHP version
 */
function stock_charts_public_admin_notice(){
    
    $message = sprintf(
        __('Minimum PHP version required to run this plugin is %s. Your current version is %s', 'stock-charts-public'),
        STOCK_CHARTS_PUBLIC__MIN_PHP_VERSION,
        PHP_VERSION
    );

    echo sprintf(
        '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
        $message
    );
}

/**
 * Check for PHP version and include file if 
 */
if ( version_compare( PHP_VERSION, STOCK_CHARTS_PUBLIC__MIN_PHP_VERSION, '>=' ) )
{    
    // Plugin init
    include "run.php";
}
else
{   
    // Current PHP version is lower than required
    add_action('admin_notices', 'stock_charts_public_admin_notice');
}

