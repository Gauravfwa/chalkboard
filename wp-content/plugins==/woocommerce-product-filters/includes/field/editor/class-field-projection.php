<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Editor;

use WooCommerce_Product_Filter_Plugin\Admin\Editor\Projection\Abstract_Projection;

class Field_Projection extends Abstract_Projection {
	public function set_title( $title ) {
		$this->projection_params['title'] = $title;
	}

	public function get_title() {
		return $this->projection_params['title'];
	}

	public function render_projection() {
		$context = wp_parse_args(
			$this->projection_params,
			array(
				'title'    => '',
				'supports' => array(
					'duplicate' => true,
					'edit'      => true,
				),
			)
		);

		$this->render( 'projection/field.php', $context );
	}
}
