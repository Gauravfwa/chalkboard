<?php
namespace Objectiv\Plugins\Checkout\Loaders;

use Objectiv\Plugins\Checkout\Main;

/**
 * Class Content
 *
 * Loads pages into normal WP content
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Content extends Loader {
	/**
	 *
	 * @since 3.6.0
	 * @access public
	 *
	 */
	public static function checkout() {
		/**
		 * Filters whether to load checkout template
		 *
		 * @since 2.0.0
		 *
		 * @param bool $load True load, false don't load
		 */
		if ( apply_filters( 'cfw_load_checkout_template', Main::is_checkout() ) ) {
			self::wp_head();
			self::wp_footer();

			add_shortcode(
				'woocommerce_checkout',
				function() {
					// Setup checkout
					$global_template_parameters = self::init_checkout();

					ob_start();

					// Output the contents of the <body></body> section
					self::display( $global_template_parameters, 'content.php' );

					return ob_get_clean();
				}
			);
		}
	}

	public static function order_pay() {
		/**
		 * Filters whether to load order pay template
		 *
		 * @since 2.0.0
		 *
		 * @param bool $load True load, false don't load
		 */
		if ( apply_filters( 'cfw_load_order_pay_template', Main::is_checkout_pay_page() ) ) {
			self::wp_head();
			self::wp_footer();

			add_shortcode(
				'woocommerce_checkout',
				function() {
					// Setup checkout
					$global_template_parameters = self::init_order_pay();

					ob_start();

					// Output the contents of the <body></body> section
					self::display( $global_template_parameters, 'order-pay.php' );

					return ob_get_clean();
				}
			);
		}
	}

	public static function order_received() {
		/**
		 * Filters whether to load order received template
		 *
		 * @since 2.0.0
		 *
		 * @param bool $load True load, false don't load
		 */
		if ( apply_filters( 'cfw_load_order_received_template', Main::is_order_received_page() ) ) {
			self::wp_head();
			self::wp_footer();

			add_shortcode(
				'woocommerce_checkout',
				function() {
					// Setup checkout
					$global_template_parameters = self::init_thank_you();

					ob_start();

					// Output the contents of the <body></body> section
					self::display( $global_template_parameters, 'thank-you.php' );

					return ob_get_clean();
				}
			);
		}
	}

	public static function wp_head() {
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_header_scripts' ), 20, 4 );
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_styles' ), 40, 5 );
	}

	public static function wp_footer() {
		add_action( 'wp_footer', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_footer_scripts' ) );
	}
}
