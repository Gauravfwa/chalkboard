<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Text_List;

use WooCommerce_Product_Filter_Plugin\Field\Editor\Abstract_List_Component,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Editor_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'multi_select_toggle',
		'toggle_content',
		'product_counts',
		'hierarchical',
		'sorting',
	);

	public function get_element_id() {
		return 'TextListField';
	}

	public function get_element_title() {
		return __( 'Text List', 'wcpf' );
	}

	public function generate_panels() {
		$result_panels = parent::generate_panels();

		$default_panel = $result_panels[0];

		$default_panel->add_control(
			'visual',
			new Control\Switch_Control(
				array(
					'key'                 => 'useInlineStyle',
					'label'               => __( 'Inline style', 'wcpf' ),
					'control_description' => __( 'Switch to show items inline or one item per line', 'wcpf' ),
					'first_option'        => array(
						'text'  => __( 'On', 'wcpf' ),
						'value' => true,
					),
					'second_option'       => array(
						'text'  => __( 'Off', 'wcpf' ),
						'value' => false,
					),
					'default_value'       => true,
				)
			),
			1
		);

		$hierarchical_control = $default_panel->get_control_by_option_key( 'itemsDisplayHierarchical' );

		$hierarchical_control->set_display_rules(
			array_merge(
				$hierarchical_control->get_display_rules(),
				array(
					array(
						'optionKey' => 'useInlineStyle',
						'operation' => '==',
						'value'     => false,
					),
				)
			)
		);

		$default_panel->remove_control_by_option_key( 'visual', 'displayHierarchicalCollapsed' );

		return $result_panels;
	}
}
