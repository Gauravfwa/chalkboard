<?php

namespace Objectiv\Plugins\Checkout;

// Base package classes
use Objectiv\BoosterSeat\Language\i18n;
use Objectiv\BoosterSeat\Utilities\Activator;
use Objectiv\BoosterSeat\Utilities\Deactivator;
use Objectiv\BoosterSeat\Base\Singleton;

// Checkout for WooCommerce
use Objectiv\Plugins\Checkout\Action\ApplyCouponAction;
use Objectiv\Plugins\Checkout\Action\CompleteOrderAction;
use Objectiv\Plugins\Checkout\Action\RemoveCouponAction;
use Objectiv\Plugins\Checkout\Action\UpdateCartAction;
use Objectiv\Plugins\Checkout\Action\UpdateCheckoutAction;
use Objectiv\Plugins\Checkout\Action\UpdatePaymentMethodAction;
use Objectiv\Plugins\Checkout\Core\Customizer;
use Objectiv\Plugins\Checkout\Core\Form;
use Objectiv\Plugins\Checkout\Loaders\Content;
use Objectiv\Plugins\Checkout\Loaders\Redirect;
use Objectiv\Plugins\Checkout\Managers\TemplatesManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;
use Objectiv\Plugins\Checkout\Stats\StatCollection;
use Objectiv\Plugins\Checkout\Managers\ActivationManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\AjaxManager;
use Objectiv\Plugins\Checkout\Managers\ExtendedPathManager;
use Objectiv\Plugins\Checkout\Action\AccountExistsAction;
use Objectiv\Plugins\Checkout\Action\LogInAction;
use Objectiv\Plugins\Checkout\Compatibility\Manager as CompatibilityManager;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout
 * @author Brandon Tassone <brandontassone@gmail.com>
 */

class Main extends Singleton {

	/**
	 * Template related functionality manager
	 *
	 * @since 1.0.0
	 * @access private
	 * @var TemplatesManager $templates_manager Handles all template related functionality.
	 */
	private $templates_manager;

	/**
	 * @since 1.1.4
	 * @access private
	 * @var ExtendedPathManager $path_manager Handles the path information for the plugin
	 */
	private $path_manager;

	/**
	 * @since 1.0.0
	 * @access private
	 * @var AjaxManager $ajax_manager
	 */
	private $ajax_manager;

	/**
	 * Language class dealing with translating the various parts of the plugin
	 *
	 * @since 1.0.0
	 * @access private
	 * @var i18n The language class
	 */
	private $i18n;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of the plugin.
	 */
	private $version;

	/**
	 * Settings class for accessing user defined settings.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var SettingsManager $settings The settings object.
	 */
	private $settings_manager;

	/**
	 * Updater class for handling licenses
	 *
	 * @since 1.0.0
	 * @access private
	 * @var UpdatesManager $updater The updater object.
	 */
	private $updater;

	/**
	 * Customizer compatibility class
	 *
	 * @since 2.4.0
	 * @access private
	 * @var Customizer $customizer The updater object.
	 */
	private $customizer;

	/**
	 * Activation manager for handling activation conditions
	 *
	 * @since 1.1.4
	 * @access private
	 * @var ActivationManager $activation_manager Handles activation
	 */
	private $activation_manager;

	/**
	 * Settings class for accessing user defined settings.
	 *
	 * @since 1.1.4
	 * @access private
	 * @var Deactivator $deactivator Handles deactivation
	 */
	private $deactivator;

	/**
	 * @since 1.1.5
	 * @access private
	 * @var Form $form Handles the WooCommerce form changes
	 */
	private $form;

	/**
	 * @since 2.4.12
	 * @access private
	 * @var StatCollection Handles the stat collection for CFW
	 */
	private $stat_collection;

	/**
	 * Main constructor.
	 */
	public function __construct() {
		// Program Details
		$this->plugin_name = 'CheckoutWC';
		$this->version     = CFW_VERSION;
	}

	/**
	 * Returns the i18n language class
	 *
	 * @since 1.0.0
	 * @access public
	 * @return i18n
	 */
	public function get_i18n() {
		return $this->i18n;
	}

