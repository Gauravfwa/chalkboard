<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

use WooCommerce_Product_Filter_Plugin\Structure;

abstract class Abstract_Control extends Structure\Component {
	protected $option_key = null;

	protected $label = null;

	protected $control_params = array();

	protected $control_source = 'options';

	protected $display_rules = array();

	protected $control_description = '';

	protected $default_value = null;

	protected $required = false;

	protected $template_root_path = null;

	public function __construct( array $params = array() ) {
		parent::__construct();

		$this->template_root_path = dirname( dirname( __DIR__ ) ) . '/views';

		if ( isset( $params['label'] ) ) {
			$this->set_label( $params['label'] );
		}

		if ( isset( $params['key'] ) ) {
			$this->set_option_key( $params['key'] );
		}

		if ( isset( $params['control_source'] ) ) {
			$this->set_control_source( $params['control_source'] );
		}

		if ( isset( $params['display_rules'] ) ) {
			$this->set_display_rules( $params['display_rules'] );
		}

		if ( isset( $params['control_description'] ) ) {
			$this->set_control_description( $params['control_description'] );
		}

		if ( isset( $params['default_value'] ) ) {
			$this->set_default_value( $params['default_value'] );
		}

		if ( isset( $params['required'] ) ) {
			$this->set_required( $params['required'] );
		}

		$this->control_params = $params;
	}

	public function get_label() {
		return $this->label;
	}

	public function set_label( $label ) {
		$this->label = $label;
	}

	public function get_option_key() {
		return $this->option_key;
	}

	public function set_option_key( $key ) {
		$this->option_key = $key;
	}

	public function get_required() {
		return $this->required;
	}

	public function set_required( $required ) {
		$this->required = $required;
	}

	public function get_control_source() {
		return $this->control_source;
	}

	public function set_control_source( $source ) {
		$this->control_source = $source;
	}

	public function get_default_value() {
		return $this->default_value;
	}

	public function set_default_value( $default_value ) {
		$this->default_value = $default_value;
	}

	public function get_control_description() {
		return $this->control_description;
	}

	public function set_control_description( $description ) {
		$this->control_description = $description;
	}

	public function get_display_rules() {
		return $this->display_rules;
	}

	public function set_display_rules( $rules ) {
		$this->display_rules = $rules;
	}

	protected function render( $template_path, array $context = array() ) {
		$context = array_merge(
			array(
				'label'               => $this->get_label(),
				'option_key'          => $this->get_option_key(),
				'control_key'         => $this->get_control_type(),
				'control_source'      => $this->get_control_source(),
				'control_description' => $this->get_control_description(),
				'default_value'       => $this->get_default_value(),
				'required'            => $this->get_required(),
			),
			$context
		);

		$this->get_template_loader()->render_template( $template_path, $context, $this->template_root_path );
	}

	public function get_structure() {
		return array(
			'label'         => $this->get_label(),
			'optionKey'     => $this->get_option_key(),
			'controlKey'    => $this->get_control_type(),
			'controlSource' => $this->get_control_source(),
			'displayRules'  => $this->get_display_rules(),
			'defaultValue'  => $this->get_default_value(),
			'required'      => $this->get_required(),
		);
	}

	abstract public function get_control_type();

	abstract public function render_control();
}
