<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Check_Box_List;

use WooCommerce_Product_Filter_Plugin\Field\Filter\Abstract_List_Component;

class Filter_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'hierarchical',
		'toggle_content',
		'product_counts',
		'once_tree_select',
		'see_more_options_by',
		'stock_status_options',
		'sorting',
	);

	public function template_render() {
		$this->get_template_loader()->render_template( 'field/check-box-list.php', $this->get_base_context() );
	}
}
