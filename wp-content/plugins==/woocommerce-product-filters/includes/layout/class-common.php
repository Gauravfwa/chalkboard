<?php

namespace WooCommerce_Product_Filter_Plugin\Layout;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Element_Panel\Element_List_Control;

class Common extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wcpf_register_entities', 'register_entities' );

		$hook_manager->add_action( 'wcpf_admin_elements_panel', 'presets', 20 );

		$hook_manager->add_filter( 'wcpf_admin_message_localize', 'message_localize' );
	}

	public function message_localize( $messages ) {
		$messages['columnsLayout'] = array(
			'column' => __( 'Column', 'wcpf' ),
			'remove' => __( 'Remove', 'wcpf' ),
			'edit'   => __( 'Edit', 'wcpf' ),
		);

		return $messages;
	}

	public function register_entities( $register ) {
		$item_post_type = $this->get_component_register()->get( 'Project/Post_Type' )->get_item_post_type();

		$register->register_entity(
			array(
				'id'                     => 'SimpleBoxLayout',
				'label'                  => __( 'Simple Box', 'wcpf' ),
				'post_type'              => $item_post_type,
				'is_grouped'             => true,
				'default_options'        => array(
					'displayToggleContent' => true,
					'defaultToggleState'   => 'show',
					'cssClass'             => '',
				),
				'editor_component_class' => Simple_Box\Editor_Component::class,
				'filter_component_class' => Simple_Box\Filter_Component::class,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'ColumnsLayout',
				'label'                  => __( 'Columns', 'wcpf' ),
				'post_type'              => $item_post_type,
				'is_grouped'             => true,
				'default_options'        => array(
					'columns' => array(
						array(
							'entities' => array(),
							'options'  => array(
								'width' => '50%',
							),
						),
					),
				),
				'editor_component_class' => Columns\Editor_Component::class,
				'filter_component_class' => Columns\Filter_Component::class,
			)
		);
	}

	public function presets( $panel ) {
		$panel->add_control(
			new Element_List_Control(
				array(
					'label'    => __( 'Layout', 'wcpf' ),
					'key'      => 'layout',
					'elements' => array(
						array(
							'id'          => 'SimpleBoxLayout',
							'title'       => __( 'Simple Box', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/layout/simple-box.png',
						),
						array(
							'id'          => 'ColumnsLayout',
							'title'       => __( 'Columns', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/layout/columns.png',
						),
					),
				)
			)
		);
	}
}