	/**
	 * Returns the path manager
	 *
	 * @since 1.1.4
	 * @access public
	 * @return ExtendedPathManager
	 */
	public function get_path_manager() {
		return $this->path_manager;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @return AjaxManager
	 */
	public function get_ajax_manager() {
		return $this->ajax_manager;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Returns the template manager
	 *
	 * @since 1.0.0
	 * @access public
	 * @return TemplatesManager
	 */
	public function get_templates_manager() {
		return $this->templates_manager;
	}

	/**
	 * Returns the template manager
	 *
	 * @param TemplatesManager $templates_manager
	 *
	 * @return void
	 * @since 1.0.0
	 * @access public
	 */
	public function set_templates_manager( $templates_manager ) {
		$this->templates_manager = $templates_manager;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the settings manager
	 *
	 * @since 1.0.0
	 * @access public
	 * @return SettingsManager The settings manager object
	 */
	public function get_settings_manager() {
		return $this->settings_manager;
	}

	/**
	 * Set the settings manager
	 *
	 * @param SettingsManager $settings_manager
	 *
	 * @return void
	 * @since 1.0.0
	 * @access public
	 */
	public function set_settings_manager( $settings_manager ) {
		$this->settings_manager = $settings_manager;
	}

	/**
	 * Get the updater object
	 *
	 * @since 1.0.0
	 * @access public
	 * @return UpdatesManager The updater object
	 */
	public function get_updater() {
		return $this->updater;
	}

	/**
	 * Get the updater object
	 *
	 * @since 1.1.4
	 * @access public
	 * @return ActivationManager The class handling activation of the plugin
	 */
	public function get_activation_manager() {
		return $this->activation_manager;
	}

	/**
	 * Get the updater object
	 *
	 * @since 1.1.4
	 * @access public
	 * @return Deactivator The class handling deactivation of the plugin
	 */
	public function get_deactivator() {
		return $this->deactivator;
	}

	/**
	 * @since 1.1.5
	 * @access public
	 * @return Form The form object
	 */
	public function get_form() {
		return $this->form;
	}

	/**
	 * @since 2.4.12
	 * @access public
	 * @return Stats\StatCollection The form object
	 */
	public function get_stat_collection() {
		return $this->stat_collection;
	}

	/**
	 * Run
	 *
	 * @since 1.0.0
	 * @param string $file The file path to the main plugin file
	 */
	public function run( $file ) {
		// Enable program flags
		$this->check_flags();

		// Create and setup the plugins main objects
		$this->create_main_objects( $file );

		// Loads all the ajax handlers on the php side
		$this->configure_objects();

		// Init actions
		add_action( 'init', array( $this, 'init' ), 0 );

		// Run this early to maximize integrations
		add_action( 'plugins_loaded', array( $this, 'add_plugin_hooks' ), 1 );
	}

	function init() {
		// Load translations
		$this->i18n->load_plugin_textdomain( $this->path_manager );
	}

	/**
	 * When run checks to see if the flag is defined and its value (inversely). If found to be active, it runs the
	 * function
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function check_flags() {
		if ( defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE ) {
			$this->enable_dev_mode();
		}
	}

	/**
	 * Enables libraries and functions for the specific task of aiding in development
	 *
	 * Kint - Pretty Debug
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function enable_dev_mode() {
		// Enable Kint
		if ( class_exists( '\Kint' ) && property_exists( '\Kint', 'enabled_mode' ) ) {
			\Kint::$enabled_mode = true;
		}
	}

	/**
	 * Creates the main objects used in this plugins setup and processing
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $file The file path to the main plugin file
	 */
	private function create_main_objects( $file ) {
		// Set up localization
		$this->i18n = new i18n( 'checkout-wc' );

		// Activation Manager
		$this->activation_manager = new ActivationManager( $this->get_activator_checks() );

		// Deactivator
		$this->deactivator = new Deactivator();

		// The path manager for the plugin
		$this->path_manager = new ExtendedPathManager( plugin_dir_path( $file ), plugin_dir_url( $file ), $file );

		// The settings manager for the plugin
		$this->settings_manager = new SettingsManager();

		// Stat collection
		$this->stat_collection = StatCollection::instance( $this->settings_manager );

		$active_template = $this->settings_manager->get_setting( 'active_template' );

		// Create the template manager
		$this->templates_manager = new TemplatesManager( $this->path_manager, empty( $active_template ) ? 'default' : $active_template );

		// Create the ajax manager
		$this->ajax_manager = new AjaxManager( $this->get_ajax_actions() );

		// License updater
		$this->updater = new UpdatesManager( '_cfw_licensing', false, CFW_UPDATE_URL, $this->get_version(), CFW_NAME, 'Objectiv', $file, $theme = false, $this->get_settings_manager()->get_setting( 'beta_updates' ), $this->get_home_url() );

		// Customizer
		$this->customizer = new Customizer( $this, $this->get_settings_manager(), $this->get_templates_manager(), $this->get_path_manager() );
	}

	public function get_activator_checks() {
		return array(
			array(
				'id'       => 'checkout-woocommerce_activation',
				'function' => 'class_exists',
				'value'    => 'WooCommerce',
				'message'  => array(
					'success' => false,
					'class'   => 'notice error',
					'message' => cfw__( 'Activation failed: Please activate WooCommerce in order to use CheckoutWC', 'checkout-wc' ),
				),
			),
		);
	}

	/**
	 * @since 1.0.0
	 * @access private
	 */
	private function configure_objects() {
		$this->ajax_manager->load_all();
	}

	/**
	 * @return array
	 */
	public function get_ajax_actions() {
		// Setting no_privilege to false because wc_ajax doesn't have a no privilege endpoint.
		return array(
			new AccountExistsAction( 'account_exists', false, 'wc_ajax_' ),
			new LogInAction( 'login', false, 'wc_ajax_' ),
			new CompleteOrderAction( 'complete_order', false, 'wc_ajax_' ),
			new ApplyCouponAction( 'cfw_apply_coupon', false, 'wc_ajax_' ),
			new RemoveCouponAction( 'cfw_remove_coupon', false, 'wc_ajax_' ),
			new UpdateCheckoutAction( 'update_checkout', false, 'wc_ajax_' ),
			new UpdatePaymentMethodAction( 'update_payment_method', false, 'wc_ajax_' ),
			new UpdateCartAction( 'update_cart', false, 'wc_ajax_' ),
		);
	}

	/**
	 * Set the plugin assets
	 */
	public function set_assets() {
		global $wp;

		if ( ! Main::is_checkout() && ! Main::is_checkout_pay_page() && ! Main::is_order_received_page() ) {
			return;
		}

		/**
		 * WP Rocket
		 *
		 * Disable minify / cdn features while we're on the checkout page due to strange issues.
		 */
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true );
		}

		$front = trailingslashit( $this->path_manager->get_assets_path() ) . 'dist';

		// Minified extension
		$min = ( ! CFW_DEV_MODE ) ? '.min' : '';

		// Version extension
		$version = CFW_VERSION;

		// Google API Key
		$google_api_key = $this->get_settings_manager()->get_setting( 'google_places_api_key' );

		/**
		 * Dequeue Native Scripts
		 */
		// Many plugins enqueue their scripts with 'woocommerce' and 'wc-checkout' as a dependent scripts
		// So, instead of modifying these scripts we dequeue WC's native scripts and then
		// queue our own scripts using the same handles. Magic!

		// Don't load our scripts when the form has been replaced.
		// This works because WP won't let you replace registered scripts
		/** This filter is documented in templates/default/content.php */
		if ( apply_filters( 'cfw_replace_form', false ) === false ) {
			wp_dequeue_script( 'woocommerce' );
			wp_deregister_script( 'woocommerce' );
			wp_dequeue_script( 'wc-checkout' );
			wp_deregister_script( 'wc-checkout' );
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'woocommerce-layout' );
		}

		/**
		 * vendor.js
		 */
		wp_enqueue_script( 'cfw_vendor_js', "{$front}/js/checkoutwc-vendor-{$version}{$min}.js", array( 'jquery', 'jquery-blockui', 'js-cookie' ) );

		if ( Main::is_checkout() ) {
			/**
			 * Styles
			 */
			wp_enqueue_style( 'cfw_front_css', "{$front}/css/checkoutwc-front-{$version}{$min}.css", array() );

			/**
			 * Scripts
			 */
			wp_enqueue_script( 'woocommerce', "{$front}/js/checkoutwc-front-{$version}{$min}.js", array( 'cfw_vendor_js' ) );

			/**
			 * Filter Google Maps compatibility mode
			 *
			 * If compatibility mode is true, we don't load Google Maps script and assume it's been loaded elsewhere
			 *
			 * @since 3.0.0
			 *
			 * @param bool $compatibility_mode Compatibility mode flag
			 */
			if ( ! apply_filters( 'cfw_google_maps_compatibility_mode', false ) && $this->get_settings_manager()->is_premium_feature_enabled( 'enable_address_autocomplete' ) ) {
				// Address Autocomplete Script
				wp_enqueue_script( 'cfw-google-places', "https://maps.googleapis.com/maps/api/js?key=$google_api_key&libraries=places", array( 'woocommerce' ) );
			}
		} elseif ( Main::is_checkout_pay_page() ) {
			/**
			 * Styles
			 */
			wp_enqueue_style( 'cfw_front_css', "{$front}/css/checkoutwc-order-pay-{$version}{$min}.css", array() );

			/**
			 * Scripts
			 */
			wp_enqueue_script( 'woocommerce', "{$front}/js/checkoutwc-order-pay-{$version}{$min}.js", array( 'cfw_vendor_js' ) );
		} elseif ( Main::is_order_received_page() ) {
			/**
			 * Styles
			 */
			wp_enqueue_style( 'cfw_front_css', "{$front}/css/checkoutwc-thank-you-{$version}{$min}.css", array() );

			// FontAwesome
			wp_enqueue_style( 'cfw-fontawesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );

			/**
			 * Scripts
			 */
			if ( $this->get_settings_manager()->is_premium_feature_enabled( 'enable_map_embed' ) && $this->get_settings_manager()->is_premium_feature_enabled( 'enable_thank_you_page' ) ) {
				wp_enqueue_script( 'cfw-google-places', "https://maps.googleapis.com/maps/api/js?key=$google_api_key", array( 'woocommerce' ) );
			}
			wp_enqueue_script( 'woocommerce', "{$front}/js/checkoutwc-thank-you-{$version}{$min}.js", array( 'cfw_vendor_js' ) );
		}

		/**
		 * Fires to trigger Templates to load their assets
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_load_template_assets' );

		/**
		 * Filter cfw_event_data array
		 *
		 * Localized data available via DataService
		 *
		 * @since 1.0.0
		 *
		 * @param array $cfw_event_data The data
		 */
		$cfw_event_data = apply_filters(
			'cfw_event_data',
			array(
				'elements'                   => array(
					/**
					 * Filter breadcrumb element ID
					 *
					 * @since 1.0.0
					 *
					 * @param string $breadCrumbElId Breadcrumb element ID
					 */
					'breadCrumbElId'       => apply_filters( 'cfw_template_breadcrumb_id', '#cfw-breadcrumb' ),

					/**
					 * Filter customer info tab ID
					 *
					 * @since 1.0.0
					 *
					 * @param string $customerInfoElId Customer info tab ID
					 */
					'customerInfoElId'     => apply_filters( 'cfw_template_customer_info_el', '#cfw-customer-info' ),

					/**
					 * Filter shipping method tab ID
					 *
					 * @since 1.0.0
					 *
					 * @param string $shippingMethodElId Shipping method tab ID
					 */
					'shippingMethodElId'   => apply_filters( 'cfw_template_shipping_method_el', '#cfw-shipping-method' ),

					/**
					 * Filter payment method tab ID
					 *
					 * @since 1.0.0
					 *
					 * @param string $paymentMethodElId Payment method tab ID
					 */
					'paymentMethodElId'    => apply_filters( 'cfw_template_payment_method_el', '#cfw-payment-method' ),

					/**
					 * Filter tab container element ID
					 *
					 * @since 1.0.0
					 *
					 * @param string $tabContainerElId Tab container element ID
					 */
					'tabContainerElId'     => apply_filters( 'cfw_template_tab_container_el', '#cfw' ),

					/**
					 * Filter alert container element ID
					 *
					 * @since 1.0.0
					 *
					 * @param string $alertContainerId Alert container element ID
					 */
					'alertContainerId'     => apply_filters( 'cfw_template_alert_container_el', '#cfw-alert-container' ),

					/**
					 * Filter checkout form selector
					 *
					 * @since 1.0.0
					 *
					 * @param string $checkoutFormSelector Checkout form selector
					 */
					'checkoutFormSelector' => apply_filters( 'cfw_checkout_form_selector', 'form.woocommerce-checkout' ),
				),
				'ajaxInfo'                   => array(
					'url' => trailingslashit( get_home_url() ),
				),
				/**
				 * Filter TypeScript compatibility classes and params
				 *
				 * @since 3.0.0
				 *
				 * @param array $compatibility TypeScript compatibility classes and params
				 */
				'compatibility'              => apply_filters( 'cfw_typescript_compatibility_classes_and_params', array() ),
				'settings'                   => array(
					'parsley_locale'                    => $this->get_parsley_locale(), // required for parsley localization
					'user_logged_in'                    => ( is_user_logged_in() ) ? true : false,

					/**
					 * Filter whether to validate required registration
					 *
					 * @since 3.0.0
					 *
					 * @param bool $validate_required_registration Validate required registration
					 */
					'validate_required_registration'    => apply_filters( 'cfw_validate_required_registration', true ),
					'default_address_fields'            => array_keys( WC()->countries->get_default_address_fields() ),

					/**
					 * Filter whether to enable zip autocomplete
					 *
					 * @since 2.0.0
					 *
					 * @param bool $enable_zip_autocomplete Enable zip autocomplete
					 */
					'enable_zip_autocomplete'           => apply_filters( 'cfw_enable_zip_autocomplete', true ) ? true : false,

					/**
					 * Filter whether to check create account by default
					 *
					 * @since 3.0.0
					 *
					 * @param bool $check_create_account_by_default Check create account by default
					 */
					'check_create_account_by_default'   => apply_filters( 'cfw_check_create_account_by_default', true ) ? true : false,

					/**
					 * Filter whether to suppress default login form
					 *
					 * @since 3.0.0
					 *
					 * @param bool $cfw_suppress_default_login_form Suppress default login form
					 */
					'enable_checkout_login_reminder'    => apply_filters( 'cfw_suppress_default_login_form', $this->get_settings_manager()->get_setting( 'login_style' ) !== 'woocommerce' ) && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) && Main::is_checkout(),
					'needs_shipping_address'            => WC()->cart->needs_shipping_address(),
					'show_shipping_tab'                 => cfw_show_shipping_tab(),
					'enable_address_autocomplete'       => $this->get_settings_manager()->is_premium_feature_enabled( 'enable_address_autocomplete' ),
					'enable_map_embed'                  => $this->get_settings_manager()->is_premium_feature_enabled( 'enable_map_embed' ),

					/**
					 * Filter whether to load tabs
					 *
					 * @since 3.0.0
					 *
					 * @param bool $load_tabs Load tabs
					 */
					'load_tabs'                         => apply_filters( 'cfw_load_tabs', Main::is_checkout() ),
					'is_checkout_pay_page'              => Main::is_checkout_pay_page(),
					'is_order_received_page'            => Main::is_order_received_page(),

					/**
					 * Filter list of shipping country restrictions for Google Maps address autocomplete
					 *
					 * @since 3.0.0
					 *
					 * @param array $address_autocomplete_shipping_countries List of country restrictions for Google Maps address autocomplete
					 */
					'address_autocomplete_shipping_countries' => apply_filters( 'cfw_address_autocomplete_shipping_countries', false ),

					/**
					 * Filter list of billing country restrictions for Google Maps address autocomplete
					 *
					 * @since 3.0.0
					 *
					 * @param array $address_autocomplete_billing_countries List of country restrictions for Google Maps address autocomplete
					 */
					'address_autocomplete_billing_countries' => apply_filters( 'cfw_address_autocomplete_billing_countries', false ),
					'delete_confirm_message'            => __( 'Are you sure you want to remove this item from your cart?', 'checkout-wc' ),
					'is_registration_required'          => WC()->checkout()->is_registration_required(),
					'account_already_registered_notice' => apply_filters( 'woocommerce_registration_error_email_exists', cfw__( 'An account is already registered with your email address. Please log in.', 'woocommerce' ), '' ),

					/**
					 * Filter whether to automatically generate password for new accounts
					 *
					 * @since 3.0.0
					 *
					 * @param bool $registration_generate_password Automatically generate password for new accounts
					 */
					'registration_generate_password'    => apply_filters( 'cfw_registration_generate_password', $this->get_settings_manager()->get_setting( 'registration_style' ) !== 'woocommerce' ),
					'thank_you_shipping_address'        => false,
					'shipping_address_label'            => __( 'Shipping address', 'checkout-wc' ),
					'enable_one_page_checkout'          => $this->get_settings_manager()->is_premium_feature_enabled( 'enable_one_page_checkout' ),
					'quantity_prompt_message'           => __( 'Please enter a new quantity:', 'checkout-wc' ),
					'cvv_tooltip_message'               => __( '3-digit security code usually found on the back of your card. American Express cards have a 4-digit code located on the front.', 'checkout-wc' ),

					/**
					 * Filter list of field persistence service excludes
					 *
					 * @since 3.0.0
					 *
					 * @param array $field_persistence_excludes List of field persistence service excludes
					 */
					'field_persistence_excludes'        => apply_filters(
						'cfw_field_data_persistence_excludes',
						array(
							'input[type="button"]',
							'input[type="file"]',
							'input[type="hidden"]',
							'input[type="submit"]',
							'input[type="reset"]',
							'input[name="payment_method"]',
							'input[name="paypal_pro-card-number"]',
							'input[name="paypal_pro-card-cvc"]',
							'input[name="wc-authorize-net-aim-account-number"]',
							'input[name="wc-authorize-net-aim-csc"]',
							'input[name="paypal_pro_payflow-card-number"]',
							'input[name="paypal_pro_payflow-card-cvc"]',
							'input[name="paytrace-card-number"]',
							'input[name="paytrace-card-cvc"]',
							'input[id="stripe-card-number"]',
							'input[id="stripe-card-cvc"]',
							'input[name="creditCard"]',
							'input[name="cvv"]',
							'input.wc-credit-card-form-card-number',
							'input[name="wc-authorize-net-cim-credit-card-account-number"]',
							'input[name="wc-authorize-net-cim-credit-card-csc"]',
							'input.wc-credit-card-form-card-cvc',
							'input.js-sv-wc-payment-gateway-credit-card-form-account-number',
							'input.js-sv-wc-payment-gateway-credit-card-form-csc',
							'input.shipping_method',
							'input[name^="tocheckoutcw"]',
							'#_sumo_pp_enable_order_payment_plan',
							'.cfw-cart-quantity-input',
							'.gift-certificate-show-form input',
						)
					),
				),
				'checkout_params'            => array(
					'ajax_url'                  => WC()->ajax_url(),
					'wc_ajax_url'               => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
					'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
					'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
					'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
					'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
					'checkout_url'              => \WC_AJAX::get_endpoint( 'checkout' ),
					'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
					'debug_mode'                => defined( 'WP_DEBUG' ) && WP_DEBUG,
					'cfw_debug_mode'            => isset( $_GET['cfw-debug'] ),
					'i18n_checkout_error'       => cfw_esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
				),
				'runtime_email_matched_user' => false, // default to false
			)
		);

		if ( Main::is_order_received_page() ) {
			$order = $this->get_order_received_order();

			if ( $order ) {
				$address = $order->get_address( 'shipping' );

				// Remove name and company before generate the Google Maps URL.
				unset( $address['first_name'], $address['last_name'], $address['company'] );

				$address = apply_filters( 'woocommerce_shipping_address_map_url_parts', $address, $order );
				$address = array_filter( $address );
				$address = implode( ', ', $address );

				$cfw_event_data['settings']['thank_you_shipping_address'] = $address;
			}
		}

		wp_localize_script(
			'woocommerce',
			'cfwEventData',
			$cfw_event_data
		);

		// Some plugins (WooCommerce Square for example?) want to use wc_cart_fragments_params on the checkout page
		wp_localize_script(
			'woocommerce',
			'wc_cart_fragments_params',
			array(
				'ajax_url'    => WC()->ajax_url(),
				'wc_ajax_url' => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
			)
		);

		if ( Main::is_checkout() || Main::is_checkout_pay_page() ) {
			// Workaround for WooCommerce 3.8 Beta 1
			global $wp_scripts;
			$wp_scripts->registered['wc-country-select']->deps = array( 'jquery' );

			// WooCommerce Native Localization Handling
			wp_enqueue_script( 'wc-country-select' );
			wp_enqueue_script( 'wc-address-i18n' );
		}
	}

