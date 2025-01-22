<?php

namespace WooCommerce_Product_Filter_Plugin\Filter\Component;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Entity,
	WooCommerce_Product_Filter_Plugin\Project\Project;

class Base_Component extends Structure\Component {
	protected $entity;

	protected $project;

	protected $child_filter_components = array();

	public function get_entity() {
		return $this->entity;
	}

	public function set_entity( Entity $entity ) {
		$this->entity = $entity;
	}

	public function get_project() {
		return $this->project;
	}

	public function set_project( Project $project ) {
		$this->project = $project;
	}

	public function get_entity_id() {
		return $this->get_entity()->get_entity_id();
	}

	public function get_all_options() {
		return $this->get_entity()->get_all_options();
	}

	public function get_option( $index, $default_value = null ) {
		return $this->get_entity()->get_option( $index, $default_value );
	}

	public function get_child_filter_components() {
		return $this->child_filter_components;
	}

	public function set_child_filter_components( $child_filter_components ) {
		$this->child_filter_components = $child_filter_components;
	}

	public function get_child_filter_component_by_entity_id( $entity_id ) {
		foreach ( $this->get_child_filter_components() as $child_component ) {
			if ( $child_component->get_entity_id() === $entity_id ) {
				return $child_component;
			}
		}

		return null;
	}

	public function get_product_query_before_filtering() {
		return $this->get_project()->get_product_query_before_filtering();
	}

	public function get_product_query_after_filtering() {
		return $this->get_project()->get_product_query_after_filtering();
	}

	public function check_rules_from_option( $option_key, array $args = array() ) {
		global $wp_the_query;

		$args = wp_parse_args(
			$args,
			array(
				'use_selected_options' => true,
			)
		);

		$option_value = $this->get_option( $option_key, false );

		if ( ! is_array( $option_value ) ) {
			return true;
		}

		$checked_rules = 0;

		foreach ( $option_value as $group ) {
			if ( ! is_array( $group ) || ! isset( $group['rules'] ) || ! is_array( $group['rules'] ) ) {
				continue;
			}

			$rules = $group['rules'];

			$checked_rules_in_group = 0;

			$and_result = true;

			foreach ( $rules as $rule_item ) {
				if ( ! is_array( $rule_item )
					|| ! isset( $rule_item['rule'], $rule_item['rule']['param'], $rule_item['rule']['operator'], $rule_item['rule']['value'] )
					|| ! $rule_item['rule']['value'] ) {
					continue;
				}

				$rule           = $rule_item['rule'];
				$rule_value_int = absint( $rule['value'] );

				$checked_rules_in_group++;

				if ( in_array( $rule['param'], array( 'category', 'attribute', 'taxonomy', 'tag' ), true ) ) {
					$queried_object = $this->get_product_query_before_filtering()->get_queried_object();

					if ( ! $args['use_selected_options'] && ! $queried_object instanceof \WP_Term ) {
						$and_result = false;

						break;
					}

					$is_selected = false;

					if ( $args['use_selected_options'] ) {
						$selected_options = $this->get_object_register()->get( 'selected_options', array() );

						if ( strpos( $rule['value'], 'any_terms_' ) === 0 ) {
							$taxonomy = substr( $rule['value'], 10 );

							foreach ( $selected_options as $selected_option ) {
								if ( $selected_option['taxonomy'] === $taxonomy ) {
									$is_selected = true;

									break;
								}
							}
						} else {
							$term = get_term( $rule_value_int );

							if ( $term instanceof \WP_Term ) {
								foreach ( $selected_options as $selected_option ) {
									if ( $selected_option['taxonomy'] === $term->taxonomy
										&& in_array( $term->slug, $selected_option['terms'], true ) ) {
										$is_selected = true;

										break;
									}
								}
							}
						}
					}

					$is_archive = $queried_object instanceof \WP_Term && $queried_object->term_id === $rule_value_int;

					if ( strpos( $rule['value'], 'any_terms_' ) === 0 ) {
						$taxonomy = substr( $rule['value'], 10 );

						$is_archive = $queried_object instanceof \WP_Term && $queried_object->taxonomy === $taxonomy;
					}

					$is_active_option = $args['use_selected_options'] && $is_selected;

					if ( ( '==' === $rule['operator'] && ! $is_archive && ! $is_active_option )
						|| ( '!=' === $rule['operator'] && ( $is_archive || $is_active_option ) ) ) {
						$and_result = false;

						break;
					}
				} elseif ( 'page' === $rule['param'] ) {
					$current_page_id = false;

					$shop_id = wc_get_page_id( 'shop' );

					if ( $wp_the_query->post && isset( $wp_the_query->post->ID ) ) {
						$current_page_id = $wp_the_query->post->ID;
					}

					if ( $rule_value_int === $shop_id && is_shop() ) {
						$current_page_id = $shop_id;
					}

					if ( ( '==' === $rule['operator'] && ( false === $current_page_id || $current_page_id !== $rule_value_int ) )
						|| ( '!=' === $rule['operator'] && $current_page_id === $rule_value_int ) ) {
						$and_result = false;

						break;
					}
				}
			}

			$checked_rules += $checked_rules_in_group;

			if ( $and_result && $checked_rules_in_group ) {
				return true;
			}
		}

		return 0 === $checked_rules;
	}
}
