<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommerceServices extends Base {
	public function is_available() {
		return defined( 'WOOCOMMERCE_CONNECT_MINIMUM_WOOCOMMERCE_VERSION' );
	}

	public function run() {

		global $wp_filter;

		$existing_hooks = $wp_filter['woocommerce_shipping_fields'];

		if ( $existing_hooks[10] ) {
			foreach ( $existing_hooks[10] as $key => $callback ) {
				if ( false !== stripos( $key, 'add_shipping_phone_to_checkout' ) ) {
					global $ActiveCampaign_Public;

					$WooCommerceServices = $callback['function'][0];
				}
			}
		}

		if ( ! empty( $WooCommerceServices ) ) {
			remove_filter( 'woocommerce_shipping_fields', array( $WooCommerceServices, 'add_shipping_phone_to_checkout' ) );
			remove_action( 'woocommerce_admin_shipping_fields', array( $WooCommerceServices, 'add_shipping_phone_to_order_fields' ) );
			remove_filter( 'woocommerce_get_order_address', array( $WooCommerceServices, 'get_shipping_phone_from_order' ), 10 );
		}
	}

	public function run_on_update_checkout() {
		$this->run();
	}
}
