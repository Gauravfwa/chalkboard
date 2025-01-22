<?php

namespace WooCommerce_Product_Filter_Plugin\Filter\Component;

abstract class Abstract_Filtering_Component extends Base_Component implements Filtering_Query_Interface {
	protected $filter_values = array();

	public function get_filter_key_by_index( $index ) {
		$filter_keys = $this->get_filter_keys();

		if ( ! isset( $filter_keys[ $index ] ) ) {
			return null;
		}

		return $filter_keys[ $index ];
	}

	public function get_filter_values() {
		return $this->filter_values;
	}

	public function get_filter_value( $index, $default_value = null ) {
		return array_key_exists( $index, $this->filter_values ) ? $this->filter_values[ $index ] : $default_value;
	}

	public function set_filter_value( $index, $filter_value ) {
		$this->filter_values[ $index ] = $filter_value;
	}
}
