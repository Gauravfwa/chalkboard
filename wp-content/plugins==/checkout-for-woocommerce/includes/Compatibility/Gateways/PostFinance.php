<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class PostFinance extends Base {
	public function is_available() {
		return function_exists( 'woocommerce_postfinancecw_proceed_to_checkout' );
	}

	public function run_immediately() {
		add_filter( 'cfw_load_checkout_template', array( $this, 'maybe_suppress_checkout' ), 10, 1 );
	}

	function maybe_suppress_checkout( $load ) {
		if ( $load && is_page( 'woo_postfinancecw' ) ) {
			$load = false;
		}

		return $load;
	}
}