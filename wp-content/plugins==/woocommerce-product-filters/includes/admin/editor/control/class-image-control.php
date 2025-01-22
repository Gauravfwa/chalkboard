<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Image_Control extends Abstract_Control {
	public function get_control_type() {
		return 'Image';
	}

	public function render_control() {
		$this->render( 'control/image.php' );
	}
}
