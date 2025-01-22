<?php

namespace WooCommerce_Product_Filter_Plugin;

class Register {
	protected $items = array();

	public function save( $index, $item ) {
		$this->items[ $index ] = $item;
	}

	public function remove( $index ) {
		if ( array_key_exists( $index, $this->items ) ) {
			unset( $this->items[ $index ] );
		}
	}

	public function get( $index, $default_value = null ) {
		return array_key_exists( $index, $this->items ) ? $this->items[ $index ] : $default_value;
	}
}
