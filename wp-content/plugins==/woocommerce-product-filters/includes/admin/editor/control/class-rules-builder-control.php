<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Control;

class Rules_Builder_Control extends Abstract_Control {
	protected $use_entries;

	protected $title_before_fields = '';

	public function get_control_type() {
		return 'RulesBuilder';
	}

	public function __construct( array $params = array() ) {
		parent::__construct( $params );

		$this->use_entries = array(
			'category'  => __( 'Category', 'wcpf' ),
			'attribute' => __( 'Attribute', 'wcpf' ),
			'taxonomy'  => __( 'Taxonomy', 'wcpf' ),
			'tag'       => __( 'Tag', 'wcpf' ),
			'page'      => __( 'Page', 'wcpf' ),
		);

		if ( isset( $this->control_params['use_entries'] ) && is_array( $this->control_params['use_entries'] ) ) {
			$this->set_use_entries( $this->control_params['use_entries'] );
		}

		if ( isset( $this->control_params['title_before_fields'] ) ) {
			$this->title_before_fields = $this->control_params['title_before_fields'];
		}
	}

	public function get_use_entries() {
		return $this->use_entries;
	}

	public function set_use_entries( array $entries ) {
		$this->use_entries = $entries;
	}

	public function get_structure() {
		return array_merge(
			parent::get_structure(),
			array(
				'useEntries'            => array_keys( $this->get_use_entries() ),
				'ruleTemplateHtml'      => $this->get_template_loader()->compile_template(
					'parts/rule-template.php',
					array(
						'option_key'  => $this->get_option_key(),
						'use_entries' => $this->get_use_entries(),
					),
					$this->template_root_path
				),
				'groupRuleTemplateHtml' => $this->get_template_loader()->compile_template( 'parts/group-rule-template.php', array(), $this->template_root_path ),
			)
		);
	}

	public function render_control() {
		$this->render(
			'control/rules-builder.php',
			array(
				'use_entries'         => $this->get_use_entries(),
				'title_before_fields' => $this->title_before_fields,
			)
		);
	}
}
