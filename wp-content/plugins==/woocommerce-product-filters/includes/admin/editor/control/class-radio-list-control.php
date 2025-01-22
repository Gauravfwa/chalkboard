<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Radio_List_Control extends Abstract_Control {
	protected $options = array();

	protected $is_inline_style = false;

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		if ( isset( $this->control_params['options'] ) && is_array( $this->control_params['options'] ) ) {
			$this->set_options( $this->control_params['options'] );
		}

		if ( isset( $this->control_params['is_inline_style'] ) ) {
			$this->set_is_inline_style( $this->control_params['is_inline_style'] );
		}
	}

	public function set_is_inline_style( $is_inline_style ) {
		$this->is_inline_style = $is_inline_style;
	}

	public function get_is_inline_style() {
		return $this->is_inline_style;
	}

	public function add_option( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	public function remove_option( $key ) {
		unset( $this->options[ $key ] );
	}

	public function get_options() {
		return $this->options;
	}

	public function set_options( array $options ) {
		$this->options = $options;
	}

	public function get_structure() {
		return array_merge(
			parent::get_structure(),
			array(
				'options'       => $this->get_options(),
				'isInlineStyle' => $this->get_is_inline_style(),
			)
		);
	}

	public function get_control_type() {
		return 'RadioList';
	}

	public function render_control() {
		$this->render(
			'control/radio-list.php',
			array(
				'options'         => $this->get_options(),
				'is_inline_style' => $this->get_is_inline_style(),
			)
		);
	}
}
