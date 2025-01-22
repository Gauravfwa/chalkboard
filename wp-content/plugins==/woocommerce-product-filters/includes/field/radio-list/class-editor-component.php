<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Radio_List;

use WooCommerce_Product_Filter_Plugin\Field\Editor\Abstract_List_Component;

class Editor_Component extends Abstract_List_Component {
	protected $supports = array(
		'reset_item',
		'hierarchical',
		'toggle_content',
		'product_counts',
		'see_more_options_by',
		'stock_status_options',
		'sorting',
	);

	public function get_element_id() {
		return 'RadioListField';
	}

	public function get_element_title() {
		return __( 'Radio', 'wcpf' );
	}
}
