<?php

namespace WooCommerce_Product_Filter_Plugin\Project;

use WooCommerce_Product_Filter_Plugin\Structure;

class Filter_Component_Storage extends Structure\Component {
	protected $loaded_projects = array();

	public function initial_properties() {
		$this->save_component_to_register( 'Project/Filter_Component_Storage' );
	}

	public function has_project_component( $project_id ) {
		return isset( $this->loaded_projects[ $project_id ] );
	}

	public function get_projects() {
		return $this->loaded_projects;
	}

	public function get_project_component( $project_id, $attributes = array() ) {
		if ( ! $this->has_project_component( $project_id ) ) {
			$this->load_project_component( $project_id, $attributes );
		} else {
			$this->apply_attributes_to_project( $this->loaded_projects[ $project_id ]->get_project(), $attributes );
		}

		return $this->has_project_component( $project_id ) ? $this->loaded_projects[ $project_id ] : null;
	}

	public function load_project_component( $project_id, $attributes = array() ) {
		$project = $this->get_component_builder()->build( Project::class );

		$this->apply_attributes_to_project( $project, $attributes );

		$project->load_project( $project_id );

		$project_entity = $project->get_project_entity();

		if ( ! $project_entity ) {
			return null;
		}

		$filter_component_builder = $this->get_component_register()->get( 'Filter/Component_Builder' );

		$project_filter_component = $filter_component_builder->build_component( $project_entity, $project );

		if ( ! $project_filter_component ) {
			return null;
		}

		$this->loaded_projects[ $project_entity->get_entity_id() ] = $project_filter_component;

		return $project_filter_component;
	}

	protected function apply_attributes_to_project( $project, $attributes ) {
		if ( isset( $attributes['product_query_vars_before_filtering'] ) ) {
			$query = new \WP_Query();

			$query->parse_query( $attributes['product_query_vars_before_filtering'] );

			$project->set_product_query_before_filtering( $query );
		}

		if ( isset( $attributes['product_query_vars_after_filtering'] ) ) {
			$query = new \WP_Query();

			$query->parse_query( $attributes['product_query_vars_after_filtering'] );

			$project->set_product_query_after_filtering( $query );
		}
	}
}
