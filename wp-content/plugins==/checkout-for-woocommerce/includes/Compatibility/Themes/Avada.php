<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;
use Objectiv\Plugins\Checkout\Main;

class Avada extends Base {
	public function is_available() {
		return defined( 'AVADA_VERSION' ); // determining if themes are available is a bit difficult and not really helpful here, so let's just always load it
	}

	public function run() {
		global $avada_woocommerce;

		// Remove actions
		remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'avada_top_user_container' ), 1 );
		remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'before_checkout_form' ) );
		remove_action( 'woocommerce_after_checkout_form', array( $avada_woocommerce, 'after_checkout_form' ) );
		remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'checkout_coupon_form' ), 10 );
		remove_action( 'woocommerce_checkout_after_order_review', array( $avada_woocommerce, 'checkout_after_order_review' ), 20 );
		remove_action( 'woocommerce_checkout_before_customer_details', array( $avada_woocommerce, 'checkout_before_customer_details' ) );
		remove_action( 'woocommerce_checkout_after_customer_details', array( $avada_woocommerce, 'checkout_after_customer_details' ) );
		remove_action( 'woocommerce_checkout_billing', array( $avada_woocommerce, 'checkout_billing' ), 20 );
		remove_action( 'woocommerce_checkout_shipping', array( $avada_woocommerce, 'checkout_shipping' ), 20 );

		add_action( 'wp_head', array( $this, 'cleanup_css' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_css_new' ), 0 ); // latest Avada

		$this->disable_lazy_loading();
	}

	function run_on_order_received() {
		global $avada_woocommerce;

		remove_action( 'woocommerce_thankyou', array( $avada_woocommerce, 'view_order' ) );

		$this->disable_lazy_loading();
	}

	function disable_lazy_loading() {
		if ( ! class_exists( '\\Fusion' ) ) {
			return;
		}

		$fusion = \Fusion::get_instance();
		remove_filter( 'wp_get_attachment_image_attributes', array( $fusion->images, 'lazy_load_attributes' ), 10 );
	}

	function cleanup_css() {
		global $wp_filter;

		$existing_hooks = $wp_filter['wp_head'];

		if ( $existing_hooks[999] ) {
			foreach ( $existing_hooks[999] as $key => $callback ) {
				if ( false !== stripos( $key, 'add_inline_css_wp_head' ) ) {
					global $Fusion_Dynamic_CSS_File;

					$Fusion_Dynamic_CSS_File = $callback['function'][0];
				}
			}
		}

		if ( empty( $Fusion_Dynamic_CSS_File ) ) {
			return;
		}

		$action = \fusion_should_defer_styles_loading() ? 'wp_body_open' : 'wp_enqueue_scripts';
		remove_action( $action, array( $Fusion_Dynamic_CSS_File, 'add_inline_css' ) );
		remove_action( 'wp_head', array( $Fusion_Dynamic_CSS_File, 'add_custom_css_to_wp_head' ), 999 );
		remove_action( 'wp_head', array( $Fusion_Dynamic_CSS_File, 'add_inline_css_wp_head' ), 999 );
	}

	function cleanup_css_new() {
		global $wp_filter;

		$existing_hooks = $wp_filter['wp_enqueue_scripts'];

		if ( $existing_hooks[11] ) {
			foreach ( $existing_hooks[11] as $key => $callback ) {
				if ( false !== stripos( $key, 'enqueue_dynamic_css' ) ) {
					global $Fusion_Dynamic_CSS_File;

					$Fusion_Dynamic_CSS_File = $callback['function'][0];
				}
			}
		}

		if ( empty( $Fusion_Dynamic_CSS_File ) ) {
			return;
		}

		remove_action( 'wp_enqueue_scripts', array( $Fusion_Dynamic_CSS_File, 'enqueue_dynamic_css' ), 11 );
	}
}
