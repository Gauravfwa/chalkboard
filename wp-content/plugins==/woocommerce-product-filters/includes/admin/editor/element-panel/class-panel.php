<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Element_Panel;

use WooCommerce_Product_Filter_Plugin\Structure,
	WooCommerce_Product_Filter_Plugin\Admin\Editor\Panel_Layout;

class Panel extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'wcpf_get_editor_panels', 'add_element_panel' );
	}

	public function add_element_panel( $panels ) {
		$panels['AddElement'] = $this->generate_panel();

		return $panels;
	}

	protected function generate_panel() {
		$panel = new Panel_Layout\List_Layout(
			array(
				'title'           => __( 'Add Element', 'wcpf' ),
				'panel_id'        => 'AddElement',
				'panel_auto_save' => true,
			)
		);

		$this->get_hook_manager()->trigger_action( 'wcpf_admin_elements_panel', $panel );

		return $panel;
	}
}
