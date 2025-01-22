<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Radio_List;

use WooCommerce_Product_Filter_Plugin\Field\Filter\Abstract_List_Component;

class Filter_Component extends Abstract_List_Component {
	protected $supports = array(
		'reset_item',
		'hierarchical',
		'toggle_content',
		'product_counts',
		'see_more_options_by',
		'stock_status_options',
		'sorting',
	);

	public function template_render() {
		$this->get_template_loader()->render_template( 'field/radio-list.php', $this->get_base_context() );
	}
}
