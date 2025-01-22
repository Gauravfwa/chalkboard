<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Check_Box_List;

use WooCommerce_Product_Filter_Plugin\Field\Editor\Abstract_List_Component;

class Editor_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'hierarchical',
		'toggle_content',
		'product_counts',
		'see_more_options_by',
		'stock_status_options',
		'sorting',
	);

	public function get_element_id() {
		return 'CheckBoxListField';
	}

	public function get_element_title() {
		return __( 'CheckBox', 'wcpf' );
	}
}
