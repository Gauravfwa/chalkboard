<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WCPont extends Base {
	public function is_available() {
		return class_exists( '\\WC_Pont' );
	}

	public function run_immediately() {
		global $wc_pont;

		if ( ! empty( $wc_pont ) ) {
			remove_action( 'woocommerce_review_order_before_payment', array( $wc_pont, 'wc_pont_html' ), 1 );
			add_action( 'cfw_checkout_shipping_method_tab', array( $wc_pont, 'wc_pont_html' ), 21 );
		}
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'WCPont',
			'params' => array(),
		);

		return $compatibility;
	}
}
