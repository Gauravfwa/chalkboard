<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Box_List;

use WooCommerce_Product_Filter_Plugin\Field\Filter\Abstract_List_Component;

class Filter_Component extends Abstract_List_Component {
	protected $supports = array(
		'multi_select',
		'multi_select_toggle',
		'toggle_content',
		'sorting',
	);

	public function template_render() {
		$this->get_template_loader()->render_template(
			'field/box-list.php',
			array_merge( $this->get_base_context(), array( 'box_size' => $this->get_option( 'boxSize' ) ) )
		);
	}
}
