<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WPCProductBundles extends Base {
	public function is_available() {
		return function_exists( 'woosb_init' );
	}

	public function run_immediately() {
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'hide_quantity_dropdown' ), 100, 2 );
	}

	function hide_quantity_dropdown( $quantity, $cart_item ) {
		if ( isset( $cart_item['woosb_parent_id'] ) ) {
			return $cart_item['quantity'];
		}

		return $quantity;
	}
}
