<?php

function wcpf_plugin() {
	return $GLOBALS['wcpf_plugin'];
}

function wcpf_component( $component ) {
	return wcpf_plugin()->get_component_register()->get( $component );
}

function wcpf_print_notes_for_product_filters( $project_id ) {
	wcpf_component( 'Filters' )->print_notes_for_product_filters( $project_id );
}

function wcpf_print_product_filters( $project_id ) {
	wcpf_component( 'Filters' )->print_product_filters( $project_id );
}

function wcpf_prepare_product_query( \WP_Query $query, $project_id = null ) {
	if ( $project_id ) {
		$query->set( 'wcpf_filter_id', $project_id );
	}

	wcpf_component( 'Filters' )->save_query_and_apply_filters( $query );
}

function wcpf_has_project_component( $project_id ) {
	$component_storage = wcpf_component( 'Project/Filter_Component_Storage' );

	return $component_storage->has_project_component( $project_id );
}

function wcpf_has_project( $project_id ) {
	return wcpf_has_project_component( $project_id );
}

function wcpf_get_project_component( $project_id ) {
	$component_storage = wcpf_component( 'Project/Filter_Component_Storage' );

	return $component_storage->get_project_component( $project_id );
}

function wcpf_get_project( $project_id ) {
	if ( ! wcpf_has_project( $project_id ) ) {
		return null;
	}

	return wcpf_get_project_component( $project_id )->get_project();
}

function wcpf_get_archive_filter_id( $default_return = null ) {
	return wcpf_component( 'Filters' )->get_default_filter_id( $default_return );
}

function wcpf_is_filtered() {
	foreach ( wcpf_component( 'Project/Filter_Component_Storage' )->get_projects() as $project ) {
		if ( $project->is_filtered() ) {
			return true;
		}
	}

	return false;
}

function wcpf_update_1_1_9_move_out_of_stock_option() {
	$archive_project_id = get_option( 'wcpf_setting_default_project', false );

	if ( ! $archive_project_id || get_post_status( $archive_project_id ) === false ) {
		return;
	}

	$entity = new WooCommerce_Product_Filter_Plugin\Entity( $archive_project_id );

	$out_of_stock_option = $entity->get_option( 'outOfStockProducts', 'no-action' );

	update_option( 'wcpf_setting_out_of_stock_products', $out_of_stock_option );
}

function wcpf_update_1_1_6_update_colors() {
	$posts = get_posts(
		array(
			'post_type'      => 'wcpf_item',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => 'wcpf_entity_key',
					'value' => 'ColorListField',
				),
			),
		)
	);

	foreach ( $posts as $post ) {
		$entity = new WooCommerce_Product_Filter_Plugin\Entity( $post->ID );

		$color_options = $entity->get_option( 'colors', null );

		if ( ! $color_options ) {
			continue;
		}

		$items_source = $entity->get_option( 'itemsSource' );

		$taxonomy = false;

		if ( 'attribute' === $items_source ) {
			$taxonomy = wc_attribute_taxonomy_name( $entity->get_option( 'itemsSourceAttribute' ) );
		} elseif ( 'tag' === $items_source ) {
			$taxonomy = 'product_tag';
		} elseif ( 'category' === $items_source ) {
			$taxonomy = 'product_cat';
		} elseif ( 'taxonomy' === $items_source ) {
			$taxonomy = $entity->get_option( 'itemsSourceTaxonomy' );
		}

		if ( ! $taxonomy || $entity->has_option( 'colors_' . $taxonomy ) ) {
			continue;
		}

		$new_colors = array();

		foreach ( $color_options as $color_option ) {
			if ( ! isset( $color_option['term'] ) ) {
				continue;
			}

			$new_colors[ $color_option['term'] ] = $color_option;
		}

		$entity->set_option( 'colors_' . $taxonomy, $new_colors );

		$entity->remove_option( 'colors' );
	}
}
