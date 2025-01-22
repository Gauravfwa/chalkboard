<?php

namespace WooCommerce_Product_Filter_Plugin\Widgets;

class Filter_Notes_Widget extends \WC_Widget {
	public function __construct() {
		$this->widget_cssclass = 'woocommerce wcpf_widget_filter_notes';

		$this->widget_description = __( 'This widget displays a list of the selected options in the filter. You can also quickly remove the required option by clicking on cross button.', 'wcpf' );

		$this->widget_id = 'wcpf_filter_notes';

		$this->widget_name = __( 'Notes for Product Filters', 'wcpf' );

		parent::__construct();
	}

	public function init_settings() {
		$filter_posts = get_posts(
			array(
				'post_type'   => wcpf_component( 'Project/Post_Type' )->get_post_type(),
				'numberposts' => -1,
			)
		);

		$filters = array(
			'' => __( 'Not selected', 'wcpf' ),
		);

		foreach ( $filter_posts as $filter_post ) {
			$filters[ $filter_post->ID ] = $filter_post->post_title;
		}

		$this->settings = array(
			'id' => array(
				'type'    => 'select',
				'std'     => wcpf_get_archive_filter_id( '' ),
				'label'   => __( 'Filters', 'wcpf' ),
				'options' => $filters,
			),
		);
	}

	public function widget( $arguments, $instance ) {
		if ( $this->get_cached_widget( $arguments ) ) {
			return;
		}

		echo $arguments['before_widget'];

		if ( isset( $instance['id'] ) && ! is_null( $instance['id'] ) ) {
			wcpf_component( 'Filters' )->print_notes_for_product_filters( $instance['id'] );
		}

		echo $arguments['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$this->init_settings();

		return parent::update( $new_instance, $old_instance );
	}

	public function form( $instance ) {
		$this->init_settings();

		parent::form( $instance );
	}
}
