<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class YITHDeliveryDate extends Base {
	public function is_available() {
		return class_exists( '\\YITH_Delivery_Date_Shipping_Manager' );
	}

	public function run() {
		$YITH_Delivery_Date_Shipping_Manager = \YITH_Delivery_Date_Shipping_Manager::get_instance();

		add_action( 'cfw_checkout_after_shipping_methods', array( $YITH_Delivery_Date_Shipping_Manager, 'print_delivery_from' ), 16 );
	}
}
