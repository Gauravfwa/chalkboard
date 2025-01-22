<?php

namespace WooCommerce_Product_Filter_Plugin\Project;

use WooCommerce_Product_Filter_Plugin\Structure;

class Registration extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wcpf_register_entities', 'register_entity' );
	}

	public function register_entity( $register ) {
		$register->register_entity(
			array(
				'id'                     => 'Project',
				'label'                  => __( 'Project', 'wcpf' ),
				'post_type'              => $this->get_component_register()->get( 'Project/Post_Type' )->get_post_type(),
				'editor_component_class' => Editor_Component::class,
				'filter_component_class' => Filter_Component::class,
				'is_grouped'             => true,
				'default_options'        => array(
					'urlNavigation'                 => 'query',
					'filteringStarts'               => 'auto',
					'urlNavigationOptions'          => array(),
					'useComponents'                 => array(
						'pagination',
						'sorting',
						'results-count',
						'page-title',
						'breadcrumb',
					),
					'paginationAjax'                => true,
					'sortingAjax'                   => true,
					'productsContainerSelector'     => '.products',
					'paginationSelector'            => '.woocommerce-pagination',
					'resultCountSelector'           => '.woocommerce-result-count',
					'sortingSelector'               => '.woocommerce-ordering',
					'pageTitleSelector'             => '.woocommerce-products-header__title',
					'breadcrumbSelector'            => '.woocommerce-breadcrumb',
					'multipleContainersForProducts' => true,
				),
			)
		);
	}
}
