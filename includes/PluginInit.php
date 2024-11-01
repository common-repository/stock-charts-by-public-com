<?php

namespace StockCharts_Public;

use StockCharts_Public\Loader;
use StockCharts_Public\I18n;
use StockCharts_Public\admin\Admin;
use StockCharts_Public\frontend\Frontend;

// Exit if accessed directly
defined( 'WPINC' ) || die;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 */
class PluginInit {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = STOCK_CHARTS_PUBLIC__PLUGIN_NAME;
		$this->version = STOCK_CHARTS_PUBLIC__PLUGIN_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ticket_Support_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        /**
         * Load TinyMCE
         *
         * @link https://codex.wordpress.org/TinyMCE_Custom_Buttons
         */
        $this->loader->add_filter( 'mce_buttons', $plugin_admin, 'register_tinymce_buttons' );
        $this->loader->add_filter( 'mce_external_plugins', $plugin_admin, 'register_tinymce_js' );

        /**
         * Add custom html element
         * 
         * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/tiny_mce_before_init
         */
        $this->loader->add_filter( 'tiny_mce_before_init', $plugin_admin, 'tiny_mce_before_init' );
        
        /**
         * Editor style
         *
         * @link https://developer.wordpress.org/reference/functions/add_editor_style/
         */
        $this->loader->add_action( 'admin_init', $plugin_admin, 'add_editor_style' );

        /**
         * TBD: Gutenberg custom format
         * /
        $this->loader->add_action( 'init', $plugin_admin, 'gutenberg_format_link_register' );
        $this->loader->add_action( 'enqueue_block_editor_assets', $plugin_admin, 'gutenberg_format_link_enqueue' ); */

        /**
         * AJAX hook to retrieve stocks when searching for a link
         */
        $this->loader->add_action( 'wp_ajax_public-link-ajax', $plugin_admin, 'fetch_stocks_by_id' );
        $this->loader->add_action( 'wp_ajax_nopriv_public-link-ajax', $plugin_admin, 'fetch_stocks_by_id' );

        /**
         * Options page
         */
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'init_settings_sections' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Frontend( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_filter( 'the_content', $plugin_public, 'parse_public_links' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ticket_Support_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
