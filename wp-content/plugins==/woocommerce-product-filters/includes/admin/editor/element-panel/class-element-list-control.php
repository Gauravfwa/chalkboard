<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Element_Panel;

use WooCommerce_Product_Filter_Plugin\Admin\Editor\Control\Abstract_Control;

class Element_List_Control extends Abstract_Control {
	protected $element_list = array();

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		if ( isset( $this->control_params['elements'] ) && is_array( $this->control_params['elements'] ) ) {
			foreach ( $this->control_params['elements'] as $element ) {
				$this->add_element( $element );
			}
		}
	}

	public function add_element( $item ) {
		$this->element_list[ $item['id'] ] = array(
			'id'            => isset( $item['element_id'] ) ? $item['element_id'] : $item['id'],
			'title'         => $item['title'],
			'picture_url'   => $item['picture_url'],
			'default_state' => isset( $item['default_state'] ) && is_array( $item['default_state'] ) ? $item['default_state'] : null,
		);
	}

	public function remove_element( $id ) {
		if ( isset( $this->element_list[ $id ] ) ) {
			unset( $this->element_list[ $id ] );
		}
	}

	public function get_elements() {
		return $this->element_list;
	}

	public function get_control_type() {
		return 'ElementList';
	}

	public function get_structure() {
		return array_merge(
			parent::get_structure(),
			array(
				'controlType' => $this->get_control_type(),
				'elements'    => $this->get_elements(),
			)
		);
	}

	public function render_control() {
		$this->render(
			'element-panel/element-list.php',
			array(
				'control_type' => $this->get_control_type(),
				'elements'     => $this->get_elements(),
			)
		);
	}
}
