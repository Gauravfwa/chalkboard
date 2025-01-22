<?php
/*
 * Plugin Name: Woocommerce Google Merchant Center Customer Reviews
 * Description: Integrates Google Merchant Center's Google Customer Reviews survey opt-in and Reviews Badge into your WooCommerce store. Additionally, if your products have GTINs and you have a product feed uploaded to your merchant center then Google can also request product ratings.
 * Author: WebPerfect.com
 * Author URI: https://webperfect.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/quick-guide-gplv3.html
 * Text Domain: wc-google-merchant-center-customer-reviews
 * Version: 1.0.1
 * Woo: 3546053:8178e275492c48154e9bf6e312867247
 * WC requires at least: 3.0.0
 * WC tested up to: 3.7.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Constant values
 */
define( 'GMC_NAMES', 'Google Merchant Center' );
define( 'GMC_PLUGIN_DEPENDENCIES', 'WooCommerce' );

class WcGoogleMerchantCenterCustomerReview {

	public function __construct() {
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__),  array( $this, 'plugin_action_links' ) );
		register_activation_hook( __FILE__, array( $this, 'gmcActivationHooks' ) );
		add_action( 'plugins_loaded', array( $this, 'load_classes' ), 9 );
	}

	/**
	 * Add plugin links to the plugins page
	 * @var $links array with the existing links passed by WP
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration' ) . '">Settings</a>',
			'<a href="https://woocommerce.com/my-account/create-a-ticket/" target="_blank">Support</a>',
			'<a href="https://docs.woocommerce.com/document/woocommerce-google-merchant-center-customer-reviews/" target="_blank">Docs</a>',
			'<a href="https://woocommerce.com/products/woocommerce-google-merchant-center-customer-reviews-integration/#comments" target="_blank">Review</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Runs only when the plugin is activated.
	 */
	public function gmcActivationHooks() {
		/* Create transient data */
		set_transient( 'gmc-notices', true, 5 );
	}

	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_Integration_GMC_Settings';

		return $integrations;
	}

	/**
	 * Prints sucess message after plugin activated
	 */
	public function gmcSuccessMessage() {
		global $wp_version;
		include_once dirname( __FILE__ ) . '/views/gmc-sucess-message.php';
	}

	public function load_classes() {
		if ( $this->is_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array( $this, 'gmcRequirementsError' ) );

			return;
		}
		$this->init();
	}

	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) ) : array();
		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function init() {
		/* Add admin notice */
		add_action( 'admin_notices', array( $this, 'gmcSuccessMessage' ) );
		$this->settings();
	}

	public function settings() {
		include_once __DIR__ . '/classes/gmc-settings.php';
		// Register the integration.
		add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
	}

	/**
	 * Prints an error that the system requirements weren't met.
	 */
	public function gmcRequirementsError() {
		global $wp_version;
		include_once dirname( __FILE__ ) . '/views/requirements-error.php';
	}

}

$wgmcCustomerReview = new WcGoogleMerchantCenterCustomerReview();