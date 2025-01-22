<?php

namespace WooCommerce_Product_Filter_Plugin\Layout\Simple_Box;

use WooCommerce_Product_Filter_Plugin\Filter\Component;

class Filter_Component extends Component\Base_Component implements Component\Rendering_Template_Interface {
	public function template_render() {
		$this->get_template_loader()->render_template(
			'layout/simple-box.php',
			array(
				'entity_id'            => $this->get_entity_id(),
				'entity'               => $this->get_entity(),
				'child_components'     => $this->get_child_filter_components(),
				'is_toggle_active'     => $this->get_option( 'displayToggleContent', false ),
				'default_toggle_state' => $this->get_option( 'defaultToggleState', null ),
				'css_class'            => $this->get_option( 'cssClass', '' ),
			)
		);
	}
}
