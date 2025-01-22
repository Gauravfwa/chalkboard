<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Price_Slider;

use WooCommerce_Product_Filter_Plugin\Filter\Component;

/**
 * Class Filter_Component
 *
 * @package WooCommerce_Product_Filter_Plugin\Field\Price_Slider
 */
class Filter_Component extends Component\Abstract_Filtering_Component implements Component\Rendering_Template_Interface {
	public function get_filter_keys() {
		$filter_keys = array();

		if ( $this->get_option( 'optionKeyFormat' ) === 'dash' ) {
			$filter_keys['price'] = $this->get_option( 'optionKey' );
		} elseif ( $this->get_option( 'optionKeyFormat' ) === 'two' ) {
			$filter_keys['minPrice'] = $this->get_option( 'minPriceOptionKey' );

			$filter_keys['maxPrice'] = $this->get_option( 'maxPriceOptionKey' );
		}

		return $filter_keys;
	}

	/**
	 * @param array|null $filter_values Filter values.
	 * @return array
	 */
	public function get_filter_range_values( $filter_values = null ) {
		$filter_range = array();

		if ( $this->get_option( 'optionKeyFormat' ) === 'dash' ) {
			$filter_value = $this->get_filter_value( 'price', null );

			if ( ! is_null( $filter_values ) ) {
				$filter_value = isset( $filter_values['price'] ) ? $filter_values['price'] : '';
			}

			if ( ! is_array( $filter_value ) && '' !== $filter_value && null !== $filter_value ) {
				$filter_value = explode( '-', $filter_value );
			}

			if ( isset( $filter_value[0] ) ) {
				$filter_range['min_price'] = floatval( $filter_value[0] );
			}

			if ( isset( $filter_value[1] ) ) {
				$filter_range['max_price'] = floatval( $filter_value[1] );
			}
		} elseif ( $this->get_option( 'optionKeyFormat' ) === 'two' ) {
			$min_value = $this->get_filter_value( 'minPrice', 0 );

			$max_value = $this->get_filter_value( 'maxPrice' );

			if ( ! is_null( $filter_values ) ) {
				$min_value = isset( $filter_values['minPrice'] ) ? $filter_values['minPrice'] : 0;

				$max_value = isset( $filter_values['maxPrice'] ) ? $filter_values['maxPrice'] : null;
			}

			if ( $min_value ) {
				$filter_range['min_price'] = floatval( $min_value );
			}

			if ( $max_value ) {
				$filter_range['max_price'] = floatval( $max_value );
			}
		}

		return $filter_range;
	}

	public function apply_filter_to_query( \WP_Query $product_query, $filter_values ) {
		$range = $this->get_filter_range_values( $filter_values );

		if ( count( $range ) ) {
			$min = isset( $range['min_price'] ) ? floatval( $range['min_price'] ) : 0;

			$max = isset( $range['max_price'] ) ? floatval( $range['max_price'] ) : 9999999999;

			$product_query->set(
				'meta_query',
				array_merge(
					$product_query->get( 'meta_query', array() ),
					array(
						'wcpf-price'   => array(
							'key'     => '_price',
							'value'   => array( $min, $max ),
							'compare' => 'BETWEEN',
							'type'    => 'DECIMAL(10,' . wc_get_price_decimals() . ')',
						),
						'price_filter' => true,
					)
				)
			);
		}
	}

	protected function get_min_max_range() {
		global $wpdb;

		$args = $this->get_product_query_before_filtering()->query_vars;

		$tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : array();

		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			$tax_query[] = array(
				'taxonomy' => $args['taxonomy'],
				'terms'    => array( $args['term'] ),
				'field'    => 'slug',
			);
		}

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		if ( is_tax() && isset( $args[ get_queried_object()->taxonomy ] ) ) {
			$tax_query[] = array(
				'taxonomy' => get_queried_object()->taxonomy,
				'terms'    => $args[ get_queried_object()->taxonomy ],
				'field'    => 'slug',
			);
		}

		$meta_query = new \WP_Meta_Query( $meta_query );

		$tax_query = new \WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );

		$tax_query_sql = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min, max( CEILING( price_meta.meta_value ) ) as max FROM {$wpdb->posts} ";
		$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
			AND {$wpdb->posts}.post_status = 'publish'
			AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
			AND price_meta.meta_value > '' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		$search = \WC_Query::get_main_query() ? \WC_Query::get_main_search_query_sql() : false;

		if ( $search ) {
			$sql .= ' AND ' . $search;
		}

		$sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );

		return $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	public function template_render() {
		$price_range = $this->get_min_max_range();

		if ( ! isset( $price_range['max'] ) || ! isset( $price_range['min'] ) ) {
			return;
		}

		if ( ! $price_range['max']
			|| $price_range['max'] === $price_range['min']
			|| $price_range['max'] === $price_range['min'] + 1 ) {
			return;
		}

		$this->get_template_loader()->render_template(
			'field/price-slider.php',
			array(
				'front_element'         => $this,
				'entity'                => $this->get_entity(),
				'entity_id'             => $this->get_entity_id(),
				'filter_keys'           => $this->get_filter_keys(),
				'filter_range'          => $this->get_filter_range_values(),
				'min_price'             => $price_range['min'],
				'max_price'             => $price_range['max'],
				'is_toggle_active'      => $this->get_option( 'displayTitle', true ) && $this->get_option( 'displayToggleContent', false ),
				'default_toggle_state'  => $this->get_option( 'defaultToggleState', null ),
				'is_display_title'      => $this->get_option( 'displayTitle', true ),
				'css_class'             => $this->get_option( 'cssClass', '' ),
				'display_min_max_input' => $this->get_option( 'displayMinMaxInput', false ),
				'display_price_label'   => $this->get_option( 'displayPriceLabel', true ),
			)
		);
	}
}
