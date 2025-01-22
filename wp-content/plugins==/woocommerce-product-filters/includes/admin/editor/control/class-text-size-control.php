<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Text_Size_Control extends Abstract_Control {
	protected $units = array();

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		if ( isset( $this->control_params['units'] ) && is_array( $this->control_params['units'] ) ) {
			$this->units = $this->control_params['units'];
		}
	}

	public function set_units( $units ) {
		$this->units = $units;
	}

	public function get_units() {
		return $this->units;
	}

	public function get_control_type() {
		return 'TextSize';
	}

	protected function get_control_data() {
		return array(
			'placeholder' => isset( $this->control_params['placeholder'] ) ? $this->control_params['placeholder'] : '',
			'units'       => $this->units,
		);
	}

	public function get_structure() {
		return array_merge( parent::get_structure(), $this->get_control_data() );
	}

	public function render_control() {
		$this->render( 'control/text-size.php', $this->get_control_data() );
	}
}
