<?php
/**
 * Elementor features.
 *
 * @package white-label
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add Elementor fields to White Label Settings.
 *
 * @param mixed $settings White Label Settings.
 */
function white_label_settings_elementor($fields)
{
    if (!$fields) {
        return;
    }

    if (is_plugin_active('elementor/elementor.php') || is_plugin_active('elementor-pro/elementor-pro.php')) {
        array_push($fields['white_label_plugins'], [
            'name' => 'plugins_elementor_section',
            'label' => __('Elementor', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/white-label-elementor/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Hide and adjust the interface of the Elementor plugin for non-White Label Administrators.', 'white-label'),
            'type' => 'subheading',
            'class' => 'subheading',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'plugins_elementor_logos_grouping',
            'label' => __('Logos', 'white-label'),
            'desc' => __('Hide or replace logos throughout the Elementor editor.', 'white-label'),
            'type' => 'grouping',
            'class' => 'grouping',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_hide_logo',
            'label' => __('Hide Logo', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/hide-elementor-logo/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Hide the Elementor logo from the editor (loading screen, panel, buttons) and the WordPress admin bar menu.', 'white-label'),
            'type' => 'checkbox',
        ]);

        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_logo_loading',
            'label' => __('Loading Logo', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/replace-elementor-loading-logo/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Replace the Elementor logo on the editor\'s loading screen.', 'white-label'),
            'type' => 'file',
            'default' => '',
            'options' => [
                'button_label' => __('Choose Logo', 'white-label'),
            ],
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'plugins_elementor_colors_grouping',
            'label' => __('Colors', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/elementor-editor-colors/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Replace colors used on the the Elementor editor.', 'white-label'),
            'type' => 'grouping',
            'class' => 'grouping',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_editor_primary_color',
            'label' => __('Editor Primary Color', 'white-label'),
            'desc' => __('Color of the panel header, panel footer, and primary buttons.', 'white-label'),
            'type' => 'color',
            'default' => '',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_editor_secondary_color',
            'label' => __('Editor Secondary Color', 'white-label'),
            'desc' => __('Color of the panel footer icons, footer text, notification dot, etc.', 'white-label'),
            'type' => 'color',
            'default' => '',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'plugins_elementor_text_grouping',
            'label' => __('Text', 'white-label'),
            'desc' => __('Replace text referring to Elementor in the WordPress admin.', 'white-label'),
            'type' => 'grouping',
            'class' => 'grouping',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_hide_post_state_text',
            'label' => __('Hide Post State Text', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/hide-elementor-post-state-text/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Hide the "— Elementor" Text on the admin\'s list of pages.', 'white-label'),
            'type' => 'checkbox',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_replace_post_state_text',
            'label' => __('Post State Text', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/replace-elementor-post-state-text/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Replace the "— Elementor" Text on the admin\'s list of pages.', 'white-label'),
            'type' => 'text',
        ]);

        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_replace_edit_with_elementor_text',
            'label' => __('"Edit with Elementor" Text', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/edit-with-elementor-text/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Replace the "Edit with Elementor" Text on buttons and menu items.', 'white-label'),
            'type' => 'text',
        ]);
        
        array_push($fields['white_label_plugins'], [
            'name' => 'plugins_elementor_branding_navigation_editor_grouping',
            'label' => __('Editor Navigation', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/elementor-editor-navigation/"><span class="dashicons dashicons-editor-help"></span></a>',
            'desc' => __('Hide menu items in the Elementor editor panel.', 'white-label'),
            'type' => 'grouping',
            'class' => 'grouping',
        ]);

        array_push($fields['white_label_plugins'], [
            'name' => 'elementor_editor_navigation',
            'label' => __('Editor Navigation', 'white-label'),
            'desc' => '',
            'type' => 'elementor_editor_navigation',
            'options' => [
                'site-settings' => __('Site Settings', 'white-label'),
                'theme-builder' => __('Theme Builder', 'white-label'),
                'user-preferences' => __('User Preferences', 'white-label'),
                'add-ons' => __('Add-ons', 'white-label'),
                'notes' => __('Notes', 'white-label'),
                'finder' => __('Finder', 'white-label'),
                'whats-new' => __('What\'s New', 'white-label'),
                'view-page' => __('View Page', 'white-label'),
                'exit' => __('Exit', 'white-label'),
            ],
        ]);

        if (!is_plugin_active('elementor-pro/elementor-pro.php')) {
            array_push($fields['white_label_plugins'], [
                'name' => 'plugins_elementor_pro_grouping',
                'label' => __('Elementor Pro', 'white-label'),
                'desc' => __('Hide Elementor Pro upsells and unavailable features.', 'white-label'),
                'type' => 'grouping',
                'class' => 'grouping',
            ]);

            array_push($fields['white_label_plugins'], [
                'name' => 'elementor_hide_upgrade_nags',
                'label' => __('Hide Upgrade Nags', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/hide-elementor-pro-upgrade-nags/"><span class="dashicons dashicons-editor-help"></span></a>',
                'desc' => __('', 'white-label'),
                'type' => 'checkbox',
            ]);

            array_push($fields['white_label_plugins'], [
                'name' => 'elementor_hide_pro_widgets',
                'label' => __('Hide Pro Widgets', 'white-label').'<a target="_blank" tabindex="-1" class="white-label-help" href="https://whitewp.com/documentation/article/hide-elementor-pro-widgets/"><span class="dashicons dashicons-editor-help"></span></a>',
                'desc' => __('', 'white-label'),
                'type' => 'checkbox',
            ]);
        }
    }

    return $fields;
}
add_filter('white_label_settings_fields', 'white_label_settings_elementor');

/**
 * Apply Elementor-related CSS rules to the Elementor editor.
 *
 * @return void
 */
function white_label_elementor_editor_css()
{
    // Exit early if White Label is not enabled.
    $enable_white_label = white_label_get_option('enable_white_label', 'white_label_general', false);
    if ($enable_white_label !== 'on') {
        return;
    }

    // Exit early if White Label admin.
    if (white_label_is_wl_admin()) {
        return;
    }

    $elementor_hide_logo = white_label_get_option('elementor_hide_logo', 'white_label_plugins', 'off');
    $elementor_logo_loading = white_label_get_option('elementor_logo_loading', 'white_label_plugins', false);
    $elementor_logo_panel = white_label_get_option('elementor_logo_panel', 'white_label_plugins', false);
    $elementor_editor_primary_color = white_label_get_option('elementor_editor_primary_color', 'white_label_plugins', false);
    $elementor_editor_secondary_color = white_label_get_option('elementor_editor_secondary_color', 'white_label_plugins', false);
    $elementor_editor_navigation = white_label_get_option('elementor_editor_navigation', 'white_label_plugins', []);
    $elementor_hide_upgrade_nags = white_label_get_option('elementor_hide_upgrade_nags', 'white_label_plugins', 'off');
    $elementor_hide_pro_widgets = white_label_get_option('elementor_hide_pro_widgets', 'white_label_plugins', 'off');
    ?>
    <style type="text/css">
        <?php if ($elementor_hide_logo === 'on') : ?>
        .elementor-loader, #elementor-panel-header-title img, #elementor-editor-button i.eicon-elementor-square {
            display: none !important;
        }
        <?php endif; ?>
        
        <?php if ($elementor_logo_loading) : ?>
        .elementor-loader {
            display: block !important;
        }

        .elementor-loader .elementor-loader-boxes {
            display: none !important;
        }

        .elementor-loader {
            background-color: transparent !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-image: url(<?php echo $elementor_logo_loading; ?>);
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }
        <?php endif; ?>

        <?php if ($elementor_logo_panel) : ?>
        #elementor-panel-header-title img {
            display: none !important;
        }

        #elementor-panel-header-title {
            height: 30px;
            background-image: url(<?php echo $elementor_logo_panel; ?>);
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }
        <?php endif; ?>

        <?php if ($elementor_editor_primary_color) : ?>
        #elementor-panel-header,
        #elementor-panel-footer, #elementor-panel-footer .elementor-button, .elementor-panel-footer-sub-menu-wrapper {
            background-color: <?php echo $elementor_editor_primary_color; ?> !important;
        }
        
        .dialog-type-alert .dialog-buttons-wrapper .dialog-button.dialog-ok, .dialog-type-alert .dialog-buttons-wrapper .dialog-button.dialog-take_over, 
        .dialog-type-alert .dialog-buttons-wrapper .dialog-button.e-primary, .dialog-type-confirm .dialog-buttons-wrapper .dialog-button.dialog-ok, 
        .dialog-type-confirm .dialog-buttons-wrapper .dialog-button.dialog-take_over, .dialog-type-confirm .dialog-buttons-wrapper .dialog-button.e-primary {
            background-color: <?php echo $elementor_editor_primary_color; ?> !important;
        }

        .elementor-control-type-switcher .elementor-switch-input:checked~.elementor-switch-label {
            background-color: <?php echo $elementor_editor_primary_color; ?> !important;
        }
        <?php endif; ?>

        <?php if ($elementor_editor_secondary_color) : ?>
        .elementor-panel #elementor-panel-header-title, .elementor-panel .elementor-panel-footer-tool, .elementor-panel .elementor-panel-footer-tool .elementor-button, .elementor-panel-footer-sub-menu-item {
            color: <?php echo $elementor_editor_secondary_color; ?> !important;
        }

        .dialog-type-alert .dialog-buttons-wrapper .dialog-button.dialog-ok, .dialog-type-alert .dialog-buttons-wrapper .dialog-button.dialog-take_over, 
        .dialog-type-alert .dialog-buttons-wrapper .dialog-button.e-primary, .dialog-type-confirm .dialog-buttons-wrapper .dialog-button.dialog-ok, 
        .dialog-type-confirm .dialog-buttons-wrapper .dialog-button.dialog-take_over, .dialog-type-confirm .dialog-buttons-wrapper .dialog-button.e-primary {
            color: <?php echo $elementor_editor_secondary_color; ?> !important;
        }

        .elementor-control-type-switcher .elementor-switch-input:checked~.elementor-switch-label {
            color: <?php echo $elementor_editor_secondary_color; ?> !important;
        }

        body.e-has-notification:not(.e-route-panel-menu) #elementor-panel-header-menu-button:after,
        body.e-has-notification .elementor-panel-menu-item.elementor-panel-menu-item-notification-center .elementor-panel-menu-item-icon:after, body.e-has-notification:not(.e-route-panel-menu) #elementor-panel-header-menu-button:after {
            background-color: <?php echo $elementor_editor_secondary_color; ?> !important;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['site-settings'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-global-settings {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['theme-builder'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-site-editor {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['user-preferences'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-editor-preferences {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['add-ons'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-apps {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['notes'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-notes {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['finder'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-finder {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['whats-new'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-notification-center {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['view-page'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-view-page {
            display: none;
        }
        <?php endif; ?>

        <?php if (is_array($elementor_editor_navigation) && isset($elementor_editor_navigation['exit'])) : ?>
        .elementor-panel-menu-items .elementor-panel-menu-item-exit {
            display: none;
        }
        <?php endif; ?>

        <?php if ($elementor_hide_upgrade_nags === 'on') : ?>
        #elementor-editor-wrapper #elementor-panel-get-pro-elements,
        #elementor-editor-wrapper .go-pro,
        #elementor-editor-wrapper #e-notice-bar,
        #elementor-panel-get-pro-elements-sticky { 
            display: none !important;
        }
        <?php endif; ?>

        <?php if ($elementor_hide_pro_widgets === 'on') : ?>
        #elementor-panel-category-pro-elements,
        #elementor-panel-category-theme-elements,
        #elementor-panel-category-theme-elements-single,
        #elementor-panel-category-woocommerce-elements {
            display: none !important;
        }
        <?php endif; ?>
    </style>
    <?php
}
add_action('elementor/editor/wp_head', 'white_label_elementor_editor_css');

/**
 * Apply Elementor-related CSS rules to the WordPress admin.
 *
 * @return void
 */
function white_label_elementor_admin_css()
{
    // Exit early if White Label is not enabled.
    $enable_white_label = white_label_get_option('enable_white_label', 'white_label_general', false);
    if ($enable_white_label !== 'on') {
        return;
    }

    // Exit early if White Label admin.
    if (white_label_is_wl_admin()) {
        return;
    }

    $elementor_hide_logo = white_label_get_option('elementor_hide_logo', 'white_label_plugins', 'off');
    $elementor_logo_loading = white_label_get_option('elementor_logo_loading', 'white_label_plugins', false);
    $elementor_hide_upgrade_nags = white_label_get_option('elementor_hide_upgrade_nags', 'white_label_plugins', 'off');
    $elementor_hide_pro_widgets = white_label_get_option('elementor_hide_pro_widgets', 'white_label_plugins', 'off');
    ?>
    <style type="text/css">
        <?php if ($elementor_hide_logo === 'on') : ?>
        .elementor-loader, #elementor-editor-button i.eicon-elementor-square {
            display: none !important;
        }
        <?php endif; ?>

        <?php if ($elementor_logo_loading) : ?>
        .elementor-loader {
            display: block !important;
        }

        .elementor-loader .elementor-loader-boxes {
            display: none !important;
        }

        .elementor-loader {
            background-color: transparent !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-image: url(<?php echo $elementor_logo_loading; ?>);
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }
        <?php endif; ?>

        <?php if ($elementor_hide_upgrade_nags === 'on') : ?>
        td.plugin-title span.go_pro,
        a.e-admin-top-bar__bar-button[href*="elementor.com"],
        a.eps-button--cta[href*="elementor.com"] { 
            display: none !important;
        }
        <?php endif; ?>
    </style>
    <?php 
}
add_action('admin_head', 'white_label_elementor_admin_css');

/**
 * Apply Elementor-related CSS rules to the WordPress admin bar.
 *
 * @return void
 */
function white_label_elementor_admin_bar_css()
{   
    // Exit early if White Label is not enabled.
    $enable_white_label = white_label_get_option('enable_white_label', 'white_label_general', false);
    if ($enable_white_label !== 'on') {
        return;
    }

    // Exit early if White Label  admin.
    if (white_label_is_wl_admin()) {
        return;
    }

    $elementor_hide_logo = white_label_get_option('elementor_hide_logo', 'white_label_plugins', 'off');
    ?>
    <style type="text/css">
        <?php if ($elementor_hide_logo === 'on') : ?>
        #wp-admin-bar-elementor_edit_page>.ab-item:before {
            content: '' !important;
        }
        <?php endif; ?>
    </style>
    <?php 
}
add_action('admin_bar_menu', 'white_label_elementor_admin_bar_css');

/**
 * Replace "Edit with Elementor" text.
 *
 * @param string $default text.
 * @return string
 */
function white_label_elementor_replace_edit_with_elementor_text($text)
{
    if ($text !== 'Edit with Elementor') {
        return $text;
    }

    // Exit early if White Label is not enabled.
    $enable_white_label = white_label_get_option('enable_white_label', 'white_label_general', false);
    if ($enable_white_label !== 'on') {
        return $text;
    }

    // Exit early if White Label admin.
    if (white_label_is_wl_admin()) {
        return $text;
    }

    $elementor_replace_edit_with_elementor_text = white_label_get_option('elementor_replace_edit_with_elementor_text', 'white_label_plugins', false);

    if (!empty($elementor_replace_edit_with_elementor_text)) {
        $text = str_replace('Edit with Elementor', $elementor_replace_edit_with_elementor_text, $text);
    }

    return $text;
}
add_filter('gettext', 'white_label_elementor_replace_edit_with_elementor_text', 2, 999);

/**
 * Hide or replace Elementor post state text.
 *
 * @param string $default text.
 * @return string
 */
function white_label_elementor_post_state_text($post_states, $post)
{
    // Exit early if White Label is not enabled.
    $enable_white_label = white_label_get_option('enable_white_label', 'white_label_general', false);
    if ($enable_white_label !== 'on') {
        return $post_states;
    }

    // Exit early if WL admin.
    if (white_label_is_wl_admin()) {
        return $post_states;
    }

    $elementor_hide_post_state_text = white_label_get_option('elementor_hide_post_state_text', 'white_label_plugins', 'off');
    $elementor_replace_post_state_text = white_label_get_option('elementor_replace_post_state_text', 'white_label_plugins', false);
	
    if (isset($post_states['elementor']) && $elementor_hide_post_state_text == 'on') {
		unset($post_states['elementor']);
	}

    if (isset($post_states['elementor']) && !empty($elementor_replace_post_state_text)) {
		$post_states['elementor'] = $elementor_replace_post_state_text;
	}

    return $post_states;
}
add_filter('display_post_states', 'white_label_elementor_post_state_text', 9999999999, 2);