<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Filter;

use WooCommerce_Product_Filter_Plugin\Filter\Component;

/**
 * Class Abstract_List_Component
 *
 * @package WooCommerce_Product_Filter_Plugin\Field\Filter
 */
abstract class Abstract_List_Component extends Component\Abstract_Filtering_Component implements Component\Rendering_Template_Interface {
	protected $supports = array();

	protected $term_item_keys = array();

	public function initial_properties() {
		parent::initial_properties();

		if ( $this->active_multi_select() ) {
			$this->filter_values['field'] = array();
		}
	}

	protected function get_query_helper() {
		return $this->get_component_register()->get( 'Query_Helper' );
	}

	/**
	 * Returns whether the enable_large_product_counts filter has been set to true.
	 *
	 * @return bool
	 *
	 * @since x.x.x
	 */
	protected function is_enabled_large_product_counts(): bool {
		return 'yes' === get_option( 'wcpf_setting_large_product_counts', 'no' );
	}

	public function get_field_key() {
		return $this->get_filter_key_by_index( 'field' );
	}

	public function get_field_value() {
		return $this->get_filter_value( 'field' );
	}

	public function get_filter_keys() {
		return array(
			'field' => $this->get_option( 'optionKey' ),
		);
	}

	protected function get_base_context() {
		return array(
			'front_element'                  => $this,
			'filter_key'                     => $this->get_field_key(),
			'filter_value'                   => $this->get_field_value(),
			'option_items'                   => $this->get_items(),
			'entity'                         => $this->get_entity(),
			'entity_id'                      => $this->get_entity_id(),
			'tree_view_style'                => $this->is_tree_view(),
			'display_hierarchical_collapsed' => $this->display_hierarchical_collapsed(),
			'is_toggle_active'               => $this->is_toggle_active(),
			'default_toggle_state'           => $this->get_option( 'defaultToggleState', null ),
			'is_display_title'               => $this->get_option( 'displayTitle', true ),
			'css_class'                      => $this->get_option( 'cssClass', '' ),
			'display_product_count'          => $this->display_product_counts(),
			'see_more_options'               => $this->is_active_see_more_options() ? $this->get_option( 'seeMoreOptionsBy', 'scrollbar' ) : false,
			'is_enabled_element'             => $this->is_enabled_element(),
		);
	}

	protected function get_product_count_policy() {
		return $this->get_option( 'productCountPolicy', 'for-option-only' );
	}

	protected function is_enabled_element() {
		return $this->check_rules_from_option(
			'displayRules',
			array(
				'use_selected_options' => true,
			)
		);
	}

	protected function is_active_see_more_options() {
		return $this->supports( 'see_more_options_by' )
			&& $this->get_option( 'seeMoreOptionsBy', 'scrollbar' ) !== 'disabled';
	}

	protected function active_multi_select() {
		if ( $this->supports( 'multi_select_toggle' ) ) {
			return $this->get_option( 'multiSelect', true );
		}

		return $this->supports( 'multi_select' );
	}

	protected function display_product_counts() {
		return $this->supports( 'product_counts' ) && $this->get_option( 'displayProductCount', true );
	}

	protected function action_for_empty_options() {
		return $this->get_option( 'actionForEmptyOptions', 'noAction' );
	}

	protected function is_enable_product_counts_query() {
		return $this->display_product_counts() || $this->action_for_empty_options() !== 'noAction';
	}

	protected function is_toggle_active() {
		return $this->get_option( 'displayTitle', true )
			&& $this->get_option( 'displayToggleContent', false )
			&& $this->supports( 'toggle_content' );
	}

	protected function is_tree_view() {
		$items_source = $this->get_option( 'itemsSource' );

		if ( in_array( $items_source, array( 'category', 'taxonomy' ), true )
			&& $this->get_option( 'itemsDisplay' ) !== 'parent'
			&& $this->get_option( 'itemsDisplayHierarchical' )
			&& $this->supports( 'hierarchical' ) ) {
			return true;
		}

		return false;
	}

	protected function display_hierarchical_collapsed() {
		return $this->is_tree_view() && $this->get_option( 'displayHierarchicalCollapsed' );
	}

	protected function get_taxonomy() {
		$items_source = $this->get_option( 'itemsSource' );

		$taxonomy = false;

		if ( 'attribute' === $items_source ) {
			$taxonomy = wc_attribute_taxonomy_name( $this->get_option( 'itemsSourceAttribute' ) );
		} elseif ( 'tag' === $items_source ) {
			$taxonomy = 'product_tag';
		} elseif ( 'category' === $items_source ) {
			$taxonomy = 'product_cat';
		} elseif ( 'taxonomy' === $items_source ) {
			$taxonomy = $this->get_option( 'itemsSourceTaxonomy' );
		}

		return $taxonomy;
	}

