<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class Stockie extends Base {
	function is_available() {
		return function_exists( 'stockie_comment' );
	}

	function run_on_update_checkout() {
		$this->run();
	}

	function run() {
		remove_action( 'woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form' );
		remove_filter( 'woocommerce_cart_item_name', 'stockie_add_cart_product_category', 99 );
	}
}
