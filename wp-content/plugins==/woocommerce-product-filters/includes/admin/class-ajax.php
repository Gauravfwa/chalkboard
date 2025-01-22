<?php

namespace WooCommerce_Product_Filter_Plugin\Admin;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Project\Project;

class Ajax extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wp_ajax_wcpf_save_project', 'save_project_ajax' );

		$hook_manager->add_action( 'wcpf_save_project', 'preparing_entities' );

		$hook_manager->add_action( 'wp_ajax_wcpf_editor_control_reload', 'control_reload_ajax' );

		$hook_manager->add_action( 'wp_ajax_wcpf_editor_rules_builder_get_options', 'rules_builder_get_options' );
	}

	public function rules_builder_get_options() {
		if ( ! isset( $_POST['wcpf_tabs_panel_nonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['wcpf_tabs_panel_nonce'] ) ), 'wcpf_tabs_panel' ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => esc_html__( 'Nonce error', 'wcpf' ),
			);
			wp_send_json_error( $response_data );
			return;
		}

		$param = isset( $_POST['param'] ) ? wc_clean( wp_unslash( $_POST['param'] ) ) : null;

		$response_data = array(
			'messages' => array(),
		);

		if ( ! current_user_can( 'edit_posts' ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => __( 'User does not have access to project change', 'wcpf' ),
			);

			wp_send_json_error( $response_data );
		}

		if ( ! $param ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => __( 'Parameter is incorrect', 'wcpf' ),
			);

			wp_send_json_error( $response_data );
		}

		$options = null;

		if ( 'category' === $param || 'tag' === $param ) {
			$options = get_terms(
				array(
					'taxonomy'               => 'category' === $param ? 'product_cat' : 'product_tag',
					'fields'                 => 'id=>name',
					'count'                  => false,
					'update_term_meta_cache' => false,
					'hide_empty'             => false,
				)
			);
		} elseif ( 'page' === $param ) {
			$options = array();

			$posts = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => 'page',
				)
			);

			foreach ( $posts as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
		} elseif ( 'attribute' === $param ) {
			$options = array();

			foreach ( wc_get_attribute_taxonomies() as $attribute ) {
				$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );

				$attribute_options = array(
					'any_terms_' . $taxonomy => __( 'Any', 'wcpf' ) . ' "' . $attribute->attribute_label . '"',
				) + get_terms(
					array(
						'taxonomy'               => $taxonomy,
						'fields'                 => 'id=>name',
						'count'                  => false,
						'update_term_meta_cache' => false,
						'hide_empty'             => false,
					)
				);

				$options[ $taxonomy ] = array(
					'label'   => $attribute->attribute_label,
					'options' => $attribute_options,
				);
			}
		} elseif ( 'taxonomy' === $param ) {
			$options = array();

			$taxonomies = array();

			foreach ( get_taxonomies( array( 'object_type' => array( 'product' ) ), 'objects' ) as $taxonomy ) {
				if ( taxonomy_is_product_attribute( $taxonomy->name )
					|| in_array( $taxonomy->name, array( 'product_cat', 'product_tag', 'product_type' ), true ) ) {
					continue;
				}

				$taxonomies[ $taxonomy->name ] = $taxonomy->label;
			}

			foreach ( $taxonomies as $taxonomy => $label ) {
				$taxonomy_options = array(
					'any_terms_' . $taxonomy => __( 'Any', 'wcpf' ) . ' "' . $label . '"',
				) + get_terms(
					array(
						'taxonomy'               => $taxonomy,
						'fields'                 => 'id=>name',
						'count'                  => false,
						'update_term_meta_cache' => false,
						'hide_empty'             => false,
					)
				);

				$options[ $taxonomy ] = array(
					'label'   => $label,
					'options' => $taxonomy_options,
				);
			}
		}

		if ( is_array( $options ) ) {
			$response_data['optionsHtml'] = $this->get_template_loader()->compile_template(
				'parts/rule-options.php',
				array(
					'options' => $options,
					'name'    => $param,
				),
				__DIR__ . '/views'
			);

			wp_send_json_success( $response_data );
		}

		$response_data['messages'][] = array(
			'level' => 'error',
			'text'  => __( 'Unknown parameter', 'wcpf' ),
		);

		wp_send_json_error( $response_data );
	}

	public function save_project_ajax() {
		if ( ! isset( $_POST['wcpf_tabs_panel_nonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['wcpf_tabs_panel_nonce'] ) ), 'wcpf_tabs_panel' ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => esc_html__( 'Nonce error', 'wcpf' ),
			);
			wp_send_json_error( $response_data );
			return;
		}

		$project_structure = isset( $_POST['projectEntity'] ) ? wc_clean( wp_unslash( $_POST['projectEntity'] ) ) : null;

		$response_data = array(
			'messages' => array(),
		);

		if ( ! current_user_can( 'edit_posts' ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => __( 'User does not have access to project change', 'wcpf' ),
			);

			wp_send_json_error( $response_data );
		}

		if ( is_string( $project_structure ) ) {
			$project_structure = json_decode( stripslashes( $project_structure ), true );
		}

		if ( ! $project_structure || ! is_array( $project_structure ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => __( 'Structure of project is incorrect', 'wcpf' ),
			);

			wp_send_json_error( $response_data );
		}

		$project = $this->get_component_builder()->build( Project::class );

		$result_save = $project->save_project_by_structure( $project_structure );

		if ( is_wp_error( $result_save ) ) {
			$response_data['messages'][] = array(
				'level'   => 'error',
				'text'    => __( 'An error occurred while saving project', 'wcpf' ),
				'context' => array(
					'messages' => $result_save->get_error_messages(),
					'codes'    => $result_save->get_error_codes(),
				),
			);

			wp_send_json_error( $response_data );
		} else {
			$response_data['projectId'] = $result_save;

			wp_send_json_success( $response_data );
		}
	}

	public function preparing_entities( $project ) {
		$editor_components = array();

		foreach ( $this->get_entity_register()->get_all_entries() as $entry ) {
			if ( $entry['editor_component_class'] && class_exists( $entry['editor_component_class'] ) ) {
				$editor_component = new $entry['editor_component_class']();

				if ( ! $editor_component instanceof Editor\Component\Base_Component ) {
					continue;
				}

				$this->get_component_builder()->implementation( $editor_component );

				$editor_components[ $entry['id'] ] = $editor_component;
			}
		}

		foreach ( $project->get_entity_list() as $entity ) {
			$entity_key = $entity->get_entity_key();

			if ( isset( $editor_components[ $entity_key ] ) && $editor_components[ $entity_key ] instanceof Editor\Component\Preparing_Entity_Interface ) {
				$editor_components[ $entity_key ]->preparing_entity( $entity, $project );
			}
		}
	}

	public function control_reload_ajax() {
		if ( ! isset( $_POST['wcpf_tabs_panel_nonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['wcpf_tabs_panel_nonce'] ) ), 'wcpf_tabs_panel' ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => esc_html__( 'Nonce error', 'wcpf' ),
			);
			wp_send_json_error( $response_data );
			return;
		}
		$response_data = array(
			'messages' => array(),
			'items'    => array(),
		);

		if ( ! current_user_can( 'edit_posts' ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => __( 'User does not have access to project change', 'wcpf' ),
			);

			wp_send_json_error( $response_data );
		}

		$controls_props = isset( $_POST['controls'] ) ? wc_clean( wp_unslash( $_POST['controls'] ) ) : null;

		$controls_props = json_decode( stripslashes( $controls_props ), true );

		if ( ! $controls_props || ! is_array( $controls_props ) ) {
			$response_data['messages'][] = array(
				'level' => 'error',
				'text'  => __( 'Controls is incorrect', 'wcpf' ),
			);

			wp_send_json_error( $response_data );
		}

		$editor_panels = $this->assembly_of_editor_panels( $this->get_entity_register() );

		foreach ( $controls_props as $item_index => $control_props ) {
			$options = $control_props['options'];

			$context = $control_props['context'];

			$parents_context = $control_props['parentsContext'];

			$control_path = $control_props['controlPath'];

			$control = null;

			if ( ! isset( $editor_panels[ $control_props['panelKey'] ] ) ) {
				$response_data['items'][ $item_index ] = false;

				continue;
			}

			$panel = $editor_panels[ $control_props['panelKey'] ];

			if ( count( $control_path ) === 1 ) {
				$control = $panel->get_control_by_option_key(
					$this->get_original_to_modified_key( $panel->get_controls(), $control_props['optionKey'] )
				);
			} elseif ( count( $control_path ) > 1 ) {
				$key_pairs = array();

				$current_control = null;

				while ( count( $control_path ) ) {
					$current_key = array_shift( $control_path );

					if ( is_null( $current_control ) ) {
						$original_key = $this->get_original_to_modified_key( $panel->get_controls(), $current_key );

						$current_control = $panel->get_control_by_option_key( $original_key );

						$key_pairs[ $original_key ] = $current_key;
					}

					if ( ! $current_control ) {
						break;
					}

					if ( count( $control_path ) ) {
						if ( $current_control instanceof Editor\Control\Contains_Controls_Interface ) {
							$next_key = array_values( $control_path )[0];

							if ( isset( $key_pairs[ $current_control->get_option_key() ] )
								&& isset( $parents_context[ $key_pairs[ $current_control->get_option_key() ] ] ) ) {
								$current_control->prepare_for_reload(
									$options,
									$parents_context[ $key_pairs[ $current_control->get_option_key() ] ]
								);
							}

							$current_control = $current_control->get_child_control_by_option_key(
								$this->get_original_to_modified_key( $current_control->get_child_controls(), $next_key )
							);

							if ( ! $current_control ) {
								break;
							}

							$key_pairs[ $next_key ] = $current_control->get_option_key();
						} else {
							break;
						}
					}
				}

				$control = $current_control;
			}

			if ( ! $control instanceof Editor\Control\Abstract_Control
				|| ! $this->compare_option_key( $control->get_option_key(), $control_props['optionKey'] ) ) {
				$response_data['items'][ $item_index ] = false;

				continue;
			}

			$control = clone $control;

			$control->set_option_key( $control_props['optionKey'] );

			if ( $control instanceof Editor\Control\Preparing_For_Reload_Interface ) {
				$control->prepare_for_reload( $options, $context, $control_props );
			}

			ob_start();

			$control->render_control();

			$control_html = ob_get_clean();

			$response_data['items'][ $item_index ] = array(
				'structure'   => $control->get_structure(),
				'controlHtml' => $control_html,
			);
		}

		wp_send_json_success( $response_data );
	}

	protected function compare_option_key( $original_key, $modified_key ) {
		if ( strpos( $original_key, '$' ) !== false ) {
			$template = preg_replace( '/\\$[a-zA-Z\\d]+/', '([a-zA-Z\\d]+)', $original_key, -1 );

			if ( ! is_string( $template ) ) {
				return false;
			}

			$template = '/' . $template . '/';

			if ( preg_match_all( $template, $modified_key ) > 0 ) {
				return true;
			}
		}

		return $original_key === $modified_key;
	}

	protected function get_original_to_modified_key( $items, $modified_key ) {
		foreach ( $items as $item ) {
			$original_key = $item;

			if ( $item instanceof Editor\Control\Abstract_Control ) {
				$original_key = $item->get_option_key();
			}

			if ( $this->compare_option_key( $original_key, $modified_key ) ) {
				return $original_key;
			}
		}

		return false;
	}

	protected function assembly_of_editor_panels( $entity_register ) {
		$editor_panels = array();

		foreach ( $entity_register->get_all_entries() as $entry ) {
			if ( ! isset( $entry['editor_component_class'] ) || is_null( $entry['editor_component_class'] ) ) {
				continue;
			}

			$editor_component = new $entry['editor_component_class']();

			if ( ! $editor_component instanceof Editor\Component\Base_Component ) {
				continue;
			}

			$editor_component->set_register_entry( $entry );

			$this->get_component_builder()->implementation( $editor_component );

			if ( $editor_component instanceof Editor\Component\Generates_Panels_Interface ) {
				$panels = $editor_component->generate_panels();

				foreach ( $panels as $panel ) {
					if ( $panel && $panel instanceof Editor\Panel_Layout\Abstract_Panel_Layout ) {
						$editor_panels[ $panel->get_panel_id() ] = $panel;
					}
				}
			}
		}

		$editor_panels = $this->get_hook_manager()->apply_filters( 'wcpf_get_editor_panels', $editor_panels );

		foreach ( $editor_panels as $key => $panel ) {
			$this->get_component_builder()->implementation( $panel );
		}

		return $editor_panels;
	}
}
