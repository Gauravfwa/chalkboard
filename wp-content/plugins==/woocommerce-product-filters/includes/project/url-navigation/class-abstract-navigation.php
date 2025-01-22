<?php

namespace WooCommerce_Product_Filter_Plugin\Project\URL_Navigation;

use WooCommerce_Product_Filter_Plugin\Structure;

abstract class Abstract_Navigation extends Structure\Component {
	protected $navigation_options = array();

	public function set_navigation_options( array $options ) {
		$this->navigation_options = $options;
	}

	public function get_navigation_options() {
		return $this->navigation_options;
	}

	abstract public function decode_and_sanitize( $value );

	abstract public function has_attribute( $key );

	abstract public function get_attribute( $key );
}
