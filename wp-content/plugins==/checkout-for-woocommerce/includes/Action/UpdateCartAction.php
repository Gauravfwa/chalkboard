<?php

namespace Objectiv\Plugins\Checkout\Action;

use Objectiv\Plugins\Checkout\Main;

/**
 * Class LogInAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class UpdateCartAction extends CFWAction {

	/**
	 * LogInAction constructor.
	 *
	 * @param $id
	 * @param $no_privilege
	 * @param $action_prefix
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		check_ajax_referer( 'update-order-review', 'security' );

		parse_str( wp_unslash( $_POST['post_data'] ), $post_data );

		// Used to tell checkout to redirect somewhere
		$redirect = false;

		if ( Main::instance()->get_settings_manager()->is_premium_feature_enabled( 'enable_cart_editing' ) ) {
			/**
			 * Cart Updates
			 */
			if ( isset( $post_data['cart'] ) ) {
				foreach ( $post_data['cart'] as $cart_item_key => $value ) {
					$cart_item = WC()->cart->get_cart_item( $cart_item_key );

					/** @var \WC_Product $cart_item_product */
					$cart_item_product = $cart_item['data'];

					$max_quantity = apply_filters( 'woocommerce_quantity_input_max', $cart_item_product->get_max_purchase_quantity() > 0 ? $cart_item_product->get_max_purchase_quantity() : PHP_INT_MAX, $cart_item_product );

					if ( $value['qty'] > $max_quantity ) {
						$value['qty'] = $max_quantity;
					}

					WC()->cart->set_quantity( $cart_item_key, $value['qty'], false );

					// Remove items from the cart contents
					// Ensures things like subscriptions update their output properly
					if ( 0 === $value['qty'] ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}

			if ( WC()->cart->get_cart_contents_count() === 0 ) {
				/**
				 * Filters whether to suppress checkout is not available message
				 * when editing cart results in empty cart
				 *
				 * @since 3.14.0
				 *
				 * @param bool $supress_notice Whether to suppress the message
				 */
				if ( false === apply_filters( 'cfw_cart_edit_redirect_suppress_notice', false ) ) {
					wc_add_notice( cfw__( 'Checkout is not available whilst your cart is empty.', 'woocommerce' ), 'notice' );
				}

				// Allow shortcodes to be used in empty cart redirect URL field
				// This is necessary so that WPML (etc) can swap in a locale specific URL
				$cart_editing_redirect_url = do_shortcode( Main::instance()->get_settings_manager()->get_setting( 'cart_edit_empty_cart_redirect' ) );

				if ( empty( $cart_editing_redirect_url ) ) {
					$redirect = wc_get_cart_url();
				} else {
					$redirect = $cart_editing_redirect_url;
				}
			}
		}

		WC()->cart->calculate_totals();

		$this->out(
			array(
				'redirect' => $redirect,
			)
		);
	}
}
