<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommerceCore extends Base {
	public function is_available() {
		return true; // always on, baby
	}

	public function pre_init() {
		// Using this instead of is_ajax() in case is_ajax() is not available
		if ( apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! isset( $_GET['wc-ajax'] ) ) {
			return;
		}

		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'suppress_add_to_cart_notices_during_checkout_redirect' ), 100000000 ); // run this late
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'suppress_add_to_cart_notices_during_ajax_add_to_cart' ), 100000000 ); // run this late
		add_action( 'woocommerce_before_checkout_process', array( $this, 'sync_billing_fields_on_process_checkout' ) );
	}

	public function run() {
		add_action(
			'cfw_checkout_before_billing_address',
			function() {
				do_action( 'woocommerce_before_checkout_billing_form', WC()->checkout() );
			}
		);

		add_action(
			'cfw_checkout_after_billing_address',
			function() {
				do_action( 'woocommerce_after_checkout_billing_form', WC()->checkout() );
			}
		);

		add_action(
			'cfw_checkout_before_shipping_address',
			function() {
				do_action( 'woocommerce_before_checkout_shipping_form', WC()->checkout() );
			}
		);

		add_action(
			'cfw_checkout_after_shipping_address',
			function() {
				do_action( 'woocommerce_after_checkout_shipping_form', WC()->checkout() );
			}
		);

		add_action(
			'cfw_customer_info_tab',
			function() {
				do_action( 'woocommerce_checkout_before_customer_details' );
			},
			10
		);

		add_action(
			'cfw_customer_info_tab',
			function() {
				do_action( 'woocommerce_checkout_after_customer_details' );
			},
			35
		);

		/**
		 * Filter whether to suppress default login form alert
		 *
		 * @since 2.0.0
		 *
		 * @param bool $supress_default_login_form True suppress the default login form, false show the default login form
		 */
		if ( apply_filters( 'cfw_suppress_default_login_form', cfw_get_main()->get_settings_manager()->get_setting( 'login_style' ) !== 'woocommerce' ) ) {
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		}

		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );

		if ( cfw_get_main()->get_settings_manager()->get_setting( 'enable_order_pay' ) === 'yes' ) {
			remove_action( 'before_woocommerce_pay', 'woocommerce_output_all_notices', 10 );
		}
	}

	function run_on_thankyou() {
		if ( cfw_get_main()->get_settings_manager()->get_setting( 'enable_thank_you_page' ) === 'yes' ) {
			remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		}

		// Remove default view order stuff
		if ( cfw_get_main()->get_settings_manager()->get_setting( 'override_view_order_template' ) === 'yes' ) {
			remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 );
		}
	}

	function sync_billing_fields_on_process_checkout() {
		// Is the CFW flag present and is it set to use the shipping address as the billing address?
		if ( isset( $_POST['bill_to_different_address'] ) && $_POST['bill_to_different_address'] == 'same_as_shipping' ) {
			foreach ( $_POST as $key => $value ) {
				// If this is a shipping field, create a duplicate billing field
				if ( substr( $key, 0, 9 ) == 'shipping_' ) {
					$billing_field_key = substr_replace( $key, 'billing_', 0, 9 );

					$_POST[ $billing_field_key ] = $value;
				}
			}
		}
	}

	function suppress_add_to_cart_notices_during_ajax_add_to_cart( $fragments ) {
		/**
		 * Filters whether to suppress add to cart notices at checkout
		 *
		 * @since 2.0.0
		 *
		 * @param bool $supress_notices True suppress, false allow
		 */
		if ( ! apply_filters( 'cfw_suppress_add_to_cart_notices', true ) ) {
			return $fragments;
		}

		$checkout_url = wc_get_checkout_url();
		$redirect_url = apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null );

		// If we are going to redirect to checkout, don't show message
		if ( ! empty( $_REQUEST['product_id'] ) && ! empty( $_REQUEST['wc-ajax'] ) && 'add_to_cart' === $_REQUEST['wc-ajax'] && $redirect_url === $checkout_url ) {
			$quantity   = $_REQUEST['quantity'] ?? 1;
			$product_id = $_REQUEST['product_id'];

			$add_to_cart_notice = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

			if ( wc_has_notice( $add_to_cart_notice ) ) {
				$notices                  = wc_get_notices();
				$add_to_cart_notice_index = array_search( $add_to_cart_notice, $notices['success'], true );

				unset( $notices['success'][ $add_to_cart_notice_index ] );
				wc_set_notices( $notices );
			}
		}

		// Continue on your way
		return $fragments;
	}

	function suppress_add_to_cart_notices_during_checkout_redirect( $url ) {
		/**
		 * Filters whether to suppress add to cart notices at checkout
		 *
		 * @since 2.0.0
		 *
		 * @param bool $suppress_notices True suppress, false allow
		 */
		if ( ! apply_filters( 'cfw_suppress_add_to_cart_notices', true ) ) {
			return $url;
		}

		$checkout_url = wc_get_checkout_url();

		// If we are going to redirect to checkout, don't show message
		if ( ! empty( $_REQUEST['add-to-cart'] ) && ( $url === $checkout_url || is_checkout() ) ) {
			$quantity   = $_REQUEST['quantity'] ?? 1;
			$quantity   = is_numeric( $quantity ) ? intval( $quantity ) : 1;
			$product_id = $_REQUEST['add-to-cart'];

			$add_to_cart_notice = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

			if ( wc_has_notice( $add_to_cart_notice ) ) {
				$notices                  = wc_get_notices();
				$add_to_cart_notice_index = array_search( $add_to_cart_notice, $notices['success'], true );

				unset( $notices['success'][ $add_to_cart_notice_index ] );
				wc_set_notices( $notices );
			}
		}

		// Continue on your way
		return $url;
	}

	public function remove_scripts( $scripts ) {
		$scripts['wc-cart-fragments'] = 'wc-cart-fragments';

		return $scripts;
	}
}