	function get_parsley_locale() {
		$raw_locale = determine_locale();

		// Handle special raw locale cases
		switch ( $raw_locale ) {
			case 'pt_BR':
				$locale = 'pt-br';
				break;
			case 'pt_PT':
			case 'pt_AO':
				$locale = 'pt-pt';
				break;
			default:
				$locale = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : strstr( $raw_locale, '_', true );
		}

		// Handle special locale cases
		switch ( $locale ) {
			case 'nb':
			case 'nn':
				$locale = 'no';
		}

		// Fallback to the raw locale
		if ( ! $locale ) {
			$locale = $raw_locale;
		}

		/**
		 * Filter Parsley validation service locale
		 *
		 * @since 3.0.0
		 *
		 * @param string $locale Parsley validation service locale
		 */
		return apply_filters( 'cfw_parsley_locale', $locale );
	}

	/**
	 * Add the actions and hooks used by the plugin
	 */
	public function add_plugin_hooks() {
		/**
		 * Filter to allow bypassing CheckoutWC template from 3rd party code
		 *
		 * @since 3.0.0
		 *
		 * @param bool $bypass Bypass CheckoutWC templates
		 */
		if ( apply_filters( 'cfw_bypass_templates', false ) ) {
			return;
		}

		if ( $this->get_settings_manager()->is_premium_feature_enabled( 'enable_address_autocomplete' ) ) {
			add_filter( 'cfw_enable_zip_autocomplete', '__return_false' );
		}

		// Handle the Activation notices
		add_action(
			'admin_notices',
			function() {
				$this->get_activation_manager()->activate_admin_notice( $this->get_path_manager() );
			}
		);

		// Admin toolbar
		add_action( 'admin_bar_menu', array( $this, 'add_admin_buttons' ), 100 );

		if ( $this->is_enabled() ) {
			/**
			 * Override some WooCommerce Options
			 */
			// WooCommerce - Registration Generate Password
			add_filter( 'pre_option_woocommerce_registration_generate_password', array( $this, 'override_woocommerce_registration_generate_password' ), 10, 1 );
			add_filter( 'pre_option_woocommerce_registration_generate_username', array( $this, 'override_woocommerce_registration_generate_username' ), 10, 1 );

			/**
			 * Load Compatibility Class
			 */
			$this->compatibility();

			if ( $this->get_settings_manager()->is_premium_feature_enabled( 'enable_thank_you_page' ) && $this->get_settings_manager()->is_premium_feature_enabled( 'override_view_order_template' ) ) {
				add_filter( 'woocommerce_get_view_order_url', array( $this, 'override_view_order_url' ), 100, 2 );
			}

			if ( $this->get_settings_manager()->get_setting( 'enable_order_notes' ) === 'yes' ) {
				add_filter( 'woocommerce_enable_order_notes_field', '__return_true' );
			}

			/**
			 * User matching
			 */
			if ( $this->get_settings_manager()->is_premium_feature_enabled( 'user_matching' ) ) {
				// Match new guest orders to accounts
				add_action( 'woocommerce_new_order', array( $this, 'maybe_match_new_order_to_user_account' ), 10, 1 );

				// Match old guest orders to accounts on registration
				add_action( 'woocommerce_created_customer', array( $this, 'maybe_link_orders_at_registration' ), 10, 1 );
			}
		}

		add_action( 'wp', array( $this, 'init_hooks' ), 1 );

		// Add image size for our cart / checkout / order pay / thank you views
		add_action( 'init', array( $this, 'add_cart_image_size' ) );

		// PHP snippets
		add_action( 'init', array( $this, 'run_php_snippets' ) );
	}

