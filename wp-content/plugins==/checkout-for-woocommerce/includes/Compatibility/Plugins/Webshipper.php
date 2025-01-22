<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class Webshipper extends Base {
	public function is_available() {
		return class_exists( '\\WebshipperAPI' );
	}

	public function run_immediately() {
		remove_action( 'woocommerce_review_order_before_order_total', 'webshipper_drop_point_selector_location' );
		add_action( 'woocommerce_review_order_after_shipping', 'webshipper_drop_point_selector_location' );
	}
}
