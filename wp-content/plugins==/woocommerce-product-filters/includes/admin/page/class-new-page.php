<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Page;

use WooCommerce_Product_Filter_Plugin\Structure;

class New_Page extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wcpf_admin_print_new_page', 'print_page' );
	}

	public function print_page( array $data ) {
		$this->get_template_loader()->render_template( 'editor.php', $data['template_context'], dirname( __DIR__ ) . '/views' );
	}
}