	protected function get_items() {
		$items = array();

		if ( $this->supports( 'reset_item' )
			&& $this->get_option( 'titleItemReset', '' ) ) {
			$items['reset_item'] = array(
				'key'                 => '',
				'title'               => $this->get_option( 'titleItemReset', '' ),
				'option_is_set'       => $this->check_option_is_set( null ),
				'child_option_is_set' => false,
				'disabled'            => false,
			);
		}

		$items_source = $this->get_option( 'itemsSource' );

		if ( in_array( $items_source, array( 'attribute', 'tag', 'category', 'taxonomy' ), true ) ) {
			$taxonomy = $this->get_taxonomy();

			if ( ! taxonomy_exists( $taxonomy ) ) {
				return array();
			}

			$query_args = array();

			$display = $this->get_option( 'itemsDisplay' );

			$need_child = 'parent' !== $display;

			if ( in_array( $items_source, array( 'attribute', 'tag' ), true ) ) {
				$display = $this->get_option( 'itemsDisplayWithoutParents' );

				$need_child = false;
			}

			$root_term_id = 0;

			if ( 'category' === $items_source && 'all' !== $this->get_option( 'itemsSourceCategory' ) ) {
				$root_term_id = absint( $this->get_option( 'itemsSourceCategory' ) );
			}

			$queried_object = $this->get_product_query_before_filtering()->get_queried_object();

			if ( $this->get_product_query_before_filtering()->is_tax
				&& $queried_object instanceof \WP_Term
				&& $queried_object->taxonomy === $taxonomy
				&& is_tax( $taxonomy, '' ) ) {
				if ( $root_term_id !== $queried_object->term_id ) {
					$queried_object_parents = get_ancestors( $queried_object->term_id, $taxonomy, 'taxonomy' );

					if ( 0 === $root_term_id || in_array( $root_term_id, $queried_object_parents, true ) ) {
						$root_term_id = $queried_object->term_id;
					}
				}
			}

			if ( 'selected' === $display ) {
				$query_args['include'] = array_map( 'absint', (array) $this->get_option( 'taxonomySelectedItems' ) );

				if ( ! count( $query_args['include'] ) ) {
					return array();
				}
			}

			if ( 'except' === $display ) {
				$query_args['exclude'] = array_map( 'absint', (array) $this->get_option( 'taxonomyExceptItems' ) );
			}

			$terms = get_terms(
				$this->get_hook_manager()->apply_filters(
					'wcpf_list_component_get_term_args',
					array_merge(
						array(
							'taxonomy'     => $taxonomy,
							'hierarchical' => true,
							'menu_order'   => 'asc',
							'order'        => 'asc',
						),
						$query_args
					)
				)
			);

			$term_slugs = array();

			foreach ( $terms as $index => $term ) {
				$term_slugs[ $term->term_id ] = $term->slug;

				if ( ( 'parent' === $display || 'all' === $display ) && $term->parent !== $root_term_id ) {
					unset( $terms[ $index ] );
				} elseif ( ( 'selected' === $display || 'except' === $display ) && 0 !== $root_term_id ) {
					$parents = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

					if ( ! in_array( $root_term_id, $parents, true ) ) {
						unset( $terms[ $index ] );

						array_pop( $term_slugs );
					}
				}
			}

			if ( 'selected' === $display || 'except' === $display ) {
				foreach ( $terms as $index => $term ) {
					if ( isset( $term_slugs[ $term->parent ] ) ) {
						unset( $terms[ $index ] );

						continue;
					}

					$parents = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

					$term_ids = array_keys( $term_slugs );

					if ( count( array_intersect( $parents, $term_ids ) ) ) {
						unset( $terms[ $index ] );
					}
				}
			}

			$term_items = $this->get_term_items(
				$terms,
				array(
					'need_child' => $need_child,
					'include'    => isset( $query_args['include'] ) ? $query_args['include'] : null,
					'exclude'    => isset( $query_args['exclude'] ) ? $query_args['exclude'] : null,
				)
			);

			if ( $term_slugs && $this->is_enable_product_counts_query() ) {
				$quantity_available = 0;

				$this->prepare_options(
					$term_items,
					$this->get_product_counts_in_terms( $term_slugs ),
					$quantity_available
				);

				if ( 0 === $quantity_available ) {
					$term_items = null;
				}
			}

			if ( is_array( $term_items ) && count( $term_items ) ) {
				$items = array_merge( $items, $term_items );
			} else {
				$items = array();
			}
		} elseif ( 'stock-status' === $items_source ) {
			$displayed_statuses = $this->get_option( 'displayedStockStatuses', array( 'in-stock', 'out-of-stock', 'on-backorder' ) );

			$stock_statuses = array();

			if ( is_array( $displayed_statuses ) && in_array( 'in-stock', $displayed_statuses, true ) ) {
				$stock_statuses['in-stock'] = array(
					'key'                 => 'in-stock',
					'title'               => $this->get_option( 'inStockText', __( 'In stock', 'woocommerce' ) ),
					'option_is_set'       => $this->check_option_is_set( 'in-stock' ),
					'child_option_is_set' => false,
					'disabled'            => false,
				);
			}

			if ( is_array( $displayed_statuses ) && in_array( 'out-of-stock', $displayed_statuses, true ) ) {
				$stock_statuses['out-of-stock'] = array(
					'key'                 => 'out-of-stock',
					'title'               => $this->get_option( 'outOfStockText', __( 'Out of stock', 'woocommerce' ) ),
					'option_is_set'       => $this->check_option_is_set( 'out-of-stock' ),
					'child_option_is_set' => false,
					'disabled'            => false,
				);
			}

			if ( is_array( $displayed_statuses ) && in_array( 'on-backorder', $displayed_statuses, true ) ) {
				$stock_statuses['on-backorder'] = array(
					'key'                 => 'on-backorder',
					'title'               => $this->get_option( 'onBackorderText', __( 'On backorder', 'woocommerce' ) ),
					'option_is_set'       => $this->check_option_is_set( 'on-backorder' ),
					'child_option_is_set' => false,
					'disabled'            => false,
				);
			}

			if ( count( $stock_statuses ) && $this->is_enable_product_counts_query() ) {
				$quantity_available = 0;

				$this->prepare_options(
					$stock_statuses,
					$this->get_product_counts_in_stock_statuses( array_keys( $stock_statuses ) ),
					$quantity_available
				);

				if ( 0 === $quantity_available ) {
					$stock_statuses = array();
				}
			}

			$items = count( $stock_statuses ) ? array_merge( $items, $stock_statuses ) : array();
		}

		return $items;
	}

