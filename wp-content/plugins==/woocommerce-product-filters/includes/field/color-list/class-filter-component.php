<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Color_List;

use WooCommerce_Product_Filter_Plugin\Field\Filter\Abstract_List_Component;

class Filter_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'multi_select_toggle',
		'toggle_content',
	);

	public function template_render() {
		$this->get_template_loader()->render_template( 'field/color-list.php', $this->get_base_context() );
	}

	protected function get_items() {
		$taxonomy = $this->get_taxonomy();

		$colors_options = $this->get_option( 'colors_' . $taxonomy, false );

		if ( ! is_array( $colors_options ) || ! $colors_options ) {
			return array();
		}

		$items = array();

		$term_slugs = array();

		foreach ( $colors_options as $color_option ) {
			$term = get_term( $color_option['term'] );

			if ( is_wp_error( $term ) || ! $term ) {
				continue;
			}

			$item = array(
				'key'                 => $term->slug,
				'title'               => $term->name,
				'type'                => isset( $color_option['type'] ) ? $color_option['type'] : 'color',
				'color'               => isset( $color_option['color'] ) ? $color_option['color'] : '',
				'image'               => isset( $color_option['image'] ) ? $color_option['image'] : null,
				'border_color'        => isset( $color_option['borderColor'] ) ? $color_option['borderColor'] : '',
				'marker_style'        => isset( $color_option['markerStyle'] ) ? $color_option['markerStyle'] : 'light',
				'child_option_is_set' => false,
				'disabled'            => false,
			);

			if ( ( 'color' === $item['type'] && ! $item['color'] )
				|| ( 'image' === $item['type'] && ! $item['image'] ) ) {
				continue;
			}

			$item['option_is_set'] = $this->check_option_is_set( $item['key'] );

			$items[ $term->term_id ] = $item;

			$term_slugs[ $term->term_id ] = $term->slug;
		}

		if ( $term_slugs && $this->is_enable_product_counts_query() ) {
			$quantity_available = 0;

			$this->prepare_options(
				$items,
				$this->get_product_counts_in_terms( $term_slugs ),
				$quantity_available
			);

			if ( 0 === $quantity_available ) {
				$items = array();
			}
		}

		return $items;
	}
}
