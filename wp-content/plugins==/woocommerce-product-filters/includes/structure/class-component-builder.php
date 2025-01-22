<?php

namespace WooCommerce_Product_Filter_Plugin\Structure;

use WooCommerce_Product_Filter_Plugin\Plugin;

class Component_Builder {
	protected $plugin;

	public function get_plugin() {
		return $this->plugin;
	}

	public function set_plugin( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function build( $component, $implementation = true ) {
		if ( is_string( $component ) ) {
			$component = new $component();
		}

		if ( ! $component instanceof Component ) {
			return false;
		}

		if ( $implementation ) {
			$this->implementation( $component );
		}

		return $component;
	}

	public function implementation( Component $component ) {
		$component->set_plugin( $this->get_plugin() );

		$component->initial_properties();

		$component->attach_hooks( $component->get_hook_manager() );
	}
}
