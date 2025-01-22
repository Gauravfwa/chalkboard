<?php

namespace WooCommerce_Product_Filter_Plugin\Admin;

use WooCommerce_Product_Filter_Plugin\Structure;

class Assets extends Structure\Component {
	public function get_project_post_type() {
		return $this->get_component_register()->get( 'Project/Post_Type' )->get_post_type();
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'admin_enqueue_scripts', 'register_assets' );

		$hook_manager->add_action( 'admin_enqueue_scripts', 'assets_fix', 15 );
	}

	public function assets_fix() {
		$screen = get_current_screen();

		if ( $screen->post_type !== $this->get_project_post_type() ) {
			return;
		}

		wp_deregister_script( 'select2' );
	}


	public function register_assets() {
		$screen = get_current_screen();

		if ( 'woocommerce_page_wc-settings' === $screen->id && isset( $_GET['section'] ) && 'wcpf' === $_GET['section'] ) {
			wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		}

		if ( $screen->post_type !== $this->get_project_post_type() ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'wcpf-admin-vendor-script',
			"{$this->get_plugin()->get_assets_url()}js/admin-vendor.js",
			array( 'jquery' ),
			WC_PRODUCT_FILTER_VERSION,
			true
		);

		wp_enqueue_script(
			'wcpf-admin-script',
			"{$this->get_plugin()->get_assets_url()}build/js/admin.js",
			array(
				'jquery',
				'wp-util',
				'wp-color-picker',
				'jquery-ui-sortable',
				'wcpf-admin-vendor-script',
			),
			WC_PRODUCT_FILTER_VERSION,
			true
		);

		wp_enqueue_style(
			'wcpf-admin-vendor',
			"{$this->get_plugin()->get_assets_url()}css/admin-vendor.css",
			array(),
			WC_PRODUCT_FILTER_VERSION
		);

		wp_enqueue_style(
			'wcpf-admin-style',
			"{$this->get_plugin()->get_assets_url()}css/admin.css",
			array(
				'wp-color-picker',
				'wcpf-admin-vendor',
			),
			WC_PRODUCT_FILTER_VERSION
		);
	}
}
