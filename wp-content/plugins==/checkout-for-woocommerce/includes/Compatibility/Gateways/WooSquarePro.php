<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooSquarePro extends Base {
	public function is_available() {
		return defined( 'WOO_SQUARE_PLUGIN_PATH' );
	}

	public function run() {
		$this->reorder_payment_tab();
	}

	function reorder_payment_tab() {
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_methods', 10 );
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_content_billing_address', 20 );

		add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_tab_content_billing_address', 10 );
		add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_methods', 20 );
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'WooSquarePro',
			'params' => array(),
		);

		return $compatibility;
	}
}
