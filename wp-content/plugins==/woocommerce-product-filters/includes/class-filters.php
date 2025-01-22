<?php

namespace WooCommerce_Product_Filter_Plugin;

class Filters extends Structure\Component {
	protected $products_query_vars = array();

	protected $filter_markers = array();

	public function get_project_component_storage() {
		return $this->get_component_register()->get( 'Project/Filter_Component_Storage' );
	}

	public function initial_properties() {
		$this->save_component_to_register( 'Filters' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'woocommerce_product_query', 'save_query_and_apply_filters', 1000 );

		if ( ! is_admin() ) {
			$hook_manager->add_action( 'template_redirect', 'start_of_buffering', 150 );

			$hook_manager->add_action( 'shutdown', 'end_of_buffering', -150 );
		}

		$hook_manager->add_filter( 'woocommerce_pagination_args', 'pagination_args', 1 );

		$hook_manager->add_action( 'init', 'on_init' );
	}

	public function on_init() {
		if ( class_exists( 'WC_Photography_Products' ) ) {
			$has_handler = has_action( 'pre_get_posts', array( wc()->query, 'pre_get_posts' ) );

			if ( $has_handler ) {
				remove_action( 'pre_get_posts', array( wc()->query, 'pre_get_posts' ) );

				add_action( 'pre_get_posts', array( wc()->query, 'pre_get_posts' ), 15 );
			}
		}
	}

	public function get_products_query_vars( $filter_id ) {
		if ( isset( $this->products_query_vars[ $filter_id ] ) ) {
			return $this->products_query_vars[ $filter_id ];
		}

		return null;
	}

	public function get_default_filter_id( $default_return = null ) {
		return $this->get_hook_manager()->apply_filters(
			'wcpf_default_filter_id',
			get_option( 'wcpf_setting_default_project', $default_return )
		);
	}

	public function pagination_args( $args ) {
		$args['base'] = str_replace( '%2C', ',', $args['base'] );

		return $args;
	}

	public function start_of_buffering() {
		ob_start();
	}

	public function replace_markers_in_buffer( $buffer_content ) {
		foreach ( $this->filter_markers as $project_id => $marker_data ) {
			if ( ! isset( $this->products_query_vars[ $project_id ] ) || strpos( $buffer_content, $marker_data['content'] ) === false ) {
				continue;
			}

			$product_filters_html = $this->print_product_filters( $project_id, false );

			if ( $marker_data['before'] ) {
				$product_filters_html = $marker_data['before'] . $product_filters_html;
			}

			if ( $marker_data['after'] ) {
				$product_filters_html .= $marker_data['after'];
			}

			$buffer_content = str_replace( $marker_data['content'], $product_filters_html, $buffer_content );
		}

		return $buffer_content;
	}

