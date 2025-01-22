<?php

namespace WooCommerce_Product_Filter_Plugin\Project;

use WooCommerce_Product_Filter_Plugin\Admin\Editor\Component,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Control,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Panel_Layout;

class Editor_Component extends Component\Base_Component implements Component\Generates_Panels_Interface {
	public function generate_panels() {
		$assets_component = $this->get_component_register()->get( 'Front/Assets' );

		$default_selectors = $assets_component->get_selectors();

		$default_panel = new Panel_Layout\Tabs_Layout(
			array(
				'title'    => __( 'Project', 'wcpf' ),
				'panel_id' => 'Project',
				'tabs'     => array(
					'general'   => array(
						'label'    => __( 'General', 'wcpf' ),
						'controls' => array(
							new Control\Text_Control(
								array(
									'key'            => 'entityTitle',
									'control_source' => 'entity',
									'label'          => __( 'Title', 'wcpf' ),
									'placeholder'    => __( 'Title', 'wcpf' ),
									'default_value'  => __( 'Filters', 'wcpf' ),
									'required'       => true,
								)
							),
							new Control\Select_Control(
								array(
									'key'                 => 'filteringStarts',
									'label'               => __( 'Filtering starts', 'wcpf' ),
									'control_description' => __( 'Apply filters to product immediately when you change options or clicking on the "send" button', 'wcpf' ),
									'options'             => array(
										'auto'        => __( 'Automatically', 'wcpf' ),
										'send-button' => __( 'When on click send button', 'wcpf' ),
									),
									'default_value'       => 'auto',
									'required'            => true,
								)
							),
							new Control\Check_List_Control(
								array(
									'key'                 => 'useComponents',
									'label'               => __( 'Which components to use', 'wcpf' ),
									'control_description' => __( 'Content of components will be updated when filtering', 'wcpf' ),
									'options'             => array(
										'pagination'    => __( 'Pagination', 'wcpf' ),
										'sorting'       => __( 'Sorting', 'wcpf' ),
										'results-count' => __( 'Results count', 'wcpf' ),
										'page-title'    => __( 'Page title', 'wcpf' ),
										'breadcrumb'    => __( 'Breadcrumb', 'wcpf' ),
									),
									'default_value'       => array(
										'pagination',
										'sorting',
										'results-count',
										'page-title',
										'breadcrumb',
									),
								)
							),
							new Control\Switch_Control(
								array(
									'key'           => 'paginationAjax',
									'label'         => __( 'Pagination ajax', 'wcpf' ),
									'first_option'  => array(
										'text'  => __( 'On', 'wcpf' ),
										'value' => true,
									),
									'second_option' => array(
										'text'  => __( 'Off', 'wcpf' ),
										'value' => false,
									),
									'default_value' => true,
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'pagination',
										),
									),
								)
							),
							new Control\Switch_Control(
								array(
									'key'           => 'sortingAjax',
									'label'         => __( 'Sorting ajax', 'wcpf' ),
									'first_option'  => array(
										'text'  => __( 'On', 'wcpf' ),
										'value' => true,
									),
									'second_option' => array(
										'text'  => __( 'Off', 'wcpf' ),
										'value' => false,
									),
									'default_value' => true,
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'sorting',
										),
									),
								)
							),
						),
					),
					'selectors' => array(
						'label'    => __( 'Selectors', 'wcpf' ),
						'controls' => array(
							new Control\Text_Control(
								array(
									'key'           => 'productsContainerSelector',
									'label'         => __( 'Products container selector', 'wcpf' ),
									'default_value' => $default_selectors['productsContainer'],
									'required'      => true,
								)
							),
							new Control\Text_Control(
								array(
									'key'           => 'paginationSelector',
									'label'         => __( 'Pagination selector', 'wcpf' ),
									'default_value' => $default_selectors['paginationContainer'],
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'pagination',
										),
									),
									'required'      => true,
								)
							),
							new Control\Text_Control(
								array(
									'key'           => 'resultCountSelector',
									'label'         => __( 'Result count selector', 'wcpf' ),
									'default_value' => $default_selectors['resultCount'],
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'results-count',
										),
									),
									'required'      => true,
								)
							),
							new Control\Text_Control(
								array(
									'key'           => 'sortingSelector',
									'label'         => __( 'Sorting selector', 'wcpf' ),
									'default_value' => $default_selectors['sorting'],
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'sorting',
										),
									),
									'required'      => true,
								)
							),
							new Control\Text_Control(
								array(
									'key'           => 'pageTitleSelector',
									'label'         => __( 'Page title selector', 'wcpf' ),
									'default_value' => $default_selectors['pageTitle'],
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'page-title',
										),
									),
									'required'      => true,
								)
							),
							new Control\Text_Control(
								array(
									'key'           => 'breadcrumbSelector',
									'label'         => __( 'Breadcrumb selector', 'wcpf' ),
									'default_value' => $default_selectors['breadcrumb'],
									'display_rules' => array(
										array(
											'optionKey' => 'useComponents',
											'operation' => 'inControl',
											'value'     => 'breadcrumb',
										),
									),
									'required'      => true,
								)
							),
							new Control\Switch_Control(
								array(
									'key'           => 'multipleContainersForProducts',
									'label'         => __( 'Multiple containers for products', 'wcpf' ),
									'first_option'  => array(
										'text'  => __( 'On', 'wcpf' ),
										'value' => true,
									),
									'second_option' => array(
										'text'  => __( 'Off', 'wcpf' ),
										'value' => false,
									),
									'default_value' => false,
								)
							),
						),
					),
				),
			)
		);

		return array( $default_panel );
	}
}
