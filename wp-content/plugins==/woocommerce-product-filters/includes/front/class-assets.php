<?php

namespace WooCommerce_Product_Filter_Plugin\Front;

use WooCommerce;
use WooCommerce_Product_Filter_Plugin\Structure;

class Assets extends Structure\Component {
	public function initial_properties() {
		$this->save_component_to_register( 'Front/Assets' );
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_action( 'wp_enqueue_scripts', 'register_assets' );
	}

	public function register_assets() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$wc_instance = WooCommerce::instance();

		wp_register_script(
			'accounting',
			"{$wc_instance->plugin_url()}assets/js/accounting/accounting{$suffix}.js",
			array( 'jquery' ),
			'0.4.2',
			true
		);

		wp_enqueue_script(
			'wcpf-plugin-vendor-script',
			"{$this->get_plugin()->get_assets_url()}js/front-vendor.js",
			array(
				'jquery',
				'jquery-ui-slider',
			),
			WC_PRODUCT_FILTER_VERSION,
			false
		);

		wp_enqueue_script(
			'wcpf-plugin-script',
			"{$this->get_plugin()->get_assets_url()}build/js/plugin.js",
			array(
				'jquery',
				'wp-util',
				'jquery-ui-slider',
				'accounting',
				'wcpf-plugin-vendor-script',
			),
			WC_PRODUCT_FILTER_VERSION,
			false
		);

		wp_enqueue_style(
			'wcpf-plugin-style',
			"{$this->get_plugin()->get_assets_url()}css/plugin.css",
			array(),
			WC_PRODUCT_FILTER_VERSION
		);

		wp_localize_script(
			'wcpf-plugin-script',
			'WCPFData',
			array(
				'registerEntities'          => $this->get_entity_register()->get_all_entries(),
				'messages'                  => array(
					'selectNoMatchesFound' => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				),
				'selectors'                 => $this->get_selectors(),
				'pageUrl'                   => get_pagenum_link(),
				'isPaged'                   => is_paged() || isset( $_GET['product-page'] ) || isset( $_GET['paged'] ),
				'scriptAfterProductsUpdate' => get_option( 'wcpf_script_after_products_update', '' ),
				'scrollTop'                 => get_option( 'wcpf_scroll_top', 'no' ),
				'priceFormat'               => array(
					'currencyFormatNumDecimals' => 0,
					'currencyFormatSymbol'      => get_woocommerce_currency_symbol(),
					'currencyFormatDecimalSep'  => esc_attr( wc_get_price_decimal_separator() ),
					'currencyFormatThousandSep' => esc_attr( wc_get_price_thousand_separator() ),
					'currencyFormat'            => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
				),
			)
		);
	}

	public function get_selectors() {
		return $this->get_hook_manager()->apply_filters(
			'wcpf_selectors',
			array(
				'productsContainer'   => '.products',
				'paginationContainer' => '.woocommerce-pagination',
				'resultCount'         => '.woocommerce-result-count',
				'sorting'             => '.woocommerce-ordering',
				'pageTitle'           => '.woocommerce-products-header__title',
				'breadcrumb'          => '.woocommerce-breadcrumb',
			)
		);
	}
}
