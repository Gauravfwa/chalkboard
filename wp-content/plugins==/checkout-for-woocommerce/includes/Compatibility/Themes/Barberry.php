<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;
use Objectiv\Plugins\Checkout\Main;

class Barberry extends Base {
	public function is_available() {
		return defined( 'BARBERRY_ADDONS_DIR' );
	}

	public function remove_scripts( $scripts ) {
		$scripts['barberry-shortcodes'] = 'barberry-shortcodes';

		return $scripts;
	}
}