<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Repeater_Control extends Abstract_Control implements Preparing_For_Reload_Interface, Contains_Controls_Interface {
	protected $add_item_text;

	protected $options_depends = array();

	protected $controls = array();

	protected $controls_handler = null;

	protected $load_controls_from_handler = false;

	protected $reset_value_on_reload = true;

	protected $item_options_depends = array();

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		if ( isset( $this->control_params['options_depends'] ) ) {
			$this->set_options_depends( $this->control_params['options_depends'] );
		}

		if ( isset( $this->control_params['item_options_depends'] ) ) {
			$this->set_item_options_depends( $this->control_params['item_options_depends'] );
		}

		if ( isset( $this->control_params['add_item_text'] ) ) {
			$this->set_add_item_text( $this->control_params['add_item_text'] );
		}

		if ( isset( $this->control_params['controls'] ) && is_array( $this->control_params['controls'] ) ) {
			$this->set_controls( $this->control_params['controls'] );
		}

		if ( isset( $this->control_params['controls_handler'] ) && is_callable( $this->control_params['controls_handler'] ) ) {
			$this->set_controls_handler( $this->control_params['controls_handler'] );

			if ( ! count( $this->get_controls() ) ) {
				$this->set_load_controls_from_handler( true );
			}
		}

		if ( isset( $this->control_params['load_controls_from_handler'] ) ) {
			$this->set_load_controls_from_handler( $this->control_params['load_controls_from_handler'] );
		}

		if ( isset( $this->control_params['reset_value_on_reload'] ) ) {
			$this->set_reset_value_on_reload( $this->control_params['reset_value_on_reload'] );
		}
	}

	public function initial_properties() {
		parent::initial_properties();

		if ( ! $this->get_add_item_text() ) {
			$this->set_add_item_text( __( 'Add Item', 'wcpf' ) );
		}

		foreach ( $this->get_child_controls() as $child_control ) {
			$this->get_component_builder()->implementation( $child_control );

			$child_control->set_option_key( $this->get_option_key() . '.$repeaterIndex.' . $child_control->get_option_key() );
		}
	}

	public function get_item_options_depends() {
		return $this->item_options_depends;
	}

	public function set_item_options_depends( $depends ) {
		$this->item_options_depends = $depends;
	}

	public function get_reset_value_on_reload() {
		return $this->reset_value_on_reload;
	}

	public function set_reset_value_on_reload( $reset ) {
		$this->reset_value_on_reload = $reset;
	}

	public function get_controls() {
		return $this->controls;
	}

	public function set_controls( $controls ) {
		$this->controls = $controls;
	}

	public function add_control( Abstract_Control $control ) {
		$this->controls[] = $control;
	}

	public function has_control( Abstract_Control $control ) {
		return array_search( $control, $this->controls, true ) !== false;
	}

	public function remove_control( Abstract_Control $control ) {
		$key = array_search( $control, $this->controls, true );

		if ( false !== $key ) {
			unset( $this->controls[ $key ] );
		}
	}

	public function get_controls_handler() {
		return $this->controls_handler;
	}

	public function set_controls_handler( $handler ) {
		$this->controls_handler = $handler;
	}

	public function get_options_depends() {
		return $this->options_depends;
	}

	public function set_options_depends( $depends ) {
		$this->options_depends = $depends;
	}

	public function get_add_item_text() {
		return $this->add_item_text;
	}

	public function set_add_item_text( $text ) {
		$this->add_item_text = $text;
	}

	public function get_load_controls_from_handler() {
		return $this->load_controls_from_handler;
	}

	public function set_load_controls_from_handler( $is_active ) {
		$this->load_controls_from_handler = $is_active;
	}

	public function get_control_type() {
		return 'Repeater';
	}

	public function prepare_for_reload( array $options, array $context, array $control_props = array() ) {
		if ( is_callable( $this->get_controls_handler() ) ) {
			$new_controls = call_user_func( $this->get_controls_handler(), $options, $context, $this );

			$this->set_controls( $new_controls );

			foreach ( $this->get_controls() as $child_control ) {
				$this->get_component_builder()->implementation( $child_control );

				$child_control->setOptionKey( $this->get_option_key() . '.$repeaterIndex.' . $child_control->get_option_key() );
			}
		}
	}

	public function get_child_controls() {
		return $this->get_controls();
	}

	public function get_child_control_by_option_key( $option_key ) {
		foreach ( $this->get_controls() as $control ) {
			if ( $control->get_option_key() === $option_key ) {
				return $control;
			}
		}

		return null;
	}

	protected function get_child_controls_structures() {
		$control_structures = array();

		foreach ( $this->get_controls() as $child_control ) {
			$relative_option_key = str_replace( $this->get_option_key() . '.$repeaterIndex.', '', $child_control->get_option_key() );

			$control_structures[ $relative_option_key ] = $child_control->get_structure();
		}

		return $control_structures;
	}

	public function get_structure() {
		return array_merge(
			parent::get_structure(),
			array(
				'addItemText'        => $this->get_add_item_text(),
				'optionsDepends'     => $this->get_options_depends(),
				'reloadAfterInit'    => $this->get_load_controls_from_handler(),
				'controlStructures'  => $this->get_child_controls_structures(),
				'resetValueOnReload' => $this->get_reset_value_on_reload(),
				'itemOptionsDepends' => $this->get_item_options_depends(),
			)
		);
	}

	public function render_control() {
		$this->render(
			'control/repeater.php',
			array(
				'add_item_text'         => $this->get_add_item_text(),
				'options_depends'       => $this->get_options_depends(),
				'reload_after_init'     => $this->get_load_controls_from_handler(),
				'control_structures'    => $this->get_child_controls_structures(),
				'reset_value_on_reload' => $this->get_reset_value_on_reload(),
				'item_options_depends'  => $this->get_item_options_depends(),
				'controls'              => $this->get_controls(),
			)
		);
	}
}
