<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Page;

use WooCommerce_Product_Filter_Plugin\Structure;

class List_Page extends Structure\Component {
	public function get_project_post_type() {
		return $this->get_component_register()->get( 'Project/Post_Type' )->get_post_type();
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'post_row_actions', 'post_row_actions', 10, 2 );

		$hook_manager->add_action( 'init', 'on_init' );
	}

	public function on_init() {
		$this->get_hook_manager()->add_filter( 'manage_' . $this->get_project_post_type() . '_posts_columns', 'posts_table_columns' );

		$this->get_hook_manager()->add_action(
			'manage_' . $this->get_project_post_type() . '_posts_custom_column',
			'table_custom_columns',
			10,
			2
		);
	}

	public function table_custom_columns( $column, $post_id ) {
		if ( 'wcpf_shortcode' === $column ) {
			echo '<input type="text" 
                         readonly="readonly"
                         onclick="this.select();"
                         value="' . esc_attr( '[wcpf_filters id="' . $post_id . '"]' ) . '"/>';
		}
	}

	public function posts_table_columns( $columns ) {
		return array(
			'cb'             => '<input type="checkbox" />',
			'title'          => __( 'Title' ),
			'wcpf_shortcode' => __( 'Shortcode', 'wcpf' ),
			'author'         => __( 'Author' ),
			'date'           => __( 'Date' ),
		);
	}

	public function post_row_actions( $actions, $post ) {
		if ( $post->post_type === $this->get_project_post_type() ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				unset( $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}
}
