<?php

namespace WooCommerce_Product_Filter_Plugin\Field\Button;

use WooCommerce_Product_Filter_Plugin\Filter\Component;

class Filter_Component extends Component\Base_Component implements Component\Rendering_Template_Interface {
	public function template_render() {
		$this->get_template_loader()->render_template(
			'field/button.php',
			array(
				'front_element' => $this,
				'action'        => $this->get_option( 'action' ),
				'entity'        => $this->get_entity(),
				'entity_id'     => $this->get_entity_id(),
				'css_class'     => $this->get_option( 'cssClass', '' ),
			)
		);
	}
}
