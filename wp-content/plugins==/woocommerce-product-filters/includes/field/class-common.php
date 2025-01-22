<?php

namespace WooCommerce_Product_Filter_Plugin\Field;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Element_Panel\Element_List_Control;

class Common extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wcpf_register_entities', 'register_entities' );

		$hook_manager->add_action( 'wcpf_admin_elements_panel', 'presets' );
	}

	public function register_entities( $register ) {
		$item_post_type = $this->get_component_register()->get( 'Project/Post_Type' )->get_item_post_type();

		$default_list_options = array(
			'itemsSource'                  => 'attribute',
			'itemsDisplay'                 => 'all',
			'queryType'                    => 'or',
			'itemsDisplayHierarchical'     => true,
			'displayHierarchicalCollapsed' => false,
			'displayTitle'                 => true,
			'displayToggleContent'         => true,
			'defaultToggleState'           => 'show',
			'cssClass'                     => '',
			'actionForEmptyOptions'        => 'hide',
			'displayProductCount'          => true,
			'productCountPolicy'           => 'for-option-only',
		);

		$register->register_entity(
			array(
				'id'                     => 'BoxListField',
				'label'                  => __( 'Box List', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array_merge(
					$default_list_options,
					array(
						'multiSelect' => true,
						'boxSize'     => '45px',
					)
				),
				'editor_component_class' => Box_list\Editor_Component::class,
				'filter_component_class' => Box_List\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'CheckBoxListField',
				'label'                  => __( 'Checkbox', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array_merge(
					$default_list_options,
					array(
						'seeMoreOptionsBy'       => 'scrollbar',
						'heightOfVisibleContent' => 12,
					)
				),
				'editor_component_class' => Check_Box_List\Editor_Component::class,
				'filter_component_class' => Check_Box_List\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'DropDownListField',
				'label'                  => __( 'Drop Down', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array_merge(
					$default_list_options,
					array(
						'titleItemReset' => __( 'Show all', 'wcpf' ),
					)
				),
				'editor_component_class' => Drop_Down_List\Editor_Component::class,
				'filter_component_class' => Drop_Down_List\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'ButtonField',
				'label'                  => __( 'Button', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array(
					'cssClass' => '',
					'action'   => 'reset',
				),
				'editor_component_class' => Button\Editor_Component::class,
				'filter_component_class' => Button\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'ColorListField',
				'label'                  => __( 'Colors', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array_merge(
					$default_list_options,
					array(
						'optionKey' => 'colors',
					)
				),
				'editor_component_class' => Color_List\Editor_Component::class,
				'filter_component_class' => Color_List\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'RadioListField',
				'label'                  => __( 'Radio', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array_merge(
					$default_list_options,
					array(
						'titleItemReset'         => __( 'Show all', 'wcpf' ),
						'seeMoreOptionsBy'       => 'scrollbar',
						'heightOfVisibleContent' => 12,
					)
				),
				'editor_component_class' => Radio_List\Editor_Component::class,
				'filter_component_class' => Radio_List\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'TextListField',
				'label'                  => __( 'Text List', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array_merge(
					$default_list_options,
					array(
						'multiSelect'    => true,
						'useInlineStyle' => false,
					)
				),
				'editor_component_class' => Text_List\Editor_Component::class,
				'filter_component_class' => Text_List\Filter_Component::class,
				'variations'             => true,
			)
		);

		$register->register_entity(
			array(
				'id'                     => 'PriceSliderField',
				'label'                  => __( 'Price slider', 'wcpf' ),
				'post_type'              => $item_post_type,
				'default_options'        => array(
					'minPriceOptionKey'    => 'min-price',
					'maxPriceOptionKey'    => 'max-price',
					'optionKey'            => 'price',
					'optionKeyFormat'      => 'dash',
					'cssClass'             => '',
					'displayMinMaxInput'   => true,
					'displayTitle'         => true,
					'displayToggleContent' => true,
					'defaultToggleState'   => 'show',
					'displayPriceLabel'    => true,
				),
				'editor_component_class' => Price_Slider\Editor_Component::class,
				'filter_component_class' => Price_Slider\Filter_Component::class,
			)
		);
	}

	public function presets( $panel ) {
		$panel->add_control(
			new Element_List_Control(
				array(
					'label'    => __( 'Field', 'wcpf' ),
					'key'      => 'field',
					'elements' => array(
						array(
							'id'          => 'CheckBoxListField',
							'title'       => __( 'CheckBox List', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/field/checkbox.png',
						),
						array(
							'id'          => 'RadioListField',
							'title'       => __( 'Radio List', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/field/radio.png',
						),
						array(
							'id'          => 'DropDownListField',
							'title'       => __( 'DropDown List', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/field/drop-down.png',
						),
						array(
							'id'          => 'ColorListField',
							'title'       => __( 'Color List', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/field/color.png',
						),
						array(
							'id'          => 'BoxListField',
							'title'       => __( 'Box List', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/field/box.png',
						),
						array(
							'id'          => 'TextListField',
							'title'       => __( 'Text List', 'wcpf' ),
							'picture_url' => $this->get_plugin()->get_resource_url() . 'images/field/text-list.png',
						),
						array(
							'id'            => 'PriceSliderField',
							'title'         => __( 'Price Slider', 'wcpf' ),
							'picture_url'   => $this->get_plugin()->get_resource_url() . 'images/field/slider.png',
							'default_state' => array(
								'title' => __( 'Price', 'wcpf' ),
							),
						),
						array(
							'id'            => 'ButtonField',
							'title'         => __( 'Button', 'wcpf' ),
							'picture_url'   => $this->get_plugin()->get_resource_url() . 'images/field/button.png',
							'default_state' => array(
								'title' => __( 'Reset', 'wcpf' ),
							),
						),
					),
				)
			)
		);

		$panel->add_control(
			new Element_List_Control(
				array(
					'label'    => __( 'Preset', 'wcpf' ),
					'key'      => 'preset',
					'elements' => array(
						array(
							'id'            => 'CategoriesPreset',
							'element_id'    => 'CheckBoxListField',
							'title'         => __( 'Categories', 'wcpf' ),
							'picture_url'   => $this->get_plugin()->get_resource_url() . 'images/field/checkbox.png',
							'default_state' => array(
								'title'   => __( 'Categories', 'wcpf' ),
								'options' => array(
									'itemsSource'          => 'category',
									'itemsDisplay'         => 'all',
									'queryType'            => 'or',
									'itemsDisplayHierarchical' => true,
									'displayTitle'         => true,
									'displayToggleContent' => true,
									'defaultToggleState'   => 'show',
									'cssClass'             => '',
									'optionKey'            => 'product-category',
									'itemsSourceCategory'  => 'all',
									'itemsDisplayWithoutParents' => 'all',
									'actionForEmptyOptions' => 'hide',
									'displayProductCount'  => true,
									'displayHierarchicalCollapsed' => true,
									'seeMoreOptionsBy'     => 'scrollbar',
									'heightOfVisibleContent' => 12,
								),
							),
						),
						array(
							'id'            => 'StockStatusPreset',
							'element_id'    => 'RadioListField',
							'title'         => __( 'Stock status', 'wcpf' ),
							'picture_url'   => $this->get_plugin()->get_resource_url() . 'images/field/radio.png',
							'default_state' => array(
								'title'   => __( 'Stock status', 'wcpf' ),
								'options' => array(
									'optionKey'       => 'stock-status',
									'itemsSource'     => 'stock-status',
									'displayedStockStatuses' => array( 'in-stock', 'out-of-stock' ),
									'actionForEmptyOptions' => 'noAction',
									'inStockText'     => __( 'In stock', 'woocommerce' ),
									'outOfStockText'  => __( 'Out of stock', 'woocommerce' ),
									'onBackorderText' => __( 'On backorder', 'woocommerce' ),
								),
							),
						),
					),
				)
			)
		);
	}
}