	public function end_of_buffering() {
		echo $this->replace_markers_in_buffer( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function save_query_and_apply_filters( $query ) {
		if ( $query->get( 'wcpf_already_filtered', false ) ) {
			return;
		}

		$out_of_stock_products = get_option( 'wcpf_setting_out_of_stock_products', 'no-action' );

		/**
		 * Fix infinity 'product_query' hook call
		 */
		$has_product_query_handler = has_action( 'pre_get_posts', array( wc()->query, 'product_query' ) );

		if ( $has_product_query_handler ) {
			remove_action( 'pre_get_posts', array( wc()->query, 'product_query' ) );
		}

		if ( $query->get( 'wcpf_filter_id', false ) ) {
			$project_id = $query->get( 'wcpf_filter_id' );
		} else {
			$project_id = $this->get_default_filter_id();
		}

		if ( ! $project_id || get_post_status( $project_id ) === false ) {
			return;
		}

		$this->products_query_vars[ $project_id ] = array(
			'before_filtering' => null,
			'after_filtering'  => null,
		);

		$query->set( 'wcpf_filter_id', $project_id );

		$this->products_query_vars[ $project_id ]['before_filtering'] = $query->query_vars;

		$this->apply_filters_to_product_query( $query, $project_id );

		if ( ( ( 'hide-if-active-any-options' === $out_of_stock_products && wcpf_is_filtered() )
				|| 'always-hide' === $out_of_stock_products )
			&& ! isset( $query->query_vars['wcpf_stock_status'] ) ) {
			$query->set( 'wcpf_stock_status', 'in-stock' );
		}

		$this->products_query_vars[ $project_id ]['after_filtering'] = $query->query_vars;

		$query->set( 'wcpf_already_filtered', true );

		$project_id = null;

		if ( $has_product_query_handler ) {
			add_action( 'pre_get_posts', array( wc()->query, 'product_query' ) );
		}
	}

	public function apply_filters_to_product_query( \WP_Query $product_query, $project_id, $filters = null ) {
		$project_component = $this->get_project_component_storage()->get_project_component( $project_id );

		if ( ! $project_component ) {
			return $product_query;
		}

		$url_navigation = $project_component->get_url_navigation();

		$need_load_filters = false;

		if ( ! is_array( $filters ) || ! count( $filters ) ) {
			$need_load_filters = true;

			$filters = array();
		}

		$components = $this->get_list_filter_components( $project_component );

		$filtering_components = array();

		foreach ( $components as $component_item ) {
			if ( ! $component_item instanceof Filter\Component\Filtering_Query_Interface ) {
				continue;
			}

			foreach ( $component_item->get_filter_keys() as $filter_index => $filter_key ) {
				if ( $need_load_filters && $url_navigation->has_attribute( $filter_key ) ) {
					$filters[ $filter_key ] = $url_navigation->get_attribute( $filter_key );
				}

				if ( array_key_exists( $filter_key, $filters ) ) {
					$component_item->set_filter_value( $filter_index, $filters[ $filter_key ] );

					$filtering_components[] = $component_item;
				}
			}
		}

		$project_component->set_filter_components( $filtering_components );

		foreach ( $filtering_components as $filtering_component ) {
			$filtering_component->apply_filter_to_query( $product_query, $filtering_component->get_filter_values() );
		}

		return $product_query;
	}

	public function print_notes_for_product_filters( $project_id, $echo = true ) {
		if ( ! $project_id ) {
			$project_id = $this->get_default_filter_id();
		}

		if ( ! $project_id || get_post_status( $project_id ) === false ) {
			$project_id = 'default';
		}

		$result = $this->get_template_loader()->compile_template(
			'notes-for-product-filters.php',
			array(
				'project_id' => $project_id,
			)
		);

		if ( $echo ) {
			echo $result; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		return $result;
	}

	public function print_product_filters( $project_id, $echo = true, $attributes = array() ) {
		if ( ! isset( $this->products_query_vars[ $project_id ] ) ) {
			return $this->print_marker_for_product_filters( $project_id, $echo, $attributes );
		}

		$project_component = $this->get_project_component_storage()->get_project_component(
			$project_id,
			array(
				'product_query_vars_before_filtering' => $this->products_query_vars[ $project_id ]['before_filtering'],
				'product_query_vars_after_filtering'  => $this->products_query_vars[ $project_id ]['after_filtering'],
			)
		);

		if ( $project_component ) {
			ob_start();

			if ( isset( $attributes['before_html'] ) ) {
				echo wp_kses_post( $attributes['before_html'] );
			}

			$project_component->template_render();

			if ( isset( $attributes['after_html'] ) ) {
				echo wp_kses_post( $attributes['after_html'] );
			}

			$result = ob_get_clean();

			if ( $echo ) {
				echo $result; // phpcs:ignore WordPress.Security.EscapeOutput
			}

			return $result;
		}

		return '';
	}

	public function print_marker_for_product_filters( $project_id, $echo = true, $attributes = array() ) {
		$this->filter_markers[ $project_id ] = array(
			'content' => '<!-- woocommerce-product-filters: ' . esc_attr( $project_id ) . ' -->',
			'before'  => isset( $attributes['before_html'] ) ? wp_kses_post( $attributes['before_html'] ) : null,
			'after'   => isset( $attributes['after_html'] ) ? wp_kses_post( $attributes['after_html'] ) : null,
		);

		if ( $echo ) {
			echo $this->filter_markers[ $project_id ]['content']; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		return $this->filter_markers[ $project_id ]['content'];
	}

	protected function get_list_filter_components( Filter\Component\Base_Component $filter_component ) {
		$list = array();

		$list[ $filter_component->get_entity_id() ] = $filter_component;

		foreach ( $filter_component->get_child_filter_components() as $child_component ) {
			$list = array_merge( $list, $this->get_list_filter_components( $child_component ) );
		}

		return $list;
	}
}
