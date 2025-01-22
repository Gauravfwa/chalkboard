<?php

namespace WooCommerce_Product_Filter_Plugin;

class Autoloader {
	protected $plugin_name_space;

	protected $include_path;

	public function __construct() {
		$this->plugin_name_space = 'WooCommerce_Product_Filter_Plugin\\';

		$this->include_path = untrailingslashit( plugin_dir_path( WC_PRODUCT_FILTER_PLUGIN_FILE ) ) . '/includes/';
	}

	public function register() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	public function autoload( $class ) {
		if ( substr( $class, 0, strlen( $this->plugin_name_space ) ) !== $this->plugin_name_space ) {
			return;
		}

		$file = $this->transform_class_to_file_path( $class );

		if ( false !== $file && is_readable( $file ) ) {
			include_once $file;
		}
	}

	public function transform_class_to_file_path( $class ) {
		$relative_class = substr( $class, strlen( $this->plugin_name_space ) );

		$chunks = explode( '\\', str_ireplace( '_', '-', strtolower( $relative_class ) ) );

		if ( ! count( $chunks ) ) {
			return false;
		}

		$file_name = array_pop( $chunks );

		$interface_slug = '-interface';

		if ( substr( $file_name, -1 * strlen( $interface_slug ) ) === $interface_slug ) {
			$file_name = 'interface-' . substr( $file_name, 0, strlen( $file_name ) - strlen( $interface_slug ) ) . '.php';
		} else {
			$file_name = 'class-' . $file_name . '.php';
		}

		return $this->include_path . implode( '/', $chunks ) . ( count( $chunks ) ? '/' : '' ) . $file_name;
	}
}
