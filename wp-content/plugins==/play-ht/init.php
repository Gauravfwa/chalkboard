<?php

namespace Play_HT;

/**
 * Plugin Name: Play.ht
 * Description: Play.ht for WordPress is an essential tool for converting your blog posts, pages and e-learning material to audio to increase content accessibility, user engagement and time on page metrics.
 * Version: 3.6.4
 * Author: Play.ht
 * Author URI: https://play.ht/wordpress/?utm_source=wordpress&utm_medium=plugin&utm_campaign=plugin-page
 * Text Domain: play-ht
 * Domain Path: /language
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Plugin main component
 *
 * @package Play_HT
 */
class Plugin extends Singular {

	/**
	 * Backend
	 *
	 * @var Backend
	 */
	var $backend;

	/**
	 * Backend
	 *
	 * @var Frontend
	 */
	var $frontend;

	/**
	 * Backend
	 *
	 * @var Ajax_Handler
	 */
	var $ajax;

	/**
	 * Request
	 *
	 * @var Play_Requests
	 */
	var $play_requests;

	/**
	 * Initialization
	 *
	 * @return void
	 */
	protected function init() {
		// load language files
		add_action( 'plugins_loaded', [ &$this, 'load_language' ] );

		// modules
		$this->ajax   = Ajax_Handler::get_instance();
		$this->assets = Assets_Handler::get_instance();

		// Backend
		$this->backend = Backend::get_instance();
		add_action( 'admin_init', [ $this, 'load_admin' ] );

		$this->frontend      = Frontend::get_instance();
		$this->play_requests = Requests::get_instance();
		$this->amp           = AMP::get_instance();

		// Integration and compatibility
		$this->elementor = Elementor::get_instance();

		// plugin loaded hook
		do_action_ref_array( 'wppp_loaded', [ &$this ] );
	}

	public function load_admin() {
		$this->admin_actions = Post_Type_Admin_Actions::get_instance();
		$this->metaboxes     = Metaboxes::get_instance();
	}

	/**
	 * Load view template
	 *
	 * @param string $view_name
	 * @param array  $args ( optional )
	 *
	 * @return void
	 */
	public function load_view( $view_name, $args = null ) {
		// build view file path
		$__view_name     = $view_name;
		$__template_path = WPPP_DIR . 'views/' . $__view_name . '.php';
		if ( ! file_exists( $__template_path ) ) {
			// file not found!
			wp_die( sprintf( __( 'Template <code>%1$s</code> File not found, calculated path: <code>%2$s</code>', 'play-ht' ), $__view_name, $__template_path ) );
		}

		// clear vars
		unset( $view_name );

		if ( ! empty( $args ) ) {
			// extract passed args into variables
			extract( $args, EXTR_OVERWRITE );
		}

		/**
		 * Before loading template hook
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 */
		do_action_ref_array( 'wppp_load_template_before', [ &$__template_path, $__view_name, $args ] );

		/**
		 * Loading template file path filter
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 *
		 * @return string
		 */
		require apply_filters( 'wppp_load_template_path', $__template_path, $__view_name, $args );

		/**
		 * After loading template hook
		 *
		 * @param string $__template_path
		 * @param string $__view_name
		 */
		do_action( 'wppp_load_template_after', $__template_path, $__view_name, $args );
	}

	/**
	 * Language file loading
	 *
	 * @return void
	 */
	public function load_language() {
		load_plugin_textdomain( 'play-ht', false, dirname( plugin_basename( WPPP_MAIN_FILE ) ) . '/languages' );
	}
}

// boot up the system
Plugin::get_instance();
