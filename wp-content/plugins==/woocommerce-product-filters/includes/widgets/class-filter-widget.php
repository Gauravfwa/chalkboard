<?php

namespace WooCommerce_Product_Filter_Plugin\Widgets;

class Filter_Widget extends \WC_Widget {
	public function __construct() {
		$this->widget_cssclass = 'woocommerce wcpf_widget_filters';

		$this->widget_description = __( 'This widget displays a form with elements that you created in the project. When interacting with options, products are filtering.', 'wcpf' );

		$this->widget_id = 'wcpf_filters';

		$this->widget_name = __( 'Product Filters', 'wcpf' );

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
			'title'           => array(
				'type'  => 'text',
				'std'   => __( 'Filters', 'wcpf' ),
				'label' => __( 'Title', 'wcpf' ),
			),
			'id'              => array(
				'type'    => 'select',
				'std'     => wcpf_get_archive_filter_id( '' ),
				'label'   => __( 'Filters', 'wcpf' ),
				'options' => $filters,
			),
			'needToShowTitle' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Display widget title?', 'wcpf' ),
			),
		);
	}

	public function widget( $arguments, $instance ) {
		if ( $this->get_cached_widget( $arguments ) ) {
			return;
		}

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );

		if ( isset( $instance['id'] ) && ! is_null( $instance['id'] ) ) {
			$attributes = array(
				'before_html' => $arguments['before_widget'],
				'after_html'  => $arguments['after_widget'],
			);

			if ( $title && isset( $instance['needToShowTitle'] ) && $instance['needToShowTitle'] ) {
				$attributes['before_html'] .= $arguments['before_title'] . $title . $arguments['after_title'];
			}

			wcpf_component( 'Filters' )->print_product_filters( $instance['id'], true, $attributes );
		}
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
