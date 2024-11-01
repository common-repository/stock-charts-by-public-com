<?php

namespace StockCharts_Public\frontend;

use StockCharts_Public\Assets;

// Exit if accessed directly
defined( 'WPINC' ) || die;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Frontend {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
        
        // Load assets from manifest.json
        $this->assets = new Assets();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
     *
     * NOTE: Remember to enqueue your styles only on templates where needed
     * 
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/css',
            $this->assets->get('styles/main.css'),
            [],
            STOCK_CHARTS_PUBLIC__PLUGIN_VERSION,
            'all'
        );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
     * 
	 * NOTE: Remember to enqueue your scripts only on templates where needed
     * 
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/js',
            $this->assets->get('scripts/main.js'),
            [],
            STOCK_CHARTS_PUBLIC__PLUGIN_VERSION,
            false
        );
	}


    /**
     * Replace <public> tags with regular HTML a href
     * 
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public function parse_public_links( $content ) {

        // Include referral ID in links
        $yay = get_option('scpublic_yay');
        $irc = get_option('scpublic_irc');

        $referral = ( $yay && $irc )
            ? sprintf('?yay=%s&adjust_impactradius_click_id=%s', $yay, $irc )
            : '';

        $search  = '/<public data-stock="(.+?)">(.*?)<\/public>/i';
        $replace = "<a href=\"https://public.com/stocks/$1$referral\" data-stock=\"$1\" target=\"_blank\" class=\"link-public\">$2</a>";


        $content = preg_replace( $search, $replace, $content );

        return $content;
    }
}
