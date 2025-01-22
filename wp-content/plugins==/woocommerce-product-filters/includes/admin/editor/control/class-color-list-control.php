<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Color_List_Control extends Abstract_Control implements Preparing_For_Reload_Interface {
	protected $colors = array();

	protected $taxonomies_colors = array();

	public function get_control_type() {
		return 'ColorList';
	}

	public function initial_properties( array $params = array() ) {
		$this->option_key = 'colors_$taxonomy';

		$this->label = __( 'Colors', 'wcpf' );

		parent::initial_properties( $params );
	}

	public function render_control() {
		$this->render(
			'control/color-list.php',
			array(
				'colors' => $this->colors,
			)
		);
	}

	public function prepare_for_reload( array $options, array $context, array $control_props = array() ) {
		$current_taxonomy = $this->get_current_taxonomy( $options );

		$entity_options = $control_props['entity']['options'];

		if ( ! $current_taxonomy ) {
			return;
		}

		$this->option_key = 'colors_' . $current_taxonomy;

		$colors_option = array();

		if ( isset( $options[ $this->option_key ] )
			&& is_array( $options[ $this->option_key ] ) ) {
			$colors_option = $options[ $this->option_key ];
		} elseif ( isset( $entity_options[ $this->option_key ] )
			&& is_array( $entity_options[ $this->option_key ] ) ) {
			$colors_option = $entity_options[ $this->option_key ];
		}

		$taxonomies = get_object_taxonomies( array( 'product' ) );

		foreach ( $taxonomies as $taxonomy ) {
			if ( isset( $options[ 'colors_' . $taxonomy ] ) && is_array( $options[ 'colors_' . $taxonomy ] ) ) {
				$this->taxonomies_colors[ 'colors_' . $taxonomy ] = $options[ 'colors_' . $taxonomy ];
			}
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $current_taxonomy,
				'hide_empty' => false,
			)
		);

		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		foreach ( $colors_option as $term => $color_option ) {
			if ( ! term_exists( $term, $current_taxonomy ) ) {
				unset( $colors_option[ $term ] );
			}
		}

		foreach ( $terms as $term ) {
			if ( ! isset( $colors_option[ $term->term_id ] ) ) {
				$colors_option[ $term->term_id ] = array(
					'type'        => 'color',
					'color'       => '',
					'image'       => '',
					'borderColor' => '',
					'markerStyle' => 'light',
					'term'        => $term->term_id,
				);
			}
		}

		$this->taxonomies_colors[ 'colors_' . $current_taxonomy ] = $colors_option;

		$this->colors = $colors_option;
	}

	public function get_structure() {
		return array_merge(
			parent::get_structure(),
			array(
				'taxonomiesColors' => $this->taxonomies_colors,
				'reloadAfterInit'  => true,
				'optionsDepends'   => array(
					'itemsSourceTaxonomy',
					'itemsSourceCategory',
					'itemsSourceAttribute',
					'itemsSource',
				),
			)
		);
	}

	protected function get_current_taxonomy( $control_values ) {
		$item_source = isset( $control_values['itemsSource'] ) ? $control_values['itemsSource'] : null;

		$taxonomy = null;

		if ( 'category' === $item_source ) {
			$taxonomy = 'product_cat';
		} elseif ( 'taxonomy' === $item_source && isset( $control_values['itemsSourceTaxonomy'] ) ) {
			$taxonomy = $control_values['itemsSourceTaxonomy'];
		} elseif ( 'attribute' === $item_source && isset( $control_values['itemsSourceAttribute'] ) ) {
			$taxonomy = wc_attribute_taxonomy_name( $control_values['itemsSourceAttribute'] );
		} elseif ( 'tag' === $item_source ) {
			$taxonomy = 'product_tag';
		}

		return $taxonomy;
	}
}
