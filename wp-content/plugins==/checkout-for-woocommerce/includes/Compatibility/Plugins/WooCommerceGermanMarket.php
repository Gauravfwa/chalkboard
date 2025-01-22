<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommerceGermanMarket extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return class_exists( '\\Woocommerce_German_Market' );
	}

	public function run() {
		remove_filter( 'woocommerce_order_button_html', array( 'WGM_Template', 'remove_order_button_html' ), 9999 );
	}

	public function run_on_update_checkout() {
		$this->run();
	}
}
