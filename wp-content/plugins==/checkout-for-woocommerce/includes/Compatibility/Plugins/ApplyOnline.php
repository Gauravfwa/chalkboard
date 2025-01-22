<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class ApplyOnline extends Base {
	function is_available() {
		return function_exists( 'run_applyonline' );
	}

	function remove_styles( $styles ) {
		$styles['apply-online-BS'] = 'apply-online-BS';

		return $styles;
	}
}