	protected function prepare_options( &$options, $product_counts, &$quantity_available = 0 ) {
		$action_for_empty_options = $this->action_for_empty_options();

		foreach ( $options as $option_id => $option ) {
			$options[ $option_id ]['product_count'] = array_key_exists( $option_id, $product_counts ) ? $product_counts[ $option_id ] : 0;

			if ( ! $option['option_is_set'] && ! $option['child_option_is_set'] ) {
				if ( 'hide' === $action_for_empty_options && 0 === $options[ $option_id ]['product_count'] ) {
					unset( $options[ $option_id ] );

					continue;
				} elseif ( 'markAsDisabled' === $action_for_empty_options && 0 === $options[ $option_id ]['product_count'] ) {
					$options[ $option_id ]['disabled'] = true;
				} else {
					$quantity_available++;
				}
			} else {
				$quantity_available++;
			}

			if ( $this->display_product_counts() ) {
				$options[ $option_id ]['product_count_html'] = $this->get_product_counts_html( $options[ $option_id ] );
			}

			if ( 'markAsDisabled' === $action_for_empty_options && 0 === $options[ $option_id ]['product_count'] ) {
				$options[ $option_id ]['disabled'] = true;
			}

			if ( isset( $option['children'] ) && is_array( $option['children'] ) ) {
				$this->prepare_options( $options[ $option_id ]['children'], $product_counts, $quantity_available );
			}
		}

		if ( $this->supports( 'sorting' ) && $this->get_option( 'orderby', 'order' ) === 'count' ) {
			uasort( $options, array( $this, 'sort_term_items' ) );
		}
	}

	protected function check_option_is_set( $value ) {
		return is_array( $this->get_field_value() )
			? in_array( $value, $this->get_field_value(), true )
			: $this->get_field_value() === $value;
	}

