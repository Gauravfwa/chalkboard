<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

interface Preparing_For_Reload_Interface {
	public function prepare_for_reload( array $options, array $context, array $control_props = array() );
}
