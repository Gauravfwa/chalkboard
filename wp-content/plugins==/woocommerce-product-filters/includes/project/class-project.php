<?php

namespace WooCommerce_Product_Filter_Plugin\Project;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Entity;

class Project extends Structure\Component {
	protected $project_entity;

	protected $virtual_id_list = array();

	protected $entity_list = array();

	protected static $loaded_project = array();

	protected $product_query_before_filtering = null;

	protected $product_query_after_filtering = null;

	public function get_product_query_before_filtering() {
		return $this->product_query_before_filtering;
	}

	public function set_product_query_before_filtering( \WP_Query $product_query_before_filtering ) {
		$this->product_query_before_filtering = $product_query_before_filtering;
	}

	public function get_product_query_after_filtering() {
		return $this->product_query_after_filtering;
	}

	public function set_product_query_after_filtering( \WP_Query $product_query_after_filtering ) {
		$this->product_query_after_filtering = $product_query_after_filtering;
	}

	public function get_project_entity() {
		return $this->project_entity;
	}

	public function set_project_entity( Entity $entity ) {
		$this->project_entity = $entity;
	}

	public function get_virtual_id_list() {
		return $this->virtual_id_list;
	}

	public function get_entity_list() {
		return $this->entity_list;
	}

	public function get_entity_key_meta() {
		return 'wcpf_entity_key';
	}

	public function get_option_key_meta() {
		return 'wcpf_entity_options';
	}

	public function load_project( $post ) {
		$post = get_post( $post );

		if ( is_null( $post ) ) {
			return null;
		}

		if ( isset( static::$loaded_project[ $post->ID ] ) ) {
			$project = static::$loaded_project[ $post->ID ];

			$this->set_project_entity( $project );

			return $project;
		}

		$entity_key = get_post_meta( $post->ID, $this->get_entity_key_meta(), true );

		$project_entity = new Entity();

		$project_entity->set_entity_post( $post );

		$project_entity->set_project( $this );

		$project_entity->set_entity_key( $entity_key );

		$this->set_project_entity( $project_entity );

		$post_types = array();

		foreach ( $this->get_entity_register()->get_all_entries() as $entry ) {
			if ( $entry['post_type'] !== $project_entity->get_entity_post()->post_type ) {
				$post_types[] = $entry['post_type'];
			}
		}

		$post_types = array_unique( $post_types );

		$this->load_entity( $project_entity, $post_types );

		static::$loaded_project[ $post->ID ] = $project_entity;

		return $this->get_project_entity();
	}

	protected function load_entity( Entity $entity, array $types ) {
		$posts = get_posts(
			array(
				'posts_per_page'         => -1,
				'post_parent'            => $entity->get_entity_id(),
				'post_type'              => $types,
				'orderby'                => 'menu_order',
				'order'                  => 'ASC',
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			)
		);

		$this->entity_list[ $entity->get_entity_id() ] = $entity;

		$child_entities = array();

		foreach ( $posts as $post ) {
			$entity_key = get_post_meta( $post->ID, $this->get_entity_key_meta(), true );

			$entry = $this->get_entity_register()->get_entry( $entity_key );

			if ( ! is_null( $entry ) ) {
				$new_entity = new $entry['class']();

				$new_entity->set_project( $this );

				$new_entity->set_entity_post( $post );

				$new_entity->set_entity_key( $entity_key );

				if ( $entry['is_grouped'] ) {
					$this->load_entity( $new_entity, $types );
				} else {
					$this->entity_list[ $new_entity->get_entity_id() ] = $new_entity;
				}

				$child_entities[] = $new_entity;
			}
		}

		$entity->set_child_entities( $child_entities );
	}

	public function get_project_structure() {
		return $this->get_entity_structure( $this->get_project_entity() );
	}

	public function get_entity_structure( Entity $entity ) {
		$post = $entity->get_entity_post();

		$entry = $this->get_entity_register()->get_entry( $entity->get_entity_key() );

		$result = array(
			'entityId'      => $entity->get_entity_id(),
			'parentId'      => $post->post_parent,
			'title'         => $post->post_title,
			'entityKey'     => $entity->get_entity_key(),
			'order'         => $post->menu_order,
			'options'       => array_merge( $entry['default_options'], $entity->get_all_options() ),
			'childEntities' => array(),
		);

		foreach ( $entity->get_child_entities() as $child_entity ) {
			$result['childEntities'][] = $this->get_entity_structure( $child_entity );
		}

		return $result;
	}

