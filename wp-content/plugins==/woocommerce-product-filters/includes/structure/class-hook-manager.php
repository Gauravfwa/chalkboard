<?php

namespace WooCommerce_Product_Filter_Plugin\Structure;

class Hook_Manager {
	protected $component;

	public function __construct( Component $component ) {
		$this->component = $component;
	}

	public function get_component() {
		return $this->component;
	}

	public function apply_filters( $filter, $value = null ) {
		return call_user_func_array( 'apply_filters', func_get_args() );
	}

	public function trigger_action( $action ) {
		call_user_func_array( 'do_action', func_get_args() );
	}

	public function add_action( $action, $handler, $priority = 10, $accepted_args = 1 ) {
		add_action( $action, $this->prepare_handler( $handler ), $priority, $accepted_args );
	}

	public function remove_action( $action, $handler, $priority = 10, $accepted_args = 1 ) {
		remove_action( $action, $this->prepare_handler( $handler ), $priority, $accepted_args );
	}

	public function add_filter( $filter, $handler, $priority = 10, $accepted_args = 1 ) {
		add_filter( $filter, $this->prepare_handler( $handler ), $priority, $accepted_args );
	}

	public function remove_filter( $action, $handler, $priority = 10, $accepted_args = 1 ) {
		remove_filter( $action, $this->prepare_handler( $handler ), $priority, $accepted_args );
	}

	protected function prepare_handler( $handler ) {
		if ( is_string( $handler ) && method_exists( $this->component, $handler ) ) {
			$handler = array( $this->component, $handler );
		}

		return $handler;
	}
}
