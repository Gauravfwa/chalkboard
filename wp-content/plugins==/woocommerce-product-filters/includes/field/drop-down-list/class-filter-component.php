<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Drop_Down_List;

use WooCommerce_Product_Filter_Plugin\Field\Filter\Abstract_List_Component;

class Filter_Component extends Abstract_List_Component {
	protected $supports = array(
		'reset_item',
		'toggle_content',
		'product_counts',
		'stock_status_options',
		'hierarchical',
		'sorting',
	);

	public function template_render() {
		$drop_down_style = $this->get_option( 'dropDownStyle', 'default' );

		if ( 'woocommerce' === $drop_down_style ) {
			wp_enqueue_script( 'selectWoo' );

			wp_enqueue_style( 'select2' );
		}

		$this->get_template_loader()->render_template( 'field/drop-down-list.php', $this->get_base_context() );
	}

	protected function get_base_context() {
		return array_merge(
			parent::get_base_context(),
			array(
				'drop_down_style' => $this->get_option( 'dropDownStyle', 'default' ),
			)
		);
	}

	protected function is_tree_view() {
		if ( $this->get_option( 'dropDownStyle', 'default' ) === 'woocommerce' && parent::is_tree_view() ) {
			return true;
		}

		return false;
	}
}
