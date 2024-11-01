<?php

namespace StockCharts_Public\admin;

use StockCharts_Public\Assets;

// Exit if accessed directly
defined( 'WPINC' ) || die;


/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Admin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/wp/css',
            $this->assets->get('styles/admin.css'),
            [],
            STOCK_CHARTS_PUBLIC__PLUGIN_VERSION,
            'all'
        );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/wp/js',
            $this->assets->get('scripts/admin.js'),
            [],
            STOCK_CHARTS_PUBLIC__PLUGIN_VERSION,
            false
        );

        wp_localize_script(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/wp/js',
            'pblc',
            [
                'plugin_uri' => STOCK_CHARTS_PUBLIC__PLUGIN_URI,
                'plugin_path' => STOCK_CHARTS_PUBLIC__PLUGIN_PATH,
            ]
        );
	}


    public function add_editor_style() {
        add_editor_style( $this->assets->get('styles/tinymce-style.css') );
    }


    /**
     * Register TinyMCE Plugin
     *
     * @link https://codex.wordpress.org/TinyMCE_Custom_Buttons
     */
    public function register_tinymce_buttons( $buttons ) {

        // Add custom button
        $buttons[] = 'public_link';
        $buttons[] = 'public_chart';

        return $buttons;
    }

    /**
     * Enqueue Js required for TinyMCE
     *
     * @link https://codex.wordpress.org/TinyMCE_Custom_Buttons
     * 
     * @param  [type] $plugin_array [description]
     * @return [type]               [description]
     */
    public function register_tinymce_js( $plugin_array ) {
        $plugin_array['public_link'] = $this->assets->get('scripts/tinymce-public.js');
        // $plugin_array['public_chart'] = $this->assets->get('scripts/public-link.js');

        return $plugin_array;
    }


    /**
     * Enable custom tag <public> to be used in TinyMCE editor
     * Otherwise it will be stripped
     * 
     * @param  array $settings Array of WordPress settings for TinyMCE editor 
     * @return array
     */
    public function tiny_mce_before_init( $settings ) {

        // Get existing custom elements
        $elements = isset($settings['extended_valid_elements'])
            ? explode(',', $settings['extended_valid_elements'] )
            : [];

        // Custom tag <public>
        $elements[] = 'public';

        // Update
        $settings['extended_valid_elements'] = implode(',', $elements);

        return $settings;
    }


    public function gutenberg_format_link_register() {

        wp_enqueue_script(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/wp/gutenberg-public/js',
            $this->assets->get('scripts/gutenberg-public.js'),
            ['wp-rich-text', 'wp-element', 'wp-editor'],
            STOCK_CHARTS_PUBLIC__PLUGIN_VERSION,
            false
        );
    }

    public function gutenberg_format_link_enqueue() {
        wp_enqueue_script(
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME . '/wp/gutenberg-public/js'
        );
    }


    /**
     * Fetch Resources from public.com
     *
     * @var $search string stock name to search for
     */
    public function fetch_stocks_by_id() {
            
        // Search Term
        $search = isset($_POST['stock'])
            ? sanitize_text_field( $_POST['stock'] )
            : false;

        // Stocks array
        $stocks = $this->fetch_stocks_from_api( $search );


        // WP_Error getting stocks
        if ( is_wp_error( $stocks ) )
        {           
            // Send error back to user
            wp_send_json_error( 
                $stocks->get_error_message(),
                $stocks->get_error_code()
            );

            die();
        }

        // 
        wp_send_json( $stocks );

        die();
    }


    /**
     * Fetch Stocks from public API or retrieve from cache
     */
    private function fetch_stocks_from_api( $search ) {

        $transient = 'public_instruments_cache';
        $api_url   = "https://public-prod-154310543964.s3.amazonaws.com/static/instruments/instruments.json";

        // Cache search results as well
        /* if ( 'development' == WP_ENV )
        {
            $api_url = sprintf('%sremote/instruments.json', STOCK_CHARTS_PUBLIC__PLUGIN_URI);
        } */

        // Fetch from transient or API
        if ( false === ( $response = get_transient( $transient ) ) ) {
            
            // Fetch static resource from API
            $response = wp_remote_get( $api_url );

            // Save stocks to transient for next time
            set_transient( $transient, $response, 48 * HOUR_IN_SECONDS );
        }

        /**
         * Delete transient if error so we can re-fetch next time fresh data
         * return WP_Error object from HTTP object
         */
        if ( is_wp_error( $response ) )
        {
            delete_transient( $transient );

            return $response;
        }

        // Get data returned by server
        $body   = wp_remote_retrieve_body( $response );
        $stocks = $this->filter_stocks( $body, $search );
        
        return $stocks;
    }



    /**
     * Filter all stocks for a specific search term
     * 
     * @param  string $search search term
     * @return array          formatted array of all stocks 
     */
    private function filter_stocks( $stocks_json = false, $search = '' ) {

        // Decode API response
        $stocks = json_decode( $stocks_json );

        // Search term
        $search = strtolower( sanitize_title($search) );

        // Look for stocks in array
        $results = array_filter($stocks, function ($obj) use ($search) {
            return stripos($obj->symbol, $search) !== false || stripos($obj->name, $search) !== false;
        });

        // Return
        return $results;
    }




    /**
     * OPTIONS PAGE
     */
    public function add_options_page() {

        // Create menu item
        add_options_page(
            __( STOCK_CHARTS_PUBLIC__PLUGIN_NAME, 'stock-charts-public' ),
            __( STOCK_CHARTS_PUBLIC__PLUGIN_NAME, 'stock-charts-public' ),
            'manage_options',
            'options-stock-charts-public',
            array(
                $this,
                'render_settings_page'
            )
        );
    }

    // Markup for settings page
    public function render_settings_page() {
        echo sprintf(
            '<div class="wrap"><h1>%s</h1></div>',
            STOCK_CHARTS_PUBLIC__PLUGIN_NAME 
            ); ?>

            <form method="POST" action="options.php">
                <?php
                settings_fields( 'options-stock-charts-public' );
                do_settings_sections( 'options-stock-charts-public' );
                submit_button();
                ?>
            </form>
        <?php 
    }

    // Generate sections and fields
    public function init_settings_sections() {

        // Register settings section
        add_settings_section(
            'public-settings-affiliate',
            'Affiliates',
            array( $this, 'render_settings_sections' ),
            'options-stock-charts-public'
        );

        // Yay link field
        add_settings_field(
            'scpublic-yay',
            'Yay ID',
            array( $this, 'render_form_field' ),
            'options-stock-charts-public',
            'public-settings-affiliate',
            array(
                'name'  => 'scpublic_yay',
                'class' => 'regular-text',
            )
        );

        // Impact Radius link field
        add_settings_field(
            'scpublic-irc',
            'Impactradius Click ID',
            array( $this, 'render_form_field' ),
            'options-stock-charts-public',
            'public-settings-affiliate',
            array(
                'name'  => 'scpublic_irc',
                'class' => 'regular-text',
            )
        );

        register_setting( 'options-stock-charts-public', 'scpublic_yay' );
        register_setting( 'options-stock-charts-public', 'scpublic_irc' );
    }

    // Section HTML
    public function render_settings_sections() {
        echo sprintf(
            '<p>%s</p>',
            __('Add your unique tracking info in the fields below.', 'stock-charts-public')
            );
    }

    // Form field markup
    public function render_form_field( $args ) {

        echo sprintf(
            '<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
            $args['name'],
            $args['name'],
            sanitize_text_field( get_option( $args['name'] ) )
        );
    }
}
