<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class FuelThemes extends Base {
	function is_available() {
		return function_exists( 'thb_wc_supported' );
	}

	public function run() {
		remove_action( 'woocommerce_checkout_before_customer_details', 'thb_checkout_before_customer_details', 5 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'thb_checkout_after_customer_details', 30 );
		remove_action( 'woocommerce_checkout_after_order_review', 'thb_checkout_after_order_review', 30 );
	}
}
