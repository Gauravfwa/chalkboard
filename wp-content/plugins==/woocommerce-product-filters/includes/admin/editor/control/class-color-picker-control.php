<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Color_Picker_Control extends Abstract_Control {
	public function get_control_type() {
		return 'ColorPicker';
	}

	public function render_control() {
		$this->render( 'control/color-picker.php' );
	}
}
