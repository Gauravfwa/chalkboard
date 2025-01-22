<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

interface Contains_Controls_Interface {
	public function get_child_controls();

	public function get_child_control_by_option_key( $option_key );
}
