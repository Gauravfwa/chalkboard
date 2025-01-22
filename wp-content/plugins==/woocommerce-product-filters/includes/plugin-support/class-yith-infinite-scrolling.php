<?php

namespace WooCommerce_Product_Filter_Plugin\Plugin_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class Yith_Infinite_Scrolling extends Structure\Component {
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
                            jQuery(document).trigger('yith-wcan-ajax-filtered');
                        });
                    });
                })();
            ",
			'after'
		);
	}
}
