<?php

namespace WooCommerce_Product_Filter_Plugin\Project\URL_Navigation;

class Query_Navigation extends Abstract_Navigation {
	public function decode_and_sanitize( $value ) {
		$value = wc_clean( wp_unslash( urldecode( $value ) ) );

		if ( strpos( $value, ',' ) !== false ) {
			$value = explode( ',', $value );
		}
		return $value;
	}

	public function has_attribute( $key ) {
		return array_key_exists( $key, $_GET ) || array_key_exists( $key, $_POST ); // phpcs:ignore WordPress.Security
	}

	public function get_attribute( $key ) {
		$value = '';

		if ( array_key_exists( $key, $_GET ) ) { // phpcs:ignore WordPress.Security
			$value = $this->decode_and_sanitize( $_GET[ $key ] ); // phpcs:ignore WordPress.Security
		} elseif ( array_key_exists( $key, $_POST ) ) { // phpcs:ignore WordPress.Security
			$value = $this->decode_and_sanitize( $_POST[ $key ] ); // phpcs:ignore WordPress.Security
		}

		return $value;
	}
}
