<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class PortugalVaspKios extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return function_exists( 'pvkw_init' );
	}

	public function run() {
		add_action( 'cfw_checkout_after_shipping_methods', 'pvkw_woocommerce_review_order_before_payment' );
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'PortugalVaspKios',
			'params' => array(),
		);

		return $compatibility;
	}
}
