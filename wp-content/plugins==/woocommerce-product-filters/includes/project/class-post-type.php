<?php

namespace WooCommerce_Product_Filter_Plugin\Project;

use WooCommerce_Product_Filter_Plugin\Structure;

class Post_Type extends Structure\Component {
	public function get_post_type() {
		return 'wcpf_project';
	}

	public function get_item_post_type() {
		return 'wcpf_item';
	}

	public function initial_properties() {
		$this->save_component_to_register( 'Project/Post_Type' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'init', 'on_init' );

		$hook_manager->add_action( 'delete_post', 'on_delete_post' );
	}

	public function on_init() {
		register_post_type(
			$this->get_post_type(),
			array(
				'public'              => false,
				'has_archive'         => false,
				'publicaly_queryable' => false,
				'show_in_menu'        => 'woocommerce',
				'show_in_admin_bar'   => false,
				'show_ui'             => true,
				'hierarchical'        => false,
				'supports'            => array(
					'author',
				),
				'labels'              => array(
					'name'          => __( 'Filters', 'wcpf' ),
					'singular_name' => __( 'Filter', 'wcpf' ),
				),
			)
		);

		register_post_type(
			$this->get_item_post_type(),
			array(
				'public'       => false,
				'hierarchical' => false,
				'supports'     => array(),
				'labels'       => array(
					'name' => __( 'Filter Item', 'wcpf' ),
				),
			)
		);
	}

	public function on_delete_post( $post_id ) {
		if ( get_post_type( $post_id ) !== $this->get_post_type() ) {
			return;
		}

		$project = $this->get_component_builder()->build( Project::class );

		$project->load_project( $post_id );

		foreach ( $project->get_entity_list() as $entity_id => $entity ) {
			if ( $entity_id === $post_id ) {
				continue;
			}

			wp_delete_post( $entity_id, true );
		}
	}
}
