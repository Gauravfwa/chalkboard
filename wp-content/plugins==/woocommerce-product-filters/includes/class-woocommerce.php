<?php

namespace WooCommerce_Product_Filter_Plugin;

class Woocommerce extends Structure\Component {
	protected $selected_attributes = array();

	protected $product_query = array();

	protected $parts_templates = array(
		'sorting'             => 'loop/orderby.php',
		'paginationContainer' => 'loop/pagination.php',
		'resultCount'         => 'loop/result-count.php',
	);

	protected $parts_selectors = array();

	protected $already_processed_parts = array();

	public function get_filters() {
		return $this->get_component_register()->get( 'Filters' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		if ( get_option( 'wcpf_setting_dynamic_image_change', 'no' ) === 'yes' ) {
			$hook_manager->add_action( 'woocommerce_before_shop_loop', 'before_shop_loop' );

			$hook_manager->add_action( 'woocommerce_after_shop_loop', 'after_shop_loop' );
		}

		if ( class_exists( 'DOMDocument' )
			&& function_exists( 'libxml_use_internal_errors' )
			&& get_option( 'search_selectors_in_overrides_templates', 'no' ) === 'yes' ) {
			$hook_manager->add_action(
				'woocommerce_before_template_part',
				'woocommerce_before_template_part',
				150,
				4
			);

			$hook_manager->add_action(
				'woocommerce_after_template_part',
				'woocommerce_after_template_part',
				-150,
				4
			);

			$hook_manager->add_action(
				'wp_footer',
				'print_theme_selectors_script',
				25
			);
		}

		add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );
	}

	public function print_theme_selectors_script() {
		if ( count( $this->parts_selectors ) ) {
			$this->get_template_loader()->render_template(
				'theme-selectors-script.php',
				array(
					'selectors' => $this->parts_selectors,
				)
			);
		}
	}

	public function woocommerce_before_template_part( $template_name, $template_path, $located, $args ) {
		$part_key = array_search( $template_name, $this->parts_templates, true );

		if ( in_array( $part_key, $this->already_processed_parts, true ) ) {
			return;
		}

		if ( false !== $part_key && strpos( $located, get_theme_root() ) === 0 ) {
			ob_start();
		}
	}

	public function woocommerce_after_template_part( $template_name, $template_path, $located, $args ) {
		$part_key = array_search( $template_name, $this->parts_templates, true );

		if ( in_array( $part_key, $this->already_processed_parts, true ) ) {
			return;
		}

		if ( false !== $part_key && strpos( $located, get_theme_root() ) === 0 ) {
			$this->already_processed_parts[] = $part_key;

			$part_html = ob_get_flush();

			$part_html = trim( $part_html );

			if ( ! strlen( $part_html ) ) {
				return;
			}

			$document = new \DOMDocument();

			libxml_use_internal_errors( true );

			$document->loadHTML( $part_html );

			$body_nodes = $document->getElementsByTagName( 'body' );

			if ( ! $body_nodes->length ) {
				return;
			}

			$root_node = null;

			foreach ( $body_nodes->item( 0 )->childNodes as $child_node ) {
				if ( XML_ELEMENT_NODE === $child_node->nodeType
					&& in_array( $child_node->nodeName, array( 'div', 'form', 'nav', 'p', 'ul' ), true ) ) {
					$root_node = $child_node;

					break;
				}
			}

			if ( ! $root_node ) {
				return;
			}

			$part_selector = null;

			if ( $root_node->hasAttribute( 'id' ) ) {
				$part_selector = $root_node->nodeName . '#' . $root_node->getAttribute( 'id' );
			} elseif ( $root_node->hasAttribute( 'class' ) ) {
				$classes = explode( ' ', $root_node->getAttribute( 'class' ) );

				$classes = array_filter( $classes, 'strlen' );

				if ( count( $classes ) ) {
					$part_selector = $root_node->nodeName . '.' . implode( '.', $classes );
				}
			}

			if ( $part_selector ) {
				$this->parts_selectors[ $part_key ] = $part_selector;
			}
		}
	}