	/**
	 * Check if theme should enabled
	 *
	 * @return bool
	 */
	function is_enabled(): bool {
		$result = false;

		if ( ! function_exists( 'WC' ) ) {
			$result = false; // superfluous, but sure
		}

		if ( ( $this->license_is_valid() && $this->settings_manager->get_setting( 'enable' ) === 'yes' ) || current_user_can( 'manage_options' ) ) {
			$result = true;
		}

		/**
		 * Filter whether CheckoutWC is enabled to load template in settings
		 *
		 * @since 3.0.0
		 *
		 * @param bool $enabled Whether CheckoutWC templates are enabled
		 */
		return apply_filters( 'cfw_checkout_is_enabled', $result );
	}

	function compatibility() {
		new CompatibilityManager();
	}

	/**
	 * Handles general purpose WordPress actions.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_actions() {
		/**
		 * Load Assets
		 */
		// Load Assets
		add_action( 'wp_enqueue_scripts', array( $this, 'set_assets' ), 11 ); // 11 is 1 after 10, which is where WooCommerce loads their scripts

		add_action( 'template_redirect', array( $this, 'one_page_checkout_layout' ), 0 );

		add_action( 'template_redirect', array( $this, 'order_review_tab_layout' ), 0 );

		// Setup the Checkout redirect
		if ( $this->get_settings_manager()->get_setting( 'template_loader' ) === 'content' ) {
			$this->content_loaders();
		} else {
			add_action( 'template_redirect', array( $this, 'template_redirect' ), 11 );
		}
	}

	function one_page_checkout_layout() {
		if ( $this->get_settings_manager()->is_premium_feature_enabled( 'enable_one_page_checkout' ) ) {
			// Remove breadcrumbs
			remove_action( 'cfw_checkout_before_order_review', 'cfw_breadcrumb_navigation', 10 );
			remove_action( 'cfw_checkout_main_container_start', 'futurist_breadcrumb_navigation', 10 );

			// Remove customer info tab nav
			remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_nav', 50 );

			// Remove shipping address review
			remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_address_review_pane', 10 );

			// Remove shipping tab nav
			remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_tab_nav', 30 );

			// Remove payment tab address review
			remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_method_address_review_pane', 0 );

			// Remove payment tab navigation
			remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_nav', 50 );
			add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_nav_one_page_checkout', 50 );
		}
	}

	function order_review_tab_layout() {
		/** Order Review Step */
		if ( cfw_get_main()->get_settings_manager()->is_premium_feature_enabled( 'enable_order_review_step' ) && ! cfw_get_main()->get_settings_manager()->is_premium_feature_enabled( 'enable_one_page_checkout' ) && ! defined( 'CFW_SUPPRESS_ORDER_REVIEW_TAB' ) ) {
			// Move payment tab nav and terms and conditions to order review
			remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_nav', 50 );
			remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_content_terms_and_conditions', 40 );

			// Add new payment tab nav
			add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_method_tab_review_nav', 50, 0 );

			// Add order review to breadcrumbs
			add_filter( 'cfw_breadcrumbs', 'cfw_add_order_review_step_breadcrumb' );

			// Add order review tab
			add_action( 'cfw_checkout_tabs', 'cfw_add_order_review_step_tab', 40 );

			/**
			 * Order Review Tab Content
			 */
			add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_tab_heading', 10 );
			add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_step_review_pane', 20 );
			add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_step_totals_review_pane', 30 );
			add_action( 'cfw_checkout_order_review_tab', 'cfw_payment_tab_content_terms_and_conditions', 40, 0 );
			add_action( 'cfw_checkout_order_review_tab', 'cfw_order_review_tab_nav', 50, 0 );
		}
	}

	function content_loaders() {
		if ( Main::is_checkout() ) {
			Content::checkout();
		} elseif ( Main::is_checkout_pay_page() ) {
			Content::order_pay();
		} elseif ( Main::is_order_received_page() ) {
			Content::order_received();
		}
	}

	function template_redirect() {
		global $wp;

		if ( isset( $_GET['order'] ) && isset( $_GET['key'] ) ) { // WPCS: input var ok, CSRF ok.
			wc_deprecated_argument( __CLASS__ . '->' . __FUNCTION__, '2.1', '"order" is no longer used to pass an order ID. Use the order-pay or order-received endpoint instead.' );

			// Get the order to work out what we are showing.
			$order_id = absint( $_GET['order'] ); // WPCS: input var ok.
			$order    = wc_get_order( $order_id );

			if ( $order && $order->has_status( 'pending' ) ) {
				$wp->query_vars['order-pay'] = absint( $_GET['order'] ); // WPCS: input var ok.
			} else {
				$wp->query_vars['order-received'] = absint( $_GET['order'] ); // WPCS: input var ok.
			}
		}

		if ( Main::is_checkout() ) {
			Redirect::checkout();
		} elseif ( Main::is_checkout_pay_page() ) {
			Redirect::order_pay();
		} elseif ( Main::is_order_received_page() ) {
			Redirect::order_received();
		}
	}

	function init_hooks() {
		if ( $this->is_enabled() && ( ! defined( 'CFW_BYPASS_TEMPLATE' ) || ! CFW_BYPASS_TEMPLATE ) ) {
			/**
			 * Form
			 */
			$this->form = new Form();

			if ( Main::is_cfw_page() ) {
				// Load the plugin actions
				$this->load_actions();
			}
		}
	}

	/**
	 * @return bool|\WC_Order|\WC_Order_Refund
	 */
	function get_order_received_order() {
		global $wp;

		$order_id = $wp->query_vars['order-received'];
		$order    = false;

		// Get the order.
		$order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $order_id ) );
		$order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET['key'] ) ? '' : wc_clean( wp_unslash( $_GET['key'] ) ) ); // WPCS: input var ok, CSRF ok.

		if ( $order_id > 0 ) {
			$order = wc_get_order( $order_id );
			if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
				$order = false;
			}
		}

		return $order;
	}

	/**
	 * Get phone field setting
	 *
	 * @return boolean
	 */
	function is_phone_fields_enabled() {
		/**
		 * Filter whether phone field is enabled
		 *
		 * @since 3.0.0
		 *
		 * @param bool $cfw_enable_phone_fields Whether phone fields are enabled
		 */
		return apply_filters( 'cfw_enable_phone_fields', 'hidden' !== get_option( 'woocommerce_checkout_phone_field', 'required' ) );
	}

	function add_admin_buttons( $admin_bar ) {
		if ( ! Main::is_checkout() ) {
			return;
		}

		// Remove irrelevant buttons
		$admin_bar->remove_node( 'new-content' );
		$admin_bar->remove_node( 'updates' );
		$admin_bar->remove_node( 'edit' );
		$admin_bar->remove_node( 'comments' );

		$admin_bar->add_node(
			array(
				'id'    => 'cfw-settings',
				'title' => '<span class="ab-icon dashicons dashicons-cart"></span>' . __( 'CheckoutWC', 'checkout-wc' ),
				'href'  => admin_url( 'options-general.php?page=cfw-settings' ),
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-general-settings',
				'title'  => cfw__( 'General', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings' ),
				'parent' => 'cfw-settings',
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-premium-settings',
				'title'  => cfw__( 'Premium Features', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings&subpage=premium' ),
				'parent' => 'cfw-settings',
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-template-settings',
				'title'  => cfw__( 'Template', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings&subpage=templates' ),
				'parent' => 'cfw-settings',
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-design-settings',
				'title'  => cfw__( 'Design', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings&subpage=design' ),
				'parent' => 'cfw-settings',
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-integration-settings',
				'title'  => cfw__( 'Integrations', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings&subpage=integrations' ),
				'parent' => 'cfw-settings',
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-license-settings',
				'title'  => cfw__( 'License', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings&subpage=license' ),
				'parent' => 'cfw-settings',
			)
		);

		$admin_bar->add_node(
			array(
				'id'     => 'cfw-support-settings',
				'title'  => cfw__( 'Support', 'checkout-wc' ),
				'href'   => admin_url( 'options-general.php?page=cfw-settings&subpage=support' ),
				'parent' => 'cfw-settings',
			)
		);
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-midas-activator.php
	 */
	public static function activation() {
		// Get main
		$main = Main::instance();

		$errors = $main->get_activation_manager()->activate();

		// Init settings
		$main->get_settings_manager()->add_setting( 'enable', 'no' );
		$main->get_settings_manager()->add_setting( 'login_style', 'enhanced' );
		$main->get_settings_manager()->add_setting( 'registration_style', 'enhanced' );
		$main->get_settings_manager()->add_setting( 'label_style', 'floating' );
		$main->get_settings_manager()->add_setting( 'cart_item_link', 'disabled' );
		$main->get_settings_manager()->add_setting( 'cart_item_data_display', 'short' );
		$main->get_settings_manager()->add_setting( 'skip_shipping_step', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_order_notes', 'no' );
		$main->get_settings_manager()->add_setting( 'active_template', 'default' );
		$main->get_settings_manager()->add_setting( 'allow_checkout_field_editor_address_modification', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_elementor_pro_support', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_beaver_themer_support', 'no' );
		$main->get_settings_manager()->add_setting( 'template_loader', 'redirect' );
		$main->get_settings_manager()->add_setting( 'override_view_order_template', 'yes' );
		$main->get_settings_manager()->add_setting( 'show_logos_mobile', 'no' );
		$main->get_settings_manager()->add_setting( 'show_mobile_coupon_field', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_cart_editing', 'no' );
		$main->get_settings_manager()->add_setting( 'cart_edit_empty_cart_redirect', '' );
		$main->get_settings_manager()->add_setting( 'enable_one_page_checkout', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_order_pay', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_thank_you_page', 'no' );
		$main->get_settings_manager()->add_setting( 'thank_you_order_statuses', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_map_embed', 'no' );
		$main->get_settings_manager()->add_setting( 'override_view_order_template', 'no' );
		$main->get_settings_manager()->add_setting( 'enable_address_autocomplete', 'no' );
		$main->get_settings_manager()->add_setting( 'google_places_api_key', '' );
		$main->get_settings_manager()->add_setting( 'php_snippets', '' );
		$main->get_settings_manager()->add_setting( 'user_matching', 'enabled' );
		$main->get_settings_manager()->add_setting( 'enable_order_review_step', 'no' );

		$custom_logo_id = get_theme_mod( 'custom_logo' );

		if ( $custom_logo_id ) {
			$main->get_settings_manager()->add_setting( 'logo_attachment_id', $custom_logo_id );
		}

		// Init the active template
		$main->get_templates_manager()->get_active_template()->init();

		// Updater license status cron
		$main->updater->set_license_check_cron();

		// Set the stat collection cron
		$main->set_stat_collection_cron();

		if ( ! $errors ) {
			// Welcome screen transient
			set_transient( '_cfw_welcome_screen_activation_redirect', true, 30 );
		}
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-midas-deactivator.php
	 */
	public static function deactivation() {
		// Get main
		$main = Main::instance();

		$main->get_deactivator()->deactivate();

		// Remove cron for license update check
		$main->updater->unset_license_check_cron();

		// Unset the stat collection cron
		$main->unset_stat_collection_cron();
	}

	/**
	 * Stat collection cron
	 */
	public function set_stat_collection_cron() {
		if ( ! wp_next_scheduled( 'cfw_weekly_scheduled_events_tracking' ) ) {
			wp_schedule_event( time(), 'weekly', 'cfw_weekly_scheduled_events_tracking' );
		}
	}

	/**
	 * Unset the collection cron
	 */
	public function unset_stat_collection_cron() {
		wp_clear_scheduled_hook( 'cfw_weekly_scheduled_events_tracking' );
	}

	/**
	 * @param mixed $result
	 *
	 * @return mixed
	 */
	function override_woocommerce_registration_generate_password( $result ) {
		if ( Main::is_checkout() && apply_filters( 'cfw_registration_generate_password', $this->get_settings_manager()->get_setting( 'registration_style' ) !== 'woocommerce' ) ) {
			return 'yes';
		}

		return $result;
	}

	/**
	 * @param mixed $result
	 *
	 * @return mixed
	 */
	function override_woocommerce_registration_generate_username( $result ) {
		if ( Main::is_checkout() ) {
			$result = 'yes';
		}

		return $result;
	}

	/**
	 * @return bool True if license valid, false if it is invalid
	 */
	function license_is_valid() {
		// Get main
		$main = Main::instance();

		$key_status  = $main->updater->get_field_value( 'key_status' );
		$license_key = $main->updater->get_field_value( 'license_key' );

		$valid = true;

		if ( getenv( 'TRAVIS' ) ) {
			return $valid;
		}

		// Validate Key Status
		if ( empty( $license_key ) || ( ( 'valid' !== $key_status || 'inactive' === $key_status || 'site_inactive' === $key_status ) ) ) {
			$valid = false;
		}

		return $valid;
	}

	public static function is_checkout() {
		/**
		 * Filter Main::is_checkout()
		 *
		 * @since 3.0.0
		 *
		 * @param bool $is_checkout Whether or not we are on the checkout page
		 */
		return apply_filters( 'cfw_is_checkout', ( function_exists( 'is_checkout' ) && \is_checkout() ) && ! \is_order_received_page() && ! \is_checkout_pay_page() );
	}

	public static function is_checkout_pay_page() {
		/**
		 * Filter Main::is_checkout_pay_page()
		 *
		 * @since 3.0.0
		 *
		 * @param bool $is_checkout_pay_page Whether or not we are on the checkout pay page
		 */
		return apply_filters( 'cfw_is_checkout_pay_page', function_exists( 'is_checkout_pay_page' ) && \is_checkout_pay_page() && Main::instance()->get_templates_manager()->get_active_template()->supports( 'order-pay' ) && Main::instance()->get_settings_manager()->is_premium_feature_enabled( 'enable_order_pay' ) );
	}

	public static function is_order_received_page() {
		/**
		 * Filter Main::is_order_received_page()
		 *
		 * @since 3.0.0
		 *
		 * @param bool $is_order_received_page Whether or not we are on the order received page
		 */
		return apply_filters( 'cfw_is_order_received_page', function_exists( 'is_order_received_page' ) && \is_order_received_page() && Main::instance()->get_templates_manager()->get_active_template()->supports( 'order-received' ) && Main::instance()->get_settings_manager()->is_premium_feature_enabled( 'enable_thank_you_page' ) );
	}

	public static function is_cfw_page() {
		return Main::is_checkout() || Main::is_checkout_pay_page() || Main::is_order_received_page();
	}

	/**
	 * Retrieves the URL for a given site where the front end is accessible.
	 *
	 * Returns the 'home' option with the appropriate protocol. The protocol will be 'https'
	 * if is_ssl() evaluates to true; otherwise, it will be the same as the 'home' option.
	 * If `$scheme` is 'http' or 'https', is_ssl() is overridden.
	 *
	 * Copied from WordPress 5.2.0
	 *
	 * @since 3.0.0
	 *
	 * @global string $pagenow
	 *
	 * @param  int         $blog_id Optional. Site ID. Default null (current site).
	 * @param  string      $path    Optional. Path relative to the home URL. Default empty.
	 * @param  string|null $scheme  Optional. Scheme to give the home URL context. Accepts
	 *                              'http', 'https', 'relative', 'rest', or null. Default null.
	 * @return string Home URL link with optional path appended.
	 */
	function get_home_url( $blog_id = null, $path = '', $scheme = null ) {
		global $pagenow;

		$orig_scheme = $scheme;

		if ( empty( $blog_id ) || ! is_multisite() ) {
			$url = get_option( 'home' );
		} else {
			switch_to_blog( $blog_id );
			$url = get_option( 'home' );
			restore_current_blog();
		}

		if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ), true ) ) {
			if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
				$scheme = 'https';
			} else {
				$scheme = parse_url( $url, PHP_URL_SCHEME );
			}
		}

		$url = set_url_scheme( $url, $scheme );

		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		/**
		 * Filters the home URL.
		 *
		 * @since 3.0.0
		 *
		 * @param string      $url         The complete home URL including scheme and path.
		 * @param string      $path        Path relative to the home URL. Blank string if no path is specified.
		 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https',
		 *                                 'relative', 'rest', or null.
		 * @param int|null    $blog_id     Site ID, or null for the current site.
		 */
		return apply_filters( 'cfw_home_url', $url, $path, $orig_scheme, $blog_id );
	}

	/**
	 * @param string $url
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	function override_view_order_url( $url, $order ) {
		$url = $order->get_checkout_order_received_url();
		$url = add_query_arg( 'view', 'true', $url );

		return $url;
	}

	/**
	 * Add a new image size for our cart views
	 */
	function add_cart_image_size() {
		/**
		 * Filter cart thumbnail width
		 *
		 * @since 3.0.0
		 *
		 * @param int $thumb_width The width of thumbnails in cart
		 */
		$cfw_cart_thumb_width = apply_filters( 'cfw_cart_thumb_width', 60 );

		/**
		 * Filter cart thumbnail height
		 *
		 * 0 indicates auto height
		 *
		 * @since 3.0.0
		 *
		 * @param int $thumb_width The height of thumbnails in cart
		 */
		$cfw_cart_thumb_height = apply_filters( 'cfw_cart_thumb_height', 0 );

		/**
		 * Filter whether to crop cart thumbnails
		 *
		 * @since 3.0.0
		 *
		 * @param bool $crop True allows cropping
		 */
		$cfw_crop_cart_thumbs = apply_filters( 'cfw_crop_cart_thumbs', false );

		add_image_size( 'cfw_cart_thumb', $cfw_cart_thumb_width, $cfw_cart_thumb_height, $cfw_crop_cart_thumbs );
	}

	/**
	 * Run PHP snippets
	 */
	function run_php_snippets() {
		if ( is_admin() ) {
			return;
		}

		$php_snippets = $this->prepare_code( $this->get_settings_manager()->get_setting( 'php_snippets' ) );

		if ( empty( $php_snippets ) ) {
			return;
		}

		if ( class_exists( '\\ParseError' ) ) {
			try {
				eval( $php_snippets ); // phpcs:ignore
			} catch( \ParseError $e ) { // phpcs:ignore
				error_log( 'CheckoutWC: Failed to load PHP snippets. Parse Error: ' . $e->getMessage() );
			}
		} else {
			eval( $php_snippets ); // phpcs:ignore
		}
	}

	/**
	 * Prepare the code by removing php tags from beginning and end
	 *
	 * @param string $code
	 *
	 * @return string
	 */
	private function prepare_code( $code ) {

		/* Remove <?php and <? from beginning of snippet */
		$code = preg_replace( '|^[\s]*<\?(php)?|', '', $code );

		/* Remove ?> from end of snippet */
		$code = preg_replace( '|\?>[\s]*$|', '', $code );

		return $code;
	}

	function get_theme_color_settings() {
		$color_settings                          = array();
		$color_settings['body_background_color'] = cfw__( 'Body Background Color', 'checkout-wc' );
		$color_settings['body_text_color']       = cfw__( 'Body Text Color', 'checkout-wc' );
		$color_settings['body_text_color']       = cfw__( 'Body Text Color', 'checkout-wc' );

		if ( $this->get_templates_manager()->get_active_template()->supports( 'header-background' ) ) {
			$color_settings['header_background_color'] = cfw__( 'Header Background Color', 'checkout-wc' );
		}

		$color_settings['header_text_color'] = cfw__( 'Header Text Color', 'checkout-wc' );

		if ( $this->get_templates_manager()->get_active_template()->supports( 'footer-background' ) ) {
			$color_settings['footer_background_color'] = cfw__( 'Footer Background Color', 'checkout-wc' );
		}

		$color_settings['footer_color'] = cfw__( 'Footer Text Color', 'checkout-wc' );

		if ( $this->get_templates_manager()->get_active_template()->supports( 'summary-background' ) ) {
			$color_settings['summary_background_color'] = cfw__( 'Summary Background Color', 'checkout-wc' );
		}

		if ( $this->get_templates_manager()->get_active_template()->supports( 'accent-color' ) ) {
			$color_settings['accent_color'] = cfw__( 'Accent Color', 'checkout-wc' );
		}

		$color_settings['button_color']                      = cfw__( 'Primary Button Background Color', 'checkout-wc' );
		$color_settings['button_text_color']                 = cfw__( 'Primary Button Text Color', 'checkout-wc' );
		$color_settings['button_hover_color']                = cfw__( 'Primary Button Background Hover Color', 'checkout-wc' );
		$color_settings['button_text_hover_color']           = cfw__( 'Primary Button Text Hover Color', 'checkout-wc' );
		$color_settings['secondary_button_color']            = cfw__( 'Secondary Button Background Color', 'checkout-wc' );
		$color_settings['secondary_button_text_color']       = cfw__( 'Secondary Button Text Color', 'checkout-wc' );
		$color_settings['secondary_button_hover_color']      = cfw__( 'Secondary Button Background Hover Color', 'checkout-wc' );
		$color_settings['secondary_button_text_hover_color'] = cfw__( 'Secondary Button Text Hover Color', 'checkout-wc' );
		$color_settings['link_color']                        = cfw__( 'Link Color', 'checkout-wc' );
		$color_settings['cart_item_quantity_color']          = cfw__( 'Cart Item Quantity Background Color', 'checkout-wc' );
		$color_settings['cart_item_quantity_text_color']     = cfw__( 'Cart Item Quantity Text Color', 'checkout-wc' );

		return $color_settings;
	}

	/**
	 * Match new guest order to existing account if it exists
	 *
	 * @param $order_id
	 */
	function maybe_match_new_order_to_user_account( $order_id ) {
		$order = wc_get_order( $order_id );
		$user  = $order->get_user();

		if ( ! $user ) {
			$user_data = get_user_by( 'email', $order->get_billing_email() );

			if ( ! empty( $user_data->ID ) ) {
				try {
					$order->set_customer_id( $user_data->ID );
					$order->save();
				} catch ( \WC_Data_Exception $e ) {
					error_log( "CheckoutWC: Error matching {$order_id} to customer {$user_data->ID}" );
				}
			}
		}
	}

	/**
	 * Match old guest orders to new account if they exist
	 *
	 * @param $user_id
	 */
	function maybe_link_orders_at_registration( $user_id ) {
		wc_update_new_customer_past_orders( $user_id );
	}
}
