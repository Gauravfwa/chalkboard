<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Text_Control extends Abstract_Control {
	public function get_control_type() {
		return 'Text';
	}

	public function render_control() {
		$this->render(
			'control/text.php',
			array(
				'placeholder' => isset( $this->control_params['placeholder'] ) ? $this->control_params['placeholder'] : '',
			)
		);
	}
}
