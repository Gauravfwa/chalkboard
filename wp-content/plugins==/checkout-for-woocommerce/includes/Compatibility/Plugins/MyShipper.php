<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class MyShipper extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return class_exists( '\\IGN_Use_My_Shipper_Base' );
	}

	function typescript_class_and_params( $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'MyShipper',
			'params' => array(
				'notice' => 'Shipping Account Number is required.',
			),
		);

		return $compatibility;
	}
}
