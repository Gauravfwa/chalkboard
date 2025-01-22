<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Text_List;

use WooCommerce_Product_Filter_Plugin\Field\Filter\Abstract_List_Component;

class Filter_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'toggle_content',
		'product_counts',
		'multi_select_toggle',
		'hierarchical',
		'once_tree_select',
		'sorting',
	);

	protected function is_tree_view() {
		if ( ! $this->get_option( 'useInlineStyle', true ) && parent::is_tree_view() ) {
			return true;
		}

		return false;
	}

	public function template_render() {
		$this->get_template_loader()->render_template(
			'field/text-list.php',
			array_merge(
				$this->get_base_context(),
				array(
					'use_inline_style' => $this->get_option( 'useInlineStyle', true ),
				)
			)
		);
	}
}
