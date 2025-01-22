<?php

namespace WooCommerce_Product_Filter_Plugin;

class Shortcode extends Structure\Component {
	public function get_filters() {
		return $this->get_component_register()->get( 'Filters' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		foreach ( array(
			'products',
			'recent_products',
			'sale_products',
			'best_selling_products',
			'top_rated_products',
			'product_attribute',
			'featured_products',
		) as $woocommerce_shortcode ) {
			$hook_manager->add_filter(
				'shortcode_atts_' . $woocommerce_shortcode,
				'product_shortcode_attribute',
				10,
				4
			);

			$hook_manager->add_filter(
				'woocommerce_shortcode_' . $woocommerce_shortcode . '_loop_no_results',
				'products_loop_no_result',
				10,
				1
			);
		}

		$hook_manager->add_filter(
			'woocommerce_shortcode_products_query',
			'filter_product_query',
			100,
			2
		);

		if ( ! is_admin() ) {
			add_shortcode( 'wcpf_filters', array( $this, 'filters_shortcode' ) );

			add_shortcode( 'wcpf_filter_notes', array( $this, 'filter_notes_shortcode' ) );
		}
	}

	public function filter_notes_shortcode( $attributes ) {
		$project_id = $this->get_filters()->get_default_filter_id();

		if ( is_array( $attributes ) && isset( $attributes['filter-id'] ) ) {
			$project_id = $attributes['filter-id'];
		}

		if ( $project_id ) {
			return $this->get_filters()->print_notes_for_product_filters( $project_id, false );
		}

		return '';
	}

	public function filters_shortcode( $attributes ) {
		$project_id = $this->get_filters()->get_default_filter_id();

		if ( is_array( $attributes ) && isset( $attributes['id'] ) ) {
			$project_id = $attributes['id'];
		}

		if ( $project_id ) {
			ob_start();

			$this->get_filters()->print_product_filters( $project_id );

			return ob_get_clean();
		}

		return '';
	}

	public function product_shortcode_attribute( $out, $pairs, $attributes, $shortcode ) {
		if ( ! isset( $attributes['filter-id'] ) && function_exists( 'wc_get_loop_prop' ) ) {
			$attributes['filter-id'] = wc_get_loop_prop( 'wcpf_filter_id', false );
		}

		if ( isset( $attributes['filter-id'] ) && $attributes['filter-id'] ) {
			$out['filter-id'] = $attributes['filter-id'];

			if ( function_exists( 'wc_set_loop_prop' ) ) {
				wc_set_loop_prop( 'wcpf_filter_id', $attributes['filter-id'] );
			}

			if ( isset( $_GET['orderby'] ) && isset( $attributes['use-sorting'] ) ) { // phpcs:ignore WordPress.Security
				$order_by = wc_clean( wp_unslash( $_GET['orderby'] ) ); // phpcs:ignore WordPress.Security

				$order_by = explode( '-', $order_by );

				$out['orderby'] = esc_attr( $order_by[0] );

				if ( isset( $order_by[1] ) ) {
					$out['order'] = $order_by[1];
				}
			}
		}

		return $out;
	}

	public function filter_product_query( $query_vars, $attributes ) {
		if ( isset( $attributes['filter-id'] ) ) {
			$product_query = new \WP_Query();

			$query_vars['wcpf_filter_id'] = $attributes['filter-id'];

			$product_query->parse_query( $query_vars );

			$this->get_filters()->save_query_and_apply_filters( $product_query );

			$query_vars = $product_query->query_vars;
		}

		return $query_vars;
	}

	public function products_loop_no_result( $attributes ) {
		if ( isset( $attributes['filter-id'] ) ) {
			$this->get_template_loader()->render_template(
				'shortcode-loop-no-results.php',
				array(
					'project_id' => $attributes['filter-id'],
				)
			);
		}
	}
}
