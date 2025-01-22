<?php

namespace Objectiv\Plugins\Checkout\Core;

use Objectiv\Plugins\Checkout\Compatibility\Plugins\CartFlows;
use Objectiv\Plugins\Checkout\Main;
use Objectiv\Plugins\Checkout\Stats\StatCollection;

/**
 * Class Admin
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Admin {

	/**
	 * @since 1.0.0
	 * @access public
	 * @var object $plugin_instance The plugin instance
	 */
	public $plugin_instance;

	/**
	 * @since 1.0.0
	 * @access public
	 * @var object $tabs The tabs for the admin navigation
	 */
	public $tabs;

	/**
	 * @var int The number of available activations for the active license key.
	 */
	private $_license_activations;

	/**
	 * Admin constructor.
	 *
	 * @param Main $plugin
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( Main $plugin ) {
		$this->plugin_instance = $plugin;
	}

	/**
	 * Initializes the admin backend
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function run() {
		// Run this as early as we can to maximize integrations
		add_action(
			'plugins_loaded',
			function() {
				// Adds the plugins hooks
				$this->start();
			},
			1
		);
	}

	public function start() {
		// Admin Menu
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );

		// Key Nag
		add_action( 'admin_menu', array( $this, 'add_key_nag' ), 11 );

		// Enqueue Admin Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 1000 );

		// Template disabled notice
		add_action( 'admin_notices', array( $this, 'template_disabled_notice' ) );

		// Admin notice
		add_action( 'admin_notices', array( $this, 'add_notice_key_nag' ) );

		// Compatibility nag
		add_action( 'admin_notices', array( $this, 'add_compatibility_nag' ) );

		// Welcome notice
		add_action( 'admin_notices', array( $this, 'add_welcome_notice' ) );

		// Welcome redirect
		add_action( 'admin_init', array( $this, 'welcome_screen_do_activation_redirect' ) );

		// Add settings link
		add_filter( 'plugin_action_links_' . plugin_basename( CFW_MAIN_FILE ), array( $this, 'add_action_links' ), 10, 1 );

		// Show shipping phone on order editor
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'shipping_phone_display_admin_order_meta' ), 10, 1 );

		// Save shipping phone on order editor
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_shipping_phone' ) );

		// Handle theme activation
		add_action( $this->plugin_instance->get_settings_manager()->prefix . '_settings_saved', array( $this, 'maybe_activate_theme' ) );

		// Maybe do upgrades
		add_action( 'admin_init', array( $this, 'maybe_do_data_upgrades' ) );

		// Add notice about potentially overridden settings
		add_action( 'woocommerce_sections_account', array( $this, 'output_woocommerce_account_settings_notice' ) );

		// Add asterisk to potentially overridden settings
		add_filter( 'woocommerce_get_settings_account', array( $this, 'mark_possibly_overridden_settings' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'maybe_upload_settings' ), 0 );

		// Export Settings.
		add_action( 'wp_ajax_cfw_generate_settings', array( $this, 'generate_settings_export' ) );

		// Compatibility
		$this->compatibility();
	}

	/**
	 * The main admin menu setup
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_menu() {
		// Initiate tab object
		$this->tabs = new \WP_Tabbed_Navigation( '' );

		/**
		 * Filters before admin tabs are added
		 *
		 * @since 2.0.0
		 *
		 * @param \WP_Tabbed_Navigation $tabs The tabbed navigation object
		 */
		do_action( 'cfw_admin_tabs', $this->tabs );

		add_options_page( 'CheckoutWC', 'CheckoutWC', 'manage_options', 'cfw-settings', array( $this, 'admin_page' ) );

		// Setup tabs
		$this->tabs->add_tab( cfw__( 'General', 'checkout-wc' ), menu_page_url( 'cfw-settings', false ), 'general' );
		$this->tabs->add_tab( cfw__( 'Premium Features', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'premium' ), menu_page_url( 'cfw-settings', false ) ), 'premium' );
		$this->tabs->add_tab( cfw__( 'Template', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'templates' ), menu_page_url( 'cfw-settings', false ) ), 'template' );
		$this->tabs->add_tab( cfw__( 'Design', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'design' ), menu_page_url( 'cfw-settings', false ) ), 'design' );
		$this->tabs->add_tab( cfw__( 'Integrations', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'integrations' ), menu_page_url( 'cfw-settings', false ) ), 'integrations' );

		if ( has_filter( 'cfw_admin_addon_tabs' ) ) {
			$this->tabs->add_tab( cfw__( 'Addons', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'addons' ), menu_page_url( 'cfw-settings', false ) ), 'addons' );
		}

		$this->tabs->add_tab( cfw__( 'License', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'license' ), menu_page_url( 'cfw-settings', false ) ), 'license' );
		$this->tabs->add_tab( cfw__( 'Tools', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'tools' ), menu_page_url( 'cfw-settings', false ) ), 'tools' );
		$this->tabs->add_tab( cfw__( 'Support', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'support' ), menu_page_url( 'cfw-settings', false ) ), 'support' );
		$this->tabs->add_tab( cfw__( 'Recommended Plugins', 'checkout-wc' ), add_query_arg( array( 'subpage' => 'recommended_plugins' ), menu_page_url( 'cfw-settings', false ) ), 'recommended-plugins' );
	}

	/**
	 * The admin page wrap
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_page() {
		// Get the current tab function
		$current_tab_function = $this->get_current_tab() === false ? 'general_tab' : $this->get_current_tab() . '_tab';

		// Get the object to call the added tab on
		/**
		 * Filters callable for admin tab
		 *
		 * @since 2.0.0
		 *
		 * @param callable $tab_function The callable to render a tab
		 * @param string $current_tab The current tab
		 */
		$callable = apply_filters( 'cfw_active_admin_settings_tab_function', array( $this, $current_tab_function ), $current_tab_function );
		?>
		<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
		<script type="text/javascript">window.Beacon('init', '355a5a54-eb9d-4b64-ac5f-39c95644ad36')</script>
		<div class="wrap about-wrap" style="margin-left:2px;">

			<h1>Checkout for WooCommerce</h1>
			<p class="about-text"><?php cfw_e( 'Checkout for WooCommerce provides beautiful, conversion optimized checkout templates for WooCommerce.', 'checkout-wc' ); ?></p>
		</div>

		<div class="wrap">
			<?php $this->tabs->display_tabs(); ?>

			<?php is_callable( $callable ) ? call_user_func( $callable ) : null; ?>
		</div>
		<?php
	}

	/**
	 * The general tab
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function general_tab() {
		$login_style                   = $this->plugin_instance->get_settings_manager()->get_setting( 'login_style' );
		$registration_style            = $this->plugin_instance->get_settings_manager()->get_setting( 'registration_style' );
		$label_style                   = $this->plugin_instance->get_settings_manager()->get_setting( 'label_style' );
		$cart_item_link                = $this->plugin_instance->get_settings_manager()->get_setting( 'cart_item_link' );
		$cart_item_data_display        = $this->plugin_instance->get_settings_manager()->get_setting( 'cart_item_data_display' );
		$login_style_enable            = ! has_filter( 'cfw_suppress_default_login_form' );
		$registration_style_enable     = ! has_filter( 'cfw_registration_generate_password' );
		$cart_item_data_display_enable = ! has_filter( 'cfw_cart_item_data_expanded' );
		$order_notes_enable            = ! has_filter( 'woocommerce_enable_order_notes_field' ) || ( $this->plugin_instance->get_settings_manager()->get_setting( 'enable_order_notes' ) === 'yes' && 1 === $this->count_filters( 'woocommerce_enable_order_notes_field' ) );

		$order_notes_notice_replacement_text = '';

		if ( ! $order_notes_enable && defined( 'WC_CHECKOUT_FIELD_EDITOR_VERSION' ) ) {
			$order_notes_notice_replacement_text = cfw__( 'This setting is overridden by WooCommerce Checkout Field Editor.', 'checkout-wc' );
		}
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<?php $this->plugin_instance->get_settings_manager()->the_nonce(); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable' ); ?>"><?php cfw_e( 'Enable / Disable', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable' ); ?>" value="no" />
							<label><input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable' ); ?>" id="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable' ); ?>" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Use Checkout for WooCommerce Template', 'checkout-wc' ); ?></label>
							<p><span class="description"><?php cfw_e( 'Enable or disable Checkout for WooCommerce theme. (NOTE: Theme is always enabled for admin users.)', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'login_style' ); ?>"><?php cfw_e( 'Login Style', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input <?php echo ! $login_style_enable ? 'disabled' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'login_style' ); ?>" value="enhanced" <?php echo 'enhanced' === $login_style || empty( $login_style ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Enhanced (Recommended)', 'checkout-wc' ); ?><br />
								</label>
								<label>
									<input <?php echo ! $login_style_enable ? 'disabled' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'login_style' ); ?>" value="woocommerce" <?php echo 'woocommerce' === $login_style ? 'checked' : ''; ?> /> <?php cfw_e( 'WooCommerce Default', 'checkout-wc' ); ?>
								</label>
							</p>
							<p class="description">
								<?php cfw_e( 'Enhanced: Automatically show and hide login fields depending on whether the entered email address matches an account. (Recommended)', 'checkout-wc' ); ?>
							</p>
							<p class="description">
								<?php cfw_e( 'WooCommerce Default: Show a login reminder in a banner above the checkout form.', 'checkout-wc' ); ?>
							</p>
							<?php $this->maybe_show_overridden_setting_notice( false === $login_style_enable ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'registration_style' ); ?>"><?php cfw_e( 'Registration Style', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input <?php echo ! $registration_style_enable ? 'disabled' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'registration_style' ); ?>" value="enhanced" <?php echo ( 'enhanced' === $registration_style || empty( $registration_style ) ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Enhanced (Recommended)', 'checkout-wc' ); ?><br />
								</label>
								<label>
									<input <?php echo ! $registration_style_enable ? 'disabled' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'registration_style' ); ?>" value="woocommerce" <?php echo ( 'woocommerce' === $registration_style ) ? 'checked' : ''; ?> /> <?php cfw_e( 'WooCommerce Default', 'checkout-wc' ); ?>
								</label>
							</p>
							<p class="description">
								<?php cfw_e( 'Enhanced: Automatically generate a username and password and email it to the customer using the native WooCommerce functionality. (Recommended)', 'checkout-wc' ); ?>
							</p>
							<p class="description">
								<?php cfw_e( 'WooCommerce Default: A password field is provided for the customer to select their own password. Not recommended.', 'checkout-wc' ); ?>
							</p>
							<?php $this->maybe_show_overridden_setting_notice( false === $registration_style_enable ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'label_style' ); ?>"><?php cfw_e( 'Field Label Style', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'label_style' ); ?>" value="floating" <?php echo ( 'floating' === $label_style || empty( $label_style ) ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Floating (Recommended)', 'checkout-wc' ); ?><br />
								</label>
								<label>
									<input type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'label_style' ); ?>" value="normal" <?php echo 'normal' === $label_style ? 'checked' : ''; ?> /> <?php cfw_e( 'Normal', 'checkout-wc' ); ?>
								</label>
							</p>
							<p class="description">
								<?php cfw_e( 'Floating: Automatically show and hide labels based on whether the field has a value. (Recommended)', 'checkout-wc' ); ?>
							</p>
							<p class="description">
								<?php cfw_e( 'Normal: Labels appear above each field at all times.', 'checkout-wc' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_item_link' ); ?>"><?php cfw_e( 'Cart Item Label', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_item_link' ); ?>" value="disabled" <?php echo ( 'disabled' === $cart_item_link || empty( $cart_item_link ) ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Text (Recommended)', 'checkout-wc' ); ?><br />
								</label>
								<label>
									<input type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_item_link' ); ?>" value="enabled" <?php echo 'enabled' === $cart_item_link ? 'checked' : ''; ?> /> <?php cfw_e( 'Link', 'checkout-wc' ); ?>
								</label>
							</p>
							<p class="description">
								<?php cfw_e( 'Text: Do not link cart items to single product page. (Recommended)', 'checkout-wc' ); ?>
							</p>
							<p class="description">
								<?php cfw_e( 'Link: Link each cart item to product page.', 'checkout-wc' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_item_data_display' ); ?>"><?php cfw_e( 'Cart Item Data Display', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input <?php echo ! $cart_item_data_display_enable ? 'disabled' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_item_data_display' ); ?>" value="short" <?php echo ( 'short' === $cart_item_data_display || empty( $cart_item_data_display ) ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Short (Recommended)', 'checkout-wc' ); ?><br />
								</label>
								<label>
									<input <?php echo ! $cart_item_data_display_enable ? 'disabled' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_item_data_display' ); ?>" value="woocommerce" <?php echo 'woocommerce' === $cart_item_data_display ? 'checked' : ''; ?> /> <?php cfw_e( 'WooCommerce Default', 'checkout-wc' ); ?>
								</label>
							</p>
							<p class="description">
								<?php cfw_e( 'Short displays only variation values. For example, Size: XL, Color: Red is displayed as XL / Red. (Recommended)', 'checkout-wc' ); ?>
							</p>
							<p class="description">
								<?php cfw_e( 'When using WooCommerce Default, each variation is displayed on a separate line using this format: Label: Value ', 'checkout-wc' ); ?>
							</p>
							<?php $this->maybe_show_overridden_setting_notice( false === $cart_item_data_display_enable ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'skip_shipping_step' ); ?>"><?php cfw_e( 'Skip Shipping Step', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'skip_shipping_step' ); ?>" value="no" />
							<label><input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'skip_shipping_step' ); ?>" id="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'skip_shipping_step' ); ?>" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'skip_shipping_step' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Skip shipping step', 'checkout-wc' ); ?></label>
							<p><span class="description"><?php cfw_e( 'Enable to hide the shipping method step. Useful if you only have one shipping option for all orders.', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_notes' ); ?>"><?php cfw_e( 'Order Notes', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input <?php echo ! $order_notes_enable ? 'disabled' : ''; ?> type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_notes' ); ?>" value="no" />
							<label>
								<input <?php echo ! $order_notes_enable ? 'disabled' : ''; ?> type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_notes' ); ?>" id="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_notes' ); ?>" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_order_notes' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable Order Notes field', 'checkout-wc' ); ?>
							</label>

							<p><span class="description"><?php cfw_e( 'Enable or disable order notes field. Disabled by default.', 'checkout-wc' ); ?></span></p>

							<?php $this->maybe_show_overridden_setting_notice( false === $order_notes_enable, $order_notes_notice_replacement_text ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_coupon_code_link' ); ?>"><?php cfw_e( 'Enable Coupon Code Link', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_coupon_code_link' ); ?>" value="no" />
							<label><input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_coupon_code_link' ); ?>" id="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_coupon_code_link' ); ?>" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_coupon_code_link' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable link to reveal coupon code field.', 'checkout-wc' ); ?></label>
							<p><span class="description"><?php cfw_e( 'Initially hide coupon code field until link is clicked.', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_mobile_coupon_field' ); ?>"><?php cfw_e( 'Mobile Coupon Field', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_mobile_coupon_field' ); ?>" value="no" />
							<label><input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_mobile_coupon_field' ); ?>" id="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_mobile_coupon_field' ); ?>" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'show_mobile_coupon_field' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Show coupon field above payment options on mobile.', 'checkout-wc' ); ?></label>
							<p><span class="description"><?php cfw_e( 'Show coupon field above payment gateways on mobile devices. Helps customers find the coupon field without expanding the cart summary.', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_logos_mobile' ); ?>"><?php cfw_e( 'Mobile Credit Card Logos', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_logos_mobile' ); ?>" value="no" />
							<label><input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_logos_mobile' ); ?>" id="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'show_logos_mobile' ); ?>" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'show_logos_mobile' ) === 'yes' ? 'checked' : ''; ?> /> <?php cfw_e( 'Enable credit card logos on mobile', 'checkout-wc' ); ?></label>
							<p><span class="description"><?php cfw_e( 'Show the credit card logos on mobile. Note: Many gateway logos cannot be rendered properly on mobile. It is recommended you test before enabling. Default: Off', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_summary_mobile_label' ) ); ?>"><?php cfw_e( 'Cart Summary Mobile Label', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="text" value="<?php echo esc_attr( $this->plugin_instance->get_settings_manager()->get_setting( 'cart_summary_mobile_label' ) ); ?>" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_summary_mobile_label' ); ?>" />
							<p>
								<span class="description">
									<?php cfw_e( 'Example: Show order summary and coupons', 'checkout-wc' ); ?><br />
									<?php cfw_e( 'If left blank, this default will be used: ', 'checkout-wc' ); ?><?php cfw_e( 'Show order summary', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'header_scripts' ); ?>"><?php cfw_e( 'Header Scripts', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<?php
							wp_editor(
								stripslashes_deep( $this->plugin_instance->get_settings_manager()->get_setting( 'header_scripts' ) ),
								sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'header_scripts' ) ),
								array(
									'textarea_rows' => 6,
									'quicktags'     => false,
									'media_buttons' => false,
									'textarea_name' => $this->plugin_instance->get_settings_manager()->get_field_name( 'header_scripts' ),
									'tinymce'       => false,
								)
							);
							?>
							<p>
								<span class="description">
									<?php cfw_e( 'This code will output immediately before the closing <code>&lt;/head&gt;</code> tag in the document source.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'footer_scripts' ); ?>"><?php cfw_e( 'Footer Scripts', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<?php
							wp_editor(
								stripslashes_deep( $this->plugin_instance->get_settings_manager()->get_setting( 'footer_scripts' ) ),
								sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'footer_scripts' ) ),
								array(
									'textarea_rows' => 6,
									'quicktags'     => false,
									'media_buttons' => false,
									'textarea_name' => $this->plugin_instance->get_settings_manager()->get_field_name( 'footer_scripts' ),
									'tinymce'       => false,
								)
							);
							?>
							<p>
								<span class="description">
									<?php cfw_e( 'This code will output immediately before the closing <code>&lt;/body&gt;</code> tag in the document source.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<tr>
						<?php
							$tracking_field_name = $this->plugin_instance->get_settings_manager()->get_field_name( 'allow_tracking' );
							$tracking_value      = $this->plugin_instance->get_settings_manager()->get_setting( 'allow_tracking' );
						?>
						<th scope="row" valign="top">
							<label for="<?php echo $tracking_field_name; ?>"><?php cfw_e( 'Enable Usage Tracking', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $tracking_field_name; ?>" value="0" />
							<label for="<?php echo $tracking_field_name; ?>">
								<input type="checkbox" name="<?php echo $tracking_field_name; ?>" id="<?php echo $tracking_field_name; ?>" value="<?php echo md5( trailingslashit( home_url() ) ); ?>" <?php echo md5( trailingslashit( home_url() ) ) === $tracking_value ? 'checked' : ''; ?> />
								<?php cfw_e( 'Allow Checkout for WooCommerce to track plugin usage?', 'checkout-wc' ); ?>
							</label>
						</td>
					</tr>

					<?php
					if ( defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE ) :
						$stats = $this->plugin_instance->get_stat_collection();
						$stats->setup_data();

						if ( function_exists( 'd' ) ) {
							d( $stats->get_data() );
						}
						?>
						<tr>
							<th scope="row" valign="top">
								<label for="#cfw-stat-collection-testing"><?php cfw_e( 'Stat Collection Data Viewer', 'checkout-wc' ); ?></label>
							</th>
							<td>
								<?php submit_button( 'Force Check-in', 'button', 'force-checkin' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	function integrations_tab() {
		$template_loader = $this->plugin_instance->get_settings_manager()->get_setting( 'template_loader' );
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<?php $this->plugin_instance->get_settings_manager()->the_nonce(); ?>
			<table class="form-table">
				<tbody>
					<?php
					/**
					 * Fires at top of Settings > CheckoutWC > Integrations
					 *
					 * Use to add additional integration settings
					 *
					 * @since 3.0.0
					 *
					 * @param Admin $admin The admin class
					 */
					do_action( 'cfw_admin_integrations_settings', $this );
					?>
				</tbody>
			</table>

			<h3><?php cfw_e( 'Experimental Integrations', 'checkout-wc' ); ?></h3>
			<p><?php cfw_e( 'Experimental integrations are not supported and do not go through the same testing processes as our recommended configurations.', 'checkout-wc' ); ?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'template_loader' ); ?>"><?php cfw_e( 'Template Loader', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'template_loader' ); ?>" value="redirect" <?php echo ( 'redirect' === $template_loader || empty( $template_loader ) ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Distraction Free Portal (Recommended)', 'checkout-wc' ); ?><br />
								</label>
								<label>
									<input type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'template_loader' ); ?>" value="content" <?php echo 'content' === $template_loader ? 'checked' : ''; ?> /> <?php cfw_e( 'WordPress Theme', 'checkout-wc' ); ?>
								</label>
							</p>
							<p class="description">
								<?php cfw_e( 'Distraction Free Portal: Display CheckoutWC templates in a distraction free portal which does not load the active WordPress theme or styles. (Recommended)', 'checkout-wc' ); ?>
							</p>
							<p class="description">
								<?php cfw_e( 'WordPress Theme: Load CheckoutWC templates within active WordPress theme content area.', 'checkout-wc' ); ?> (<span style="color:red"><?php cfw_e( 'Experimental - Unsupported Configuration', 'checkout-wc' ); ?></span>)
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	function premium_tab() {
		$user_matching            = $this->plugin_instance->get_settings_manager()->get_setting( 'user_matching' );
		$thank_you_order_statuses = false === $this->plugin_instance->get_settings_manager()->get_setting( 'thank_you_order_statuses' ) ? array() : $this->plugin_instance->get_settings_manager()->get_setting( 'thank_you_order_statuses' );

		$this->maybe_display_upgrade_required_notice();
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<?php $this->plugin_instance->get_settings_manager()->the_nonce(); ?>
			<table class="form-table">
				<tbody>
					<!--- Cart Editing -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_cart_editing"><?php cfw_e( 'Cart Editing', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_cart_editing' ); ?>" value="no" />
							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_cart_editing' ); ?>" id="enable_cart_editing" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_cart_editing' ) === 'yes' ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable cart editing.', 'checkout-wc' ); ?>
							</label>

							<p><span class="description"><?php cfw_e( 'Enable or disable cart editing feature. Allows customer to remove or adjust quantity of cart items.', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_edit_empty_cart_redirect' ) ); ?>"><?php cfw_e( 'Cart Editing Empty Cart Redirect', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="text" size="40" id="cart_edit_empty_cart_redirect" value="<?php echo esc_attr( $this->plugin_instance->get_settings_manager()->get_setting( 'cart_edit_empty_cart_redirect' ) ); ?>" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'cart_edit_empty_cart_redirect' ); ?>" />
							<p>
								<span class="description">
									<?php cfw_e( 'URL to redirect to when customer empties cart from checkout page.', 'checkout-wc' ); ?><br />
									<?php cfw_e( 'If left blank, customer will be redirected to the cart page.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!-- Order Pay -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_order_pay"><?php cfw_e( 'Order Pay', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_pay' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_pay' ); ?>" id="enable_order_pay" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_order_pay' ) === 'yes' ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable support for order pay page.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'Use checkout template for order pay page.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!-- One Page Checkout -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_order_pay"><?php cfw_e( 'One Page Checkout', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_one_page_checkout' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_one_page_checkout' ); ?>" id="enable_one_page_checkout" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_one_page_checkout' ) === 'yes' ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable one page checkout.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'Show all checkout fields on one tab. Useful for digital stores or stores that force the billing and shipping address to be the same. (Cannot be used with Order Review Step)', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!-- Thank You Page -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_thank_you_page"><?php cfw_e( 'Thank You Page', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_thank_you_page' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_thank_you_page' ); ?>" id="enable_thank_you_page" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_thank_you_page' ) === 'yes' ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable support for thank you page.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'Enable thank you page / order received template.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!--- Order Statuses -->
					<tr>
						<th scope="row" valign="top">
							<label for="thank_you_order_statuses"><?php cfw_e( 'Order Statuses', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'thank_you_order_statuses' ); ?>" value="no" />
							<label>
								<select multiple class="wc-enhanced-select" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'thank_you_order_statuses' ); ?>[]" id="thank_you_order_statuses" <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> >
									<?php if ( is_array( wc_get_order_statuses() ) ) : ?>
										<?php foreach ( wc_get_order_statuses() as $key => $status ) : ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( is_array( $thank_you_order_statuses ) && in_array( $key, $thank_you_order_statuses, true ) ) ? 'selected' : ''; ?> >
												<?php echo esc_html( $status ); ?>
											</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</label>
							<p><span class="description"><?php cfw_e( 'The statuses to show on the thank you page.', 'checkout-wc' ); ?></span></p>
						</td>
					</tr>

					<!--- Map Embed -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_map_embed"><?php cfw_e( 'Map Embed', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_map_embed' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_map_embed' ); ?>" id="enable_map_embed" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'enable_map_embed' ) === 'yes' ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable map embed.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'Enable or disable map embed on thank you page. Requires Google API key.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!--- My Account View Order -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_map_embed"><?php cfw_e( 'My Account', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'override_view_order_template' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'override_view_order_template' ); ?>" id="override_view_order_template" value="yes" <?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'override_view_order_template' ) === 'yes' ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Use thank you template for viewing orders in My Account.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'When checked, viewing orders in My Account will use the thank you page template.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!--- Address Autocomplete -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_address_autocomplete"><?php cfw_e( 'Address Autocomplete', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_address_autocomplete' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_address_autocomplete' ); ?>" id="enable_address_autocomplete" value="yes" <?php echo ( $this->plugin_instance->get_settings_manager()->get_setting( 'enable_address_autocomplete' ) === 'yes' ) ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable address autocomplete.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'Enable or disable address autocomplete feature. Requires Google API key.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'google_places_api_key' ) ); ?>"><?php cfw_e( 'Google API Key', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="text" size="40" id="google_places_api_key" value="<?php echo esc_attr( $this->plugin_instance->get_settings_manager()->get_setting( 'google_places_api_key' ) ); ?>" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'google_places_api_key' ); ?>" />
							<p>
								<span class="description">
									<?php cfw_e( 'Google API Key. Available in the <a target="_blank" href="https://developers.google.com/places/web-service/get-api-key">Google Cloud Platform Console</a>.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<!-- User Matching -->
					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'user_matching' ); ?>"><?php cfw_e( 'User Matching', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<p>
								<label>
									<input <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'user_matching' ); ?>" value="enabled" <?php echo ( 'enabled' === $user_matching || empty( $user_matching ) ) ? 'checked' : ''; ?> /> <?php cfw_e( 'Enabled (Recommended)', 'checkout-wc' ); ?><br />
								</label>

								<label>
									<input <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> type="radio" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'user_matching' ); ?>" value="woocommerce" <?php echo ( 'woocommerce' === $user_matching ) ? 'checked' : ''; ?> /> <?php cfw_e( 'WooCommerce Default', 'checkout-wc' ); ?>
								</label>
							</p>

							<p class="description">
								<?php cfw_e( 'Enabled: Automatically matches guest orders to user accounts on new purchase as well as on registration of a new user. (Recommended)', 'checkout-wc' ); ?>
							</p>

							<p class="description">
								<?php cfw_e( 'WooCommerce Default: Guest orders will not be linked to matching accounts.', 'checkout-wc' ); ?>
							</p>
						</td>
					</tr>

					<!-- Order Review Tab -->
					<tr>
						<th scope="row" valign="top">
							<label for="enable_order_review_step"><?php cfw_e( 'Order Review Step', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_review_step' ); ?>" value="no" />

							<label>
								<input type="checkbox" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'enable_order_review_step' ); ?>" id="enable_order_review_step" value="yes" <?php echo ( $this->plugin_instance->get_settings_manager()->get_setting( 'enable_order_review_step' ) === 'yes' ) ? 'checked' : ''; ?> <?php echo $this->_license_activations < 5 ? 'disabled="disabled"' : ''; ?> /> <?php cfw_e( 'Enable Order Review Step.', 'checkout-wc' ); ?>
							</label>

							<p>
								<span class="description">
									<?php cfw_e( 'Adds a review step after payment information before finalizing order. Useful for jurisdictions which require additional confirmation before order submission. (Cannot be used with One Page Checkout)', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'php_snippets' ) ); ?>"><?php cfw_e( 'PHP Snippets', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<?php if ( $this->_license_activations < 5 ) : ?>
							<div id="cfw_textarea_placeholder"></div>
							<?php else : ?>
								<?php
								wp_editor(
									$this->plugin_instance->get_settings_manager()->get_setting( 'php_snippets' ),
									sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'php_snippets' ) ),
									array(
										'textarea_rows' => 10,
										'textarea_name' => $this->plugin_instance->get_settings_manager()->get_field_name( 'php_snippets' ),
										'tinymce'       => false,
										'media_buttons' => false,
										'quicktags'     => false,
									)
								);
								?>
							<?php endif; ?>
							<p>
								<span class="description">
									<?php echo sprintf( cfw__( 'Add PHP snippets to modify your checkout page here. If you have lots of snippets, you may want to consider using <a target="_blank" href="%s">Code Snippets</a>.', 'checkout-wc' ), 'https://wordpress.org/plugins/code-snippets/' ); ?>
								</span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	function maybe_display_upgrade_required_notice( $min_activations = 5 ) {
		if ( $this->_license_activations < $min_activations ) :
			?>
			<div class='cfw-notification-message'>
				<strong><?php cfw_e( 'License Upgrade Required', 'checkout-wc' ); ?></strong> &mdash; <?php cfw_e( 'A Growth or Developer License is required to enable these features.', 'checkout-wc' ); ?>
				<?php echo sprintf( cfw__( 'You can upgrade your license in <a target="_blank" href="%1$s">My Account</a>. For help upgrading your license, <a target="_blank" href="%2$s">click here.</a>', 'checkout-wc' ), 'https://www.checkoutwc.com/account/', 'https://kb.checkoutwc.com/article/53-upgrading-your-license' ); ?>
			</div>
			<?php
		endif;
	}

	function maybe_show_overridden_setting_notice( $show = false, $replacement_text = '' ) {
		if ( ! $show ) {
			return;
		}
		?>
		<div class='cfw-notification-message'>
			<strong><?php cfw_e( 'Setting Overridden', 'checkout-wc' ); ?></strong> &mdash;

			<?php if ( empty( $replacement_text ) ) : ?>
				<?php cfw_e( 'This setting is currently programmatically overridden. To enable it remove your custom code.', 'checkout-wc' ); ?>
			<?php else : ?>
				<?php echo $replacement_text; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * The template tab
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function templates_tab() {
		$templates       = $this->plugin_instance->get_templates_manager()->get_available_templates();
		$active_template = $this->plugin_instance->get_settings_manager()->get_setting( 'active_template' );
		?>
		<h3><?php cfw_e( 'Templates', 'checkout-wc' ); ?></h3>

		<div class="theme-browser">
			<div class="themes wp-clearfix">
				<?php
				foreach ( $templates as $template ) :
					$screenshot = $template->get_template_uri() . '/screenshot.png';

					$active = ( $active_template === $template->get_slug() );
					?>
					<?php add_thickbox(); ?>
					<div class="theme <?php echo $active ? 'active' : ''; ?>">
						<div class="theme-screenshot">
							<a href="#TB_inline?width=1200&height=900&inlineId=theme-preview-<?php echo $template->get_slug(); ?>" class="thickbox">
								<img class="theme-screenshot-img" src="<?php echo $screenshot; ?>" />
							</a>
							<div id="theme-preview-<?php echo $template->get_slug(); ?>" style="display:none;">
								<img src="<?php echo $screenshot; ?>" />
							</div>
						</div>
						<div class="theme-id-container">

							<h2 class="theme-name" id="<?php echo $template->get_slug(); ?>-name">
								<strong>
									<?php echo $active ? cfw__( 'Active: ' ) : ''; ?>
								</strong>

								<?php echo $template->get_name(); ?>
							</h2>

							<?php if ( ! $active ) : ?>
								<div class="theme-actions">
									<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
										<input type="hidden" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'active_template' ); ?>" value="<?php echo $template->get_slug(); ?>" />
										<?php $this->plugin_instance->get_settings_manager()->the_nonce(); ?>
										<?php submit_button( cfw__( 'Activate', 'checkout-wc' ), 'button-secondary', $name = 'submit', $wrap = false ); ?>
									</form>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * The design tab
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function design_tab() {
		global $cfw_google_fonts_list;
		$current_body_font    = $this->plugin_instance->get_settings_manager()->get_setting( 'body_font' );
		$current_heading_font = $this->plugin_instance->get_settings_manager()->get_setting( 'heading_font' );
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<?php $this->plugin_instance->get_settings_manager()->the_nonce(); ?>

			<h2 class="is-section-heading"><?php cfw_e( 'Global Settings', 'checkout-wc' ); ?></h2>
			<p><?php cfw_e( 'These settings apply to all themes.', 'checkout-wc' ); ?></p>
			<hr>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<?php cfw_e( 'Logo', 'checkout-wc' ); ?>
						</th>
						<td>
							<div class='image-preview-wrapper'>
								<img id='image-preview' src='<?php echo wp_get_attachment_url( $this->plugin_instance->get_settings_manager()->get_setting( 'logo_attachment_id' ) ); ?>' width='100' style='max-height: 100px; width: 100px;'>
							</div>
							<input id="upload_image_button" type="button" class="button" value="<?php cfw_e( 'Upload image' ); ?>" />
							<input type='hidden' name='<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'logo_attachment_id' ); ?>' id='logo_attachment_id' value="<?php echo $this->plugin_instance->get_settings_manager()->get_setting( 'logo_attachment_id' ); ?>">

							<a class="delete-custom-img button secondary-button"><?php cfw_e( 'Clear Logo', 'checkout-wc' ); ?></a>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'footer_text' ) ); ?>"><?php cfw_e( 'Footer Text', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<?php
							wp_editor(
								$this->plugin_instance->get_settings_manager()->get_setting( 'footer_text' ),
								sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'footer_text' ) ),
								array(
									'textarea_rows' => 5,
									'textarea_name' => $this->plugin_instance->get_settings_manager()->get_field_name( 'footer_text' ),
									'tinymce'       => true,
								)
							);
							?>
							<p>
								<span class="description">
									<?php cfw_e( 'If left blank, a standard copyright notice will be displayed. Set to a single space to override this behavior.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<h2 class="is-section-heading"><?php cfw_e( 'Theme Specific Settings', 'checkout-wc' ); ?></h2>
			<p>
				<?php echo sprintf( cfw__( 'These settings apply to your selected theme. (%s)', 'checkout-wc' ), $this->plugin_instance->get_templates_manager()->get_active_template()->get_name() ); ?>
			</p>

			<hr />

			<?php $template_path = $this->plugin_instance->get_templates_manager()->get_active_template()->get_slug(); ?>

			<h3><?php cfw_e( 'Fonts', 'checkout-wc' ); ?></h3>

			<table class="form-table template-settings template-<?php echo $template_path; ?>" style="margin-bottom: 3em">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'body_font' ) ); ?>"><?php cfw_e( 'Body Font', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<select id="cfw-body-font-selector" class="wc-enhanced-select" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'body_font' ); ?>">
								<option value="System Font Stack"><?php cfw_e( 'System Font Stack', 'checkout-wc' ); ?></option>
								<?php foreach ( $cfw_google_fonts_list->items as $font ) : ?>
									<option value="<?php echo $font->family; ?>"<?php echo $font->family === $current_body_font ? 'selected="selected"' : ''; ?> >
										<?php echo $font->family; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'heading_font' ) ); ?>"><?php cfw_e( 'Heading Font', 'checkout-wc' ); ?></label>
						</th>
						<td>
							<select id="cfw-heading-font-selector" class="wc-enhanced-select" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'heading_font' ); ?>">
								<option value="System Font Stack"><?php cfw_e( 'System Font Stack', 'checkout-wc' ); ?></option>

								<?php foreach ( $cfw_google_fonts_list->items as $font ) : ?>
									<option value="<?php echo $font->family; ?>" <?php echo $font->family === $current_heading_font ? 'selected="selected"' : ''; ?> >
										<?php echo $font->family; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="padding: 0;">
							<p>
								<span class="description">
									<?php cfw_e( 'By default, CheckoutWC uses a System Font Stack, which yields the fastest performance. You may choose to use a Google Font here.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<h3><?php cfw_e( 'Color Settings', 'checkout-wc' ); ?></h3>

			<table class="form-table template-settings template-<?php echo $template_path; ?>">
				<tbody>
					<?php foreach ( $this->plugin_instance->get_theme_color_settings() as $key => $label ) : ?>
						<?php
						$saved_value   = $this->plugin_instance->get_settings_manager()->get_setting( $key, array( $template_path ) );
						$default_value = $this->plugin_instance->get_templates_manager()->get_active_template()->get_default_setting( $key );
						?>
						<tr>
							<th scope="row" valign="top">
								<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( $key, array( $template_path ) ); ?>"><?php echo $label; ?></label>
							</th>
							<td>
								<input class="color-picker" type="text" name="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( $key, array( $template_path ) ); ?>" value="<?php echo empty( $saved_value ) ? $default_value : $saved_value; ?>" data-default-color="<?php echo $default_value; ?>" />
							</td>
						</tr>
					<?php endforeach; ?>

					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->plugin_instance->get_settings_manager()->get_field_name( 'custom_css', array( $template_path ) ); ?>"><?php cfw_e( 'Custom CSS', 'checkout-wc' ); ?></label>
						</th>
						<td id="cfw_css_editor">
							<?php
							wp_editor(
								$this->plugin_instance->get_settings_manager()->get_setting( 'custom_css', array( $template_path ) ),
								sanitize_title_with_dashes( $this->plugin_instance->get_settings_manager()->get_field_name( 'custom_css', array( $template_path ) ) ),
								array(
									'textarea_rows' => 5,
									'quicktags'     => false,
									'media_buttons' => false,
									'textarea_name' => $this->plugin_instance->get_settings_manager()->get_field_name( 'custom_css', array( $template_path ) ),
									'tinymce'       => false,
								)
							);
							?>
							<p>
								<span class="description">
									<?php cfw_e( 'Add Custom CSS rules to fully control the appearance of the checkout template.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	function addons_tab() {
		/**
		 * Filters an array of addon tabs
		 *
		 * 'foo' => array(
		 *      'name'    => 'Foo',
		 *      'function => callable,
		 * )
		 *
		 * @since 3.0.0
		 *
		 * @param array $addon_tabs The addon tabs
		 */
		$addon_tabs        = apply_filters( 'cfw_admin_addon_tabs', array() );
		$current_addon_tab = isset( $_GET['addontab'] ) ? esc_attr( $_GET['addontab'] ) : key( $addon_tabs );
		$callable          = $addon_tabs[ $current_addon_tab ]['function'];

		$array_keys = array_keys( $addon_tabs );

		if ( empty( $addon_tabs ) ) {
			return;
		}
		?>
		<div class="wrap">
			<ul class="subsubsub">
				<?php foreach ( $addon_tabs as $id => $addon_tab ) : ?>
					<li>
						<a href="<?php echo add_query_arg( array( 'addontab' => $id ) ); ?>" class="<?php echo $id === $current_addon_tab ? 'current' : ''; ?>">
							<?php echo $addon_tab['name']; ?>
						</a>

						<?php echo ( end( $array_keys ) ) !== $id ? '|' : ''; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<br class="clear">

			<?php is_callable( $callable ) ? call_user_func( $callable ) : null; ?>
		</div>
		<?php
	}

	/**
	 * The Import/Export tab
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function tools_tab() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<?php cfw_e( 'Export Settings', 'checkout-wc' ); ?>
						</th>
						<td>
							<input id="export_settings_button" type="button" class="button" data-nonce="<?php echo esc_attr( wp_create_nonce( '_cfw__export_settings' ) ); ?>" value="<?php cfw_e( 'Export Settings', 'checkout-wc' ); ?>" />
							<p>
								<span class="description">
									<?php cfw_e( 'Download a backup file of your settings.', 'checkout-wc' ); ?>
								</span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<hr />

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<?php cfw_e( 'Import Settings', 'checkout-wc' ); ?>
						</th>
						<td>
							<input name="uploaded_settings" type="file" class="" value="<?php cfw_e( 'Import Settings', 'checkout-wc' ); ?>" />
							<?php wp_nonce_field( 'import_cfw_settings_nonce' ); ?>
							<p style="margin-top: 1em">
								<input id="import_settings_button" type="submit" class="button" name="import_cfw_settings" value="<?php cfw_e( 'Upload File and Import Settings', 'checkout-wc' ); ?>" />
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
	}

	/**
	 * The license tab
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function license_tab() {
		$this->plugin_instance->get_updater()->admin_page();
	}

	/**
	 * The support tab
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function support_tab() {
		?>
		<h3><?php cfw_e( 'Before Contacting Support', 'checkout-wc' ); ?></h3>

		<p><?php cfw_e( 'You can find the answer to most questions in our <a href="https://kb.checkoutwc.com" target="_blank">knowledge base</a>.', 'checkout-wc' ); ?></p>
		<p><?php cfw_e( 'Here are some popular guides:', 'checkout-wc' ); ?></p>
		<ul>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/35-getting-started">Getting Started</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/36-troubleshooting">Troubleshooting</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/53-upgrading-your-license">Upgrading Your License</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/69-how-to-enable-billing-and-shipping-phone-fields">How To Enable Billing and Shipping Phone Fields</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/70-how-to-enable-cart-editing">How To Enable Cart Editing</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/86-how-to-get-and-configure-your-google-api-key">How To Register and Configure Your Google API Key</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/49-how-to-add-a-custom-field">How To Add a Custom Field to Checkout for WooCommerce</a></li>
			<li><a target="_blank" href="https://kb.checkoutwc.com/article/34-how-to-enable-the-woocommerce-notes-field">How to Enable The WooCommerce Notes Field</a></li>
		</ul>

		<h3><?php cfw_e( 'Still Need Help?', 'checkout-wc' ); ?></h3>
		<p><?php cfw_e( 'If you still need help after searching our knowledge base, we would be happy to assist you.', 'checkout-wc' ); ?></p>

		<?php submit_button( cfw__( 'Contact Support', 'checkout-wc' ), 'primary', false, false, array( 'id' => 'checkoutwc-support-button' ) ); ?>

		<script>
			jQuery("#checkoutwc-support-button").click(function() {
				Beacon("open");
			});
		</script>
		<?php
	}

	/**
	 * The recommended plugins tab
	 *
	 * @since 2.10.0
	 * @access public
	 */
	public function recommended_plugins_tab() {
		$plugins   = array();
		$plugins[] = array(
			'slug'        => 'paypal-for-woocommerce',
			'url'         => 'https://www.angelleye.com/product/woocommerce-paypal-plugin/',
			'name'        => 'PayPal for WooCommerce',
			'description' => 'Upgrade the WooCommerce PayPal Gateway options available to your buyers for FREE!',
			'author'      => 'Angell EYE, LLC',
			'image'       => 'https://www.angelleye.com/wp-content/uploads/2014/02/paypal-for-woocommerce-thumbnail.jpg',
		);

		$plugins[] = array(
			'slug'        => 'wp-sent-mail',
			'url'         => 'https://www.wpsentmail.com',
			'name'        => 'WP Sent Mail',
			'description' => 'A sent mail folder for WordPress. View every email your store sends, track opens, and re-send right from the dashboard.',
			'author'      => 'Objectiv',
			'image'       => 'https://www.checkoutwc.com/wp-content/uploads/2019/07/Smaller-Square-WPSM.jpg',
		);
		?>
		<p><?php cfw_e( 'The WooCommerce ecosystem is full of fantastic plugins. Here are a few that work really well with CheckoutWC that we wholeheartedly recommend to our customers. (A few of these links may include affiliate tracking. This does not impact our decision to include a plugin.)' ); ?>
		<div class="recommended-plugin-list">
			<?php foreach ( $plugins as $plugin_info ) : ?>
				<?php $this->recommended_plugin_card( $plugin_info ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	function recommended_plugin_card( $plugin_info ) {
		?>
		<div class="plugin-card plugin-card-<?php echo $plugin_info['slug']; ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a target="_blank" href="<?php echo $plugin_info['url']; ?>">
							<?php echo $plugin_info['name']; ?> <img src="<?php echo $plugin_info['image']; ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<a class="button" target="_blank"  href="<?php echo $plugin_info['url']; ?>" role="button"><?php cfw_e( 'More Info' ); ?></a></li>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p><?php echo $plugin_info['description']; ?></p>
					<p class="authors"> <cite><?php echo sprintf( cfw__( 'By %s' ), $plugin_info['author'] ); ?></cite></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieves the current tab
	 *
	 * @since 1.0.0
	 * @access public
	 * @return bool
	 */
	public function get_current_tab() {
		return empty( $_GET['subpage'] ) ? false : $_GET['subpage'];
	}

	/**
	 * Retrieves the current tab
	 *
	 * @since 1.0.0
	 * @access public
	 * @return bool
	 */
	public function get_current_addon_tab() {
		return empty( $_GET['addontab'] ) ? false : $_GET['addontab'];
	}

	/**
	 * Adds a notification that nags about the license key
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_key_nag() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			add_action( 'after_plugin_row_' . $this->plugin_instance->get_path_manager()->get_base(), array( $this, 'after_plugin_row_message' ), 10, 2 );
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function after_plugin_row_message() {
		$key_status = $this->plugin_instance->get_updater()->get_field_value( 'key_status' );

		if ( empty( $key_status ) ) {
			return;
		}

		if ( 'valid' !== $key_status ) {
			$current = get_site_transient( 'update_plugins' );
			if ( isset( $current->response[ plugin_basename( __FILE__ ) ] ) ) {
				return;
			}

			if ( is_network_admin() || ! is_multisite() ) {
				$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
				echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message">';
				echo $this->keynag();
				echo '</div></td></tr>';
			}
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function keynag() {
		return "<span style='color:red'>You're missing out on important updates because your license key is missing, invalid, or expired.</span>";
	}

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_scripts() {
		if ( ! empty( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
			$this->_license_activations = $this->plugin_instance->get_updater()->get_license_activation_limit();

			wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

			// WooCommerce admin styles
			wp_enqueue_style( 'woocommerce_admin_styles' );

			// Add the admin stylesheet
			wp_enqueue_style( 'objectiv-cfw-admin-styles', CFW_URL . 'assets/admin/css/admin.css', array(), CFW_VERSION );

			// Enqueue the admin stylesheet
			wp_enqueue_style( 'objectiv-cfw-admin-styles' );

			// Add the color picker css file
			wp_enqueue_style( 'wp-color-picker' );

			// Add media picker script
			wp_enqueue_media();

			// Include our custom jQuery file with WordPress Color Picker dependency
			wp_enqueue_script( 'objectiv-cfw-admin', CFW_URL . 'assets/admin/js/admin.js', array( 'jquery', 'wp-color-picker', 'wc-enhanced-select' ), CFW_VERSION );
			wp_enqueue_script( 'cfw-webfont-loader', 'https://cdnjs.cloudflare.com/ajax/libs/webfont/1.6.28/webfontloader.js' );

			// Localize the script with new data
			$settings_array = array(
				'logo_attachment_id' => $this->plugin_instance->get_settings_manager()->get_setting( 'logo_attachment_id' ),
			);
			wp_localize_script( 'objectiv-cfw-admin', 'objectiv_cfw_admin', $settings_array );

			if ( ! empty( $_GET['subpage'] ) && 'design' === $_GET['subpage'] ) {
				global $cfw_google_fonts_list;
				$cfw_google_fonts_list = get_transient( 'cfw_google_font_list' );

				if ( empty( $cfw_google_fonts_list ) ) {
					$cfw_google_fonts_list = wp_remote_get( 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyAkSLrj88M_Y-rFfjRI2vgIzjIZ0N1fynE&sort=popularity' );
					$cfw_google_fonts_list = json_decode( wp_remote_retrieve_body( $cfw_google_fonts_list ) );

					set_transient( 'cfw_google_font_list', $cfw_google_fonts_list, 30 * DAY_IN_SECONDS );
				}
			}
		}
	}

	/**
	 * add_notice_key_nag function
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_notice_key_nag() {
		$key_status  = $this->plugin_instance->get_updater()->get_field_value( 'key_status' );
		$license_key = $this->plugin_instance->get_updater()->get_field_value( 'license_key' );

		if ( ! empty( $_GET['cfw_welcome'] ) ) {
			return;
		}

		// Validate Key Status
		if ( empty( $license_key ) || ( ( 'valid' !== $key_status || 'inactive' === $key_status || 'site_inactive' === $key_status ) ) ) {
			$important = '';
			if ( isset( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
				$important = "style='display:block !important'";
			}

			echo "<div $important class='notice notice-error is-dismissible checkout-wc'> <p>" . $this->renew_or_purchase_nag( $key_status, $license_key ) . '</p></div>';
		}
	}

	/**
	 * add_compatibility_nag function
	 *
	 * @since 2.32.1
	 * @access public
	 */
	public function add_compatibility_nag() {
		$active_plugins       = get_option( 'active_plugins' );
		$incompatible_plugins = array();

		if ( in_array( 'woo-checkout-field-editor-pro/checkout-form-designer.php', $active_plugins, true ) ) {
			$incompatible_plugins[] = 'Checkout Field Editor for WooCommerce';
		}

		if ( in_array( 'wc-fields-factory/wcff.php', $active_plugins, true ) ) {
			$incompatible_plugins[] = 'WC Fields Factory';
		}

		if ( ! empty( $incompatible_plugins ) ) {
			$important = '';
			if ( isset( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
				$important = "style='display:block !important'";
			}

			echo "<div $important class=\"notice notice-error is-dismissible checkout-wc\"><p><strong>" . sprintf( cfw__( '<strong>%s</strong>: Warning incompatible plugins detected!', 'checkout-wc' ), 'CheckoutWC' ) . '</strong>';
			echo '<ol>';

			foreach ( $incompatible_plugins as $incompatible_plugin ) {
				echo "<li>{$incompatible_plugin}</li>";
			}

			echo '</ol>';
			echo cfw__( 'Please deactivate these plugins to avoid problems with your checkout!', 'checkout-wc' ) . '</p></div>';
		}
	}

	function template_disabled_notice() {
		$enabled     = $this->plugin_instance->get_settings_manager()->get_setting( 'enable' ) === 'yes';
		$key_status  = $this->plugin_instance->get_updater()->get_field_value( 'key_status' );
		$license_key = $this->plugin_instance->get_updater()->get_field_value( 'license_key' );

		if ( ! $enabled && ! empty( $license_key ) && 'valid' === $key_status ) {
			$important = '';
			if ( isset( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
				$important = "style='display:block !important'";
			}

			echo "<div $important class='notice notice-warning is-dismissible checkout-wc'><p>" . sprintf( cfw__( '<strong>%s</strong>: Your license is valid and activated for this site but your template is disabled for normal users. To fix this, go to %s > %s > %s and check "%s".', 'checkout-wc' ), 'CheckoutWC', cfw__( 'Settings', 'checkout-wc' ), cfw__( 'CheckoutWC', 'checkout-wc' ), cfw__( 'General', 'checkout-wc' ), cfw__( 'Use Checkout for WooCommerce Template', 'checkout-wc' ) ) . '</p></div>';
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param $key_status
	 * @param $license_key
	 * @return String The renewal or purchase notice.
	 */
	public function renew_or_purchase_nag( $key_status, $license_key ) {
		if ( 'expired' === $key_status ) {
			return sprintf( cfw__( '<strong>%s</strong>: Your license key appears to have expired. Please verify that your license key is valid or <a target="_blank" href="https://www.checkoutwc.com/checkout/?edd_license_key=%s">renew your license now</a> to restore full functionality.', 'checkout-wc' ), 'CheckoutWC', $license_key );
		}

		return sprintf( cfw__( '<strong>%s</strong>: Your license key is missing or invalid. Please verify that your license key is valid or <a target="_blank" href="https://www.checkoutwc.com/">purchase a license</a> to restore full functionality.', 'checkout-wc' ), 'CheckoutWC' );
	}

	function add_welcome_notice() {
		if ( ! empty( $_GET['cfw_welcome'] ) ) {
			echo "<div style='display:block !important' class='notice notice-info'><p>" . cfw__( 'Thank you for installing Checkout for WooCommerce! To get started, click on <strong>License</strong> below and activate your license key!', 'checkout-wc' ) . '</p></div>';
		}
	}

	function welcome_screen_do_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_cfw_welcome_screen_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_cfw_welcome_screen_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirect to bbPress about page
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => 'cfw-settings',
					'cfw_welcome' => 'true',
				),
				admin_url( 'options-general.php' )
			)
		);
	}

	function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=cfw-settings' ) . '">' . cfw__( 'Settings', 'checkout-wc' ) . '</a>',
		);

		return array_merge( $settings_link, $links );
	}

	/**
	 * @since 1.1.5
	 * @param $order
	 */
	public function shipping_phone_display_admin_order_meta( $order ) {
		$shipping_phone = get_post_meta( $order->get_id(), '_shipping_phone', true );

		if ( empty( $shipping_phone ) ) {
			return;
		}

		/**
		 * Filter whether to enable editable shipping phone field in admin
		 *
		 * @since 3.0.0
		 *
		 * @param bool $enable_editable_admin_phone_field True show editable field, false show label
		 */
		if ( apply_filters( 'cfw_enable_editable_admin_shipping_phone_field', true ) ) {
			$field                = array();
			$field['placeholder'] = cfw__( 'Phone', 'woocommerce' );
			$field['label']       = cfw__( 'Phone', 'woocommerce' );
			$field['value']       = $shipping_phone;
			$field['name']        = '_cfw_shipping_phone';
			$field['id']          = 'cfw_shipping_phone';

			woocommerce_wp_text_input( $field );
		} else {
			echo '<p><strong>' . cfw__( 'Phone' ) . ':</strong><br /><a href="tel:' . $shipping_phone . '">' . $shipping_phone . '</a></p>';
		}
	}

	function save_shipping_phone( $order_id ) {
		if ( isset( $_POST['_cfw_shipping_phone'] ) ) {
			$order = wc_get_order( $order_id );
			$order->update_meta_data( '_shipping_phone', $_POST['_cfw_shipping_phone'] );
			$order->save();
		}
	}

	function maybe_activate_theme() {
		$prefix = $this->plugin_instance->get_settings_manager()->prefix;

		$new_settings = stripslashes_deep( $_REQUEST[ "{$prefix}_setting" ] );

		if ( ! empty( $new_settings['active_template'] ) ) {
			$active_template = new Template( $this->plugin_instance->get_settings_manager()->get_setting( 'active_template' ), $this->plugin_instance->get_path_manager() );
			$active_template->init();
		}
	}

	function maybe_do_data_upgrades() {
		$db_version = get_option( 'cfw_db_version', '0.0.0' );

		// 3.0.0 upgrades
		if ( version_compare( '3.0.0', $db_version, '>' ) ) {
			$this->plugin_instance->get_templates_manager()->get_active_template()->init();

			if ( $this->plugin_instance->get_settings_manager()->get_setting( 'allow_tracking' ) === 1 ) {
				$this->plugin_instance->get_settings_manager()->update_setting( 'allow_tracking', md5( trailingslashit( home_url() ) ) );
			}
		}

		// 3.3.0 upgrades
		if ( version_compare( '3.3.0', $db_version, '>' ) ) {
			$this->plugin_instance->get_settings_manager()->add_setting( 'override_view_order_template', 'yes' );

			// Do this again because we are dumb
			if ( $this->plugin_instance->get_settings_manager()->get_setting( 'allow_tracking' ) === 1 ) {
				$this->plugin_instance->get_settings_manager()->update_setting( 'allow_tracking', md5( trailingslashit( home_url() ) ) );
			}
		}

		// 3.6.1 upgrades
		if ( version_compare( '3.6.1', $db_version, '>' ) ) {
			// Set default glass accent color
			$this->plugin_instance->get_settings_manager()->update_setting( 'accent_color', '#dee6fe', array( 'glass' ) );
		}

		// 3.14.0 upgrades
		if ( version_compare( '3.14.0', $db_version, '>' ) ) {
			// Set default glass accent color
			$this->plugin_instance->get_settings_manager()->add_setting( 'enable_order_review_step', 'no' );
		}

		// Only update db version if the current version is greater than the db version
		if ( version_compare( CFW_VERSION, $db_version, '>' ) ) {
			update_option( 'cfw_db_version', CFW_VERSION );
		}
	}

	function compatibility() {
		$cart_flows = new CartFlows();
		$cart_flows->admin_init();
	}

	/**
	 * @param array $settings
	 *
	 * @return mixed
	 */
	function mark_possibly_overridden_settings( $settings ) {
		foreach ( $settings as $key => $setting ) {
			if ( 'woocommerce_registration_generate_username' === $setting['id'] || 'woocommerce_registration_generate_password' === $setting['id'] ) {
				$settings[ $key ]['desc'] = "{$setting['desc']} **";
			}
		}

		return $settings;
	}

	function output_woocommerce_account_settings_notice() {
		?>
		<div id="message" class="updated woocommerce-message inline">
			<p>
				<strong><?php cfw_e( 'CheckoutWC:' ); ?></strong>
				<?php cfw_e( 'Settings marked with asterisks (**) may be overridden on the checkout page based on your Login and Registration settings. (Settings > CheckoutWC > General)' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Generate Settings JSON file
	 *
	 * @author Jason Witt
	 * @since  3.8.0
	 *
	 * @return void
	 */
	public static function generate_settings_export() {

		// Bail if not admin.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		// Bail if nonce check fails.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], '_cfw__export_settings' ) ) {
			wp_die();
		}

		$settings = get_option( '_cfw__settings' );

		$settings['logo_attachment_url'] = wp_get_attachment_url( $settings['logo_attachment_id'] );

		if ( ! empty( $settings ) ) {
			echo json_encode( $settings );
			wp_die();
		}

		wp_die();
	}

	/**
	 * Upload Settings
	 *
	 * @author Jason Witt
	 * @since  3.8.0
	 *
	 * @return void
	 */
	public function maybe_upload_settings() {
		// Make sure we're an admin and that we have a valid request
		if ( ! current_user_can( 'manage_options' ) || empty( $_POST['import_cfw_settings'] ) ) {
			return;
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : false;

		if ( ! wp_verify_nonce( $nonce, 'import_cfw_settings_nonce' ) || ( empty( $_FILES['uploaded_settings'] ) || 0 === $_FILES['uploaded_settings']['size'] ) ) {
			add_action(
				'admin_notices',
				function() {
					$important = '';
					if ( isset( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
						$important = "style='display:block !important'";
					}
					?>
				<div <?php echo $important; ?> class="notice notice-error is-dismissible checkout-wc">
					<p><?php cfw_e( 'CheckoutWC: Unable to import settings. Did you select a JSON file to upload?', 'checkout-wc' ); ?></p>
				</div>
					<?php
				}
			);

			return;
		}

		$upload = ! empty( $_FILES['uploaded_settings'] ) ? $_FILES['uploaded_settings'] : array();

		if ( ! empty( $upload ) ) {
			$file_tmp_path  = $upload['tmp_name'];
			$file_name      = $upload['name'];
			$file_name_cmps = explode( '.', $file_name );
			$file_extension = strtolower( end( $file_name_cmps ) );

			$new_file_name = md5( time() . $file_name ) . '.' . $file_extension;

			if ( 'json' === $file_extension ) {
				$wp_uploads = wp_upload_dir();
				$upload_dir = trailingslashit( $wp_uploads['basedir'] );
				$dest_path  = $upload_dir . $new_file_name;

				if ( move_uploaded_file( $file_tmp_path, $dest_path ) ) {
					$contents = file_get_contents( $dest_path );
					$decoded  = json_decode( $contents, JSON_OBJECT_AS_ARRAY );

					if ( ! is_null( $decoded ) && isset( $decoded['logo_attachment_id'] ) && ! empty( $decoded['logo_attachment_id'] && false !== $decoded['logo_attachment_url'] ) ) {
						$image_upload                  = $this->upload_logo( $decoded['logo_attachment_url'] );
						$decoded['logo_attachment_id'] = $image_upload ? $image_upload : '';
					} else {
						wp_die( 'An error occurred while importing settings!' );
					}

					update_option( '_cfw__settings_backup', get_option( '_cfw__settings' ) ); // backup settings to be safe
					update_option( '_cfw__settings', $decoded );

					unlink( $dest_path );

					add_action(
						'admin_notices',
						function() {
							$important = '';
							if ( isset( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
								$important = "style='display:block !important'";
							}
							?>
						<div <?php echo $important; ?> class="notice notice-success is-dismissible checkout-wc">
							<p><?php cfw_e( 'CheckoutWC: Successfully imported settings.', 'checkout-wc' ); ?></p>
						</div>
							<?php
						}
					);
				}
			}
		}
	}

	/**
	 * Upload Logo
	 *
	 * @param $file_url
	 * @return int|\WP_Error
	 * @author Jason Witt
	 * @since  3.8.0
	 */
	public function upload_logo( $file_url ) {
		$filename = basename( $file_url );

		add_filter( 'https_ssl_verify', '__return_false' );
		$logo = wp_remote_get( $file_url );

		if ( is_wp_error( $logo ) ) {
			wp_die( 'An error occurred retrieving logo.' );
		}

		$upload_file = wp_upload_bits( $filename, null, wp_remote_retrieve_body( $logo ) );

		if ( ! $upload_file['error'] ) {
			$wp_file_type = wp_check_filetype( $filename, null );

			$attachment = array(
				'post_mime_type' => $wp_file_type['type'],
				'post_parent'    => 0,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0 );

			if ( ! is_wp_error( $attachment_id ) ) {
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
				wp_update_attachment_metadata( $attachment_id, $attachment_data );
			}

			return $attachment_id;
		}

		return '';
	}

	/**
	 * @param $filter
	 *
	 * @return int
	 */
	function count_filters( $filter ): int {
		global $wp_filter;
		$count = 0;

		if ( isset( $wp_filter[ $filter ] ) ) {
			foreach ( $wp_filter[ $filter ]->callbacks as $priority => $callbacks ) {
				$count += (int) count( $callbacks );
			}
		}

		return $count;
	}
}
