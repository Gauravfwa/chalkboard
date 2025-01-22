<?php

namespace WooCommerce_Product_Filter_Plugin\Layout\Columns;

use WooCommerce_Product_Filter_Plugin\Admin\Editor\Component,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Panel_Layout,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Control,
	WooCommerce_Product_Filter_Plugin\Entity,
	WooCommerce_Product_Filter_Plugin\Project\Project;

class Editor_Component extends Component\Base_Component implements Component\Generates_Panels_Interface, Component\Generates_Projection_Interface, Component\Preparing_Entity_Interface {
	public function generate_panels() {
		$item_panel = new Panel_Layout\List_Layout(
			array(
				'panel_id' => 'ColumnsLayoutItem',
				'title'    => __( 'Column', 'wcpf' ),
				'controls' => array(
					new Control\Text_Size_Control(
						array(
							'key'           => 'width',
							'label'         => __( 'Width', 'wcpf' ),
							'placeholder'   => __( 'width', 'wcpf' ),
							'default_value' => '50%',
							'units'         => array(
								'px' => 'px',
								'%'  => '%',
							),
							'required'      => true,
						)
					),
				),
			)
		);

		return array( $item_panel );
	}

	public function generate_projection() {
		return new Editor_Projection(
			array(
				'title' => __( 'Columns', 'wcpf' ),
			)
		);
	}

	public function preparing_entity( Entity $entity, Project $project ) {
		$virtual_ids = $project->get_virtual_id_list();

		$columns = $entity->get_option( 'columns', array() );

		$new_columns = array();

		if ( $columns && is_array( $columns ) ) {
			foreach ( $columns as $index => $column ) {
				$entities = $column['entities'];

				$new_entities = array();

				foreach ( $entities as $entity_id ) {
					if ( isset( $virtual_ids[ $entity_id ] ) ) {
						$new_entities[] = $virtual_ids[ $entity_id ];
					} else {
						$new_entities[] = $entity_id;
					}
				}

				$new_columns[ $index ]['entities'] = $new_entities;

				$new_columns[ $index ]['options'] = $column['options'];
			}
		}

		$entity->set_option( 'columns', $new_columns );

		return true;
	}
}
