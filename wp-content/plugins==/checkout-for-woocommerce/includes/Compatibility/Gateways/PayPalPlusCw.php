<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class PayPalPlusCw extends Base {
	public function is_available() {
		return class_exists( '\\PayPalPlusCw_Util' );
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'PayPalPlusCw',
			'params' => array(),
		);

		return $compatibility;
	}
}
