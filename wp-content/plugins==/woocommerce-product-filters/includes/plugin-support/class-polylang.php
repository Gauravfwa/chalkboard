<?php

namespace WooCommerce_Product_Filter_Plugin\Plugin_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class Polylang extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'wcpf_wc_filter_settings', 'wc_settings' );

		$hook_manager->add_filter( 'wcpf_default_filter_id', 'default_filter_id' );

		$hook_manager->add_action( 'init', 'init' );
	}

	public function init() {
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( 'price', 'Price', 'WooCommerce Product Filters' );
		}

		if ( function_exists( 'pll__' ) ) {
			$this->get_hook_manager()->add_filter( 'wcpf_translate', 'pll__' );
		}
	}

	public function default_filter_id( $filter_id ) {
		if ( ! function_exists( 'pll_current_language' ) ) {
			return $filter_id;
		}

		$lang_project_id = get_option( 'wcpf_setting_default_project_' . pll_current_language( 'slug' ), false );

		if ( $lang_project_id ) {
			return $lang_project_id;
		}

		return $filter_id;
	}

	public function wc_settings( $settings ) {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return $settings;
		}

		$lang_terms = pll_languages_list(
			array(
				'hide_empty' => false,
				'fields'     => null,
			)
		);

		if ( count( $lang_terms ) ) {
			$settings['section_polylang_title'] = array(
				'name' => __( 'Polylang', 'wcpf' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wcpf_setting_section_polylang_title',
			);

			foreach ( $lang_terms as $lang_term ) {
				$lang_setting = $settings['default_project'];

				$lang_setting['id'] = $lang_setting['id'] . '_' . $lang_term->slug;

				$lang_setting['title'] .= ' (' . $lang_term->name . ')';

				$settings[ 'default_project_' . $lang_term->slug ] = $lang_setting;
			}

			$settings['section_polylang_end'] = array(
				'type' => 'sectionend',
				'id'   => 'wcpf_setting_section_polylang_end',
			);
		}

		return $settings;
	}
}
