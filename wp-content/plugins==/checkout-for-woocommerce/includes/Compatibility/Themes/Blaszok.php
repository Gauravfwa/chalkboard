<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class Blaszok extends Base {
	public function is_available() {
		return function_exists( 'mpcth_woo_fix' );
	}

	function pre_init() {
		add_action(
			'init',
			function() {
				remove_action( 'init', 'mpcth_woo_fix' );
			},
			1
		);
	}
}
