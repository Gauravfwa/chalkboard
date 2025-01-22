<?php

namespace WooCommerce_Product_Filter_Plugin\Project;

use WooCommerce_Product_Filter_Plugin\Filter\Component;
use WooCommerce_Product_Filter_Plugin\Structure\Hook_Manager;

class Filter_Component extends Component\Base_Component implements Component\Rendering_Template_Interface {
	protected $url_navigation = null;

	protected $variations_components = array();

	protected $filter_components = array();

	protected function get_query_helper() {
		return $this->get_component_register()->get( 'Query_Helper' );
	}

	public function set_filter_components( $filter_components ) {
		$this->filter_components = $filter_components;

		$this->variations_components = array();

		foreach ( $filter_components as $filter_component ) {
			$entry = $this->get_entity_register()->get_entry(
				$filter_component->get_entity()->get_entity_key()
			);

			if ( $entry['variations'] ) {
				$this->variations_components[] = $filter_component;
			}
		}
	}

	public function get_filter_components() {
		return $this->filter_components;
	}

	public function get_variations_components() {
		return $this->variations_components;
	}

	public function get_url_navigation() {
		return $this->url_navigation;
	}

	public function initial_properties() {
		parent::initial_properties();

		$this->url_navigation = $this->get_component_builder()->build( URL_Navigation\Query_Navigation::class );

		$navigation_options = is_array( $this->get_option( 'urlNavigationOptions' ) )
			? $this->get_option( 'urlNavigationOptions' )
			: array();

		$this->url_navigation->set_navigation_options( $navigation_options );
	}

	public function attach_hooks( Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'woocommerce_is_filtered', 'is_filtered', 1, 1 );
	}

	public function is_filtered( $is_filtered = false ) {
		if ( count( $this->get_filter_components() ) ) {
			$is_filtered = true;
		}

		return $is_filtered;
	}

	public function template_render() {
		$child_components = array();

		foreach ( $this->get_child_filter_components() as $child_component ) {
			if ( $child_component instanceof Component\Rendering_Template_Interface ) {
				$child_components[] = $child_component;
			}
		}

		$this->get_template_loader()->render_template(
			'product-filters.php',
			array(
				'project_id'        => $this->get_entity_id(),
				'project_structure' => $this->get_project()->get_project_structure(),
				'child_components'  => $child_components,
			)
		);
	}
}
