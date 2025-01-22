<?php

namespace WooCommerce_Product_Filter_Plugin\Structure;

use WooCommerce_Product_Filter_Plugin\Plugin;

class Component {
	protected $plugin;

	protected $hook_manager;

	public function __construct( Hook_Manager $hook_manager = null ) {
		if ( null === $hook_manager ) {
			$hook_manager = new Hook_Manager( $this );
		}

		$this->set_hook_manager( $hook_manager );
	}

	public function get_plugin() {
		return $this->plugin;
	}

	public function set_plugin( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function get_hook_manager() {
		return $this->hook_manager;
	}

	public function set_hook_manager( Hook_Manager $hook_manager ) {
		$this->hook_manager = $hook_manager;
	}

	public function get_component_register() {
		return $this->get_plugin()->get_component_register();
	}

	public function get_object_register() {
		return $this->get_plugin()->get_object_register();
	}

	public function get_entity_register() {
		return $this->get_plugin()->get_entity_register();
	}

	public function get_component_builder() {
		return $this->get_object_register()->get( 'Component_Builder' );
	}

	public function get_template_loader() {
		return $this->get_component_register()->get( 'Template_Loader' );
	}

	protected function save_component_to_register( $index ) {
		$this->get_component_register()->save( $index, $this );
	}

	public function initial_properties() {}

	public function attach_hooks( Hook_Manager $hook_manager ) {}
}
