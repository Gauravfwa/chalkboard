<?php

namespace WooCommerce_Product_Filter_Plugin\Filter;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Project\Project,
	WooCommerce_Product_Filter_Plugin\Entity;

class Component_Builder extends Structure\Component {
	public function initial_properties() {
		$this->save_component_to_register( 'Filter/Component_Builder' );
	}

	public function build_component( Entity $entity, Project $project ) {
		$register_entry = $this->get_entity_register()->get_entry( $entity->get_entity_key() );

		if ( ! $register_entry || ! $register_entry['filter_component_class'] ) {
			return null;
		}

		$filter_component = new $register_entry['filter_component_class']();

		if ( ! $filter_component instanceof Component\Base_Component ) {
			return null;
		}

		$filter_component->set_entity( $entity );

		$filter_component->set_project( $project );

		if ( count( $entity->get_child_entities() ) ) {
			$child_filter_components = array();

			foreach ( $entity->get_child_entities() as $child_entity ) {
				$child_filter_component = $this->build_component( $child_entity, $project );

				if ( $child_filter_component ) {
					$child_filter_components[] = $child_filter_component;
				}
			}

			$filter_component->set_child_filter_components( $child_filter_components );
		}

		$this->get_component_builder()->implementation( $filter_component );

		return $filter_component;
	}
}
