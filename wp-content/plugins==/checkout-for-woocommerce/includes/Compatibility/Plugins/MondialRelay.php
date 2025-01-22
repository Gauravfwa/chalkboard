<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class MondialRelay extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return function_exists( 'run_MRWP' );
	}

	public function run() {
		add_filter( 'cfw_body_classes', array( $this, 'add_body_class' ) );
	}

	/**
	 * @param array $classes The body classes
	 *
	 * @return array
	 */
	function add_body_class( $classes ) {
		$classes[] = 'checkoutwc-mondial-relay';

		return $classes;
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'MondialRelay',
			'params' => array(),
		);

		return $compatibility;
	}
}
