<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Drop_Down_List;

use WooCommerce_Product_Filter_Plugin\Field\Editor\Abstract_List_Component,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Editor_Component extends Abstract_List_Component {
	protected $supports = array(
		'reset_item',
		'toggle_content',
		'product_counts',
		'stock_status_options',
		'hierarchical',
		'sorting',
	);

	public function get_element_id() {
		return 'DropDownListField';
	}

	public function get_element_title() {
		return __( 'DropDown', 'wcpf' );
	}

	public function generate_panels() {
		$result_panels = parent::generate_panels();

		$field_panel = $result_panels[0];

		$field_panel->add_control(
			'visual',
			new Control\Select_Control(
				array(
					'key'           => 'dropDownStyle',
					'label'         => __( 'Drop Down Style', 'wcpf' ),
					'options'       => array(
						'default'     => __( 'Default', 'wcpf' ),
						'woocommerce' => __( 'WooCommerce', 'wcpf' ),
					),
					'default_value' => 'default',
				)
			),
			3
		);

		$hierarchical_control = $field_panel->get_control_by_option_key( 'itemsDisplayHierarchical' );

		$hierarchical_control->set_display_rules(
			array_merge(
				$hierarchical_control->get_display_rules(),
				array(
					array(
						'optionKey' => 'dropDownStyle',
						'operation' => '==',
						'value'     => 'woocommerce',
					),
				)
			)
		);

		$field_panel->remove_control_by_option_key( 'visual', 'displayHierarchicalCollapsed' );

		return $result_panels;
	}
}