	public function after_shop_loop() {
		$this->get_hook_manager()->remove_filter(
			'woocommerce_product_get_image',
			'product_get_image',
			1,
			3
		);
	}

	public function before_shop_loop() {
		$this->get_hook_manager()->add_filter(
			'woocommerce_product_get_image',
			'product_get_image',
			1,
			3
		);
	}

	public function product_get_image( $image, $product, $size ) {
		global $wp_query;

		$filter_id = $wp_query->get( 'wcpf_filter_id', 0 );

		if ( 0 === $filter_id && function_exists( 'wc_get_loop_prop' ) ) {
			$filter_id = wc_get_loop_prop( 'wcpf_filter_id', 0 );
		}

		$filter_id = absint( $filter_id );

		if ( ! $filter_id || $product->get_type() !== 'variable' ) {
			return $image;
		}

		$product_query = null;

		if ( $wp_query->get( 'wcpf_filter_id' ) === $filter_id ) {
			$product_query = $wp_query;
		} elseif ( isset( $this->product_query[ $filter_id ] ) ) {
			$product_query = $this->product_query[ $filter_id ];
		} else {
			$query_vars = $this->get_filters()->get_products_query_vars( $filter_id );

			if ( ! is_array( $query_vars ) || ! isset( $query_vars['after_filtering'] ) ) {
				return $image;
			}

			$product_query = new \WP_Query();

			$product_query->parse_query( $query_vars['after_filtering'] );

			$this->product_query[ $filter_id ] = $product_query;
		}

		if ( ! $product_query->tax_query instanceof \WP_Tax_Query ) {
			$product_query->parse_tax_query( $product_query->query_vars );
		}

		if ( ! $product_query->tax_query instanceof \WP_Tax_Query
			|| ! count( $product_query->tax_query->queried_terms ) ) {
			return $image;
		}

		if ( ! isset( $this->selected_attributes[ $filter_id ] ) ) {
			$this->selected_attributes[ $filter_id ] = array();

			foreach ( $product_query->tax_query->queried_terms as $queried_taxonomy => $queried_data ) {
				if ( ! taxonomy_is_product_attribute( $queried_taxonomy ) ) {
					continue;
				}

				$slugs = 'slug' === $queried_data['field']
					? $queried_data['terms']
					: get_terms(
						array(
							'taxonomy' => $queried_taxonomy,
							'fields'   => 'slugs',
							'include'  => (array) $queried_data['terms'],
						)
					);

				$this->selected_attributes[ $filter_id ][ 'attribute_' . $queried_taxonomy ] = (array) $slugs;
			}
		}

		$selected_attributes = $this->selected_attributes[ $filter_id ];

		$variations = $product->get_children();

		if ( ! count( $selected_attributes ) || ! count( $variations ) ) {
			return $image;
		}

		$variations_rating = array();

		foreach ( $variations as $index => $variation_id ) {
			$variations_rating[ $variation_id ] = 0;

			$variation = wc_get_product( $variation_id );

			if ( ! $variation instanceof \WC_Product_Variation ) {
				continue;
			}

			$attributes = $variation->get_variation_attributes();

			foreach ( $selected_attributes as $selected_attribute => $selected_slugs ) {
				if ( isset( $attributes[ $selected_attribute ] )
					&& in_array( $attributes[ $selected_attribute ], $selected_slugs, true ) ) {
					$variations_rating[ $variation_id ]++;
				}
			}
		}

		$max_rating = max( $variations_rating );

		if ( 0 === $max_rating ) {
			return $image;
		}

		$variations = (array) array_keys( $variations_rating, $max_rating, true );

		if ( ! count( $variations ) ) {
			return $image;
		}

		$best_variation_id = $variations[0];

		$best_variation = wc_get_product( $best_variation_id );

		$this->get_hook_manager()->remove_filter( 'woocommerce_product_get_image', 'product_get_image', 1, 3 );

		if ( $best_variation instanceof \WC_Product_Variation ) {
			$image = $best_variation->get_image( $size );
		}

		$this->get_hook_manager()->add_filter( 'woocommerce_product_get_image', 'product_get_image', 1, 3 );

		return $image;
	}
}
