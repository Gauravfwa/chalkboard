<?php

namespace Objectiv\Plugins\Checkout\Core;
use Objectiv\Plugins\Checkout\Main;
use Objectiv\Plugins\Checkout\Managers\ExtendedPathManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\TemplatesManager;

/**
 * Class Customizer
 *
 * @link checkoutwc.com
 * @since 2.4.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Customizer {
	var $settings_manager;
	var $templates_manager;
	var $path_manager;
	var $plugin_instance;

	/**
	 * Customizer constructor.
	 *
	 * @param Main $plugin_instance
	 * @param SettingsManager $settings_manager
	 * @param TemplatesManager $template_manager
	 * @param ExtendedPathManager $path_manager
	 */
	public function __construct( $plugin_instance, $settings_manager, $template_manager, $path_manager ) {
		$this->settings_manager  = $settings_manager;
		$this->templates_manager = $template_manager;
		$this->path_manager      = $path_manager;
		$this->plugin_instance   = $plugin_instance;

		$this->customizer_workaround();
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
	}

	function customizer_workaround() {
		add_action(
			'wp', function() {
				if ( is_customize_preview() || ! empty( $_GET['customize_changeset_uuid'] ) ) {
					// Reload the settings manager
					$cfw = Main::instance();

					$cfw->set_settings_manager( new SettingsManager() );
					$this->settings_manager = $cfw->get_settings_manager();

					$active_template = $this->settings_manager->get_setting( 'active_template' );

					// Create the template manager
					$cfw->set_templates_manager( new TemplatesManager( $this->path_manager, empty( $active_template ) ? 'default' : $active_template ) );
					$this->templates_manager = $cfw->get_templates_manager();
				}
			}
		);
	}

	/**
	 * @param \WP_Customize_Manager $wp_customize
	 */
	function register_customizer_settings( $wp_customize ) {
		/**
		 * Register Settings
		 */
		// Enabled
		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'enable' ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'           => $this->settings_manager->get_setting( 'enable' ), //Default setting/value to save
				'type'              => 'option', //Is this an 'option' or a 'theme_mod'?
				'capability'        => 'edit_theme_options', //Optional. Special permissions for accessing this setting.
				'transport'         => 'refresh', //What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
				'sanitize_callback' => array( $this, 'checkbox_sanitize' ),
			)
		);

		// Header Scripts
		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'header_scripts' ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'    => $this->settings_manager->get_setting( 'header_scripts' ), // Default setting/value to save
				'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
				'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
				'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			)
		);

		// Footer Scripts
		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'footer_scripts' ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'    => $this->settings_manager->get_setting( 'footer_scripts' ), // Default setting/value to save
				'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
				'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
				'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			)
		);

		/**
		 * Templates
		 */
		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'active_template' ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'    => $this->settings_manager->get_setting( 'active_template' ), // Default setting/value to save
				'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
				'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
				'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			)
		);

		/**
		 * Design: Global
		 */
		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'logo_attachment_id' ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'    => $this->settings_manager->get_setting( 'logo_attachment_id' ), // Default setting/value to save
				'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
				'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
				'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			)
		);

		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'footer_text' ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'    => $this->settings_manager->get_setting( 'footer_text' ), // Default setting/value to save
				'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
				'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
				'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			)
		);

		$active_template    = $this->templates_manager->get_active_template();

		/**
		 * Design: Theme Specific Settings
		 */
		foreach( $this->plugin_instance->get_theme_color_settings() as $key => $label ) {
			$wp_customize->add_setting(
				$this->get_customizer_field_name( $key, array( $active_template->get_slug() ) ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
				array(
					'default'    => $this->templates_manager->get_active_template()->get_default_setting( $key ), // Default setting/value to save
					'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
					'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
					'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
				)
			);
		}

		$wp_customize->add_setting(
			$this->get_customizer_field_name( 'custom_css', array( $active_template->get_slug() ) ), // No need to use a SERIALIZED name, as `theme_mod` settings already live under one db record
			array(
				'default'    => $this->settings_manager->get_setting( 'custom_css', array( $active_template->get_slug() ) ), // Default setting/value to save
				'type'       => 'option', // Is this an 'option' or a 'theme_mod'?
				'capability' => 'edit_theme_options', // Optional. Special permissions for accessing this setting.
				'transport'  => 'refresh', // What triggers a refresh of the setting? 'refresh' or 'postMessage' (instant)?
			)
		);


		/**
		 * Checkout for WooCommerce - Panel
		 */
		$wp_customize->add_panel(
			'cfw',
			array(
				'title'       => 'CheckoutWC',
				'priority'    => 1000,
				'capability'  => 'edit_theme_options',
				'description' => cfw__( 'Checkout for WooCommerce provides beautiful, conversion optimized checkout templates for WooCommerce.', 'checkout-wc' ),
			)
		);

		/**
		 * General Section
		 */
		$wp_customize->add_section(
			'cfw-general',
			array(
				'title'      => cfw__( 'General', 'checkout-wc' ),
				'priority'   => 10,
				'capability' => 'edit_theme_options',
				'panel'      => 'cfw',
			)
		);

		$wp_customize->add_control(
			$this->get_customizer_field_name( 'enable' ),
			array(
				'type'     => 'checkbox',
				'label'    => cfw__( 'Enable / Disable', 'checkout-wc' ), // Admin-visible name of the control
				'settings' => $this->get_customizer_field_name( 'enable' ), // Which setting to load and manipulate (serialized is okay)
				'priority' => 10, // Determines the order this control appears in for the specified section
				'section'  => 'cfw-general', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
			)
		);

		$wp_customize->add_control(
			new \WP_Customize_Code_Editor_Control(
				$wp_customize,
				$this->get_customizer_field_name( 'header_scripts' ),
				array(
					'code_type' => 'html',
					'label'     => cfw__( 'Header Scripts', 'checkout-wc' ), // Admin-visible name of the control
					'settings'  => $this->get_customizer_field_name( 'header_scripts' ), // Which setting to load and manipulate (serialized is okay)
					'priority'  => 10, // Determines the order this control appears in for the specified section
					'section'   => 'cfw-general', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
				)
			)
		);

		$wp_customize->add_control(
			new \WP_Customize_Code_Editor_Control(
				$wp_customize,
				$this->get_customizer_field_name( 'footer_scripts' ),
				array(
					'code_type' => 'html',
					'label'     => cfw__( 'Footer Scripts', 'checkout-wc' ), // Admin-visible name of the control
					'settings'  => $this->get_customizer_field_name( 'footer_scripts' ), // Which setting to load and manipulate (serialized is okay)
					'priority'  => 10, // Determines the order this control appears in for the specified section
					'section'   => 'cfw-general', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
				)
			)
		);

		/**
		 * Template Section
		 */
		$wp_customize->add_section(
			'cfw-templates',
			array(
				'title'      => cfw__( 'Template', 'checkout-wc' ),
				'priority'   => 10,
				'capability' => 'edit_theme_options',
				'panel'      => 'cfw',
			)
		);

		$template_choices = [];

		foreach ($this->templates_manager->get_available_templates() as $folder_name => $template ) {
			$template_choices[ $folder_name ] = $template->get_name();
		}

		$wp_customize->add_control(
			$this->get_customizer_field_name( 'active_template' ),
			array(
				'type'     => 'select',
				'label'    => cfw__( 'Template', 'checkout-wc' ), // Admin-visible name of the control
				'settings' => $this->get_customizer_field_name( 'active_template' ), // Which setting to load and manipulate (serialized is okay)
				'priority' => 10, // Determines the order this control appears in for the specified section
				'section'  => 'cfw-templates', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
				'choices'  => $template_choices,
			)
		);

		/**
		 * Design Section
		 */
		$wp_customize->add_section(
			'cfw-design',
			array(
				'title'      => cfw__( 'Design', 'checkout-wc' ),
				'priority'   => 10,
				'capability' => 'edit_theme_options',
				'panel'      => 'cfw',
			)
		);

		$wp_customize->add_control(
			new \WP_Customize_Media_Control(
				$wp_customize,
				$this->get_customizer_field_name( 'logo_attachment_id' ),
				array(
					'label'    => cfw__( 'Logo', 'checkout-wc' ), // Admin-visible name of the control
					'settings' => $this->get_customizer_field_name( 'logo_attachment_id' ), // Which setting to load and manipulate (serialized is okay)
					'priority' => 10, // Determines the order this control appears in for the specified section
					'section'  => 'cfw-design', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
				)
			)
		);

		$wp_customize->add_control(
			$this->get_customizer_field_name( 'footer_text' ),
			array(
				'type'     => 'textarea',
				'label'    => cfw__( 'Footer Text', 'checkout-wc' ), // Admin-visible name of the control
				'settings' => $this->get_customizer_field_name( 'footer_text' ), // Which setting to load and manipulate (serialized is okay)
				'priority' => 10, // Determines the order this control appears in for the specified section
				'section'  => 'cfw-design', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
			)
		);

		/**
		 * Design: Theme Specific Settings Section
		 */
		$wp_customize->add_section(
			'cfw-design-template',
			array(
				'title'       => cfw__( 'Design', 'checkout-wc' ) . ': ' . cfw__( 'Theme Specific Settings', 'checkout-wc' ),
				'priority'    => 10,
				'capability'  => 'edit_theme_options',
				'panel'       => 'cfw',
				'description' => cfw__( 'These settings only apply to the currently active template. If you switch templates in the customizer, these settings will become unavailable but saving will publish any changes here prior to switching templates!', 'checkout-wc' ),
			)
		);

		foreach( $this->plugin_instance->get_theme_color_settings() as $key => $label ) {
			$wp_customize->add_control(
				new \WP_Customize_Color_Control(
					$wp_customize,
					$this->get_customizer_field_name( $key, array( $active_template->get_slug() ) ),
					array(
						'label'           => $label, // Admin-visible name of the control
						'settings'        => $this->get_customizer_field_name( $key, array( $active_template->get_slug() ) ), // Which setting to load and manipulate (serialized is okay)
						'priority'        => 10, // Determines the order this control appears in for the specified section
						'section'         => 'cfw-design-template', // ID of the section this control should render in (can be one of yours, or a WordPress default section)
						'active_callback' => array( $this, 'is_correct_template_active' ),
					)
				)
			);
		}

		$wp_customize->add_control(
			new \WP_Customize_Code_Editor_Control(
				$wp_customize,
				$this->get_customizer_field_name( 'custom_css', array( $active_template->get_slug() ) ),
				array(
					'code_type'       => 'css',
					'label'           => cfw__( 'Custom CSS', 'checkout-wc' ), // Admin-visible name of the control
					'settings'        => $this->get_customizer_field_name( 'custom_css', array( $active_template->get_slug() ) ), // Which setting to load and manipulate (serialized is okay)
					'priority'        => 10, // Determines the order this control appears in for the specified section
					'section'         => 'cfw-design-template', // ID of the section this control should render in (can be one of yours, or a WordPress default section),
					'active_callback' => array( $this, 'is_correct_template_active' ),
				)
			)
		);
	}

	function is_correct_template_active( $control ) {
		return stripos( $control->id, $control->manager->get_setting( $this->get_customizer_field_name( 'active_template' ) )->value() ) !== false;
	}

	function get_customizer_field_name( $setting, $keys = array() ) {
		$field_name = str_ireplace( '[string]', '', $this->settings_manager->get_field_name( $setting, $keys ) );

		return str_ireplace( '__setting', '__settings', $field_name );
	}

	function checkbox_sanitize( $checked ) {
		return $checked == 'yes';
	}
}
