<?php

namespace WooCommerce_Product_Filter_Plugin;

class Activator extends Structure\Component {
	public function get_project_post_type() {
		return $this->get_component_register()->get( 'Project/Post_Type' )->get_post_type();
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		register_activation_hook( WC_PRODUCT_FILTER_PLUGIN_FILE, array( $this, 'on_activate_plugin' ) );

		register_deactivation_hook( WC_PRODUCT_FILTER_PLUGIN_FILE, array( $this, 'on_deactivate_plugin' ) );

		$hook_manager->add_action( 'init', 'check_version', 5 );
	}

	public function check_version() {
		if ( ! is_blog_installed() ) {
			return;
		}

		if ( version_compare( $this->get_version_option(), WC_PRODUCT_FILTER_VERSION, '<' ) ) {
			self::on_update_plugin();
		}
	}

	public function on_update_plugin() {
		$update_functions = array(
			'1.1.6' => array(
				'wcpf_update_1_1_6_update_colors',
			),
			'1.1.9' => array(
				'wcpf_update_1_1_9_move_out_of_stock_option',
			),
		);

		foreach ( $update_functions as $version => $version_update_functions ) {
			if ( version_compare( $version, WC_PRODUCT_FILTER_VERSION, '<=' )
				&& version_compare( $version, $this->get_version_option(), '>' ) ) {
				foreach ( $version_update_functions as $update_function ) {
					call_user_func( $update_function );
				}
			}
		}

		$this->update_version_option();
	}

	protected function get_version_option() {
		return get_option( 'wcpf_version', '1.1.5' );
	}

	protected function update_version_option() {
		update_option( 'wcpf_version', WC_PRODUCT_FILTER_VERSION, true );
	}

	public function on_activate_plugin() {
		$this->get_hook_manager()->trigger_action( 'wcpf_register_entities', $this->get_entity_register() );

		$this->import_demo_data();

		$this->create_admin_notice();

		$this->on_update_plugin();
	}

	public function on_deactivate_plugin() {
		if ( class_exists( 'WC_Admin_Notices' ) ) {
			\WC_Admin_Notices::remove_notice( 'wcpf_activation' );
		}
	}

	public function import_demo_data() {
		if ( get_option( 'wcpf_export_demo_project', false ) || get_option( 'wcpf_setting_default_project', false ) ) {
			return;
		}

		$project = $this->get_component_builder()->build( Project\Project::class );

		$result = $project->save_project_by_structure( $this->get_demo_project_structure() );

		if ( ! is_wp_error( $result ) ) {
			update_option( 'wcpf_setting_default_project', $project->get_project_entity()->get_entity_id(), true );

			update_option( 'wcpf_export_demo_project', true );
		}
	}

	public function create_admin_notice() {
		$list_projects_url = add_query_arg(
			array(
				'post_type' => $this->get_project_post_type(),
			),
			admin_url( 'edit.php' )
		);

		$setting_url = add_query_arg(
			array(
				'page'    => 'wc-settings',
				'tab'     => 'products',
				'section' => 'wcpf',
			),
			admin_url( 'admin.php' )
		);

		\WC_Admin_Notices::add_custom_notice(
			'wcpf_activation',
			'<p>
                <strong>' . __( 'Product Filter for WooCommerce Installed', 'wcpf' ) . '</strong> <span> - ' . __( 'Now you\'re ready to edit filters', 'wcpf' ) . '</span>
            </p>
            <p class="submit">
                <a href="' . esc_attr( $list_projects_url ) . '" class="button button-primary">' . __( 'Edit Filters', 'wcpf' ) . '</a> <a href="' . esc_attr( $setting_url ) . '" class="button button-primary">' . __( 'Settings', 'wcpf' ) . '</a>
            </p>'
		);
	}

	public function get_demo_project_structure() {
		return array(
			'entityId'      => 'virtual-1',
			'parentId'      => 0,
			'title'         => __( 'Filters for product archive', 'wcpf' ),
			'entityKey'     => 'Project',
			'order'         => 0,
			'status'        => 'virtual',
			'options'       => array(
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
			'childEntities' => array(
				array(
					'entityId'  => 'virtual-2',
					'parentId'  => 'virtual-1',
					'title'     => __( 'Categories', 'wcpf' ),
					'entityKey' => 'CheckBoxListField',
					'order'     => 0,
					'status'    => 'virtual',
					'options'   =>
						array(
							'itemsSource'                  => 'category',
							'itemsDisplay'                 => 'all',
							'queryType'                    => 'or',
							'itemsDisplayHierarchical'     => true,
							'displayTitle'                 => true,
							'displayToggleContent'         => true,
							'defaultToggleState'           => 'show',
							'cssClass'                     => '',
							'optionKey'                    => 'product-category',
							'itemsSourceCategory'          => 'all',
							'itemsDisplayWithoutParents'   => 'all',
							'actionForEmptyOptions'        => 'hide',
							'displayProductCount'          => true,
							'displayHierarchicalCollapsed' => true,
							'seeMoreOptionsBy'             => 'scrollbar',
							'heightOfVisibleContent'       => 12,
						),
				),
				array(
					'entityId'  => 'virtual-3',
					'parentId'  => 'virtual-1',
					'title'     => __( 'Tags', 'wcpf' ),
					'entityKey' => 'TextListField',
					'order'     => 1,
					'status'    => 'virtual',
					'options'   => array(
						'itemsSource'                => 'tag',
						'itemsDisplay'               => 'all',
						'queryType'                  => 'and',
						'itemsDisplayHierarchical'   => false,
						'displayTitle'               => true,
						'displayToggleContent'       => true,
						'defaultToggleState'         => 'show',
						'cssClass'                   => '',
						'multiSelect'                => true,
						'useInlineStyle'             => true,
						'optionKey'                  => 'product-tag',
						'itemsDisplayWithoutParents' => 'all',
						'actionForEmptyOptions'      => 'hide',
						'displayProductCount'        => true,
					),
				),
				array(
					'entityId'  => 'virtual-4',
					'parentId'  => 'virtual-1',
					'title'     => __( 'Price', 'wcpf' ),
					'entityKey' => 'PriceSliderField',
					'order'     => 2,
					'status'    => 'virtual',
					'options'   => array(
						'minPriceOptionKey'    => 'min-price',
						'maxPriceOptionKey'    => 'max-price',
						'optionKey'            => 'product-price',
						'cssClass'             => '',
						'optionKeyFormat'      => 'dash',
						'displayTitle'         => true,
						'displayToggleContent' => true,
						'defaultToggleState'   => 'show',
						'displayPriceLabel'    => true,
						'displayMinMaxInput'   => true,
					),
				),
				array(
					'entityId'  => 'virtual-5',
					'parentId'  => 'virtual-1',
					'title'     => __( 'Reset', 'wcpf' ),
					'entityKey' => 'ButtonField',
					'order'     => 3,
					'status'    => 'virtual',
					'options'   => array(
						'cssClass' => '',
						'action'   => 'reset',
					),
				),
			),
		);
	}
}
