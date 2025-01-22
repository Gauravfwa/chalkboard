<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Page;

use WooCommerce_Product_Filter_Plugin\Structure;

class Edit_Page extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wcpf_admin_print_edit_page', 'print_page' );

		$hook_manager->add_action( 'wcpf_admin_load_assets_for_edit_page', 'load_assets' );
	}

	public function load_assets( array $data ) {
		wp_localize_script( 'wcpf-admin-script', 'ProductFilterProjectData', $data['project']->get_project_structure() );
	}

	public function print_page( array $data ) {
		$this->get_template_loader()->render_template( 'editor.php', $data['template_context'], dirname( __DIR__ ) . '/views' );
	}
}
