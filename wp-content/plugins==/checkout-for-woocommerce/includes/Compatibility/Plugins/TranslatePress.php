<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class TranslatePress extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		global $trp_output_buffer_started;

		return ! empty( $trp_output_buffer_started );
	}

	public function pre_init() {
		define( 'OBJECTIV_BOOSTER_NO_BUFFER', true );
	}
}