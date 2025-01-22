<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Component;

use WooCommerce_Product_Filter_Plugin\Structure;

class Base_Component extends Structure\Component {
	protected $register_entry;

	public function get_register_entry() {
		return $this->register_entry;
	}

	public function set_register_entry( $entry ) {
		$this->register_entry = $entry;
	}

	public function get_entity_key() {
		return $this->get_register_entry()['id'];
	}

	public function get_entity_label() {
		return $this->get_register_entry()['label'];
	}

	public function get_entity_post_type() {
		return $this->get_register_entry()['post_type'];
	}

	public function is_entity_grouped() {
		return $this->get_register_entry()['is_grouped'];
	}
}
