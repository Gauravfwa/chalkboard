<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class Square extends Base {
	public function is_available() {
		return class_exists( '\\WooCommerce_Square_Loader' );
	}

	/**
	 * @param array $compatibility
	 *
	 * @return array
	 */
	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'Square',
			'params' => array(),
		);

		return $compatibility;
	}
}
