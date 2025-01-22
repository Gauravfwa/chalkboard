<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class SalientWPBakery extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return defined( 'SALIENT_VC_ACTIVE' );
	}

	function remove_styles( $styles ) {
		$styles['js_composer_front'] = 'js_composer_front';

		return $styles;
	}
}
