<?php
/**
 * Plugin Name: Dynamic Cart Messages for WooCommerce
 * Description: Dynamic Cart Messages allows you to boost your sales on your WooCommerce site by showing specific messages to your site visitors or customers based on the products they have added to their cart.
 * Author: SaffireTech
 * Author URI: https://www.saffiretech.com/
 * Text Domain: dynamic-cart-messages-woocommerce
 * Domain Path: /languages
 * Stable Tag : 1.0.7
 * Requires at least: 5.0
 * Tested up to: 6.3.2
 * Requires PHP: 7.2
 * WC requires at least: 5.0
 * WC tested up to: 8.2.1
 * License:     GPLv3
 * License URI: URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Version: 1.0.7
 */

defined( 'ABSPATH' ) || exit; // exit if accessed directly.

define( 'DCMPW_DYNMAIC_CART_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check the installation of pro version.
 *
 * @return bool
 */
function dcm_check_pro_version() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( 'dynamic-cart-messages-pro/dynamic-cart-messages-pro.php' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Display notice if pro plugin found.
 */
function dcm_free_plugin_install() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	// if pro plugin found deactivate free plugin.
	if ( dcm_check_pro_version() ) {

		deactivate_plugins( plugin_basename( __FILE__ ), true ); // deactivate free plugin if pro found.
		if ( defined( 'DCM_PRO_PLUGIN' ) ) {
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			add_action( 'admin_notices', 'dcm_install_free_admin_notice' );
		}
	}
}
add_action( 'plugins_loaded', 'dcm_free_plugin_install' );

/**
 * Add message if pro version is installed.
 */
function dcm_install_free_admin_notice() {    ?>
	<div class="notice notice-error is-dismissible">
		<p><?php esc_html_e( 'Free version deactivated Pro version Installed', 'dynamic-cart-messages-woocommerce' ); ?></p>
	</div>
	<?php
}

/**
 * To load the plugin textdomain
 * i.e.to translate the Custom Post Type Name and summenu present inside it
 */
function dcmwp_cart_messages_load_file() {
	/**
	 * Detect plugin. For frontend only.
	 */
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) && ! class_exists( 'Woocommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// } elseif ( is_plugin_active( 'dynamic-cart-messages-pro/dynamic-cart-messages-pro.php' ) && current_user_can( 'activate_plugins' ) && class_exists( 'Dcm_Pro_Plugin_Updater' ) ) {
	} elseif ( is_plugin_active( 'dynamic-cart-messages-pro/dynamic-cart-messages-pro.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {

		wp_enqueue_style( 'dcmp_awesome_css', esc_url( 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' ), false, '4.7.0' );
		wp_enqueue_style( 'dcmp_public_css', plugins_url( 'assets/css/dcmp-public-dynamic-msg.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style( 'dcmp_sweet_alert_css', plugins_url( 'assets/css/sweetalert2.min.css', __FILE__ ), array(), '10.10.1' );

		require_once dirname( __FILE__ ) . '/includes/dcmp-settings.php';
		require_once dirname( __FILE__ ) . '/includes/dcmp-functions.php';
	}

}
add_action( 'init', 'dcmwp_cart_messages_load_file' );

/**
 * Checks if the woocommerce plugin is installed and activated
 */
function dcmwp_check_is_woocommerce_active() {
	// Require parent plugin.
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) && ! class_exists( 'Woocommerce' ) ) {
		// Stop activation redirect and show error.

		/* translators: %s: search term */
		wp_die( wp_kses_post( sprintf( __( 'Sorry, but this plugin requires the Woocommerce Plugin to be installed and active. <br><a href="%s">&laquo; Return to Plugins</a>', 'dynamic-cart-messages-woocommerce' ), '' . admin_url( 'plugins.php' ) . '' ) ) );
	}
}
register_activation_hook( __FILE__, 'dcmwp_check_is_woocommerce_active' );


/**
 * Include the JS and the CSS file.
 *
 * @param string $hook .
 */
function dcmwp_enqueue_dynamic_message_assets( $hook ) {
	if ( ( 'post.php' === $hook && isset( $_GET['post'] ) && 'dcmp_msg' === get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) || ( 'dcmp_msg_page_dynamic-cart-message-settings' === $hook ) || ( isset( $_GET['post_type'] ) && 'dcmp_msg' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) ) {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'dcmp_sweet_alert_js', plugins_url( 'assets/js/sweetalert2.all.min.js', __FILE__ ), array(), '10.10.1', false );
		wp_enqueue_style( 'dcmp_admin_css', plugins_url( 'assets/css/dcmp-admin-dynamic-msg.css', __FILE__ ), array(), '1.0' );
		wp_register_script( 'dcmp_cart_js', plugins_url( 'assets/js/dcmp-dynamic-cart-message.js', __FILE__ ), array( 'jquery', 'wp-i18n', 'wp-color-picker' ), '1.0', 'false' );
		wp_enqueue_script( 'dcmp_cart_js' );

		wp_localize_script(
			'dcmp_cart_js',
			'sft_dcmp_cart',
			array(
				'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
				'dcmp_free_to_pro_alert_title'   => __( 'Pro Field Alert !', 'dynamic-cart-messages-woocommerce' ),
				'dcmp_free_to_pro_alert_message' => __( 'This field is available with pro version of Dynamic Cart Messages Pro for WooCommerce', 'dynamic-cart-messages-woocommerce' ),
				'dcmp_free_to_pro_upgrade'       => __( 'Upgrade Now', 'dynamic-cart-messages-woocommerce' ),
			)
		);

		wp_set_script_translations( 'dcmp_cart_js', 'dynamic-cart-messages-woocommerce', plugin_dir_path( __FILE__ ) . 'languages/' );
	}
}
add_action( 'admin_enqueue_scripts', 'dcmwp_enqueue_dynamic_message_assets' );

/**
 * To load Dynamic cart message CPT.
 */
add_action( 'init', 'dcmwp_custom_post_type', 10 );

/**
 * To load text domain file. i.e .mo file.
 */
function dcmwp_load_messages_textdomain_file() {
	load_plugin_textdomain( 'dynamic-cart-messages-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'admin_init', 'dcmwp_load_messages_textdomain_file' );

// HPOS Compatibility.
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
