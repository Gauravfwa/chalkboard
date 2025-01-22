<?php

namespace WooCommerce_Product_Filter_Plugin;

class Entity_Register {
	protected $entries = array();

	public function register_entity( array $entry ) {
		$entry = wp_parse_args(
			$entry,
			array(
				'class'                  => Entity::class,
				'label'                  => '',
				'default_options'        => array(),
				'is_grouped'             => false,
				'editor_component_class' => null,
				'filter_component_class' => null,
				'variations'             => false,
			)
		);

		$this->entries[ $entry['id'] ] = array(
			'id'                     => $entry['id'],
			'class'                  => $entry['class'],
			'post_type'              => $entry['post_type'],
			'label'                  => $entry['label'],
			'default_options'        => $entry['default_options'],
			'is_grouped'             => $entry['is_grouped'],
			'editor_component_class' => $entry['editor_component_class'],
			'filter_component_class' => $entry['filter_component_class'],
			'variations'             => $entry['variations'],
		);
	}

	public function remove( $id ) {
		if ( array_key_exists( $id, $this->entries ) ) {
			unset( $this->entries[ $id ] );
		}
	}

	public function get_entry( $id ) {
		return array_key_exists( $id, $this->entries ) ? $this->entries[ $id ] : null;
	}

	public function get_entry_by_post_type( $type ) {
		foreach ( $this->entries as $entry ) {
			if ( $type === $entry['post_type'] ) {
				return $entry;
			}
		}

		return null;
	}

	public function get_entry_by_entity( $entity ) {
		foreach ( $this->entries as $entry ) {
			if ( is_a( $entity, $entry['class'] ) ) {
				return $entry;
			}
		}

		return null;
	}

	public function get_all_entries() {
		return $this->entries;
	}
}
