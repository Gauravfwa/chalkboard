<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class Vipps extends Base {
	public function is_available() {
		return defined( 'WOO_VIPPS_VERSION' );
	}

	public function run() {
		add_action( 'cfw_payment_request_buttons', array( $this, 'add_vipps_button' ) );
	}

	function add_vipps_button() {
		$button = do_shortcode( '[woo_vipps_express_checkout_button]' );

		if ( ! empty( $button ) ) {
			echo $button;

			add_action( 'cfw_checkout_customer_info_tab', array( $this, 'add_separator' ), 11 );
		}
	}
}