	public function save_entity_by_structure( array $entity_structure ) {
		$result = null;

		$parent_id = $entity_structure['parentId'];

		if ( substr( $parent_id, 0, 8 ) === 'virtual-' && isset( $this->virtual_id_list[ $parent_id ] ) ) {
			$parent_id = $this->virtual_id_list[ $parent_id ];
		}

		if ( 0 !== $parent_id && false === get_post_status( $parent_id ) ) {
			$parent_id = 0;
		}

		$entry = $this->get_entity_register()->get_entry( $entity_structure['entityKey'] );

		if ( is_null( $entry ) ) {
			$result = new \WP_Error(
				'entity_key_not_found_in_register',
				__( 'Error saving post', 'wcpf' )
			);
		}

		$entity_structure = apply_filters( 'wcpf_before_save_entity_' . $entity_structure['entityKey'] . '_structure', $entity_structure, $this );

		$entity_structure = apply_filters( 'wcpf_before_save_entity_structure', $entity_structure, $this );

		$post_data = array(
			'post_title'  => $entity_structure['title'],
			'post_parent' => $parent_id,
			'post_type'   => $entry['post_type'],
			'menu_order'  => $entity_structure['order'],
			'post_status' => 'publish',
			'meta_input'  => array(
				$this->get_option_key_meta() => isset( $entity_structure['options'] ) ? $entity_structure['options'] : array(),
				$this->get_entity_key_meta() => $entity_structure['entityKey'],
			),
		);

		if ( 'virtual' === $entity_structure['status'] ) {
			$result = wp_insert_post( $post_data );

			if ( 0 === $result ) {
				$result = new \WP_Error( 'failure_insert_post', __( 'Error inserting post', 'wcpf' ) );
			} else {
				$this->virtual_id_list[ $entity_structure['entityId'] ] = $result;
			}
		} elseif ( 'remove' === $entity_structure['status'] ) {
			$result = $entity_structure['entityId'];

			$is_virtual = substr( $entity_structure['entityId'], 0, 8 ) === 'virtual-';

			if ( ! $is_virtual && get_post_status( $entity_structure['entityId'] ) !== false ) {
				if ( wp_delete_post( $entity_structure['entityId'], true ) === false ) {
					$result = new \WP_Error( 'failure_delete_post', __( 'Error deleting post', 'wcpf' ) );
				}
			} elseif ( ! $is_virtual ) {
				$result = new \WP_Error( 'failure_delete_post', __( 'Error deleting post', 'wcpf' ) );
			}
		} elseif ( 'published' === $entity_structure['status'] ) {
			if ( get_post_status( $entity_structure['entityId'] ) !== false ) {
				$post_data['ID'] = $entity_structure['entityId'];

				$result = wp_update_post( $post_data, true );

				if ( 0 === $result ) {
					$result = new \WP_Error( 'failure_update_post', __( 'Error updating post', 'wcpf' ) );
				}
			} else {
				$result = new \WP_Error( 'failure_update_post', __( 'Error updating post', 'wcpf' ) );
			}
		}

		if ( ! is_wp_error( $result ) && isset( $entity_structure['childEntities'] ) ) {
			foreach ( $entity_structure['childEntities'] as $child ) {
				$original_id = $child['entityId'];

				if ( 'remove' === $entity_structure['status'] ) {
					$child['status'] = 'remove';
				}

				$result_child = $this->save_entity_by_structure( $child );

				if ( is_wp_error( $result_child ) ) {
					return $result_child;
				}

				if ( 'remove' !== $child['status'] ) {
					$this->virtual_id_list[ $original_id ] = $result_child;
				}
			}
		}

		return $result;
	}

	public function save_project_by_structure( array $project_structure ) {
		$result = null;

		$db = new \WP_Query();

		$db->query( 'START TRANSACTION' );

		try {
			$result = $this->save_entity_by_structure( $project_structure );

			if ( ! is_wp_error( $result ) ) {
				$this->load_project( $result );

				$this->get_hook_manager()->trigger_action(
					'wcpf_save_project',
					$this,
					array(
						'structure' => $project_structure,
					)
				);
			}
		} catch ( \Exception $exception ) {
			$result = new WP_Error( 'save_project_error', __( 'An error occurred while saving project', 'wcpf' ) );
		}

		if ( is_wp_error( $result ) ) {
			$db->query( 'ROLLBACK' );
		} else {
			$db->query( 'COMMIT' );
		}

		return $result;
	}
}
