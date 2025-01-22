<?php

namespace WooCommerce_Product_Filter_Plugin\Front;

use WooCommerce_Product_Filter_Plugin\Structure;

class Product_Loop extends Structure\Component {
	public function get_filters() {
		return $this->get_component_register()->get( 'Filters' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'woocommerce_no_products_found', 'not_found_start_container', 1 );

		$hook_manager->add_action( 'woocommerce_no_products_found', 'not_found_end_container', 1000 );
	}

	public function not_found_start_container() {
		global $wp_query;

		$project_id = $wp_query->get( 'wcpf_filter_id', false );

		if ( $project_id ) {
			echo '<div class="wcpf-products-container wcpf-products-container-' . esc_attr( $project_id ) . '">';
		}
	}

	public function not_found_end_container() {
		global $wp_query;

		$project_id = $wp_query->get( 'wcpf_filter_id', false );

		if ( $project_id ) {
			echo '</div>';
		}
	}
}
