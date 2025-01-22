<?php

namespace WooCommerce_Product_Filter_Plugin\Filter\Component;

interface Filtering_Query_Interface {
	public function get_filter_keys();

	public function get_filter_key_by_index( $index );

	public function get_filter_values();

	public function get_filter_value( $index, $default_value = null );

	public function set_filter_value( $index, $filter_value );

	public function apply_filter_to_query( \WP_Query $product_query, $filter_values );
}
