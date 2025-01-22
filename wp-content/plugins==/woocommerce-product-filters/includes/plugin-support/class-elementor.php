<?php

namespace WooCommerce_Product_Filter_Plugin\Plugin_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class Elementor extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'elementor/element/woocommerce-products/section_query/after_section_start', 'products_settings', 10, 2 );

		$hook_manager->add_action( 'elementor/widget/before_render_content', 'before_render_widget' );

		$hook_manager->add_action( 'woocommerce_product_query', 'shortcode_products_query', 1, 1 );
	}

	public function shortcode_products_query( $query ) {
		if ( $query->get( 'taxonomy', false ) ) {
			foreach ( $this->get_object_register()->get( 'selected_options', array() ) as $index => $options ) {
				if ( $query->get( 'taxonomy' ) === $options['taxonomy'] ) {
					unset( $query->query_vars['taxonomy'] );

					if ( $query->get( 'terms', false ) ) {
						unset( $query->query_vars['terms'] );
					}
				}
			}
		}

		return $query;
	}

	public function before_render_widget( $widget ) {
		if ( $widget->get_name() !== 'woocommerce-products' ) {
			return;
		}

		$settings = $widget->get_settings();

		if ( isset( $settings['filter-id'] ) && $settings['filter-id'] && function_exists( 'wc_set_loop_prop' ) ) {
			wc_set_loop_prop( 'wcpf_filter_id', $settings['filter-id'] );
		}
	}

	public function products_settings( $control_manager, $args ) {
		$filter_posts = get_posts(
			array(
				'post_type'   => $this->get_component_register()->get( 'Project/Post_Type' )->get_post_type(),
				'numberposts' => -1,
			)
		);

		$filters = array(
			'' => __( 'Not selected', 'wcpf' ),
		);

		foreach ( $filter_posts as $filter_post ) {
			$filters[ $filter_post->ID ] = $filter_post->post_title;
		}

		$control_manager->add_control(
			'filter-id',
			array(
				'label'     => __( 'Filters', 'wcpf' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => wcpf_get_archive_filter_id( '' ),
				'options'   => $filters,
				'condition' => array(),
			)
		);
	}
}
