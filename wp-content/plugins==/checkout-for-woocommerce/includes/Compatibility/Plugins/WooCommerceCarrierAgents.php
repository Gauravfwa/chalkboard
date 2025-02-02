<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommerceCarrierAgents extends Base {
	public function is_available() {
		return function_exists( 'woo_carrier_agents_load_textdomain' );
	}

	public function pre_init() {
		add_filter( 'woo_carrier_agents_search_output', array( $this, 'add_output_area' ) );
	}

	function add_output_area( $action_hooks ) {
		$action_hooks['cfw_checkout_after_shipping_methods'] = 10;

		return $action_hooks;
	}
}
