<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Panel_Layout;

use WooCommerce_Product_Filter_Plugin\Structure;

abstract class Abstract_Panel_Layout extends Structure\Component {
	protected $panel_id = null;

	protected $panel_title = null;

	protected $panel_params = array();

	protected $panel_auto_save = false;

	protected $template_root_path = null;

	public function __construct( array $params = array() ) {
		parent::__construct();

		$this->template_root_path = dirname( dirname( __DIR__ ) ) . '/views';

		if ( isset( $params['title'] ) ) {
			$this->set_panel_title( $params['title'] );
		}

		if ( isset( $params['panel_id'] ) ) {
			$this->set_panel_id( $params['panel_id'] );
		}

		if ( isset( $params['panel_auto_save'] ) ) {
			$this->set_panel_auto_save( $params['panel_auto_save'] );
		}

		$this->panel_params = $params;
	}

	public function get_panel_id() {
		return $this->panel_id;
	}

	public function set_panel_id( $panel_id ) {
		$this->panel_id = $panel_id;
	}

	public function get_panel_title() {
		return $this->panel_title;
	}

	public function set_panel_title( $title ) {
		$this->panel_title = $title;
	}

	public function get_panel_auto_save() {
		return $this->panel_auto_save;
	}

	public function set_panel_auto_save( $auto_save ) {
		$this->panel_auto_save = $auto_save;
	}

	public function initial_properties() {
		foreach ( $this->get_controls() as $child_component ) {
			$this->get_component_builder()->implementation( $child_component );
		}
	}

	protected function render( $template_path, array $context = array() ) {
		$context = array_merge(
			array(
				'panel_id'         => $this->get_panel_id(),
				'panel_title'      => $this->get_panel_title(),
				'panel_layout_key' => $this->get_panel_layout_type(),
				'panel_auto_save'  => $this->get_panel_auto_save(),
				'panel'            => $this,
			),
			$context
		);

		$this->get_template_loader()->render_template( $template_path, $context, $this->template_root_path );
	}

	public function get_structure() {
		return array(
			'panelId'        => $this->get_panel_id(),
			'panelTitle'     => $this->get_panel_title(),
			'panelLayoutKey' => $this->get_panel_layout_type(),
			'panelAutoSave'  => $this->get_panel_auto_save(),
		);
	}

	public function get_control_by_option_key( $option_key ) {
		foreach ( $this->get_controls() as $control ) {
			if ( $control->get_option_key() === $option_key ) {
				return $control;
			}
		}

		return null;
	}

	abstract public function get_panel_layout_type();

	abstract public function get_controls();

	abstract public function render_panel();
}
