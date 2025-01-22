<?php

namespace WooCommerce_Product_Filter_Plugin;

use WP_Query, WP_Tax_Query, WP_Error;

/**
 * Class Query_Helper
 *
 * @package WooCommerce_Product_Filter_Plugin
 */
class Query_Helper extends Structure\Component {
	protected $stopwords = null;

	public function initial_properties() {
		$this->save_component_to_register( 'Query_Helper' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'posts_clauses', 'posts_clauses', 10, 2 );

		$hook_manager->add_filter( 'wcpf_product_counts_clauses', 'product_counts_clauses', 10, 2 );

		$hook_manager->add_filter( 'wcpf_product_counts_is_customized', 'product_counts_is_customized', 10, 2 );
	}

	public function product_counts_is_customized( $is_customized, $args ) {
		$product_query = $args['component']->get_product_query_after_filtering();

		$out_of_stock_products = get_option( 'wcpf_setting_out_of_stock_products', 'no-action' );

		if ( taxonomy_is_product_attribute( $args['taxonomy'] )
			&& ( $product_query->get( 'wcpf_stock_status' ) || 'hide-if-active-any-options' === $out_of_stock_products ) ) {
			$is_customized = true;
		}

		return $is_customized;
	}

	public function product_counts_clauses( $pieces, $args ) {
		$out_of_stock_products = get_option( 'wcpf_setting_out_of_stock_products', 'no-action' );

		$product_query = $args['component']->get_product_query_after_filtering();

		if ( ! $product_query->get( 'wcpf_stock_status' ) && 'hide-if-active-any-options' !== $out_of_stock_products ) {
			return $pieces;
		}

		$attributes = $this->get_selected_attributes();

		$stock_status_args = array();

		if ( taxonomy_is_product_attribute( $args['taxonomy'] ) && $args['is_customized'] ) {
			$attributes[ $args['option_key'] ] = array(
				'query_type' => $args['query_type'],
				'taxonomy'   => $args['taxonomy'],
				'terms'      => $args['terms'],
			);

			$stock_status_args['check_by_index'] = $args['option_key'];

			$stock_status_args['terms_table'] = 'terms';
		}

		$stock_status = 'in-stock';

		if ( $product_query->get( 'wcpf_stock_status' ) ) {
			$stock_status = $product_query->get( 'wcpf_stock_status' );
		}

		$where_sql = $this->get_stock_status_sql( $stock_status, $attributes, $stock_status_args );

		if ( strlen( $where_sql ) ) {
			$pieces['where'] .= ' AND ' . $where_sql;
		}

		return $pieces;
	}

	public function posts_clauses( $pieces, $query ) {
		if ( $query->get( 'wcpf_stock_status' ) ) {
			$where_sql = $this->get_stock_status_sql(
				$query->get( 'wcpf_stock_status' ),
				$this->get_selected_attributes()
			);

			if ( strlen( $where_sql ) ) {
				$pieces['where'] .= ' AND ' . $where_sql;
			}
		}

		return $pieces;
	}

	public function get_selected_attributes() {
		$selected_attributes = array();

		foreach ( $this->get_object_register()->get( 'selected_options', array() ) as $index => $options ) {
			if ( taxonomy_is_product_attribute( $options['taxonomy'] ) ) {
				$selected_attributes[ $index ] = $options;
			}
		}

		return $selected_attributes;
	}

	public function get_stock_status_meta_keys() {
		return array(
			'in-stock'     => 'instock',
			'out-of-stock' => 'outofstock',
			'on-backorder' => 'onbackorder',
		);
	}