	protected function get_term_items( $terms, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'need_child' => false,
				'include'    => null,
				'exclude'    => null,
				'hide_terms' => array(),
			)
		);

		$items = array();

		$need_sort = $this->supports( 'sorting' ) && $this->get_option( 'orderby', 'order' ) === 'name';

		foreach ( $terms as $term ) {
			$key = urldecode( $term->slug );

			$item = array(
				'key'                 => $key,
				'title'               => $term->name,
				'option_is_set'       => $this->check_option_is_set( $key ),
				'child_option_is_set' => false,
				'disabled'            => false,
			);

			if ( taxonomy_is_product_attribute( $term->taxonomy ) ) {
				$item['order'] = get_term_meta( $term->term_id, 'order_' . $term->taxonomy, true );
			} else {
				$item['order'] = get_term_meta( $term->term_id, 'order', true );
			}

			if ( $item['order'] && $this->get_option( 'orderby', 'order' ) === 'order' ) {
				$need_sort = true;
			}

			$this->term_item_keys[ $key ] = $term->term_id;

			if ( $args['need_child'] ) {
				$child_term_ids = $this->get_query_helper()->get_term_children( $term->term_id, $term->taxonomy );

				if ( is_array( $args['exclude'] ) ) {
					foreach ( $child_term_ids as $index => $child_term_id ) {
						$item_child_ids = get_term_children( $child_term_id, $term->taxonomy );

						$child_counts = count( $item_child_ids );

						$exclude_child = $child_counts && count( array_intersect( $item_child_ids, $args['exclude'] ) ) === $child_counts;

						$exclude_item = in_array( $child_term_id, $args['exclude'], true );

						if ( $exclude_item ) {
							if ( $exclude_child ) {
								unset( $child_term_ids[ $index ] );
							} else {
								$args['hide_terms'][] = $child_term_id;
							}
						}
					}
				}

				if ( is_array( $args['include'] ) ) {

					foreach ( $child_term_ids as $index => $child_term_id ) {
						$item_child_ids = get_term_children( $child_term_id, $term->taxonomy );

						$child_in_tree = count( array_intersect( $item_child_ids, $args['include'] ) ) > 0;

						$include_item = in_array( $child_term_id, $args['include'], true );

						if ( ! $include_item ) {
							if ( ! $child_in_tree ) {
								unset( $child_term_ids[ $index ] );
							} else {
								$args['hide_terms'][] = $child_term_id;
							}
						}
					}
				}

				if ( count( $child_term_ids ) ) {
					$child_terms = array();

					foreach ( $child_term_ids as $child_term_id ) {
						$child_terms[] = get_term( $child_term_id );
					}

					$item['children'] = $this->get_term_items( $child_terms, $args );

					foreach ( $item['children'] as $child_item ) {
						if ( $child_item['option_is_set'] || $child_item['child_option_is_set'] ) {
							$item['child_option_is_set'] = true;

							break;
						}
					}
				} else {
					$item['children'] = array();
				}
			}

			$is_hidden_term = in_array( $term->term_id, $args['hide_terms'], true );

			if ( $is_hidden_term && isset( $item['children'] ) && is_array( $item['children'] ) ) {
				$items += $item['children'];
			} elseif ( ! $is_hidden_term ) {
				$items[ $term->term_id ] = $item;
			}
		}

		if ( $need_sort ) {
			uasort( $items, array( $this, 'sort_term_items' ) );
		}

		return $items;
	}

	/**
	 * Sort term items based on the configured "orderby" value.
	 *
	 * This method needs to return an integer value. -1 when the first value is less than the second,
	 * 0 when they are equal, and 1 when the first is greater than the second.
	 *
	 * @param array $first  First item to compare.
	 * @param array $second Second item to compare.
	 *
	 * @return int
	 */
	public function sort_term_items( $first, $second ) {
		$order_by = $this->get_option( 'orderby', 'order' );

		switch ( $order_by ) {
			case 'order':
				return $first['order'] <=> $second['order'];

			case 'name':
				if ( is_numeric( $first['title'] ) && is_numeric( $second['title'] ) ) {
					return $first['title'] <=> $second['title'];
				}

				return strcmp( $first['title'], $second['title'] );

			case 'count':
				return $second['product_count'] <=> $first['product_count'];

			default:
				return 0;
		}
	}

	protected function get_product_counts_html( $item ) {
		return apply_filters(
			'wcpf_product_counts_html',
			'<span class="wcpf-product-counts">(' . $item['product_count'] . ')</span>',
			$item['product_count'],
			$item,
			$this
		);
	}

	public function apply_filter_to_query( \WP_Query $product_query, $filter_values ) {
		$filter_value = isset( $filter_values['field'] ) ? $filter_values['field'] : null;

		$items_source = $this->get_option( 'itemsSource' );

		$query_type = $this->get_option( 'queryType', 'or' );

		if ( in_array( $items_source, array( 'attribute', 'tag', 'category', 'taxonomy' ), true ) ) {
			if ( ! $this->active_multi_select() ) {
				$filter_value = array( $filter_value );
			} else {
				$filter_value = (array) $filter_value;
			}

			$taxonomy = $this->get_taxonomy();

			$operator = '';

			if ( $this->active_multi_select() ) {
				if ( 'and' === $query_type ) {
					$operator = 'AND';
				} elseif ( 'or' === $query_type ) {
					$operator = 'IN';
				}
			} else {
				$operator = 'IN';
			}

			$tax_query_item = array();

			if ( 'AND' === $operator ) {
				$tax_query_item['relation'] = 'AND';

				foreach ( $filter_value as $index => $value ) {
					$tax_rule = array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $value,
						'operator' => 'IN',
					);

					if ( in_array( '0', $filter_value, true ) ) {
						$tax_rule['terms'] = get_terms(
							array(
								'taxonomy' => $taxonomy,
								'fields'   => 'ids',
								'slug'     => $filter_value,
							)
						);

						$tax_rule['field'] = 'term_id';
					}

					$tax_query_item[] = $tax_rule;
				}
			} elseif ( 'IN' === $operator ) {
				$tax_query_item = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $filter_value,
					'operator' => $operator,
				);

				if ( in_array( '0', $filter_value, true ) ) {
					$tax_query_item['terms'] = get_terms(
						array(
							'taxonomy' => $taxonomy,
							'fields'   => 'ids',
							'slug'     => $filter_value,
						)
					);

					$tax_query_item['field'] = 'term_id';
				}
			}

			$product_query->set(
				'tax_query',
				array_merge(
					$product_query->get( 'tax_query', array() ),
					array( 'wcpf_' . $this->get_field_key() => $tax_query_item )
				)
			);

			$this->get_object_register()->save(
				'selected_options',
				array_merge(
					$this->get_object_register()->get( 'selected_options', array() ),
					array(
						'wcpf_' . $this->get_field_key() => array(
							'terms'      => $filter_value,
							'query_type' => $query_type,
							'taxonomy'   => $taxonomy,
						),
					)
				)
			);
		} elseif ( 'stock-status' === $items_source ) {
			$statuses = $this->get_selected_stock_statuses( $filter_value );

			if ( count( $statuses ) ) {
				$product_query->set( 'wcpf_stock_status', $statuses );
			}
		}
	}

	protected function get_selected_stock_statuses( $filter_value = false ) {
		$statuses = array();

		$status_alias = array_keys( $this->get_query_helper()->get_stock_status_meta_keys() );

		if ( false === $filter_value ) {
			$filter_value = $this->get_field_value();
		}

		if ( is_array( $filter_value ) && $this->active_multi_select() ) {
			foreach ( $filter_value as $value ) {
				if ( in_array( $value, $status_alias, true ) ) {
					$statuses[] = $value;
				}
			}
		} elseif ( in_array( $filter_value, $status_alias, true ) ) {
			$statuses[] = $filter_value;
		}

		return $statuses;
	}

	protected function get_selected_term_ids() {
		if ( ! $this->get_field_value() ) {
			return null;
		}

		$selected_term_ids = array();

		if ( is_array( $this->get_field_value() ) ) {
			foreach ( $this->get_field_value() as $value ) {
				if ( isset( $this->term_item_keys[ $value ] ) ) {
					$selected_term_ids[] = $this->term_item_keys[ $value ];
				}
			}
		} else {
			if ( isset( $this->term_item_keys[ $this->get_field_value() ] ) ) {
				$selected_term_ids[] = $this->term_item_keys[ $this->get_field_value() ];
			}
		}

		return $selected_term_ids;
	}

	/**
	 * @todo remove"wcpf_product_counts_in_stock_statuses_clauses" filter by 2.0.0
	 */
	protected function get_product_counts_in_stock_statuses( $statuses ) {
		global $wpdb;

		$product_query = $this->get_product_query_after_filtering();

		$tax_query = $this->get_query_helper()->get_tax_query( $product_query );

		$meta_query = $this->get_query_helper()->get_meta_query( $product_query );

		$meta_query = new \WP_Meta_Query( $meta_query );

		$tax_query = new \WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );

		$tax_query_sql = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$search_sql = $this->get_query_helper()->get_search_query_sql( $product_query );

		$status_meta_keys = $this->get_query_helper()->get_stock_status_meta_keys();

		$use_meta_keys = array();

		foreach ( $statuses as $status ) {
			if ( isset( $status_meta_keys[ $status ] ) ) {
				$use_meta_keys[] = $status_meta_keys[ $status ];
			}
		}

		$status_values_in_meta_sql = $wpdb->prepare(
			substr( str_repeat( ',%s', count( $use_meta_keys ) ), 1 ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$use_meta_keys
		);

		$query = array(
			'select'   => "SELECT wcpf_sspc_postmeta.meta_value as status, GROUP_CONCAT( {$wpdb->posts}.ID ) as post_ids",
			'from'     => "FROM {$wpdb->posts}",
			'join'     => "
                INNER JOIN {$wpdb->postmeta} AS wcpf_sspc_postmeta ON {$wpdb->posts}.ID = wcpf_sspc_postmeta.post_id
			" . $tax_query_sql['join'] . $meta_query_sql['join'],
			'where'    => "
                WHERE {$wpdb->posts}.post_type IN ( 'product' )
                AND {$wpdb->posts}.post_status = 'publish'
                AND (wcpf_sspc_postmeta.meta_key = '_stock_status' AND wcpf_sspc_postmeta.meta_value IN ($status_values_in_meta_sql))
                "
					. $tax_query_sql['where'] . $meta_query_sql['where'],
			'group_by' => 'GROUP BY wcpf_sspc_postmeta.meta_value',
		);

		$enable_large_product_counts = $this->is_enabled_large_product_counts();

		if ( $enable_large_product_counts ) {
			$query['select']   = "SELECT wcpf_sspc_postmeta.meta_value as status, $wpdb->posts.ID as post_id";
			$query['group_by'] = '';
		}

		if ( isset( $product_query->query_vars['post__in'] ) && is_array( $product_query->query_vars['post__in'] ) && count( $product_query->query_vars['post__in'] ) ) {
			$post__in = implode( ',', array_map( 'absint', $product_query->query_vars['post__in'] ) );

			$query['where'] .= " AND {$wpdb->posts}.ID IN ($post__in)";
		}

		if ( $search_sql ) {
			$search_sql = apply_filters( 'wcpf_product_counts_search_sql', $search_sql );

			$query['where'] .= ' ' . $search_sql;
		}

		$query = apply_filters_deprecated(
			'wcpf_product_counts_in_stock_statuses_clauses',
			array(
				$query,
				array(
					'component' => $this,
					'statuses'  => $statuses,
				),
			),
			'1.2.0',
			null,
			__( 'This filter will be removed completely by version 2.0.0', 'wcpf' )
		);

		$query_sql = implode( ' ', $query );

		$query_hash = md5( $query_sql );

		$cached_posts = array();

		$cache = apply_filters( 'wcpf_product_counts_maybe_cache', ! $this->get_plugin()->is_debug_mode() );

		if ( $cache ) {
			$cached_posts = (array) get_transient( 'wcpf_products_in_stock_statuses' );
		}

		if ( ! isset( $cached_posts[ $query_hash ] ) ) {
			$results = $wpdb->get_results( $query_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( $enable_large_product_counts ) {
				$temp_status_posts = [];
				foreach ( $results as $r ) {
					$temp_status_posts[ $r['status'] ][] = $r['post_id'];
				}
				foreach ( $temp_status_posts as $status => $post_ids ) {
					$cached_posts[ $query_hash ][ $status ] = implode( ',', $post_ids );
				}
			} else {
				$cached_posts[ $query_hash ] = wp_list_pluck( $results, 'post_ids', 'status' );
			}

			if ( $cache ) {
				set_transient( 'wcpf_products_in_stock_statuses', $cached_posts, DAY_IN_SECONDS );
			}
		}

		$status_meta_to_alias = array_flip( $status_meta_keys );

		$product_counts = array();

		foreach ( $cached_posts[ $query_hash ] as $status => $post_ids ) {
			if ( isset( $status_meta_to_alias[ $status ] ) ) {
				$status = $status_meta_to_alias[ $status ];
			}

			if ( $post_ids ) {
				$post_ids = array_unique( explode( ',', $post_ids ) );
			}

			if ( ! is_array( $post_ids ) || ! count( $post_ids ) ) {
				$product_counts[ $status ] = 0;

				continue;
			}

			$product_counts[ $status ] = count( $post_ids );
		}

		return $product_counts;
	}

	/**
	 * @param array $terms array of terms
	 *
	 * @return array array of term id and post counts
	 */
	protected function get_product_counts_in_terms( $terms ) {
		global $wpdb;

		$term_ids = array_keys( $terms );

		$tax_query_index = 'wcpf_' . $this->get_field_key();

		$once_tree_select = $this->supports( 'once_tree_select' ) && $this->is_tree_view();

		$multi_select = $this->active_multi_select();

		$query_type = $this->get_option( 'queryType' );

		$taxonomy = $this->get_taxonomy();

		$query_term_ids = array_map( 'absint', $term_ids );

		$child_term_ids = array();

		foreach ( $term_ids as $term_id ) {
			$child_term_ids[ $term_id ] = get_term_children( $term_id, $taxonomy );

			if ( count( $child_term_ids[ $term_id ] ) ) {
				$query_term_ids = array_merge( $query_term_ids, $child_term_ids[ $term_id ] );
			}
		}

		$is_customized = apply_filters(
			'wcpf_product_counts_is_customized',
			false,
			array(
				'terms'      => $terms,
				'taxonomy'   => $taxonomy,
				'query_type' => $query_type,
				'component'  => $this,
				'option_key' => $tax_query_index,
			)
		);

		// if query type "or", consider selected options.
		$selected_term_ids = $is_customized || $once_tree_select || ( $multi_select && 'or' === $query_type )
			? $this->get_selected_term_ids()
			: false;

		if ( is_array( $selected_term_ids ) ) {
			$query_term_ids = array_merge( $query_term_ids, $selected_term_ids );
		}

		$query_term_ids = array_map( 'esc_sql', array_unique( $query_term_ids ) );

		$product_query = $this->get_product_query_after_filtering();

		$tax_query = null;

		$meta_query = $this->get_query_helper()->get_meta_query( $product_query );

		// deleting component conditions to keep condition "or".
		if ( ( $is_customized
			|| ( 'or' === $query_type && $multi_select )
			|| ! $multi_select
			|| $once_tree_select
			|| ( $this->get_product_count_policy() === 'with-selected-options' && $multi_select ) )
			&& isset( $product_query->query_vars['tax_query'], $product_query->query_vars['tax_query'][ $tax_query_index ] ) ) {
			$original_query_vars = $product_query->query_vars;

			unset( $product_query->query_vars['tax_query'][ $tax_query_index ] );

			$product_query->parse_tax_query( $product_query->query_vars );

			$tax_query = $this->get_query_helper()->get_tax_query( $product_query );

			// restore original query_vars and tax_query.
			$product_query->query_vars = $original_query_vars;

			$product_query->parse_tax_query( $product_query->query_vars );
		} else {
			$tax_query = $this->get_query_helper()->get_tax_query( $product_query );
		}

		$meta_query = new \WP_Meta_Query( $meta_query );

		$tax_query = new \WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );

		$tax_query_sql = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$search_sql = $this->get_query_helper()->get_search_query_sql( $product_query );

		$query = array(
			'select'   => "SELECT terms.term_id as term_id, GROUP_CONCAT( {$wpdb->posts}.ID ) as post_ids",
			'from'     => "FROM {$wpdb->posts}",
			'join'     => "
                INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
                INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
                INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'],
			'where'    => "
                WHERE {$wpdb->posts}.post_type IN ( 'product' )
                AND {$wpdb->posts}.post_status = 'publish'"
					. $tax_query_sql['where'] . $meta_query_sql['where']
					. ' AND terms.term_id IN (' . implode( ',', $query_term_ids ) . ')',
			'group_by' => 'GROUP BY terms.term_id',
		);

		$enable_large_product_counts = $this->is_enabled_large_product_counts();

		if ( $enable_large_product_counts ) {
			$query['select']   = "SELECT terms.term_id as term_id, $wpdb->posts.ID as post_id";
			$query['group_by'] = '';
		}

		if ( isset( $product_query->query_vars['post__in'] ) && is_array( $product_query->query_vars['post__in'] ) && count( $product_query->query_vars['post__in'] ) ) {
			$post__in = implode( ',', array_map( 'absint', $product_query->query_vars['post__in'] ) );

			$query['where'] .= " AND {$wpdb->posts}.ID IN ($post__in)";
		}

		if ( $search_sql ) {
			$search_sql = apply_filters( 'wcpf_product_counts_search_sql', $search_sql );

			$query['where'] .= ' ' . $search_sql;
		}

		$query = apply_filters(
			'wcpf_product_counts_clauses',
			$query,
			array(
				'terms'         => $terms,
				'taxonomy'      => $taxonomy,
				'query_type'    => $query_type,
				'component'     => $this,
				'option_key'    => $tax_query_index,
				'is_customized' => $is_customized,
			)
		);

		$query_sql = implode( ' ', $query );

		$query_hash = md5( $query_sql );

		$cached_posts = array();

		$cache = apply_filters( 'wcpf_product_counts_maybe_cache', ! $this->get_plugin()->is_debug_mode() );

		if ( $cache ) {
			$cached_posts = (array) get_transient( 'wcpf_products_in_' . $taxonomy );
		}

		if ( ! isset( $cached_posts[ $query_hash ] ) ) {
			$results = $wpdb->get_results( $query_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			if ( $enable_large_product_counts ) {
				$temp_term_posts = [];
				foreach ( $results as $r ) {
					$temp_term_posts[ $r['term_id'] ][] = $r['post_id'];
				}
				foreach ( $temp_term_posts as $term_id => $post_ids ) {
					$cached_posts[ $query_hash ][ $term_id ] = implode( ',', $post_ids );
				}
			} else {
				$cached_posts[ $query_hash ] = wp_list_pluck( $results, 'post_ids', 'term_id' );
			}

			if ( $cache ) {
				set_transient( 'wcpf_products_in_' . $taxonomy, $cached_posts, DAY_IN_SECONDS );
			}
		}

		$term_posts = $cached_posts[ $query_hash ];

		$pad_term_posts = array();

		foreach ( $term_ids as $term_id ) {
			if ( isset( $pad_term_posts[ $term_id ] ) ) {
				continue;
			}

			$posts_in_term = array();

			if ( isset( $term_posts[ $term_id ] ) ) {
				$posts_in_term = explode( ',', $term_posts[ $term_id ] );
			}

			foreach ( $child_term_ids[ $term_id ] as $child_term_id ) {
				if ( isset( $term_posts[ $child_term_id ] ) ) {
					$posts_in_term = array_merge(
						$posts_in_term,
						explode( ',', $term_posts[ $child_term_id ] )
					);
				}
			}

			$pad_term_posts[ $term_id ] = $posts_in_term;
		}

		$selected_term_post_ids = array();

		if ( ( $this->get_product_count_policy() === 'with-selected-options' && $multi_select )
			&& ! $once_tree_select
			&& $multi_select
			&& 'or' === $query_type
			&& is_array( $selected_term_ids ) ) {
			foreach ( $selected_term_ids as $selected_term_id ) {
				if ( isset( $pad_term_posts[ $selected_term_id ] ) ) {
					$selected_term_post_ids = array_merge(
						$selected_term_post_ids,
						$pad_term_posts[ $selected_term_id ]
					);
				}
			}

			$selected_term_post_ids = array_unique( $selected_term_post_ids );
		}

		$product_counts = array();

		foreach ( $pad_term_posts as $term_id => $post_ids ) {
			if ( ! count( $post_ids ) ) {
				$product_counts[ $term_id ] = 0;

				continue;
			}

			if ( ( $this->get_product_count_policy() === 'with-selected-options' && $multi_select )
				&& is_array( $selected_term_ids ) && in_array( $term_id, $selected_term_ids, true ) ) {
				$post_ids = array_unique( $post_ids );

				$product_counts[ $term_id ] = count( $post_ids );

				continue;
			}

			if ( $this->get_product_count_policy() === 'with-selected-options' && $multi_select ) {
				if ( ( $is_customized || $once_tree_select ) && is_array( $selected_term_ids ) ) {
					foreach ( $selected_term_ids as $selected_term_id ) {
						if ( ! isset( $pad_term_posts[ $selected_term_id ] ) ) {
							continue;
						}

						if ( 'or' === $query_type && ! in_array( $term_id, $child_term_ids[ $selected_term_id ], true ) ) {
							$post_ids = array_merge( $post_ids, $pad_term_posts[ $selected_term_id ] );
						} elseif ( 'and' === $query_type && ! in_array( $selected_term_id, $child_term_ids[ $term_id ], true ) ) {
							$post_ids = array_intersect( $post_ids, $pad_term_posts[ $selected_term_id ] );
						}
					}
				} elseif ( ! $once_tree_select && 'or' === $query_type && $selected_term_post_ids ) {
					$post_ids = array_merge( $post_ids, $selected_term_post_ids );
				}
			}

			$post_ids = array_unique( $post_ids );

			$product_counts[ $term_id ] = count( $post_ids );
		}

		return $product_counts;
	}

	/**
	 * Return whether the given feature is supported by this class.
	 *
	 * @param string $feature The feature name.
	 *
	 * @return bool
	 */
	protected function supports( $feature ) {
		return in_array( $feature, $this->supports, true );
	}
}
