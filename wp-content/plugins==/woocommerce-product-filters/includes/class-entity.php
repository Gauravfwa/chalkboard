<?php

namespace WooCommerce_Product_Filter_Plugin;

class Entity {
	protected $entity_key;

	protected $entity_post = null;

	protected $entity_project;

	protected $child_entity_list = array();

	protected $options = array();

	public function __construct( $entity_id = null ) {
		if ( null !== $entity_id ) {
			$this->set_entity_post( \WP_Post::get_instance( $entity_id ) );
		}
	}

	protected function get_option_meta_key() {
		return 'wcpf_entity_options';
	}

	public function get_all_options() {
		return $this->options;
	}

	public function get_option( $index, $default_value = null ) {
		$options = $this->get_all_options();

		return array_key_exists( $index, $options ) ? $options[ $index ] : $default_value;
	}

	public function set_option( $index, $value ) {
		$this->set_options( array_merge( $this->get_all_options(), array( $index => $value ) ) );
	}

	public function has_option( $index ) {
		$options = $this->get_all_options();

		return array_key_exists( $index, $options );
	}

	public function remove_option( $index ) {
		$options = $this->get_all_options();

		if ( array_key_exists( $index, $options ) ) {
			unset( $options[ $index ] );

			$this->set_options( $options );
		}
	}

	public function set_options( array $options ) {
		update_post_meta( $this->get_entity_id(), $this->get_option_meta_key(), $options );

		$this->options = $options;
	}

	public function get_project() {
		return $this->entity_project;
	}

	public function set_project( Project\Project $project ) {
		$this->entity_project = $project;
	}

	public function set_entity_post( \WP_Post $post ) {
		$this->entity_post = $post;

		$this->options = get_post_meta( $this->get_entity_id(), $this->get_option_meta_key(), true );

		if ( ! is_array( $this->options ) ) {
			$this->options = array();
		}
	}

	public function get_entity_post() {
		return $this->entity_post;
	}

	public function get_entity_id() {
		return $this->entity_post->ID;
	}

	public function get_title() {
		return $this->entity_post->post_title;
	}

	public function get_child_entities() {
		return $this->child_entity_list;
	}

	public function set_child_entities( array $entities ) {
		$this->child_entity_list = $entities;
	}

	public function get_entity_key() {
		return $this->entity_key;
	}

	public function set_entity_key( $entity_key ) {
		$this->entity_key = $entity_key;
	}
}
