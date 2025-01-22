<?php

namespace WooCommerce_Product_Filter_Plugin\Plugin_Support;

use WooCommerce_Product_Filter_Plugin\Structure;

class Wpml extends Structure\Component {
	public function attach_hooks( Structure\Hook_Manager $hook_manager ) {
		$hook_manager->add_filter( 'wcpf_wc_filter_settings', 'wc_settings' );

		$hook_manager->add_filter( 'wcpf_default_filter_id', 'default_filter_id' );
	}

	public function default_filter_id( $filter_id ) {
		if ( ! isset( $GLOBALS['sitepress'] )
			&& ! method_exists( $GLOBALS['sitepress'], 'get_current_language' ) ) {
			return $filter_id;
		}

		$lang_project_id = get_option( 'wcpf_setting_default_project_' . $GLOBALS['sitepress']->get_current_language(), false );

		if ( $lang_project_id ) {
			return $lang_project_id;
		}

		return $filter_id;
	}

	public function wc_settings( $settings ) {
		if ( ! isset( $GLOBALS['sitepress'] )
			&& ! method_exists( $GLOBALS['sitepress'], 'get_active_languages' ) ) {
			return $settings;
		}

		$langs = $GLOBALS['sitepress']->get_active_languages();

		if ( count( $langs ) ) {
			$settings['section_wpml_title'] = array(
				'name' => __( 'WPML', 'wcpf' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wcpf_setting_section_wpml_title',
			);

			foreach ( $langs as $lang_code => $lang_data ) {
				$lang_setting = $settings['default_project'];

				$lang_setting['id'] = $lang_setting['id'] . '_' . $lang_code;

				$lang_setting['title'] .= ' (' . $lang_data['native_name'] . ')';

				$settings[ 'default_project_' . $lang_code ] = $lang_setting;
			}

			$settings['section_wpml_end'] = array(
				'type' => 'sectionend',
				'id'   => 'wcpf_setting_section_wpml_end',
			);
		}

		return $settings;
	}
}
