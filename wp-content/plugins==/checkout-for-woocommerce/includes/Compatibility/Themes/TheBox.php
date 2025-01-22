<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class TheBox extends Base {
	public function is_available() {
		return function_exists( 'themecube_product_thumbnail_in_checkout' );
	}

	public function run_on_update_checkout() {
		$this->run();
	}

	public function run() {
		remove_filter( 'woocommerce_cart_item_name', 'themecube_product_thumbnail_in_checkout', 20 );
		remove_filter( 'woocommerce_checkout_cart_item_quantity', 'themecube_filter_checkout_cart_item_quantity', 20 );
		remove_filter( 'woocommerce_cart_item_name', 'themecube_cart_item_category', 99 );
	}
}
