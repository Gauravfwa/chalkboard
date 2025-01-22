<?php

namespace WooCommerce_Product_Filter_Plugin\Plugin_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class BeRocket_Load_More_Products extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wp_enqueue_scripts', 'register_assets' );
	}

	public function register_assets() {
		wp_add_inline_script(
			'wcpf-plugin-script',
			"
                (function () {
                    window.addEventListener('load', function () {
                        jQuery(window).on('wcpf_update_products', function () {
                            jQuery(document).trigger('berocket_ajax_products_loaded');
                        });
                        
                        jQuery(window).on('wcpf_before_ajax_filtering', function () {
                            jQuery(document).trigger('berocket_ajax_filtering_start');
                        });
                        
                        jQuery(window).on('wcpf_after_ajax_filtering', function () {
                            jQuery(document).trigger('berocket_ajax_filtering_end');
                        });
                    });
                })();
            ",
			'after'
		);
	}
}
