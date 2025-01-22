<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommerceAdvancedMessages extends Base {
	public function is_available() {
		return function_exists( 'WooCommerce_Advanced_Messages' );
	}

	public function pre_init() {
		add_filter( 'wcam_locations', array( $this, 'add_checkoutwc_options' ) );
	}

	public function run_on_update_checkout() {
		WooCommerce_Advanced_Messages()->messages->process_messages();
	}

	function add_checkoutwc_options( $locations ) {
		$locations['Checkout']['woocommerce_checkout_before_order_review'] = array(
			'action_hook' => 'woocommerce_checkout_before_order_review',
			'priority'    => 10,
			'name'        => 'CheckoutWC: Before cart',
		);

		$locations['Checkout']['woocommerce_checkout_after_order_review'] = array(
			'action_hook' => 'woocommerce_checkout_after_order_review',
			'priority'    => 10,
			'name'        => 'CheckoutWC: After coupon',
		);

		return $locations;
	}
}
