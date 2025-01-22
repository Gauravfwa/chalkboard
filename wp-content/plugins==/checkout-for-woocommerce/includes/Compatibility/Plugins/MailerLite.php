<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class MailerLite extends Base {
	public function __construct() {
		parent::__construct();
	}

	function is_available() {
		return function_exists( 'woo_ml_load' );
	}

	public function run() {
		add_action( 'cfw_after_customer_info_tab_login', 'woo_ml_checkout_label' );
	}
}
