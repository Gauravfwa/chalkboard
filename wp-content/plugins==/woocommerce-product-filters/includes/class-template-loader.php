<?php

namespace WooCommerce_Product_Filter_Plugin;

class Template_Loader extends Structure\Component {
	protected $templates_path;

	public function initial_properties() {
		$this->save_component_to_register( 'Template_Loader' );

		$this->templates_path = plugin_dir_path( WC_PRODUCT_FILTER_PLUGIN_FILE ) . 'templates/';
	}

	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'wcpf_template_context', 'default_context' );
	}

	public function default_context( array $context ) {
		if ( ! array_key_exists( 'template_loader', $context ) ) {
			$context['template_loader'] = $this;
		}

		if ( ! array_key_exists( 'plugin', $context ) ) {
			$context['plugin'] = $this->get_plugin();
		}

		return $context;
	}

	public function get_template_locate( $template_name, $path = null ) {
		if ( $path ) {
			$template = trailingslashit( $path ) . $template_name;
		} else {
			$template = locate_template( array( trailingslashit( $this->get_plugin()->get_plugin_uri() ) . $template_name ) );

			if ( ! $template ) {
				$template = $this->templates_path . $template_name;
			}
		}

		return $this->get_hook_manager()->apply_filters(
			'wcpf_template_locate',
			$template,
			$template_name,
			$path
		);
	}

	public function render_template( $template_name, array $context = array(), $path = null ) {
		$template = $this->compile_template( $template_name, $context, $path );

		if ( is_string( $template ) ) {
			echo $template;
		}
	}

	public function compile_template( $template_name, array $context = array(), $path = null ) {
		$template_file = $this->get_template_locate( $template_name, $path );

		if ( ! is_readable( $template_file ) ) {
			return false;
		}

		$context = $this->get_hook_manager()->apply_filters( 'wcpf_template_context', $context );

		ob_start();

		if ( count( $context ) ) {
			extract( $context );
		}

		include $template_file;

		return ob_get_clean();
	}
}