	/**
	 * Generate SQL for the stock status query.
	 * Switches to get_reduced_stock_status_sql() if the 'reduced_stock_query_size' settings option is enabled.
	 *
	 * @param string|array $stock_status        Stock status or statuses to query for.
	 * @param array        $selected_attributes Any selected attribute values (can also include full list of attribute values if nothing is selected).
	 * @param array        $args                Additional arguments (check_by_index is used to indicate product count by attribute, terms_table).
	 *
	 * @return string
	 */
	protected function get_stock_status_sql( $stock_status, $selected_attributes = array(), $args = array() ) {
		$reduced_stock_query_size = ( 'yes' === get_option( 'wcpf_setting_reduced_stock_query_size', 'no' ) );

		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'check_by_index' => false,
				'terms_table'    => $wpdb->terms,
			)
		);

		$status_meta_keys = $this->get_stock_status_meta_keys();

		$stock_aliases = array_keys( $status_meta_keys );

		$meta_status_keys = array();

		if ( is_array( $stock_status ) ) {
			foreach ( $stock_status as $status ) {
				if ( in_array( $status, $stock_aliases, true ) ) {
					$meta_status_keys[] = $status_meta_keys[ $status ];
				}
			}
		} elseif ( in_array( $stock_status, $stock_aliases, true ) ) {
			$meta_status_keys[] = $status_meta_keys[ $stock_status ];
		}

		if ( ! count( $meta_status_keys ) ) {
			return '';
		}

		$where_sql = '';

		$status_values_in_meta_sql = $wpdb->prepare(
			substr( str_repeat( ',%s', count( $meta_status_keys ) ), 1 ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$meta_status_keys
		);

		$check_post_by_status_sql = " {$wpdb->posts}.ID IN ( 
                SELECT wcpf_post_meta.post_id
                FROM {$wpdb->postmeta} as wcpf_post_meta
                WHERE wcpf_post_meta.meta_key = '_stock_status' AND wcpf_post_meta.meta_value IN ({$status_values_in_meta_sql}) 
            ) ";

		$or_conditions = array();

		$and_conditions = array();

		foreach ( $selected_attributes as $index => $selected_attribute ) {
			if ( 'and' === $selected_attribute['query_type'] ) {
				$and_conditions[ $index ] = $selected_attribute;
			} elseif ( 'or' === $selected_attribute['query_type'] ) {
				$or_conditions[ $index ] = $selected_attribute;
			}
		}

		if ( ! count( $or_conditions ) && ! count( $and_conditions ) ) {
			return $check_post_by_status_sql;
		}

		$variation_ids_in_attributes = array();

		foreach ( $selected_attributes as $selected_attribute ) {
			if ( isset( $variation_ids_in_attributes[ $selected_attribute['taxonomy'] ] ) ) {
				continue;
			}

			$variation_ids = get_transient( 'wcpf_variations_in_' . $selected_attribute['taxonomy'] . '_attribute' );

			if ( ! is_array( $variation_ids ) ) {
				$variation_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = %s",
						trim( 'attribute_' . $selected_attribute['taxonomy'] )
					)
				);

				if ( ! $variation_ids ) {
					$variation_ids = array( 0 );
				}

				set_transient( 'wcpf_variations_in_' . $selected_attribute['taxonomy'] . '_attribute', $variation_ids, DAY_IN_SECONDS );
			}

			$variation_ids_in_attributes[ $selected_attribute['taxonomy'] ] = $variation_ids;
		}

		$stock_variation_pieces = array(
			'select' => 'SELECT wcpf_cv_posts.post_parent',
			'from'   => "FROM {$wpdb->posts} as wcpf_cv_posts",
			'join'   => "INNER JOIN {$wpdb->postmeta} AS wcpf_cmps_postmeta ON wcpf_cv_posts.ID = wcpf_cmps_postmeta.post_id",
			'where'  => "WHERE (wcpf_cmps_postmeta.meta_key = '_stock_status' AND wcpf_cmps_postmeta.meta_value IN ({$status_values_in_meta_sql}) )
                      AND wcpf_cv_posts.post_type = 'product_variation' 
                      AND wcpf_cv_posts.post_status = 'publish'",
		);

		$condition_number = 0;

		foreach ( $or_conditions as $condition_index => $or_condition ) {
			$meta_table_alias = 'wcpf_cv_postmeta_' . $condition_number;

			$stock_variation_pieces['join'] .= " INNER JOIN {$wpdb->postmeta} AS {$meta_table_alias} ON wcpf_cv_posts.ID = {$meta_table_alias}.post_id ";

			$stock_variation_pieces['where'] .= ' AND ';

			$variation_ids_in_attribute = isset( $variation_ids_in_attributes[ $or_condition['taxonomy'] ] ) ? $variation_ids_in_attributes[ $or_condition['taxonomy'] ] : array();

			if ( $args['check_by_index'] === $condition_index ) {
				$is_first_term = true;

				$stock_variation_pieces['where'] .= '(';

				if ( $reduced_stock_query_size ) {
					$stock_variation_pieces['where'] .= $wpdb->prepare(
						"( {$meta_table_alias}.meta_key = %s AND {$meta_table_alias}.meta_value IN ({$args['terms_table']}.slug, '') )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						trim( 'attribute_' . $or_condition['taxonomy'] )
					);
				} else {
					foreach ( $or_condition['terms'] as $term_id => $term_slug ) {
						if ( ! $is_first_term ) {
							$stock_variation_pieces['where'] .= ' OR ';
						}

						$stock_variation_pieces['where'] .= $wpdb->prepare(
							"({$args['terms_table']}.term_id = %d AND ( ( $meta_table_alias.meta_key = %s AND $meta_table_alias.meta_value IN (%s, '') )" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							. ( $variation_ids_in_attribute ? ' OR wcpf_cv_posts.ID NOT IN (' . implode( ',', (array) $variation_ids_in_attribute ) . ') ' : '' ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							. ') )',
							absint( $term_id ),
							trim( 'attribute_' . $or_condition['taxonomy'] ),
							$term_slug
						);

						$is_first_term = false;
					}
				}

				$stock_variation_pieces['where'] .= ')';
			} else {
				$or_condition['terms'][] = ''; // any value.

				$terms_sql = $wpdb->prepare(
					substr( str_repeat( ',%s', count( $or_condition['terms'] ) ), 1 ),
					$or_condition['terms']
				);

				if ( $reduced_stock_query_size ) {
					$stock_variation_pieces['where'] .= $wpdb->prepare(
						"( ( $meta_table_alias.meta_key = %s AND $meta_table_alias.meta_value IN ($terms_sql) )" . ' )', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						trim( 'attribute_' . $or_condition['taxonomy'] )
					);

				} else {
					$stock_variation_pieces['where'] .= $wpdb->prepare(
						"( ( $meta_table_alias.meta_key = %s AND $meta_table_alias.meta_value IN ($terms_sql) )" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						. ( $variation_ids_in_attribute ? ' OR wcpf_cv_posts.ID NOT IN (' . implode( ',', (array) $variation_ids_in_attribute ) . ') ' : '' ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						. ' )',
						trim( 'attribute_' . $or_condition['taxonomy'] )
					);
				}
			}

			$condition_number++;
		}

		$stock_variations_sql = '';

		if ( count( $and_conditions ) ) {
			foreach ( $and_conditions as $condition_index => $and_condition ) {
				$meta_table_alias = 'wcpf_cv_postmeta_' . $condition_number;

				$is_first = true;

				if ( $args['check_by_index'] === $condition_index ) {
					if ( strlen( $stock_variations_sql ) ) {
						$stock_variations_sql .= ' AND ';
					}

					$stock_variations_sql .= ' ( ';
				}

				$variation_ids_in_attribute = isset( $variation_ids_in_attributes[ $and_condition['taxonomy'] ] ) ? $variation_ids_in_attributes[ $and_condition['taxonomy'] ] : array();

				foreach ( $and_condition['terms'] as $term_index => $term_slug ) {
					$condition_pieces = $stock_variation_pieces;

					$condition_pieces['join'] .= " INNER JOIN {$wpdb->postmeta} AS {$meta_table_alias} ON wcpf_cv_posts.ID = {$meta_table_alias}.post_id ";

					$condition_pieces['where'] .= ' AND ';

					if ( $reduced_stock_query_size ) {
						// Use `term.slug` instead of actual names if trying to get individual counts
						if ( $args['check_by_index'] === $condition_index ) {
							$condition_pieces['where'] .= $wpdb->prepare(
								"( ( $meta_table_alias.meta_key = %s AND $meta_table_alias.meta_value IN ({$args['terms_table']}.slug, '') )" . ' )', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
								trim( 'attribute_' . $and_condition['taxonomy'] )
							);
						} else {
							$condition_pieces['where'] .= $wpdb->prepare(
								"( ( $meta_table_alias.meta_key = %s AND $meta_table_alias.meta_value IN (%s, '') )" . ' )', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
								trim( 'attribute_' . $and_condition['taxonomy'] ),
								$term_slug
							);
						}
					} else {
						$condition_pieces['where'] .= $wpdb->prepare(
							"( ( $meta_table_alias.meta_key = %s AND $meta_table_alias.meta_value IN (%s, '') )" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							. ( $variation_ids_in_attribute ? ' OR wcpf_cv_posts.ID NOT IN (' . implode( ',', (array) $variation_ids_in_attribute ) . ') ' : '' ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							. ' )',
							trim( 'attribute_' . $and_condition['taxonomy'] ),
							$term_slug
						);
					}

					if ( $args['check_by_index'] === $condition_index ) {
						if ( ! $is_first ) {
							$stock_variations_sql .= ' OR ';
						}
					} elseif ( strlen( $stock_variations_sql ) ) {
						$stock_variations_sql .= ' AND ';
					}

					if ( $reduced_stock_query_size ) {
						$stock_variations_sql .= '('
												 . "{$wpdb->posts}.ID IN (" . implode( ' ', $condition_pieces )
												 . '))';

						// When getting counts, no need to add multiple conditions
						if ( $args['check_by_index'] === $condition_index ) {
							break;
						}
					} else {
						$stock_variations_sql .= '('
												. ( $args['check_by_index'] === $condition_index
												? $wpdb->prepare(
													"{$args['terms_table']}.term_id = %d AND ", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
													absint( $term_index )
												) : '' )
												. "{$wpdb->posts}.ID IN (" . implode( ' ', $condition_pieces )
												. '))';
					}

					$is_first = false;
				}

				if ( $args['check_by_index'] === $condition_index ) {
					$stock_variations_sql .= ' ) ';
				}

				$condition_number++;
			}
		} else {
			$stock_variations_sql = "({$wpdb->posts}.ID IN (" . implode( ' ', $stock_variation_pieces ) . '))';
		}

		$variable_term = get_term_by( 'slug', 'variable', 'product_type' );

		if ( ! $variable_term ) {
			return '';
		}

		$where_sql .= " (
            CASE 
                WHEN (
                    {$wpdb->posts}.ID IN (
                        SELECT object_id 
                        FROM {$wpdb->term_relationships} 
                        WHERE term_taxonomy_id IN ( {$variable_term->term_id} )
                    )
                )
                THEN ({$stock_variations_sql})
                ELSE ({$check_post_by_status_sql})
            END
        )";

		return $where_sql;
	}

	public function get_tax_query( WP_Query $query ) {
		if ( ! $query->tax_query instanceof WP_Tax_Query ) {
			$query->parse_tax_query( $query->query_vars );
		}

		return isset( $query->tax_query, $query->tax_query->queries ) ? $query->tax_query->queries : array();
	}

	public function get_meta_query( WP_Query $query ) {
		return isset( $query->query_vars['meta_query'] ) ? $query->query_vars['meta_query'] : array();
	}

	public function get_search_query_sql( WP_Query $query ) {
		global $wpdb;

		$search = '';

		$args = $query->query_vars;

		if ( isset( $args['s'] ) && is_string( $args['s'] ) && strlen( $args['s'] ) && ! isset( $args['search_terms'] ) ) {
			$args['s'] = stripslashes( $args['s'] );

			$args['s'] = urldecode( $args['s'] );

			$args['s'] = str_replace( array( "\r", "\n" ), '', $args['s'] );

			$args['search_terms_count'] = 1;

			if ( ! empty( $args['sentence'] ) ) {
				$args['search_terms'] = array( $args['s'] );
			} else {
				if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $args['s'], $matches ) ) {
					$args['search_terms_count'] = count( $matches[0] );

					$args['search_terms'] = $this->wp_parse_search_terms( $matches[0] );

					// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
					if ( empty( $args['search_terms'] ) || count( $args['search_terms'] ) > 9 ) {
						$args['search_terms'] = array( $args['s'] );
					}
				} else {
					$args['search_terms'] = array( $args['s'] );
				}
			}
		}

		$n = ! empty( $args['exact'] ) ? '' : '%';

		$searchand = '';

		$args['search_orderby_title'] = array();

		$exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );

		if ( ! isset( $args['search_terms'] ) ) {
			$args['search_terms'] = array();
		}

		foreach ( $args['search_terms'] as $term ) {
			// If there is an $exclusion_prefix, terms prefixed with it should be excluded.
			$exclude = $exclusion_prefix && ( substr( $term, 0, 1 ) === $exclusion_prefix );
			if ( $exclude ) {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			} else {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			}

			if ( $n && ! $exclude ) {
				$like                           = '%' . $wpdb->esc_like( $term ) . '%';
				$args['search_orderby_title'][] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $like );
			}

			$like      = $n . $wpdb->esc_like( $term ) . $n;
			$search   .= $wpdb->prepare( "{$searchand}(({$wpdb->posts}.post_title $like_op %s) $andor_op ({$wpdb->posts}.post_excerpt $like_op %s) $andor_op ({$wpdb->posts}.post_content $like_op %s))", $like, $like, $like );
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ({$wpdb->posts}.post_password = '') ";
			}
		}

		return $search;
	}

	protected function wp_parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';

		$checked = array();

		$stopwords = $this->wp_get_search_stopwords();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}

	protected function wp_get_search_stopwords() {
		if ( isset( $this->stopwords ) ) {
			return $this->stopwords;
		}

		/*
		 * translators: This is a comma-separated list of very common words that should be excluded from a search,
		 * like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
		 * words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		 */
		$words = explode(
			',',
			_x(
				'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
				'Comma-separated list of search stopwords in your language'
			)
		);

		$stopwords = array();

		foreach ( $words as $word ) {
			$word = trim( $word, "\r\n\t " );

			if ( $word ) {
				$stopwords[] = $word;
			}
		}

		/**
		 * Filters stopwords used when parsing search terms.
		 *
		 * @since 3.7.0
		 *
		 * @param array $stopwords Stopwords.
		 */
		$this->stopwords = apply_filters( 'wp_search_stopwords', $stopwords );

		return $this->stopwords;
	}

	/**
	 * @see _get_term_hierarchy
	 * @file wp-includes/taxonomy.php
	 */
	public function get_term_hierarchy( $taxonomy ) {
		if ( ! is_taxonomy_hierarchical( $taxonomy ) ) {
			return array();
		}

		$children = get_option( $taxonomy . '_children' );

		if ( is_array( $children ) ) {
			return $children;
		}

		$children = array();

		$terms = get_terms(
			$taxonomy,
			array(
				'get'     => 'all',
				'orderby' => 'id',
				'fields'  => 'id=>parent',
			)
		);

		foreach ( $terms as $term_id => $parent ) {
			if ( $parent > 0 ) {
				$children[ $parent ][] = $term_id;
			}
		}

		update_option( $taxonomy . '_children', $children );

		return $children;
	}

	/**
	 * @see get_term_children
	 * @file wp-includes/taxonomy.php
	 */
	public function get_term_children( $term_id, $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.' ) );
		}

		$term_id = intval( $term_id );

		$term_ids = $this->get_term_hierarchy( $taxonomy );

		if ( ! isset( $term_ids[ $term_id ] ) ) {
			return array();
		}

		return $term_ids[ $term_id ];
	}
}
