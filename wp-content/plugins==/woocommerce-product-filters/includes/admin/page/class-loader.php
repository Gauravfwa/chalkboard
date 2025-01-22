<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Page;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Admin\Editor,
	WooCommerce_Product_Filter_Plugin\Project\Project;

class Loader extends Structure\Component {
	protected $data = array();

	protected $editor_post = null;

	public function get_project_post_type() {
		return $this->get_component_register()->get( 'Project/Post_Type' )->get_post_type();
	}

	public function initial_properties() {
		$this->data = array(
			'page_action'      => 'list',
			'editor'           => array(
				'panels'      => array(),
				'projections' => array(),
			),
			'template_context' => array(),
		);
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		global $wp_version;

		if ( version_compare( $wp_version, '4.9', '<' ) ) {
			$hook_manager->add_action( 'dbx_post_advanced', 'replace_editor_old_wp', 1, 1 );
		} else {
			$hook_manager->add_filter( 'replace_editor', 'replace_editor', 10, 2 );
		}
	}

	public function replace_editor_old_wp( $post ) {
		if ( ! $this->editor_post && $post->post_type === $this->get_project_post_type() ) {
			$this->editor_post = $post;

			$this->load_page();

			$this->print_page();

			include ABSPATH . 'wp-admin/admin-footer.php';

			die();
		}
	}

	public function replace_editor( $replace, $post ) {
		if ( $post->post_type === $this->get_project_post_type() ) {
			if ( ! get_current_screen() ) {
				return true;
			}

			$this->editor_post = $post;

			$this->load_page();

			$this->print_page();

			return true;
		}

		return $replace;
	}

	public function load_page() {
		$this->assembly_of_editor_components( $this->get_entity_register() );

		$this->data['project_post_type'] = $this->get_project_post_type();

		$this->data['project'] = $this->get_component_builder()->build( Project::class );

		$page_links = array(
			'list_project' => add_query_arg(
				array(
					'post_type' => $this->get_project_post_type(),
				),
				admin_url( 'edit.php' )
			),
			'new_project'  => add_query_arg(
				array(
					'post_type' => $this->get_project_post_type(),
				),
				admin_url( 'post-new.php' )
			),
			'edit_project' => add_query_arg(
				array(
					'post'   => '%project_id%',
					'action' => 'edit',
				),
				admin_url( 'post.php' )
			),
		);

		$this->create_route( $page_links );

		$this->data['template_context']['page_links'] = $page_links;

		$this->data['template_context']['editor'] = $this->data['editor'];

		$this->data['template_context'] = $this->get_hook_manager()->apply_filters( 'wcpf_admin_page_template_context', $this->data['template_context'] );

		$this->data = $this->get_hook_manager()->apply_filters( 'wcpf_admin_page_data', $this->data, $this->data['page_action'] );

		$this->get_hook_manager()->trigger_action( 'wcpf_admin_load_page' );

		$this->get_hook_manager()->trigger_action( 'wcpf_admin_load_' . $this->data['page_action'] . '_page' );

		$this->get_hook_manager()->add_action( 'admin_enqueue_scripts', 'load_assets' );
	}

	public function load_assets() {
		$panels_data = array();

		foreach ( $this->data['editor']['panels'] as $panel_key => $panel ) {
			$panels_data[ $panel_key ] = $panel->get_structure();
		}

		$messages = $this->get_hook_manager()->apply_filters(
			'wcpf_admin_message_localize',
			array(
				'panel'           => array(
					'shouldSaveOptionsOnPanel' => __( 'Save options on panel "--panel-title"?', 'wcpf' ),
				),
				'projection'      => array(
					'shouldRemoveElement' => __( 'Are you sure you want to remove element?', 'wcpf' ),
				),
				'validatorErrors' => array(
					'filterKeyText'    => __( 'Allowed letters, numbers and symbols "-", "_"', 'wcpf' ),
					'filterKeyUniquer' => __( 'Key must be unique for project', 'wcpf' ),
				),
			)
		);

		wp_localize_script(
			'wcpf-admin-script',
			'ProductFilterData',
			array(
				'pageLinks'        => $this->data['template_context']['page_links'],
				'pageAction'       => $this->data['page_action'],
				'registerEntities' => $this->get_entity_register()->get_all_entries(),
				'editor'           => array(
					'panels' => $panels_data,
				),
				'messages'         => $messages,
				'postId'           => $this->editor_post ? $this->editor_post->ID : false,
			)
		);

		$this->get_hook_manager()->trigger_action( 'wcpf_admin_load_assets_for_' . $this->data['page_action'] . '_page', $this->data );
	}

	public function print_page() {
		require_once ABSPATH . 'wp-admin/admin-header.php';
		$this->get_hook_manager()->trigger_action( 'wcpf_admin_print_page', $this->data, $this->data['page_action'] );
		$this->get_hook_manager()->trigger_action( 'wcpf_admin_print_' . $this->data['page_action'] . '_page', $this->data );
	}

	protected function create_route( $links ) {
		$screen = get_current_screen();

		if ( 'post' === $screen->base && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
			$project_post = null;

			if ( $this->editor_post ) {
				$project_post = $this->editor_post;
			}

			if ( is_null( $project_post ) || $project_post->post_type !== $this->data['project_post_type'] ) {
				wp_safe_redirect( $links['new_project'] );
			}

			$this->data['template_context']['post'] = $project_post;

			$this->data['project']->load_project( $project_post );

			$this->data['template_context']['project_entity'] = $this->data['project']->get_project_entity();

			$this->data['project_entity'] = $this->data['project']->get_project_entity();

			$this->data['page_action'] = 'edit';
		} elseif ( 'post' === $screen->base && 'add' === $screen->action ) {
			$this->data['page_action'] = 'new';
		}
	}

	protected function assembly_of_editor_components( $entity_register ) {
		foreach ( $entity_register->get_all_entries() as $entry ) {
			if ( ! isset( $entry['editor_component_class'] ) || is_null( $entry['editor_component_class'] ) ) {
				continue;
			}

			$entity_key = $entry['id'];

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
						$this->data['editor']['panels'][ $panel->get_panel_id() ] = $panel;
					}
				}
			}

			if ( $editor_component instanceof Editor\Component\Generates_Projection_Interface ) {
				$projection = $editor_component->generate_projection();

				if ( $projection && $projection instanceof Editor\Projection\Abstract_Projection ) {
					$this->data['editor']['projections'][ $entity_key ] = $projection;
				}
			}
		}

		$this->data['editor']['projections'] = $this->get_hook_manager()->apply_filters( 'wcpf_get_editor_projections', $this->data['editor']['projections'] );

		$this->data['editor']['panels'] = $this->get_hook_manager()->apply_filters( 'wcpf_get_editor_panels', $this->data['editor']['panels'] );

		foreach ( $this->data['editor']['projections'] as $key => $projection ) {
			$this->get_component_builder()->implementation( $projection );
		}

		foreach ( $this->data['editor']['panels'] as $key => $panel ) {
			$this->get_component_builder()->implementation( $panel );
		}
	}
}
