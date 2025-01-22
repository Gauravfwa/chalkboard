<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Color_List;

use WooCommerce_Product_Filter_Plugin\Field\Editor\Abstract_List_Component,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Editor_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'multi_select_toggle',
		'toggle_content',
	);

	public function get_element_id() {
		return 'ColorListField';
	}

	public function get_element_title() {
		return __( 'Color List', 'wcpf' );
	}

	public function generate_panels() {
		$result_panels = parent::generate_panels();

		$field_panel = $result_panels[0];

		foreach ( array( 'itemsDisplayWithoutParents', 'itemsDisplay', 'taxonomySelectedItems', 'taxonomyExceptItems' ) as $option_key ) {
			$field_panel->remove_control_by_option_key( 'general', $option_key );
		}

		$field_panel->add_control( 'general', new Control\Color_List_Control(), 8 );

		return $result_panels;
	}
}
