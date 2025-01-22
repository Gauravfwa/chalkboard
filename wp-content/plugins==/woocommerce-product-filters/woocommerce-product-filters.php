<?php
/**
 * Plugin Name: Product Filters for WooCommerce
 * Plugin URI: https://woocommerce.com/products/product-filters/
 * Description: This is a tool to create product filters that make the process of finding products in your store simple and fast.
 * Version: 1.4.1
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Text Domain: wcpf
 * Domain Path: /languages
 * Tested up to: 6.1
 * WC tested up to: 7.4
 * WC requires at least: 3.9
 * Requires PHP: 7.0
 *
 * Copyright: © 2023 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Woo: 3546049:762bae993b965c395f0bf27fe08dd4cd
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'WC_PRODUCT_FILTER_VERSION', '1.4.1' ); // WRCS: DEFINED_VERSION.

define( 'WC_PRODUCT_FILTER_INDEX', 'wcpf' );

define( 'WC_PRODUCT_FILTER_PLUGIN_FILE', __FILE__ );

// HPOS compatibility declaration.
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
		}
	}
);

require_once __DIR__ . '/includes/functions.php';

require_once __DIR__ . '/includes/class-plugin.php';

add_filter( 'woocommerce_translations_updates_for_woocommerce-product-filters', '__return_true' );

$GLOBALS['wcpf_plugin'] = new WooCommerce_Product_Filter_Plugin\Plugin();
