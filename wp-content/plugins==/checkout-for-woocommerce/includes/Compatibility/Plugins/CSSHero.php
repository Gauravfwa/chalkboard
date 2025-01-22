<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class CSSHero extends Base {
	public function __construct() {
		parent::__construct();
	}

	function is_available() {
		return function_exists( 'csshero_activation_notice' );
	}

	public function remove_styles( $styles ) {
		$styles['csshero-main-stylesheet'] = 'csshero-main-stylesheet';
		$styles['csshero-aos-stylesheet']  = 'csshero-aos-stylesheet';

		return $styles;
	}

	public function remove_scripts( $scripts ) {
		$scripts['csshero_aos']         = 'csshero_aos';
		$scripts['csshero_aos-trigger'] = 'csshero_aos-trigger';

		return $scripts;
	}
}
