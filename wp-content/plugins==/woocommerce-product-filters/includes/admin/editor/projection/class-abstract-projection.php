<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Projection;

use WooCommerce_Product_Filter_Plugin\Structure;

abstract class Abstract_Projection extends Structure\Component {
	protected $projection_params = array();

	protected $template_root_path = null;

	public function __construct( array $params = array() ) {
		parent::__construct();

		$this->template_root_path = dirname( dirname( __DIR__ ) ) . '/views';

		$this->projection_params = $params;
	}

	protected function render( $template_path, array $context = array() ) {
		$context = array_merge( $this->projection_params, $context );

		$this->get_template_loader()->render_template( $template_path, $context, $this->template_root_path );
	}

	abstract public function render_projection();
}
