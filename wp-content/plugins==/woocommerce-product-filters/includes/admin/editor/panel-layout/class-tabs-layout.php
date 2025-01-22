<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Panel_Layout;

use WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Tabs_Layout extends Abstract_Panel_Layout {
	protected $panel_tabs = array();

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		if ( isset( $this->panel_params['tabs'] ) && is_array( $this->panel_params['tabs'] ) ) {
			$this->set_tabs( $this->panel_params['tabs'] );
		}
	}

	public function set_tab_label( $tab_id, $tab_label ) {
		if ( $this->has_tab( $tab_id ) ) {
			$this->panel_tabs[ $tab_id ]['label'] = $tab_label;
		}
	}

	public function get_tab_label( $tab_id ) {
		if ( $this->has_tab( $tab_id ) ) {
			return $this->panel_tabs[ $tab_id ]['label'];
		}

		return false;
	}

	public function add_tab( $tab_id, $tab_label, array $controls = array() ) {
		$this->panel_tabs[ $tab_id ] = array(
			'label'    => $tab_label,
			'controls' => $controls,
		);
	}

	public function has_tab( $tab_id ) {
		return array_key_exists( $tab_id, $this->panel_tabs );
	}

	public function remove_tab( $tab_id ) {
		if ( $this->has_tab( $tab_id ) ) {
			unset( $this->panel_tabs[ $tab_id ] );
		}
	}

	public function get_tab( $tab_id ) {
		if ( $this->has_tab( $tab_id ) ) {
			return $this->panel_tabs[ $tab_id ];
		}

		return false;
	}

	public function get_tabs() {
		return $this->panel_tabs;
	}

	public function set_tabs( array $tabs ) {
		$this->panel_tabs = $tabs;
	}

	public function add_control( $tab_id, Control\Abstract_Control $control, $position = false ) {
		if ( $this->has_tab( $tab_id ) ) {
			if ( false === $position ) {
				$this->panel_tabs[ $tab_id ]['controls'][] = $control;
			} else {
				array_splice( $this->panel_tabs[ $tab_id ]['controls'], $position, 0, array( $control ) );
			}
		}
	}

	public function has_control( $tab_id, Control\Abstract_Control $control ) {
		if ( $this->has_tab( $tab_id ) ) {
			$key = array_search( $control, $this->get_tab_controls( $tab_id ), true );

			if ( false !== $key ) {
				return true;
			}
		}

		return false;
	}

	public function remove_control( $tab_id, Control\Abstract_Control $control ) {
		if ( $this->has_tab( $tab_id ) ) {
			$key = array_search( $control, $this->get_tab_controls( $tab_id ), true );

			if ( false !== $key ) {
				unset( $this->panel_tabs[ $tab_id ]['controls'][ $key ] );
			}
		}
	}

	public function remove_control_by_option_key( $tab_id, $option_key ) {
		if ( $this->has_tab( $tab_id ) ) {
			$remove_index = false;

			foreach ( $this->get_tab_controls( $tab_id ) as $index => $control ) {
				if ( $control->get_option_key() === $option_key ) {
					$remove_index = $index;

					break;
				}
			}

			if ( false !== $remove_index ) {
				unset( $this->panel_tabs[ $tab_id ]['controls'][ $remove_index ] );
			}
		}
	}

	public function get_tab_controls( $tab_id ) {
		if ( $this->has_tab( $tab_id ) ) {
			return $this->get_tab( $tab_id )['controls'];
		}

		return array();
	}

	public function get_structure() {
		$data = parent::get_structure();

		$data['tabs'] = array();

		foreach ( $this->get_tabs() as $tab_id => $tab_row ) {
			$tab_controls = array();

			foreach ( $this->get_tab_controls( $tab_id ) as $control ) {
				$tab_controls[] = $control->get_structure();
			}

			$data['tabs'][ $tab_id ] = $tab_controls;
		}

		return $data;
	}

	public function get_panel_layout_type() {
		return 'Tabs';
	}

	public function get_controls() {
		$controls = array();

		foreach ( $this->get_tabs() as $tab ) {
			$controls = array_merge( $controls, $tab['controls'] );
		}

		return $controls;
	}

	public function render_panel() {
		$this->render(
			'panel/tabs.php',
			array(
				'tabs' => $this->get_tabs(),
			)
		);
	}
}
