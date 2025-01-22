<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Switch_Control extends Abstract_Control {
	protected $first_option = array();

	protected $second_option = array();

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		if ( isset( $this->control_params['first_option'] ) ) {
			$this->first_option = $this->control_params['first_option'];
		} else {
			$this->first_option = array(
				'text'  => __( 'On', 'wcpf' ),
				'value' => 'on',
			);
		}

		if ( isset( $this->control_params['second_option'] ) ) {
			$this->second_option = $this->control_params['second_option'];
		} else {
			$this->second_option = array(
				'text'  => __( 'Off', 'wcpf' ),
				'value' => 'off',
			);
		}
	}

	public function get_first_option() {
		return $this->first_option;
	}

	public function set_first_option( $option ) {
		$this->first_option = $option;
	}

	public function get_second_option() {
		return $this->second_option;
	}

	public function set_second_option( $option ) {
		$this->second_option = $option;
	}

	public function get_control_type() {
		return 'Switch';
	}

	public function get_structure() {
		return array_merge(
			parent::get_structure(),
			array(
				'firstOption'  => $this->get_first_option(),
				'secondOption' => $this->get_second_option(),
			)
		);
	}

	public function render_control() {
		$this->render(
			'control/switch.php',
			array(
				'first_option'  => $this->get_first_option(),
				'second_option' => $this->get_second_option(),
			)
		);
	}
}
