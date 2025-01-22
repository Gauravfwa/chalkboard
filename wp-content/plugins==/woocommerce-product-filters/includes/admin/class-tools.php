<?php

namespace WooCommerce_Product_Filter_Plugin\Admin;

use WooCommerce_Product_Filter_Plugin\Structure;

class Tools extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'woocommerce_debug_tools', 'debug_tools' );

		$hook_manager->add_action( 'admin_head-edit.php', 'add_clear_cache_button' );
	}

	public function add_clear_cache_button() {
		$screen = get_current_screen();

		if ( $screen && 'wcpf_project' === $screen->post_type ) {
			$this->get_template_loader()->render_template(
				'clear-cache-button.php',
				array(
					'tool_link' => add_query_arg(
						array(
							'page'     => 'wc-status',
							'tab'      => 'tools',
							'action'   => 'wcpf_clear_cache',
							'_wpnonce' => wp_create_nonce( 'debug_action' ),
						),
						admin_url( 'admin.php' )
					),
				),
				__DIR__ . '/views'
			);
		}
	}

	public function debug_tools( $tools ) {
		$tools = array(
			'wcpf_clear_cache' => array(
				'name'     => __( 'Product Filters cache', 'wcpf' ),
				'button'   => __( 'Clear cache', 'wcpf' ),
				'desc'     => __( 'Clear cache results of heavy queries to database (product counts query)', 'wcpf' ),
				'callback' => array( $this, 'clear_cache_tool' ),
			),
		) + $tools;

		return $tools;
	}

	public function clear_cache_tool() {
		$taxonomies = get_object_taxonomies( array( 'product' ) );

		foreach ( $taxonomies as $taxonomy ) {
			delete_transient( 'wcpf_products_in_' . $taxonomy );
		}

		delete_transient( 'wcpf_products_in_stock_statuses' );

		echo '<div class="updated"><p>' . esc_html__( 'Removed entire cache for "Product Filters"', 'wcpf' ) . '</p></div>';
	}
}
