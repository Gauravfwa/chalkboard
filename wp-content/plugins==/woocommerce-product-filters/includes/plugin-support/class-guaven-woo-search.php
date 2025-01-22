<?php

namespace WooCommerce_Product_Filter_Plugin\Plugin_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class Guaven_Woo_Search extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'wcpf_product_counts_search_sql', 'wcpf_product_counts_search_sql', 10, 1 );
	}

	public function wcpf_product_counts_search_sql( $search_sql ) {
		if ( isset( $GLOBALS['guaven_woo_search_backend'] )
			&& is_object( $GLOBALS['guaven_woo_search_backend'] ) ) {
			if ( method_exists( $GLOBALS['guaven_woo_search_backend'], 'backend_search_replacer' ) ) {
				$search_sql = $GLOBALS['guaven_woo_search_backend']->backend_search_replacer( $search_sql );
			}

			if ( method_exists( $GLOBALS['guaven_woo_search_backend'], 'backend_search_filter' ) ) {
				$search_sql = $GLOBALS['guaven_woo_search_backend']->backend_search_filter( $search_sql );
			}
		}

		return $search_sql;
	}
}
