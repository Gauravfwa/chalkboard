<?php

// TODO move script for copying debug info into a proper .js enqueued file, or switch tabs to JavaScript switching and always save all settings at the same time
namespace WCPM\Classes\Admin;

use  WCPM\Classes\Helpers ;
use  WCPM\Classes\Options ;
use  WCPM\Classes\Pixels\Google\Google ;
use  WCPM\Classes\Pixels\Pixel_Manager ;
use  WP_Post ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Admin
{
    public  $ip ;
    protected  $text_domain ;
    protected  $options ;
    protected  $plugin_hook ;
    private  $google ;
    private static  $instance ;
    public static function get_instance()
    {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->options = Options::get_options();
        $this->plugin_hook = 'woocommerce_page_wpm';
        $this->google = new Google( $this->options );
        add_action( 'admin_enqueue_scripts', [ $this, 'wpm_admin_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'pmw_edit_order_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'wpm_admin_css' ] );
        // add the admin options page
        add_action( 'admin_menu', [ $this, 'wpm_plugin_admin_add_page' ], 99 );
        // install a settings page in the admin console
        add_action( 'admin_init', [ $this, 'wpm_plugin_admin_init' ] );
        // add admin scripts to plugins.php page
        add_action( 'load-plugins.php', [ $this, 'freemius_load_deactivation_button_js' ] );
        // Load textdomain
        add_action( 'init', [ $this, 'load_plugin_textdomain' ] );
        wpm_fs()->add_filter( 'templates/checkout.php', [ $this, 'fs_inject_additional_scripts' ] );
        wpm_fs()->add_filter( 'checkout/purchaseCompleted', [ $this, 'fs_after_purchase_js' ] );
        // end __construct
        if ( wpm_fs()->can_use_premium_code__premium_only() && $this->google->is_ga4_data_api_active() ) {
            // https://stackoverflow.com/a/45617265/4688612
            // https://stackoverflow.com/a/37780501/4688612
            add_action( 'add_meta_boxes', function () {
                $screen = ( Helpers::is_wc_hpos_enabled() && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order' );
                add_meta_box(
                    'ga4-attribution-modal',
                    esc_html__( 'GA4 Attribution', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                    [ $this, 'ga4_attribution_modal__premium_only' ],
                    $screen,
                    'side',
                    'core'
                );
            } );
        }
    }
    
    protected function if_is_wpm_admin_page()
    {
        $_get = Helpers::get_input_vars( INPUT_GET );
        
        if ( !empty($_get['page']) && 'wpm' === $_get['page'] ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    // This function is only called when our plugin's page loads!
    public function freemius_load_deactivation_button_js()
    {
        add_action( 'admin_enqueue_scripts', [ $this, 'freemius_enqueue_deactivation_button_js' ] );
    }
    
    public function freemius_enqueue_deactivation_button_js()
    {
        wp_enqueue_script(
            'freemius-enqueue-deactivation-button',
            WPM_PLUGIN_DIR_PATH . 'js/admin/wpm-admin-freemius.p1.min.js',
            [ 'jquery' ],
            WPM_CURRENT_VERSION,
            true
        );
    }
    
    public function fs_after_purchase_js( $js_function )
    {
        return "\n\t\tfunction ( response ) {\n\n            let\n                isTrial = (null != response.purchase.trial_ends),\n                isSubscription = (null != response.purchase.initial_amount),\n                total = isTrial ? 0 : (isSubscription ? response.purchase.initial_amount : response.purchase.gross).toString(),\n                productName = 'Pixel Manager for WooCommerce',\n                // storeUrl = 'https://sweetcode.com',\n                storeName = 'SweetCode';\n            \n            window.dataLayer = window.dataLayer || [];\n\n            function gtag() {\n                dataLayer.push(arguments);\n            }\n    \n            gtag('js', new Date());            \n    \n            gtag('config', 'UA-39746956-10', {'anonymize_ip': true});\n            gtag('config', 'G-2QE000DX8D');\n            gtag('config', 'AW-406204436');\n            \n            gtag('event', 'purchase', {\n                'send_to':['UA-39746956-10', 'G-2QE000DX8D'],\n                'transaction_id':response.purchase.id.toString(),\n                'currency': response.purchase.currency.toUpperCase(),\n                'discount':0,\n                'items':[{\n                    'id':response.purchase.plan_id.toString(),\n                    'quantity':1,\n                    'price':total,\n                    'name':productName,\n                    'category': 'Plugin',\n                }],\n                'affiliation': storeName,\n                'value':response.purchase.initial_amount.toString()\n            });\n            \n            gtag('event', 'conversion', {\n              'send_to': 'AW-406204436/XrUYCK3J8YoCEJTg2MEB',\n              'value': response.purchase.initial_amount.toString(),\n              'currency': response.purchase.currency.toUpperCase(),\n              'transaction_id': response.purchase.id.toString()\n            });\n            \n            var _dcq = _dcq || [];\n\t\t\tvar _dcs = _dcs || {};\n\t\t\t_dcs.account = '5594556';\n\t\n\t\t\t(function() {\n\t\t\t\tvar dc = document.createElement('script');\n\t\t\t\tdc.type = 'text/javascript'; dc.async = true;\n\t\t\t\tdc.src = '//tag.getdrip.com/5594556.js';\n\t\t\t\tvar s = document.getElementsByTagName('script')[0];\n\t\t\t\ts.parentNode.insertBefore(dc, s);\n\t\t\t})();\n\t\t\t\n\t\t\twindow._dcq.push([\n\t\t\t\t'track',\n\t\t\t\t'Placed an order',\n\t\t\t]);\n\t\t\t\n\t\t\twindow._dcq.push([\n\t\t\t\t'track',\n\t\t\t\t'purchase',\n\t\t\t\t{\n\t\t\t\t\tvalue: total * 100,\n\t\t\t\t\tcurrency_code: response.purchase.currency.toUpperCase(),\n\t\t\t\t}\n\t\t\t]);\n  \n        }";
    }
    
    // phpcs:disable
    public function fs_inject_additional_scripts( $html )
    {
        return '<script async src="https://www.googletagmanager.com/gtag/js?id=UA-39746956-10"></script>' . $html;
    }
    
    // phpcs:enable
    public function wpm_admin_css( $hook_suffix )
    {
        // Only output the css on PMW pages and the order page
        if ( !(strpos( $hook_suffix, 'page_wpm' ) || Helpers::is_orders_page() || Helpers::is_edit_order_page() || Helpers::is_dashboard()) ) {
            return;
        }
        wp_enqueue_style(
            'wpm-admin',
            WPM_PLUGIN_DIR_PATH . 'css/admin.css',
            [],
            WPM_CURRENT_VERSION
        );
    }
    
    public function wpm_admin_scripts( $hook_suffix )
    {
        // Only output the remaining scripts on PMW settings page
        if ( !strpos( $hook_suffix, 'page_wpm' ) ) {
            return;
        }
        wp_enqueue_script(
            'wpm-admin',
            WPM_PLUGIN_DIR_PATH . 'js/admin/wpm-admin.p1.min.js',
            [ 'jquery' ],
            WPM_CURRENT_VERSION,
            false
        );
        wp_localize_script( 'wpm-admin', 'pmwAdminApi', [
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
        //        wp_enqueue_script('wpm-script-blocker-warning', WPM_PLUGIN_DIR_PATH . 'js/admin/script-blocker-warning.js', ['jquery'], WPM_CURRENT_VERSION, false);
        //        wp_enqueue_script('wpm-admin-helpers', WPM_PLUGIN_DIR_PATH . 'js/admin/helpers.js', ['jquery'], WPM_CURRENT_VERSION, false);
        //        wp_enqueue_script('wpm-admin-tabs', WPM_PLUGIN_DIR_PATH . 'js/admin/tabs.js', ['jquery'], WPM_CURRENT_VERSION, false);
        wp_enqueue_script(
            'wpm-selectWoo',
            WPM_PLUGIN_DIR_PATH . 'js/admin/selectWoo.full.min.js',
            [ 'jquery' ],
            WPM_CURRENT_VERSION,
            false
        );
        wp_enqueue_style(
            'wpm-selectWoo',
            WPM_PLUGIN_DIR_PATH . 'css/selectWoo.min.css',
            [],
            WPM_CURRENT_VERSION
        );
    }
    
    public function pmw_edit_order_scripts( $hook_suffix )
    {
        //		error_log('hook_suffix: ' . $hook_suffix);
        //		error_log('get_current_screen: ' . get_current_screen()->id);
        // Only output the remaining scripts on PMW settings page
        if ( 'shop_order' !== get_current_screen()->id ) {
            return;
        }
        //		error_log('pmw_edit_order_scripts');
        //
        wp_enqueue_script(
            'pmw-edit-order',
            WPM_PLUGIN_DIR_PATH . 'js/admin/edit-order-page.js',
            [ 'jquery' ],
            WPM_CURRENT_VERSION,
            false
        );
        wp_localize_script( 'pmw-edit-order', 'pmwAdminApi', [
            'root'  => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
    }
    
    // Load text domain function
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain( 'woocommerce-google-adwords-conversion-tracking-tag', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
    // add the admin options page
    public function wpm_plugin_admin_add_page()
    {
        //add_options_page('WPM Plugin Page', 'WPM Plugin Menu', 'manage_options', 'wpm', array($this, 'wpm_plugin_options_page'));
        add_submenu_page(
            $this->get_submenu_parent_slug(),
            esc_html__( 'Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            esc_html__( 'Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'manage_options',
            'wpm',
            [ $this, 'wpm_plugin_options_page' ]
        );
    }
    
    protected function get_submenu_parent_slug()
    {
        
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return 'woocommerce';
        } else {
            return 'options-general.php';
        }
    
    }
    
    // add the admin settings and such
    public function wpm_plugin_admin_init()
    {
        register_setting( 'wpm_plugin_options_group', 'wgact_plugin_options', [ $this, 'wpm_options_validate' ] );
        // don't load the UX if we are not on the plugin UX page
        if ( !$this->if_is_wpm_admin_page() ) {
            return;
        }
        $this->add_section_main();
        $this->add_section_advanced();
        $this->add_section_dynamic_remarketing();
        if ( defined( 'EXPERIMENTAL_PMW_OPPORTUNITIES_TAB' ) && EXPERIMENTAL_PMW_OPPORTUNITIES_TAB ) {
            $this->add_section_opportunities();
        }
        //		$this->add_section_opportunities();
        $this->add_section_diagnostics();
        $this->add_section_support();
        $this->add_section_author();
    }
    
    public function add_section_main()
    {
        $section_ids = [
            'title'         => esc_html__( 'Main', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'          => 'main',
            'settings_name' => 'wpm_plugin_main_section',
        ];
        $this->output_section_data_field( $section_ids );
        add_settings_section(
            $section_ids['settings_name'],
            esc_html__( $section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_main_description' ],
            'wpm_plugin_options_page'
        );
        $this->add_section_main_subsection_google_ads( $section_ids );
        $this->add_section_main_subsection_facebook( $section_ids );
        $this->add_section_main_subsection_more_pixels( $section_ids );
    }
    
    public function add_section_main_subsection_google_ads( $section_ids )
    {
        $sub_section_ids = [
            'title' => esc_html__( 'Google', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'  => 'google',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add the field for the conversion id
        add_settings_field(
            'wpm_plugin_conversion_id',
            esc_html__( 'Google Ads Conversion ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_google_ads_conversion_id' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add the field for the conversion label
        add_settings_field(
            'wpm_plugin_conversion_label',
            esc_html__( 'Google Ads Purchase Conversion Label', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_google_ads_conversion_label' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        add_settings_field(
            'wpm_plugin_analytics_ua_property_id',
            esc_html__( 'Google Analytics UA', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_google_analytics_universal_property' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        add_settings_field(
            'wpm_plugin_analytics_4_measurement_id',
            esc_html__( 'Google Analytics 4', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_google_analytics_4_id' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        add_settings_field(
            'wpm_plugin_google_optimize_container_id',
            esc_html__( 'Google Optimize', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_google_optimize_container_id' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }
    
    public function add_section_main_subsection_facebook( $section_ids )
    {
        $sub_section_ids = [
            'title' => esc_html__( 'Meta (Facebook)', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'  => 'facebook',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add the field for the conversion label
        add_settings_field(
            'wpm_plugin_facebook_pixel_id',
            esc_html__( 'Meta (Facebook) pixel ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_facebook_pixel_id' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }
    
    public function add_section_main_subsection_more_pixels( $section_ids )
    {
        $sub_section_ids = [
            'title' => esc_html__( 'more pixels', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'  => 'more-pixels',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->options['general']['pro_version_demo'] ) {
            // add the field for the Bing Ads UET tag ID
            add_settings_field(
                'wpm_plugin_bing_uet_tag_id',
                esc_html__( 'Microsoft Advertising UET tag ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_option_html_bing_uet_tag_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add the field for the Twitter pixel
            add_settings_field(
                'wpm_plugin_twitter_pixel_id',
                esc_html__( 'Twitter pixel ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_option_html_twitter_pixel_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add the field for the Pinterest pixel
            add_settings_field(
                'pmw_plugin_pinterest_pixel_id',
                esc_html__( 'Pinterest pixel ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'pmw_option_html_pinterest_pixel_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add the field for the Pinterest enhanced match
            add_settings_field(
                'wpm_plugin_pinterest_enhanced_match',
                esc_html__( 'Pinterest Enhanced Match', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'pmw_option_html_pinterest_enhanced_match' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add the field for the Snapchat pixel
            add_settings_field(
                'wpm_plugin_snapchat_pixel_id',
                esc_html__( 'Snapchat pixel ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_option_html_snapchat_pixel_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add the field for the TikTok pixel
            add_settings_field(
                'wpm_plugin_tiktok_pixel_id',
                esc_html__( 'TikTok pixel ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_option_html_tiktok_pixel_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        
        // add the field for the Hotjar pixel
        add_settings_field(
            'wpm_plugin_hotjar_site_id',
            esc_html__( 'Hotjar site ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_hotjar_site_id' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }
    
    public function add_section_advanced()
    {
        $section_ids = [
            'title'         => esc_html__( 'Advanced', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'          => 'advanced',
            'settings_name' => 'wpm_plugin_advanced_section',
        ];
        add_settings_section(
            $section_ids['settings_name'],
            esc_html__( $section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_advanced_description' ],
            'wpm_plugin_options_page'
        );
        $this->output_section_data_field( $section_ids );
        $this->add_section_advanced_subsection_shop( $section_ids );
        $this->add_section_advanced_subsection_google( $section_ids );
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->pro_version_demo_active() ) {
            $this->add_section_advanced_subsection_facebook( $section_ids );
            $this->add_section_advanced_subsection_tiktok( $section_ids );
            $this->add_section_advanced_subsection_twitter( $section_ids );
            $this->add_section_advanced_subsection_cookie_consent_mgmt( $section_ids );
        }
    
    }
    
    public function add_section_advanced_subsection_shop( $section_ids )
    {
        $sub_section_ids = [
            'title' => 'Shop',
            'slug'  => 'shop',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add fields for the order total logic
        add_settings_field(
            'wpm_plugin_order_total_logic',
            esc_html__( 'Order Total Logic', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->get_documentation_html_e( Documentation::get_link( 'order_total_logic' ) ),
            [ $this, 'pmw_option_html_shop_order_total_logic' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add checkbox for order duplication prevention
        add_settings_field(
            'wpm_setting_order_duplication_prevention',
            esc_html__( 'Order Duplication Prevention', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_order_duplication_prevention' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add checkbox for maximum compatibility mode
        add_settings_field(
            'wpm_setting_maximum_compatibility_mode',
            esc_html__( 'Maximum Compatibility Mode', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_maximum_compatibility_mode' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->options['general']['pro_version_demo'] ) {
            // add checkbox for disabling tracking for user roles
            add_settings_field(
                'wpm_setting_disable_tracking_for_user_roles',
                esc_html__( 'Disable Tracking for User Roles', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_disable_tracking_for_user_roles' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add checkbox for disabling tracking for user roles
            add_settings_field(
                'wpm_setting_acr',
                esc_html__( 'ACR', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'wpm_info_html_acr' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        
        // add checkbox for disabling tracking for user roles
        add_settings_field(
            'pmw_setting_order_list_info',
            esc_html__( 'Order List Info', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_info_html_order_list_info' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->options['general']['pro_version_demo'] ) {
            // Add checkbox for the scroll tracker
            add_settings_field(
                'pmw_setting_scroll_tracker_thresholds',
                esc_html__( 'Scroll Tracker', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'pmw_info_html_scroll_tracker_thresholds' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add a subscription value multiplier
            add_settings_field(
                'pmw_setting_subscription_value_multiplier',
                esc_html__( 'Subscription Value Multiplier', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'html_subscription_value_multiplier' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add option for lazy loading PMW
            add_settings_field(
                'pmw_setting_lazy_load_pmw',
                esc_html__( 'Lazy Load PMW', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'html_lazy_load_pmw' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
    
    }
    
    public function add_section_advanced_subsection_google( $section_ids )
    {
        $sub_section_ids = [
            'title' => 'Google',
            'slug'  => 'google',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add the field for the aw_merchant_id
        add_settings_field(
            'wpm_plugin_aw_merchant_id',
            esc_html__( 'Conversion Cart Data', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_setting_aw_merchant_id' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->pro_version_demo_active() ) {
            // add fields for the Google enhanced e-commerce
            add_settings_field(
                'wpm_setting_google_analytics_eec',
                esc_html__( 'Enhanced E-Commerce', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_info_html_google_analytics_eec' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add fields for the Google GA4 API secret
            add_settings_field(
                'wpm_setting_google_analytics_4_api_secret',
                esc_html__( 'GA4 API secret', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_google_analytics_4_api_secret' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add fields for the GA4 Data API property ID
            add_settings_field(
                'pmw_setting_ga4_property_id',
                esc_html__( 'GA4 Property ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'pmw_setting_html_ga4_property_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add fields for the GA4 Data API credentials upload
            add_settings_field(
                'pmw_setting_ga4_data_api_credentials',
                esc_html__( 'GA4 Data API Credentials', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'pmw_setting_html_g4_data_api_credentials' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        
        // add fields for the Google Analytics link attribution
        add_settings_field(
            'wpm_setting_google_analytics_link_attribution',
            esc_html__( 'Enhanced Link Attribution', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_google_analytics_link_attribution' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->pro_version_demo_active() ) {
            // add user_id for the Google
            add_settings_field(
                'wpm_setting_google_user_id',
                esc_html__( 'Google User ID', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_google_user_id' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add Google Ads Enhanced Conversions
            add_settings_field(
                'wpm_setting_google_ads_enhanced_conversions',
                esc_html__( 'Google Ads Enhanced Conversions', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_google_ads_enhanced_conversions' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->pro_version_demo_active() ) {
            // add fields for the Google Ads phone conversion number
            add_settings_field(
                'wpm_plugin_google_ads_phone_conversion_number',
                esc_html__( 'Google Ads Phone Conversion Number', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_google_ads_phone_conversion_number' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add fields for the Google Ads phone conversion label
            add_settings_field(
                'wpm_plugin_google_ads_phone_conversion_label',
                esc_html__( 'Google Ads Phone Conversion Label', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_google_ads_phone_conversion_label' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add fields for the Google Ads Conversion Adjustments Conversion Name
            add_settings_field(
                'pmw_plugin_google_ads_conversion_adjustments_conversion_name',
                esc_html__( 'Google Ads Conversion Adjustments: Conversion Name', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'pmw_setting_html_google_ads_conversion_adjustments_conversion_name' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // add fields for the Google Ads Conversion Adjustments Feed
            add_settings_field(
                'pmw_plugin_google_ads_conversion_adjustments_feed',
                esc_html__( 'Google Ads Conversion Adjustments: Feed', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'pmw_setting_html_google_ads_conversion_adjustments_feed' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
            // Add html for the Google Optimize anti-flicker snippet
            add_settings_field(
                'pmw_plugin_google_optimize_anti_flicker_snippet',
                esc_html__( 'Google Optimize Anti-Flicker Snippet', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
                [ $this, 'pmw_setting_html_google_optimize_anti_flicker_snippet' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
    
    }
    
    public function add_section_advanced_subsection_cookie_consent_mgmt( $section_ids )
    {
        $sub_section_ids = [
            'title' => esc_html__( 'Cookie Consent Management', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'  => 'cookie-consent-mgmt',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add fields for the Google Consent beta
        add_settings_field(
            'wpm_setting_google_consent_mode_active',
            esc_html__( 'Google Consent Mode', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_google_consent_mode_active' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add fields for the Google consent regions
        add_settings_field(
            'wpm_setting_google_consent_regions',
            esc_html__( 'Google Consent Regions', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_google_consent_regions' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add fields for explicit cookie consent mode
        add_settings_field(
            'wpm_setting_explicit_consent_mode',
            esc_html__( 'Explicit Consent Mode', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_explicit_consent_mode' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        if ( Environment::get_instance()->is_borlabs_cookie_active() ) {
            // add fields for the Borlabs Cookie support
            add_settings_field(
                'wpm_setting_borlabs_support',
                esc_html__( 'Borlabs Cookie Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_borlabs_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        if ( Environment::get_instance()->is_cookiebot_active() ) {
            // add fields for the Cookiebot support
            add_settings_field(
                'wpm_setting_cookiebot_support',
                esc_html__( 'Cookiebot Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_cookiebot_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        if ( Environment::get_instance()->is_complianz_active() ) {
            // add fields for the Complianz GDPR support
            add_settings_field(
                'wpm_setting_complianz_support',
                esc_html__( 'Complianz GDPR Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_complianz_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        if ( Environment::get_instance()->is_cookie_notice_active() ) {
            // add fields for the Cookie Notice by hu-manity.co support
            add_settings_field(
                'wpm_setting_cookie_notice_support',
                esc_html__( 'Cookie Notice Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_cookie_notice_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        if ( Environment::get_instance()->is_cookie_script_active() ) {
            // add fields for the Cookie Script support
            add_settings_field(
                'wpm_setting_cookie_script_support',
                esc_html__( 'Cookie Script Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_cookie_script_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        if ( Environment::get_instance()->is_moove_gdpr_active() ) {
            // add fields for the GDPR Cookie Compliance support
            add_settings_field(
                'wpm_setting_moove_gdpr_support',
                esc_html__( 'GDPR Cookie Compliance Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_moove_gdpr_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
        if ( Environment::get_instance()->is_cookie_law_info_active() ) {
            // add fields for the GDPR Cookie Consent support
            add_settings_field(
                'wpm_setting_cookie_law_info_support',
                esc_html__( 'GDPR Cookie Consent Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_cookie_law_info_support' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
    }
    
    public function add_section_advanced_subsection_facebook( $section_ids )
    {
        $sub_section_ids = [
            'title' => 'Meta (Facebook)',
            'slug'  => 'facebook',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add field for the Facebook CAPI token
        add_settings_field(
            'wpm_setting_facebook_capi_token',
            esc_html__( 'Meta (Facebook) CAPI: token', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_html_facebook_capi_token' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Facebook CAPI test event code
        add_settings_field(
            'pmw_setting_facebook_capi_test_event_code',
            esc_html__( 'Meta (Facebook) CAPI: test event code', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'pmw_setting_html_facebook_capi_test_event_code' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add field for the Facebook CAPI user transparency process anonymous hits
        add_settings_field(
            'wpm_setting_facebook_capi_user_transparency_process_anonymous_hits',
            esc_html__( 'Meta (Facebook) CAPI: process anonymous hits', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_setting_facebook_capi_user_transparency_process_anonymous_hits' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add field for the Facebook CAPI user transparency send additional client identifiers
        add_settings_field(
            'wpm_setting_facebook_capi_user_transparency_send_additional_client_identifiers',
            esc_html__( 'Meta (Facebook): Advanced Matching', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'pmw_setting_facebook_advanced_matching' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // This feature is deprecated since 1.25.1
        // We keep it active for users who are currently using it.
        if ( $this->options['facebook']['microdata'] ) {
            // add fields for Facebook microdata
            add_settings_field(
                'wpm_setting_facebook_microdata_active',
                esc_html__( 'Meta (Facebook) Microdata Tags for Catalogues', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                [ $this, 'wpm_setting_html_facebook_microdata' ],
                'wpm_plugin_options_page',
                $section_ids['settings_name']
            );
        }
    }
    
    public function add_section_advanced_subsection_tiktok( $section_ids )
    {
        $sub_section_ids = [
            'title' => 'TikTok',
            'slug'  => 'tiktok',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the TikTok Events API token
        add_settings_field(
            'pmw_setting_tiktok_eapi_token',
            esc_html__( 'TikTok Events API: token', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_html_tiktok_eapi_token' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the TikTok EAPI test event code
        add_settings_field(
            'pmw_setting_tiktok_eapi_test_event_code',
            esc_html__( 'TikTok EAPI: test event code', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'pmw_setting_html_tiktok_eapi_test_event_code' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add field for the TikTok EAPI user transparency process anonymous hits
        add_settings_field(
            'pmw_setting_tiktok_eapi_user_transparency_process_anonymous_hits',
            esc_html__( 'TikTok Events API: process anonymous hits', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_tiktok_eapi_process_anonymous_hits' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // add field for the TikTok Advanced Matching
        add_settings_field(
            'pmw_setting_tiktok_eapi_user_transparency_advanced_matching',
            esc_html__( 'TikTok: Advanced Matching', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_tiktok_advanced_matching' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }
    
    public function add_section_advanced_subsection_twitter( $section_ids )
    {
        $sub_section_ids = [
            'title' => 'Twitter',
            'slug'  => 'twitter',
        ];
        add_settings_field(
            'wpm_plugin_subsection_' . $sub_section_ids['slug'] . '_opening_div',
            esc_html__( $sub_section_ids['title'], 'woocommerce-google-adwords-conversion-tracking-tag' ),
            function () use( $section_ids, $sub_section_ids ) {
            $this->wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Twitter event add_to_cart
        add_settings_field(
            'pmw_setting_twitter_add_to_cart',
            esc_html__( 'Add To Cart Event ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_twitter_add_to_cart' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Twitter event add_to_wishlist
        add_settings_field(
            'pmw_setting_twitter_add_to_wishlist',
            esc_html__( 'Add To Wishlist Event ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_twitter_add_to_wishlist' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Twitter event view_content
        add_settings_field(
            'pmw_setting_twitter_view_content',
            esc_html__( 'Content View Event ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_twitter_view_content' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Twitter event search
        add_settings_field(
            'pmw_setting_twitter_search',
            esc_html__( 'Search Event ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_twitter_search' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Twitter event initiate_checkout
        add_settings_field(
            'pmw_setting_twitter_initiate_checkout',
            esc_html__( 'Checkout Initiated Event ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_twitter_initiate_checkout' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
        // Add field for the Twitter event purchase
        add_settings_field(
            'pmw_setting_twitter_purchase',
            esc_html__( 'Purchase Event ID', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            [ $this, 'pmw_setting_twitter_purchase' ],
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }
    
    public function add_section_dynamic_remarketing()
    {
        $section_ids = [
            'title'         => esc_html__( 'Dynamic Remarketing', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'          => 'dynamic-remarketing',
            'settings_name' => 'wpm_plugin_beta_section',
        ];
        $this->output_section_data_field( $section_ids );
        // add new section for cart data
        add_settings_section(
            'wpm_plugin_beta_section',
            esc_html__( 'Dynamic Remarketing', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_add_cart_data_description' ],
            'wpm_plugin_options_page'
        );
        // add checkbox for dynamic remarketing
        add_settings_field(
            'wpm_plugin_option_gads_dynamic_remarketing',
            esc_html__( 'Dynamic Remarketing', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_google_ads_dynamic_remarketing' ],
            'wpm_plugin_options_page',
            'wpm_plugin_beta_section'
        );
        // add fields for the product identifier
        add_settings_field(
            'wpm_plugin_option_product_identifier',
            esc_html__( 'Product Identifier', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_option_product_identifier' ],
            'wpm_plugin_options_page',
            'wpm_plugin_beta_section'
        );
        // add checkbox for variations output
        add_settings_field(
            'wpm_plugin_option_variations_output',
            esc_html__( 'Variations output', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_option_html_variations_output' ],
            'wpm_plugin_options_page',
            'wpm_plugin_beta_section'
        );
        if ( wpm_fs()->can_use_premium_code__premium_only() || $this->pro_version_demo_active() ) {
            // google_business_vertical
            add_settings_field(
                'wpm_plugin_google_business_vertical',
                esc_html__( 'Google Business Vertical', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_pro_feature(),
                [ $this, 'wpm_plugin_option_google_business_vertical' ],
                'wpm_plugin_options_page',
                'wpm_plugin_beta_section'
            );
        }
    }
    
    public function add_section_diagnostics()
    {
        $section_ids = [
            'title'         => esc_html__( 'Diagnostics', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'          => 'diagnostics',
            'settings_name' => 'wpm_plugin_diagnostics_section',
        ];
        $this->output_section_data_field( $section_ids );
        add_settings_section(
            'wpm_plugin_diagnostics_section',
            esc_html__( 'Diagnostics', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_diagnostics_html' ],
            'wpm_plugin_options_page'
        );
    }
    
    public function add_section_opportunities()
    {
        $section_ids = [
            'title'         => esc_html__( 'Opportunities', 'woocommerce-google-adwords-conversion-tracking-tag' ) . $this->html_beta(),
            'slug'          => 'opportunities',
            'settings_name' => 'wpm_plugin_opportunities_section',
        ];
        $this->output_section_data_field( $section_ids );
        add_settings_section(
            'wpm_plugin_opportunities_section',
            esc_html__( 'Opportunities', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_opportunities_html' ],
            'wpm_plugin_options_page'
        );
    }
    
    public function add_section_support()
    {
        $section_ids = [
            'title'         => esc_html__( 'Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'          => 'support',
            'settings_name' => 'wpm_plugin_support_section',
        ];
        $this->output_section_data_field( $section_ids );
        add_settings_section(
            'wpm_plugin_support_section',
            esc_html__( 'Support', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_support_description' ],
            'wpm_plugin_options_page'
        );
    }
    
    public function add_section_author()
    {
        $section_ids = [
            'title'         => esc_html__( 'Author', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'slug'          => 'author',
            'settings_name' => 'wpm_plugin_author_section',
        ];
        $this->output_section_data_field( $section_ids );
        add_settings_section(
            'wpm_plugin_author_section',
            esc_html__( 'Author', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            [ $this, 'wpm_plugin_section_author_description' ],
            'wpm_plugin_options_page'
        );
        // end add_section_author
    }
    
    protected function output_section_data_field( array $section_ids )
    {
        add_settings_field(
            'wgact_plugin_section_' . $section_ids['slug'] . '_opening_div',
            '',
            function () use( $section_ids ) {
            $this->wpm_section_generic_opening_div_html( $section_ids );
        },
            'wpm_plugin_options_page',
            $section_ids['settings_name']
        );
    }
    
    public function wpm_section_generic_opening_div_html( $section_ids )
    {
        echo  '<div class="section" data-section-title="' . esc_js( $section_ids['title'] ) . '" data-section-slug="' . esc_js( $section_ids['slug'] ) . '"></div>' ;
    }
    
    public function wpm_subsection_generic_opening_div_html( $section_ids, $sub_section_ids )
    {
        echo  '<div class="subsection" data-section-slug="' . esc_js( $section_ids['slug'] ) . '" data-subsection-title="' . esc_js( $sub_section_ids['title'] ) . '" data-subsection-slug="' . esc_js( $sub_section_ids['slug'] ) . '"></div>' ;
    }
    
    // display the admin options page
    public function wpm_plugin_options_page()
    {
        ?>

		<div id="script-blocker-notice"
			 style="
			 font-weight: bold;
			 width:90%;
			 float: left;
			 margin: 5px 15px 2px;
			 padding: 1px 12px;
			 background: #fff;
			 border: 1px solid #c3c4c7;
			 border-left-width: 4px;
			 border-left-color: #d63638;
			 box-shadow: 0 1px 1px rgb(0 0 0 / 4%);">
			<p>
				<?php 
        esc_html_e( 'It looks like you are using some sort of ad or script blocker in your browser which is blocking the script and CSS files of this plugin.
                    In order for the plugin to work properly you need to disable the script blocker.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
			</p>
			<p>
				<a href="<?php 
        echo  esc_url( Documentation::get_link( 'script_blockers' ) ) ;
        ?>"
				   target="_blank">
					<?php 
        esc_html_e( 'Learn more', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				</a>
			</p>

			<script>
				if (typeof wpm_hide_script_blocker_warning === "function") {
					wpm_hide_script_blocker_warning()
				}
			</script>

		</div>

		<div style="width:90%; margin: 5px">

			<?php 
        settings_errors();
        ?>

			<h2 class="nav-tab-wrapper"></h2>

			<form id="wpm_settings_form" action="options.php" method="post">

				<?php 
        settings_fields( 'wpm_plugin_options_group' );
        do_settings_sections( 'wpm_plugin_options_page' );
        submit_button();
        $this->inject_developer_banner();
        ?>

			</form>
		</div>
		<?php 
    }
    
    private function inject_developer_banner()
    {
        ?>

		<div class="pmw-developer-banner">
			<div style="display: flex; justify-content: space-between">
					<span>
						<?php 
        esc_html_e( 'Profit Driven Marketing by SweetCode', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>

				<?php 
        ?>

					<div class="pmwCaTooltip" style="float: right; padding-left: 20px">
								<span style="padding-right: 6px">
									<?php 
        esc_html_e( 'Show Pro version settings', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
								</span>
						<label class="pmw-switch" id="wpm_pro_version_demo">

							<input type='hidden' value='0'
								   name='wgact_plugin_options[general][pro_version_demo]'>
							<input type="checkbox" value='1'
								   name='wgact_plugin_options[general][pro_version_demo]'
								<?php 
        checked( $this->options['general']['pro_version_demo'] );
        ?>
							/>
							<span class="pmw-slider round"></span>
						</label>
						<span class="pmwCaTooltiptext" id="myPmwCaTooltip"
							  style="width: 400px; text-align: left"><?php 
        esc_html_e( "Enabling this will only show you the pro settings in the user interface. It won't actually enable the pro features. If you want to try out the pro features head over to sweetcode.com and sign up for a trial.", 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></span>
					</div>

				<?php 
        ?>

				<span style=" padding-left: 20px;">
							<?php 
        esc_html_e( 'Visit us here:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
							<a href="https://sweetcode.com/?utm_source=plugin&utm_medium=banner&utm_campaign=wpm"
							   target="_blank">https://sweetcode.com
							</a>
				</span>

			</div>
		</div>
		<?php 
    }
    
    private function get_link_locale()
    {
        
        if ( substr( get_user_locale(), 0, 2 ) === 'de' ) {
            return 'de';
        } else {
            return 'en';
        }
    
    }
    
    /*
     * descriptions
     */
    public function wpm_plugin_section_main_description()
    {
        // do nothing
    }
    
    public function wpm_plugin_section_advanced_description()
    {
        // do nothing
    }
    
    public function wpm_plugin_section_add_cart_data_description()
    {
        //        echo '<div id="beta-description" style="margin-top:20px">';
        //        esc_html_e('Find out more about this new feature: ', 'woocommerce-google-adwords-conversion-tracking-tag');
        //        echo '<a href="https://support.google.com/google-ads/answer/9028254" target="_blank">https://support.google.com/google-ads/answer/9028254</a><br>';
        //        echo '</div>';
    }
    
    public function wpm_plugin_section_diagnostics_html()
    {
        ?>
		<div style="margin-top:20px">
			<h2>
				<?php 
        esc_html_e( 'Payment Gateway Tracking Accuracy Report', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				<div class="pmw-status-icon beta"><?php 
        esc_html_e( 'beta', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></div>
			</h2>

			<div style="margin-bottom: 20px">
				<?php 
        esc_html_e( "What's this? Follow this link to learn more", 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				:
				<?php 
        $this->get_documentation_html_by_key( 'payment_gateway_tracking_accuracy' );
        ?>
			</div>

			<div>
				<div>

					<b><?php 
        esc_html_e( 'Available payment gateways', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
						:</b>
				</div>
				<div style="margin-left: 10px; font-family: Courier">
					<table>
						<thead style="align:left">
						<tr>
							<th style="text-align: left"><?php 
        esc_html_e( 'id', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></th>
							<th style="text-align: left"><?php 
        esc_html_e( 'method_title', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></th>
							<th style="text-align: left"><?php 
        esc_html_e( 'class', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></th>
						</tr>
						</thead>
						<tbody>
						<?php 
        foreach ( Debug_Info::get_instance()->get_payment_gateways() as $gateway ) {
            ?>
							<tr>
								<td><?php 
            esc_html_e( $gateway->id );
            ?></td>
								<td><?php 
            esc_html_e( $gateway->method_title );
            ?></td>
								<td><?php 
            esc_html_e( get_class( $gateway ) );
            ?></td>
							</tr>
						<?php 
        }
        ?>
						</tbody>
					</table>
				</div>

			</div>
			<div style="margin-top: 10px">

				<b><?php 
        esc_html_e( 'Purchase confirmation page reached per gateway (active and inactive)', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					:</b>

				<?php 
        
        if ( Debug_Info::get_instance()->get_gateway_analysis_array() === false ) {
            ?>
					<br>
					<?php 
            esc_html_e( 'The analysis is being generated. Please check back in 5 minutes.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            $per_gateway_analysis = [];
        } else {
            $per_gateway_analysis = Debug_Info::get_instance()->get_gateway_analysis_array();
        }
        
        ?>

				<div style="margin-left: 10px; font-family: Courier;">
					<table>

						<?php 
        $order_count_total = 0;
        $order_count_measured = 0;
        ?>

						<tbody>
						<?php 
        foreach ( $per_gateway_analysis as $gateway_analysis ) {
            ?>
							<?php 
            $order_count_total += $gateway_analysis['order_count_total'];
            $order_count_measured += $gateway_analysis['order_count_measured'];
            ?>
							<tr>
								<td><?php 
            esc_html_e( $gateway_analysis['gateway_id'] );
            ?></td>
								<td><?php 
            esc_html_e( $gateway_analysis['order_count_measured'] );
            ?></td>
								<td>of</td>
								<td><?php 
            esc_html_e( $gateway_analysis['order_count_total'] );
            ?></td>
								<td>=</td>
								<td><?php 
            esc_html_e( $gateway_analysis['percentage'] );
            ?>%</td>
								<td><?php 
            $this->get_gateway_accuracy_warning_status( $gateway_analysis['percentage'] );
            ?></td>
							</tr>
						<?php 
        }
        ?>

						</tbody>
					</table>
				</div>

			</div>

			<div style="margin-top: 10px">

				<b><?php 
        esc_html_e( 'Purchase confirmation page reached per gateway (only active), weighted by frequency', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					:</b>

				<?php 
        
        if ( Debug_Info::get_instance()->get_gateway_analysis_weighted_array() === false ) {
            ?>
					<br>
					<?php 
            esc_html_e( 'The analysis is being generated. Please check back in 5 minutes.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            $per_gateway_analysis = [];
        } else {
            $per_gateway_analysis = Debug_Info::get_instance()->get_gateway_analysis_weighted_array();
        }
        
        ?>

				<div style="margin-left: 10px; font-family: Courier;">
					<table>
						<?php 
        $order_count_total = 0;
        $order_count_measured = 0;
        ?>

						<tbody>
						<?php 
        foreach ( $per_gateway_analysis as $gateway_analysis ) {
            ?>
							<?php 
            $order_count_total += $gateway_analysis['order_count_total'];
            $order_count_measured += $gateway_analysis['order_count_measured'];
            ?>
							<tr>
								<td><?php 
            esc_html_e( $gateway_analysis['gateway_id'] );
            ?></td>
								<td><?php 
            esc_html_e( $gateway_analysis['order_count_measured'] );
            ?></td>
								<td>of</td>
								<td><?php 
            esc_html_e( $gateway_analysis['order_count_total'] );
            ?></td>
								<td>=</td>
								<td><?php 
            esc_html_e( $gateway_analysis['percentage'] );
            ?>%</td>
								<td><?php 
            $this->get_gateway_accuracy_warning_status( $gateway_analysis['percentage'] );
            ?></td>
							</tr>
						<?php 
        }
        ?>
						<tr>
							<td>Total</td>
							<td><?php 
        esc_html_e( $order_count_measured );
        ?></td>
							<td>of</td>
							<td><?php 
        esc_html_e( $order_count_total );
        ?></td>
							<td>=</td>
							<td>
								<?php 
        $percent = Helpers::get_percentage( $order_count_measured, $order_count_total );
        
        if ( $order_count_total > 0 ) {
            esc_html_e( $percent . '%' );
        } else {
            echo  '0%' ;
        }
        
        ?>
							</td>
							<td><?php 
        $this->get_gateway_accuracy_warning_status( $percent );
        ?></td>

						</tr>
						</tbody>
					</table>
				</div>
			</div>

		</div>

		<div style="margin-top: 10px">

			<b><?php 
        esc_html_e( 'Automatic Conversion Recovery (ACR)', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				:</b>
			<?php 
        $this->get_documentation_html_by_key( 'acr' );
        ?>
			<?php 
        $this->html_beta_e( '-2px' );
        ?>
			<?php 
        ?>

				<div style="margin-top: 10px">

					<div style="margin-left: 10px">
						<p>
							<?php 
        esc_html_e( 'This feature is only available in the pro version of the plugin. Follow the link to learn more about it:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
							<?php 
        $this->get_documentation_html_by_key( 'acr' );
        ?></br>
							<?php 
        esc_html_e( 'Get the pro version of the Pixel Manager for WooCommerce over here', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
							: <a href="//sweetcode.com/pricing"
								 target="_blank"><?php 
        esc_html_e( 'Go Pro', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></a>
						</p>
					</div>
				</div>
				<?php 
        ?>
		</div>
		<?php 
    }
    
    public function wpm_plugin_section_opportunities_html()
    {
        Opportunities::html();
    }
    
    private function get_gateway_accuracy_warning_status( $percent )
    {
        
        if ( 0 === intval( $percent ) ) {
            echo  '' ;
        } elseif ( $percent > 0 && $percent < 90 ) {
            echo  '<span style="color:red">warning</span>' ;
        } elseif ( $percent >= 90 && $percent < 95 ) {
            echo  '<span style="color:orange">monitor</span>' ;
        } elseif ( 0 !== $percent ) {
            echo  '<span style="color:green">good</span>' ;
        } else {
            echo  '' ;
        }
    
    }
    
    public function wpm_plugin_section_support_description()
    {
        ?>
		<!-- Contacting Support -->
		<div style="margin-top:20px">
			<h2><?php 
        esc_html_e( 'Contacting Support', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>

		</div>
		<?php 
        $this->support_info_for_freemius();
        ?>
		<!-- Contacting Support -->

		<hr style="border: none;height: 1px; color: #333; background-color: #333;">

		<!-- Info for translators -->
		<?php 
        $this->info_for_translators();
        ?>
		<!-- Info for translators -->

		<!-- Debug Info -->
		<div>
			<h2><?php 
        esc_html_e( 'Debug Information', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>

			<div>
				<textarea id="debug-info-textarea" class=""
						  style="display:block; margin-bottom: 10px; width: 100%;resize: none;color:dimgrey;font-family: Courier"
						  cols="100%" rows="30"
						  readonly><?php 
        esc_html_e( Debug_Info::get_instance()->get_debug_info() );
        ?>
				</textarea>
				<button id="debug-info-button"
						type="button"><?php 
        esc_html_e( 'copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></button>
			</div>

		</div>
		<!-- Debug Info -->

		<hr class="pmw-hr">

		<!-- Export Settings -->
		<div>
			<h2><?php 
        esc_html_e( 'Export settings', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>

			<div>
				<textarea id="export-settings-json" class=""
						  style="display:block; margin-bottom: 10px; width: 100%;resize: none;color:dimgrey;"
						  cols="100%" rows="10"
						  readonly><?php 
        echo  wc_esc_json( wp_json_encode( $this->options ) ) ;
        ?>
				</textarea>
				<button
						id="debug-info-button"
						type="button"
						onclick="wpm.saveSettingsToDisk('<?php 
        esc_html_e( WPM_CURRENT_VERSION );
        ?>')"
				><?php 
        esc_html_e( 'Export to disk', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></button>
			</div>
		</div>
		<!-- Export Settings -->

		<hr class="pmw-hr">

		<!-- Import Settings -->
		<div>
			<h2><?php 
        esc_html_e( 'Import settings', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>
			<div>
				<input type="file" id="json-settings-file-input"/>
				<pre id="upload-status-success" style="display: none; white-space: pre-line;">
					<span style="color: green; font-weight: bold">
						<?php 
        esc_html_e( 'Settings imported successfully!', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>
					<span>
						<?php 
        esc_html_e( 'Reloading...(in 5 seconds)!', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>
				</pre>

				<pre id="upload-status-error" style="display: none; white-space: pre-line;">
					<span style="color: red; font-weight: bold">
						<?php 
        esc_html_e( 'There was an error importing that file! Please try again.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>
				</pre>
			</div>
		</div>
		<!-- Import Settings -->

		<hr class="pmw-hr">

		<?php 
    }
    
    private function info_for_translators()
    {
        ?>

		<div style="margin-bottom: 20px">
			<h2><?php 
        esc_html_e( 'Translations', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>
			<?php 
        esc_html_e( 'If you want to participate improving the translations of this plugin into your language, please follow this link:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
			<a href="https://translate.wordpress.org/projects/wp-plugins/woocommerce-google-adwords-conversion-tracking-tag/"
			   target="_blank">translate.wordpress.org</a>

		</div>
		<hr style="border: none;height: 1px; color: #333; background-color: #333;">
		<?php 
    }
    
    private function support_info_for_freemius()
    {
        ?>
		<div style="margin-bottom: 30px;">
			<ul>

				<li>
					<?php 
        esc_html_e( 'Post a support request in the WordPress support forum here: ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					<a href="https://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag/"
					   target="_blank">
						<?php 
        esc_html_e( 'Support forum', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</a>
					&nbsp;
					<span class="dashicons dashicons-info"></span>
					<?php 
        esc_html_e( '(Never post the debug or other sensitive information to the support forum. Instead send us the information by email.)', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				</li>
				<li>
					<?php 
        esc_html_e( 'Or send us an email to the following address: ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					<a href="mailto:support@sweetcode.com" target="_blank">support@sweetcode.com</a>
				</li>
			</ul>
		</div>

		<?php 
    }
    
    private function support_info_for_wc_market()
    {
        ?>
		<div style="margin-bottom: 30px;">
			<ul>
				<li>
					<?php 
        esc_html_e( 'Send us your support request through the WooCommerce.com dashboard: ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					<a href="https://woocommerce.com/my-account/create-a-ticket/" target="_blank">WooCommerce support
						dashboard</a>
				</li>
			</ul>
		</div>

		<?php 
    }
    
    public function wpm_plugin_section_author_description()
    {
        ?>
		<div style="margin-top:20px;margin-bottom: 30px">
			<?php 
        esc_html_e( 'More details about the developer of this plugin: ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<div style="margin-bottom: 30px;">
			<div><?php 
        esc_html_e( 'Developer: SweetCode', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></div>
			<div>
				<?php 
        esc_html_e( 'Website: ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				<a href="https://sweetcode.com/?utm_source=plugin&utm_medium=banner&utm_campaign=wpm"
				   target="_blank">https://sweetcode.com</a>
			</div>
		</div>
		<?php 
    }
    
    public function wpm_option_html_google_analytics_universal_property()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_analytics_ua_property_id'
			   name='wgact_plugin_options[google][analytics][universal][property_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['analytics']['universal']['property_id'] );
        ?>'
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['analytics']['universal']['property_id'] );
        $this->get_documentation_html_by_key( 'google_analytics_universal_property' );
        echo  '<br><br>' ;
        esc_html_e( 'The Google Analytics Universal property ID looks like this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>UA-12345678-1</i>' ;
    }
    
    public function wpm_option_html_google_analytics_4_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_analytics_4_measurement_id'
			   name='wgact_plugin_options[google][analytics][ga4][measurement_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['analytics']['ga4']['measurement_id'] );
        ?>'
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['analytics']['ga4']['measurement_id'] );
        $this->get_documentation_html_by_key( 'google_analytics_4_id' );
        echo  '<br><br>' ;
        esc_html_e( 'The Google Analytics 4 measurement ID looks like this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>G-R912ZZ1MHH0</i>' ;
    }
    
    public function wpm_option_html_google_ads_conversion_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_conversion_id'
			   name='wgact_plugin_options[google][ads][conversion_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['ads']['conversion_id'] );
        ?>'
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['conversion_id'] );
        $this->get_documentation_html_by_key( 'google_ads_conversion_id' );
        echo  '<br><br>' ;
        esc_html_e( 'The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>123456789</i>' ;
    }
    
    public function wpm_option_html_google_ads_conversion_label()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_conversion_label'
			   name='wgact_plugin_options[google][ads][conversion_label]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['ads']['conversion_label'] );
        ?>'
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['conversion_label'], $this->options['google']['ads']['conversion_id'] );
        $this->get_documentation_html_by_key( 'google_ads_conversion_label' );
        echo  '<br><br>' ;
        esc_html_e( 'The purchase conversion label looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>Xt19CO3axGAX0vg6X3gM</i>' ;
        
        if ( $this->options['google']['ads']['conversion_label'] && !$this->options['google']['ads']['conversion_id'] ) {
            echo  '<p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'Requires an active Google Ads Conversion ID', 'woocommerce-google-adwords-conversion-tracking-tag' );
        }
        
        echo  '</p>' ;
    }
    
    public function wpm_option_html_google_optimize_container_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_google_optimize_container_id'
			   name='wgact_plugin_options[google][optimize][container_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['optimize']['container_id'] );
        ?>'
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['optimize']['container_id'] );
        //        echo $this->get_documentation_html('/wgact/#/plugin-configuration?id=configure-the-plugin');
        $this->get_documentation_html_by_key( 'google_optimize_container_id' );
        echo  '<br><br>' ;
        esc_html_e( 'The Google Optimize container ID looks like this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>GTM-WMAB1BM</i>&nbsp;' ;
        esc_html_e( 'or', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>OPT-WMAB1BM</i>' ;
    }
    
    public function wpm_option_html_facebook_pixel_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_facebook_pixel_id'
			   name='wgact_plugin_options[facebook][pixel_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['facebook']['pixel_id'] );
        ?>'
		/>
		<?php 
        $this->get_status_icon( $this->options['facebook']['pixel_id'] );
        $this->get_documentation_html_by_key( 'facebook_pixel_id' );
        echo  '<br><br>' ;
        esc_html_e( 'The Meta (Facebook) pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>765432112345678</i>' ;
    }
    
    public function wpm_option_html_bing_uet_tag_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_bing_uet_tag_id'
			   name='wgact_plugin_options[bing][uet_tag_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['bing']['uet_tag_id'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['bing']['uet_tag_id'] );
        $this->get_documentation_html_by_key( 'bing_uet_tag_id' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        esc_html_e( 'The Microsoft Advertising UET tag ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>12345678</i>' ;
    }
    
    public function wpm_option_html_twitter_pixel_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_twitter_pixel_id'
			   name='wgact_plugin_options[twitter][pixel_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['twitter']['pixel_id'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_pixel_id' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        esc_html_e( 'The Twitter pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>a1cde</i>' ;
    }
    
    public function pmw_setting_twitter_add_to_cart()
    {
        $text_length = max( strlen( $this->options['twitter']['event_ids']['add_to_cart'] ), 14 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_twitter_add_to_cart'
				name='wgact_plugin_options[twitter][event_ids][add_to_cart]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['twitter']['event_ids']['add_to_cart'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['event_ids']['add_to_cart'], $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_event_ids' );
        $this->html_pro_feature();
    }
    
    public function pmw_setting_twitter_add_to_wishlist()
    {
        $text_length = max( strlen( $this->options['twitter']['event_ids']['add_to_wishlist'] ), 14 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_twitter_add_to_wishlist'
				name='wgact_plugin_options[twitter][event_ids][add_to_wishlist]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['twitter']['event_ids']['add_to_wishlist'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['event_ids']['add_to_wishlist'], $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_event_ids' );
        $this->html_pro_feature();
    }
    
    public function pmw_setting_twitter_view_content()
    {
        $text_length = max( strlen( $this->options['twitter']['event_ids']['view_content'] ), 14 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_twitter_view_content'
				name='wgact_plugin_options[twitter][event_ids][view_content]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['twitter']['event_ids']['view_content'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['event_ids']['view_content'], $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_event_ids' );
        $this->html_pro_feature();
    }
    
    public function pmw_setting_twitter_search()
    {
        $text_length = max( strlen( $this->options['twitter']['event_ids']['search'] ), 14 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_twitter_search'
				name='wgact_plugin_options[twitter][event_ids][search]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['twitter']['event_ids']['search'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['event_ids']['search'], $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_event_ids' );
        $this->html_pro_feature();
    }
    
    public function pmw_setting_twitter_initiate_checkout()
    {
        $text_length = max( strlen( $this->options['twitter']['event_ids']['initiate_checkout'] ), 14 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_twitter_initiate_checkout'
				name='wgact_plugin_options[twitter][event_ids][initiate_checkout]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['twitter']['event_ids']['initiate_checkout'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['event_ids']['initiate_checkout'], $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_event_ids' );
        $this->html_pro_feature();
    }
    
    public function pmw_setting_twitter_purchase()
    {
        $text_length = max( strlen( $this->options['twitter']['event_ids']['purchase'] ), 14 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_twitter_purchase'
				name='wgact_plugin_options[twitter][event_ids][purchase]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['twitter']['event_ids']['purchase'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['twitter']['event_ids']['purchase'], $this->options['twitter']['pixel_id'] );
        $this->get_documentation_html_by_key( 'twitter_event_ids' );
        $this->html_pro_feature();
    }
    
    public function pmw_option_html_pinterest_pixel_id()
    {
        ?>
		<input class="pmw mono"
			   id='pmw_plugin_pinterest_pixel_id'
			   name='wgact_plugin_options[pinterest][pixel_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['pinterest']['pixel_id'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['pinterest']['pixel_id'] );
        $this->get_documentation_html_by_key( 'pinterest_pixel_id' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        esc_html_e( 'The Pinterest pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>1234567890123</i>' ;
    }
    
    public function pmw_option_html_pinterest_enhanced_match()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[pinterest][enhanced_match]'>
			<input type='checkbox' id='wpm_plugin_pinterest_enhanced_match'
				   name='wgact_plugin_options[pinterest][enhanced_match]'
				   value='1' <?php 
        checked( $this->options['pinterest']['enhanced_match'] );
        ?> />

			<?php 
        esc_html_e( 'Enable Pinterest enhanced match', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['pinterest']['enhanced_match'] );
        $this->get_documentation_html_by_key( 'pinterest_enhanced_match' );
        ?>
		<?php 
        //		$this->get_documentation_html_by_key('pinterest_enhanced_match');
    }
    
    public function wpm_option_html_snapchat_pixel_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_snapchat_pixel_id'
			   name='wgact_plugin_options[snapchat][pixel_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['snapchat']['pixel_id'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['snapchat']['pixel_id'] );
        $this->get_documentation_html_by_key( 'snapchat_pixel_id' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        esc_html_e( 'The Snapchat pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>1a2345b6-cd78-9012-e345-fg6h7890ij12</i>' ;
    }
    
    public function wpm_option_html_tiktok_pixel_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_tiktok_pixel_id'
			   name='wgact_plugin_options[tiktok][pixel_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['tiktok']['pixel_id'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['tiktok']['pixel_id'] );
        $this->get_documentation_html_by_key( 'tiktok_pixel_id' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        esc_html_e( 'The TikTok pixel ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>ABCD1E2FGH3IJK45LMN6</i>' ;
    }
    
    public function wpm_option_html_hotjar_site_id()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_hotjar_site_id'
			   name='wgact_plugin_options[hotjar][site_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['hotjar']['site_id'] );
        ?>'/>
		<?php 
        $this->get_status_icon( $this->options['hotjar']['site_id'] );
        $this->get_documentation_html_by_key( 'hotjar_site_id' );
        echo  '<br><br>' ;
        esc_html_e( 'The Hotjar site ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '&nbsp;<i>1234567</i>' ;
    }
    
    public function pmw_option_html_shop_order_total_logic()
    {
        ?>
		<label>
			<input type='radio' id='wpm_plugin_order_total_logic_0'
				   name='wgact_plugin_options[shop][order_total_logic]'
				   value='0' <?php 
        echo  checked( 0, $this->options['shop']['order_total_logic'], false ) ;
        ?> >
			<?php 
        esc_html_e( 'Order Subtotal: Doesn\'t include tax, shipping, and if available, fees like PayPal or Stripe fees (default)', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
			<?php 
        $this->get_documentation_html_by_key( 'order_subtotal' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_order_total_logic_1'
				   name='wgact_plugin_options[shop][order_total_logic]'
				   value='1' <?php 
        echo  checked( 1, $this->options['shop']['order_total_logic'], false ) ;
        ?> >
			<?php 
        esc_html_e( 'Order Total: Includes tax and shipping', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
			<?php 
        $this->get_documentation_html_by_key( 'order_total' );
        ?>

		</label>
		<?php 
        
        if ( wpm_fs()->can_use_premium_code__premium_only() || '2' === $this->options['shop']['order_total_logic'] || $this->options['general']['pro_version_demo'] ) {
            ?>
			<br>
			<label>
				<input type='radio' id='wpm_plugin_order_total_logic_2'
					   name='wgact_plugin_options[shop][order_total_logic]'
					   value='2' <?php 
            echo  checked( 2, $this->options['shop']['order_total_logic'], false ) ;
            ?>
					<?php 
            if ( !Environment::get_instance()->is_a_cog_plugin_active() || !wpm_fs()->can_use_premium_code__premium_only() ) {
                ?>
						disabled
					<?php 
            }
            ?>
				>
				<?php 
            esc_html_e( 'Profit Margin: Only reports the profit margin. Excludes tax, shipping, and where possible, gateway fees.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				<?php 
            $this->get_documentation_html_by_key( 'order_profit_margin' );
            ?>
				<?php 
            $this->html_beta_e();
            ?>
				<?php 
            $this->html_pro_feature();
            ?>
			</label>
		<?php 
        }
        
        ?>
		<div style="margin-top: 10px">
			<div>
				<?php 
        esc_html_e( 'This is the order total amount reported back to the paid ads pixels (such as Google Ads, Facebook, etc.)', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				<?php 
        
        if ( wpm_fs()->can_use_premium_code__premium_only() && !Environment::get_instance()->is_a_cog_plugin_active() ) {
            ?>
			</div>
			<div style="margin-top: 10px">
				<span class="dashicons dashicons-info"></span>
				<?php 
            esc_html_e( 'To use the Profit Margin setting you will need to install one of the following two Cost of Goods plugins:', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				<a href="https://woocommerce.com/products/woocommerce-cost-of-goods/" target="_blank">WooCommerce Cost
					of Goods (SkyVerge)</a>
				<?php 
            esc_html_e( 'or', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				<a href="https://wordpress.org/plugins/cost-of-goods-for-woocommerce/" target="_blank">Cost of Goods for
					WooCommerce (WPFactory)</a>
				<?php 
        }
        
        ?>
			</div>
		</div>
		<?php 
    }
    
    private function get_documentation_html_by_key( $key = 'default' )
    {
        return $this->get_documentation_html( Documentation::get_link( $key ) );
    }
    
    protected function get_documentation_html( $path )
    {
        //		$html  = '<a class="pmw-documentation-icon" href="' . $path . '" target="_blank">';
        //		$html .= '<span style="vertical-align: top; margin-top: 0px" class="dashicons dashicons-info-outline tooltip"><span class="tooltiptext">';
        //		$html .= esc_html__('open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag');
        //		$html .= '</span></span></a>';
        //
        //		return $html;
        ?>
		<a class="pmw-documentation-icon" href="<?php 
        echo  esc_url( $path ) ;
        ?>" target="_blank">
		<span style="vertical-align: top; margin-top: 0" class="dashicons dashicons-info-outline pmw-tooltip"><span
					class="tooltiptext">
		<?php 
        esc_html_e( 'open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</span></span></a>

		<?php 
    }
    
    protected function get_documentation_html_e( $path )
    {
        $html = '<a class="pmw-documentation-icon" href="' . $path . '" target="_blank">';
        $html .= '<span style="vertical-align: top; margin-top: 0px" class="dashicons dashicons-info-outline pmw-tooltip"><span class="tooltiptext">';
        $html .= esc_html__( 'open the documentation', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $html .= '</span></span></a>';
        return $html;
    }
    
    public function wpm_setting_html_google_consent_mode_active()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][consent_mode][active]'>
			<input type='checkbox' id='wpm_setting_google_consent_mode_active'
				   name='wgact_plugin_options[google][consent_mode][active]'
				   value='1'
				<?php 
        checked( $this->options['google']['consent_mode']['active'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable Google consent mode with standard settings', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['google']['consent_mode']['active'], true, true );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'google_consent_mode' );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_google_consent_regions()
    {
        // https://semantic-ui.com/modules/dropdown.html#multiple-selection
        // https://developer.woocommerce.com/2017/08/08/selectwoo-an-accessible-replacement-for-select2/
        // https://github.com/woocommerce/selectWoo
        ?>
		<select id="wpm_setting_google_consent_regions" multiple="multiple"
				name="wgact_plugin_options[google][consent_mode][regions][]"
				style="width:350px;"
				data-placeholder="<?php 
        esc_html_e( 'Choose countries', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>&hellip;"
				aria-label="Country"
				class="wc-enhanced-select"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		>
			<?php 
        foreach ( Consent_Mode_Regions::get_consent_mode_regions() as $region_code => $region_name ) {
            ?>
				<option value="<?php 
            esc_html_e( $region_code );
            ?>" <?php 
            esc_html_e( ( in_array( $region_code, Options::get_options_obj()->google->consent_mode->regions, false ) ? 'selected' : '' ) );
            ?>><?php 
            esc_html_e( $region_name );
            ?></option>
			<?php 
        }
        ?>

		</select>
		<script>
			jQuery("#wpm_setting_google_consent_regions").select2({
				// theme: "classic"
			})
		</script>
		<?php 
        $this->get_documentation_html_by_key( 'google_consent_regions' );
        $this->html_pro_feature();
        ?>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php 
        esc_html_e( 'If no region is set, then the restrictions are enabled for all regions. If you specify one or more regions, then the restrictions only apply for the specified regions.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<?php 
    }
    
    public function wpm_info_html_google_analytics_eec()
    {
        esc_html_e( 'Google Analytics Enhanced E-Commerce is ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( wpm_fs()->can_use_premium_code__premium_only() && $this->google->is_google_analytics_active() );
        $this->html_pro_feature();
        //		$this->get_documentation_html_by_key('eec');
    }
    
    public function wpm_setting_html_google_analytics_4_api_secret()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_setting_google_analytics_4_api_secret'
			   name='wgact_plugin_options[google][analytics][ga4][api_secret]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['analytics']['ga4']['api_secret'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['analytics']['ga4']['api_secret'] );
        $this->get_documentation_html_by_key( 'google_analytics_4_api_secret' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        
        if ( !$this->options['google']['analytics']['ga4']['measurement_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info" style="margin-right: 10px"></span>' ;
            esc_html_e( 'Google Analytics 4 activation required', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p>' ;
        }
        
        esc_html_e( 'If enabled, purchase and refund events will be sent to Google through the measurement protocol for increased accuracy.', 'woocommerce-google-adwords-conversion-tracking-tag' );
    }
    
    public function pmw_setting_html_ga4_property_id()
    {
        ?>
		<input class="pmw mono"
			   id='pmw_setting_ga4_property_id'
			   name='wgact_plugin_options[google][analytics][ga4][data_api][property_id]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['analytics']['ga4']['data_api']['property_id'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['analytics']['ga4']['data_api']['property_id'], count( $this->options['google']['analytics']['ga4']['data_api']['credentials'] ) > 0 );
        $this->get_documentation_html_by_key( 'ga4_data_api_property_id' );
        $this->html_pro_feature();
        //		echo '<br><br>';
        //		if (!$this->options['google']['analytics']['ga4']['measurement_id']) {
        //			echo '<p></p><span class="dashicons dashicons-info" style="margin-right: 10px"></span>';
        //			esc_html_e('Google Analytics 4 activation required', 'woocommerce-google-adwords-conversion-tracking-tag');
        //			echo '</p>';
        //		}
        //		esc_html_e('If enabled, purchase and refund events will be sent to Google through the measurement protocol for increased accuracy.', 'woocommerce-google-adwords-conversion-tracking-tag');
    }
    
    public function pmw_setting_html_g4_data_api_credentials()
    {
        $client_email = ( isset( $this->options['google']['analytics']['ga4']['data_api']['credentials']['client_email'] ) ? $this->options['google']['analytics']['ga4']['data_api']['credentials']['client_email'] : '' );
        $text_length = max( strlen( $client_email ), 80 );
        ?>
		<div style="margin-top: 5px">

			<input class="pmw mono"
				   id='pmw_setting_ga4_data_api_client_email'
				   name='pmw_setting_ga4_data_api_client_email'
				   size='<?php 
        esc_html_e( $text_length );
        ?>'
				   type='text'
				   style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
				   value='<?php 
        esc_html_e( $client_email );
        ?>'
				   disabled
			/>

			<script>
				const pmwCopyGa4ClientEmailToClipboardCA = () => {
					navigator.clipboard.writeText(document.getElementById("pmw_setting_ga4_data_api_client_email").value)
					const pmwCaFeedTooltip     = document.getElementById("myPmwGa4ClientEmailTooltip")
					pmwCaFeedTooltip.innerHTML = "<?php 
        esc_html_e( 'Copied the account email to the clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>"
				}

				const resetGa4ClientEmailCopyButton = () => {
					const pmwCaFeedTooltip     = document.getElementById("myPmwGa4ClientEmailTooltip")
					pmwCaFeedTooltip.innerHTML = "<?php 
        esc_html_e( 'Copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>"
				}
			</script>

			<div class="pmwCaTooltip">
				<a href="javascript:void(0)" class="pmw-copy-icon pmwCaTooltip"
				   onclick="pmwCopyGa4ClientEmailToClipboardCA()" onmouseout="resetGa4ClientEmailCopyButton()"></a>
				<span class="pmwCaTooltiptext"
					  id="myPmwGa4ClientEmailTooltip"><?php 
        esc_html_e( 'Copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></span>
			</div>


			<?php 
        $this->get_status_icon( isset( $this->options['google']['analytics']['ga4']['data_api']['credentials']['client_email'] ), $this->options['google']['analytics']['ga4']['data_api']['property_id'] );
        ?>
			<?php 
        $this->get_documentation_html_by_key( 'ga4_data_api_credentials' );
        ?>
			<?php 
        $this->html_pro_feature();
        ?>

			<div style="margin-top: 5px">

				<!-- Import Settings -->
				<div class="button">
					<div>
						<label for="ga4-data-api-credentials-upload-button">
							<?php 
        esc_html_e( 'Import credentials', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
							<input type="file" id="ga4-data-api-credentials-upload-button" style="display: none;"/>
						</label>
					</div>
				</div>
				<!-- Import Settings -->

				<!-- Delete Settings -->
				<div class="button">
					<div>
						<label for="ga4-data-api-credentials-delete-button">
							<?php 
        esc_html_e( 'Delete credentials', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
							<input id="ga4-data-api-credentials-delete-button" style="display: none;"/>
						</label>
					</div>
				</div>
				<!-- Delete Settings -->
			</div>

			<div style="margin-top: 20px">
				<?php 
        
        if ( !$this->options['google']['analytics']['ga4']['data_api']['property_id'] ) {
            ?>
					<span class="dashicons dashicons-info" style="padding-right: 10px"></span>
					<?php 
            esc_html_e( 'The GA4 property ID must be set.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				<?php 
        }
        
        ?>
			</div>

			<div>
				<pre id="ga4-api-credentials-upload-status-success" style="display: none; white-space: pre-line;">
					<span style="color: green; font-weight: bold">
						<?php 
        esc_html_e( 'Settings imported successfully!', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>
					<span>
						<?php 
        esc_html_e( 'Reloading...(in 5 seconds)!', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>
				</pre>

				<pre id="ga4-api-credentials-upload-status-error" style="display: none; white-space: pre-line;">
					<span style="color: red; font-weight: bold">
						<?php 
        esc_html_e( 'There was an error importing that file! Please try again.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</span>
				</pre>
			</div>
		</div>
		<?php 
    }
    
    public function wpm_setting_html_google_analytics_link_attribution()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][analytics][link_attribution]'>
			<input type='checkbox' id='wpm_setting_google_analytics_link_attribution'
				   name='wgact_plugin_options[google][analytics][link_attribution]'
				   value='1' <?php 
        checked( $this->options['google']['analytics']['link_attribution'] );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable Google Analytics enhanced link attribution', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['google']['analytics']['link_attribution'], $this->options['google']['analytics']['universal']['property_id'] || $this->options['google']['analytics']['ga4']['measurement_id'], true );
        ?>
		<?php 
        //        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-consent-mode#/consent-mgmt/google-consent-mode');
        ?>
		<?php 
        
        if ( $this->options['google']['analytics']['link_attribution'] && (!$this->options['google']['analytics']['universal']['property_id'] && !$this->options['google']['analytics']['ga4']['measurement_id']) ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate at least Google Analytics UA or Google Analytics 4', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function wpm_setting_html_google_user_id()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][user_id]'>
			<input type='checkbox' id='wpm_setting_google_user_id'
				   name='wgact_plugin_options[google][user_id]'
				   value='1'
				<?php 
        checked( $this->options['google']['user_id'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable Google user ID', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['google']['user_id'], $this->options['google']['analytics']['universal']['property_id'] || $this->options['google']['analytics']['ga4']['measurement_id'] || $this->google->is_google_ads_active(), true );
        $this->html_pro_feature();
        //        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-consent-mode#/consent-mgmt/google-consent-mode');
        ?>
		<?php 
        
        if ( !$this->options['google']['analytics']['universal']['property_id'] && !$this->options['google']['analytics']['ga4']['measurement_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate at least Google Analytics UA or Google Analytics 4', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function wpm_setting_html_google_ads_enhanced_conversions()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][ads][enhanced_conversions]'>
			<input type='checkbox' id='wpm_setting_google_user_id'
				   name='wgact_plugin_options[google][ads][enhanced_conversions]'
				   value='1'
				<?php 
        checked( $this->options['google']['ads']['enhanced_conversions'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable Google Ads Enhanced Conversions', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['enhanced_conversions'], $this->google->is_google_ads_active(), false );
        $this->html_pro_feature();
        $this->get_documentation_html_by_key( 'google_ads_enhanced_conversions' );
        ?>
		<a
				href="https://sweetcode.wistia.com/medias/73e5op37xg"
				target="_blank"
				style="text-decoration: none;"
		>
			<span class="dashicons dashicons-video-alt3"></span>
		</a>
		<?php 
        
        if ( !$this->google->is_google_ads_active() ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate Google Ads', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function wpm_setting_html_google_ads_phone_conversion_number()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_google_ads_phone_conversion_number'
			   name='wgact_plugin_options[google][ads][phone_conversion_number]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['ads']['phone_conversion_number'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['phone_conversion_number'], $this->options['google']['ads']['phone_conversion_label'] && $this->options['google']['ads']['phone_conversion_number'] );
        $this->get_documentation_html_by_key( 'google_ads_phone_conversion_number' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        esc_html_e( 'The Google Ads phone conversion number must be in the same format as on the website.', 'woocommerce-google-adwords-conversion-tracking-tag' );
    }
    
    public function wpm_setting_html_google_ads_phone_conversion_label()
    {
        ?>
		<input class="pmw mono"
			   id='wpm_plugin_google_ads_phone_conversion_label'
			   name='wgact_plugin_options[google][ads][phone_conversion_label]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['ads']['phone_conversion_label'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['phone_conversion_label'], $this->options['google']['ads']['phone_conversion_label'] && $this->options['google']['ads']['phone_conversion_number'] );
        $this->get_documentation_html_by_key( 'google_ads_phone_conversion_label' );
        $this->html_pro_feature();
        echo  '<br><br>' ;
        //        esc_html_e('The Google Ads phone conversion label must be in the same format as on the website.', 'woocommerce-google-adwords-conversion-tracking-tag');
    }
    
    public function pmw_setting_html_google_ads_conversion_adjustments_conversion_name()
    {
        ?>
		<input class="pmw mono"
			   id='pmw_plugin_google_ads_conversion_adjustments_conversion_name'
			   name='wgact_plugin_options[google][ads][conversion_adjustments][conversion_name]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( $this->options['google']['ads']['conversion_adjustments']['conversion_name'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['conversion_adjustments']['conversion_name'], $this->google->is_google_ads_active() );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'google_ads_conversion_adjustments' );
        ?>
		<?php 
        $this->html_pro_feature();
        ?>
		<div style="margin-top: 20px">
			<span class="dashicons dashicons-info" style="margin-right: 10px"></span>
			<?php 
        esc_html_e( 'The conversion name must match the conversion name in Google Ads exactly.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<?php 
        
        if ( !$this->options['google']['ads']['conversion_id'] || !$this->options['google']['ads']['conversion_label'] ) {
            ?>
			<span class="dashicons dashicons-info" style="padding-right: 10px"></span>
			<?php 
            esc_html_e( 'Requires an active Google Ads Conversion ID and Conversion Label.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '<br>' ;
        }
    
    }
    
    public function pmw_setting_html_google_ads_conversion_adjustments_feed()
    {
        $feed_url = get_site_url() . Pixel_Manager::get_instance()->get_google_ads_conversion_adjustments_endpoint();
        $text_length = strlen( $feed_url );
        ?>
		<div style="margin-top: 5px">

			<input class="pmw mono"
				   id='pmw_plugin_google_ads_conversion_adjustments_feed'
				   name='pmw_plugin_google_ads_conversion_adjustments_feed'
				   size='<?php 
        esc_html_e( $text_length );
        ?>'
				   type='text'
				   style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
				   value='<?php 
        echo  esc_url( $feed_url ) ;
        ?>'
				   disabled
			/>
			<script>
				const pmwCopyToClipboardCA = () => {

					// const feedUrlElement = document.getElementById("pmw_plugin_google_ads_conversion_adjustments_feed")
					// feedUrlElement.select()
					// feedUrlElement.setSelectionRange(0, 99999)
					navigator.clipboard.writeText(document.getElementById("pmw_plugin_google_ads_conversion_adjustments_feed").value)

					const pmwCaFeedTooltip     = document.getElementById("myPmwCaTooltip")
					pmwCaFeedTooltip.innerHTML = "<?php 
        esc_html_e( 'Copied feed URL to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>"
				}

				const resetCaCopyButton = () => {
					const pmwCaFeedTooltip     = document.getElementById("myPmwCaTooltip")
					pmwCaFeedTooltip.innerHTML = "<?php 
        esc_html_e( 'Copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>"
				}
			</script>
			<div class="pmwCaTooltip">
				<a href="javascript:void(0)" class="pmw-copy-icon pmwCaTooltip"
				   onclick="pmwCopyToClipboardCA()" onmouseout="resetCaCopyButton()"></a>
				<span class="pmwCaTooltiptext"
					  id="myPmwCaTooltip"><?php 
        esc_html_e( 'Copy to clipboard', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></span>
			</div>
			<?php 
        $this->get_status_icon( $this->options['google']['ads']['conversion_adjustments']['conversion_name'], $this->google->is_google_ads_active() );
        ?>
			<?php 
        $this->get_documentation_html_by_key( 'google_ads_conversion_adjustments' );
        ?>
			<?php 
        $this->html_pro_feature();
        ?>
			<div style="margin-top: 20px">
				<?php 
        
        if ( !$this->options['google']['ads']['conversion_adjustments']['conversion_name'] ) {
            ?>
					<span class="dashicons dashicons-info" style="padding-right: 10px"></span>
					<?php 
            esc_html_e( 'The Conversion Name must be set.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				<?php 
        }
        
        ?>
			</div>
		</div>
		<?php 
    }
    
    public function pmw_setting_html_google_optimize_anti_flicker_snippet()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        
        if ( !Options::is_lazy_load_pmw_active() ) {
            ?>
			<label>
				<input type='hidden' value='0' name='wgact_plugin_options[google][optimize][anti_flicker]'>
				<input type='checkbox' id='pmw_plugin_google_optimize_anti_flicker_snippet'
					   name='wgact_plugin_options[google][optimize][anti_flicker]'
					   value='1'
					<?php 
            checked( $this->options['google']['optimize']['anti_flicker'] );
            ?>
					<?php 
            esc_html_e( $this->disable_if_demo() );
            ?>
				/>
				<?php 
            esc_html_e( 'Google Optimize anti-flicker snippet', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
			</label>
		<?php 
        } else {
            esc_html_e( 'The Google Optimize anti-flicker snippet is ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        }
        
        $this->get_status_icon( Options::get_options_obj()->google->optimize->anti_flicker || Options::is_google_optimize_active() && Options::is_lazy_load_pmw_active(), Options::is_google_optimize_active(), true );
        $this->html_pro_feature();
        $this->get_documentation_html_by_key( 'google_optimize_anti_flicker' );
        ?>
		<div style="margin-top: 10px">
			<?php 
        
        if ( Options::is_google_optimize_active() && Options::is_lazy_load_pmw_active() ) {
            ?>
				<span class="dashicons dashicons-info" style="padding-right: 10px"></span>
				<?php 
            esc_html_e( 'The anti-flicker snippet has been automatically activated because Google Optimize and Lazy Load PMW are enabled.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
			<?php 
        }
        
        ?>

			<?php 
        
        if ( !Options::is_google_optimize_active() && Options::get_options_obj()->google->optimize->anti_flicker ) {
            ?>
				<span class="dashicons dashicons-info" style="padding-right: 10px"></span>
				<?php 
            esc_html_e( 'Enabling the Google Optimize anti-flicker snippet requires Google Optimize to be active.', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
			<?php 
        }
        
        ?>
		</div>
		<?php 
    }
    
    public function wpm_setting_html_borlabs_support()
    {
        esc_html_e( 'Borlabs Cookie detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_cookiebot_support()
    {
        esc_html_e( 'Cookiebot detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_complianz_support()
    {
        esc_html_e( 'Complianz GDPR detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_cookie_notice_support()
    {
        esc_html_e( 'Cookie Notice (by hu-manity.co) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_cookie_script_support()
    {
        esc_html_e( 'Cookie Script (by cookie-script.com) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_moove_gdpr_support()
    {
        esc_html_e( 'GDPR Cookie Compliance (by Moove Agency) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_cookie_law_info_support()
    {
        esc_html_e( 'GDPR Cookie Consent (by WebToffee) detected. Automatic support is:', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( true, true, true );
        $this->html_pro_feature();
    }
    
    public function wpm_setting_html_explicit_consent_mode()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[shop][cookie_consent_mgmt][explicit_consent]'>
			<input type='checkbox' id='wpm_setting_explicit_consent_mode'
				   name='wgact_plugin_options[shop][cookie_consent_mgmt][explicit_consent]'
				   value='1'
				<?php 
        checked( $this->options['shop']['cookie_consent_mgmt']['explicit_consent'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable Explicit Consent Mode', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['shop']['cookie_consent_mgmt']['explicit_consent'], true, true );
        $this->get_documentation_html_by_key( 'explicit_consent_mode' );
        $this->html_pro_feature();
        echo  '<p style="margin-top:10px">' ;
        esc_html_e( 'Only activate the Explicit Consent Mode if you are also using a Cookie Management Platform (a cookie banner) that is compatible with this plugin. Find a list of compatible plugins in the documentation.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        echo  '</p>' ;
    }
    
    public function wpm_setting_html_facebook_capi_token()
    {
        ?>
		<textarea class="pmw mono"
				  id='wpm_setting_facebook_capi_token'
				  name='wgact_plugin_options[facebook][capi][token]'
				  cols='60'
				  rows='5'
		<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		><?php 
        esc_html_e( $this->options['facebook']['capi']['token'] );
        ?></textarea>
		<?php 
        $this->get_status_icon( $this->options['facebook']['capi']['token'], $this->options['facebook']['pixel_id'] );
        $this->get_documentation_html_by_key( 'facebook_capi_token' );
        $this->html_pro_feature();
        
        if ( !$this->options['facebook']['pixel_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
        
        //        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-conversion-id#/pixels/google-ads?id=configure-the-plugin');
        echo  '<br><br>' ;
        //        esc_html_e('The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
        //        echo '&nbsp;<i>123456789</i>';
    }
    
    public function pmw_setting_html_facebook_capi_test_event_code()
    {
        $text_length = max( strlen( $this->options['facebook']['capi']['test_event_code'] ), 9 );
        ?>
		<input class="pmw mono"
			   id='pmw_setting_facebook_capi_test_event_code'
			   name='wgact_plugin_options[facebook][capi][test_event_code]'
			   size='<?php 
        esc_html_e( $text_length );
        ?>'
			   type='text'
			   style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			   value='<?php 
        esc_html_e( $this->options['facebook']['capi']['test_event_code'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['facebook']['capi']['test_event_code'], $this->options['facebook']['capi']['token'] && $this->options['facebook']['capi']['test_event_code'], true );
        ?>
		<?php 
        //		$this->get_documentation_html_by_key('facebook_capi_test_event_code');
        ?>
		<?php 
        $this->html_pro_feature();
        ?>
		<div style="margin-top: 20px">
			<span class="dashicons dashicons-info" style="margin-right: 10px"></span>
			<?php 
        esc_html_e( "The test event code automatically rotates frequently within Facebook. If you don't see the server events flowing in, first make sure that you've set the latest test event code.", 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<?php 
    }
    
    public function wpm_setting_facebook_capi_user_transparency_process_anonymous_hits()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0'
				   name='wgact_plugin_options[facebook][capi][user_transparency][process_anonymous_hits]'>
			<input type='checkbox' id='wpm_setting_facebook_capi_user_transparency_process_anonymous_hits'
				   name='wgact_plugin_options[facebook][capi][user_transparency][process_anonymous_hits]'
				   value='1'
				<?php 
        checked( $this->options['facebook']['capi']['user_transparency']['process_anonymous_hits'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Send CAPI hits for anonymous visitors who likely have blocked the Meta (Facebook) pixel.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['facebook']['capi']['user_transparency']['process_anonymous_hits'], $this->options['facebook']['pixel_id'], true );
        $this->get_documentation_html_by_key( 'facebook_capi_user_transparency_process_anonymous_hits' );
        $this->html_pro_feature();
        
        if ( $this->options['facebook']['capi']['user_transparency']['process_anonymous_hits'] && !$this->options['facebook']['pixel_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function pmw_setting_facebook_advanced_matching()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0'
				   name='wgact_plugin_options[facebook][capi][user_transparency][send_additional_client_identifiers]'>
			<input type='checkbox' id='wpm_setting_facebook_capi_user_transparency_send_additional_client_identifiers'
				   name='wgact_plugin_options[facebook][capi][user_transparency][send_additional_client_identifiers]'
				   value='1'
				<?php 
        checked( $this->options['facebook']['capi']['user_transparency']['send_additional_client_identifiers'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Send events with additional visitor identifiers, such as email and phone number, if available.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['facebook']['capi']['user_transparency']['send_additional_client_identifiers'], $this->options['facebook']['pixel_id'], true );
        $this->get_documentation_html_by_key( 'facebook_advanced_matching' );
        $this->html_pro_feature();
        
        if ( $this->options['facebook']['capi']['user_transparency']['send_additional_client_identifiers'] && !$this->options['facebook']['pixel_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function wpm_setting_html_facebook_microdata()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[facebook][microdata]'>
			<input type='checkbox' id='wpm_setting_facebook_microdata_active'
				   name='wgact_plugin_options[facebook][microdata]'
				   value='1'
				<?php 
        checked( $this->options['facebook']['microdata'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable Meta (Facebook) product microdata output', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['facebook']['microdata'], $this->options['facebook']['pixel_id'], true );
        $this->get_documentation_html_by_key( 'facebook_microdata' );
        $this->html_pro_feature();
        
        if ( $this->options['facebook']['microdata'] && !$this->options['facebook']['pixel_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate the Meta (Facebook) pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function pmw_setting_html_tiktok_eapi_token()
    {
        $text_length = max( strlen( $this->options['tiktok']['eapi']['token'] ), 40 );
        ?>
		<input
				class="pmw mono"
				id='pmw_setting_tiktok_eapi_token'
				name='wgact_plugin_options[tiktok][eapi][token]'
				size='<?php 
        esc_html_e( $text_length );
        ?>'
				type='text'
				value='<?php 
        esc_html_e( $this->options['tiktok']['eapi']['token'] );
        ?>'
				style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['tiktok']['eapi']['token'], $this->options['tiktok']['pixel_id'] );
        $this->get_documentation_html_by_key( 'tiktok_eapi_token' );
        $this->html_pro_feature();
        
        if ( !$this->options['tiktok']['pixel_id'] ) {
            ?>
			<br><span class="dashicons dashicons-info"></span>
			<?php 
            esc_html_e( 'You need to activate the TikTok pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
			<?php 
        }
        
        //        echo $this->get_documentation_html('/wgact/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=pixel-manager-for-woocommerce-docs&utm_content=google-ads-conversion-id#/pixels/google-ads?id=configure-the-plugin');
        //        esc_html_e('The conversion ID looks similar to this:', 'woocommerce-google-adwords-conversion-tracking-tag');
        //        echo '&nbsp;<i>123456789</i>';
    }
    
    public function pmw_setting_html_tiktok_eapi_test_event_code()
    {
        $text_length = max( strlen( $this->options['tiktok']['eapi']['test_event_code'] ), 9 );
        ?>
		<input class="pmw mono"
			   id='pmw_setting_tiktok_eapi_test_event_code'
			   name='wgact_plugin_options[tiktok][eapi][test_event_code]'
			   size='<?php 
        esc_html_e( $text_length );
        ?>'
			   type='text'
			   style="width:<?php 
        esc_html_e( $text_length );
        ?>ch"
			   value='<?php 
        esc_html_e( $this->options['tiktok']['eapi']['test_event_code'] );
        ?>'
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		/>
		<?php 
        $this->get_status_icon( $this->options['tiktok']['eapi']['test_event_code'], $this->options['tiktok']['eapi']['token'] && $this->options['tiktok']['eapi']['test_event_code'], true );
        ?>
		<?php 
        //		$this->get_documentation_html_by_key('facebook_capi_test_event_code');
        ?>
		<?php 
        $this->html_pro_feature();
        ?>
		<div style="margin-top: 20px">
			<span class="dashicons dashicons-info" style="margin-right: 10px"></span>
			<?php 
        esc_html_e( "The test event code automatically rotates frequently within TikTok. If you don't see the server events flowing in, first make sure that you've set the latest test event code.", 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<?php 
    }
    
    public function pmw_setting_tiktok_eapi_process_anonymous_hits()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0'
				   name='wgact_plugin_options[tiktok][eapi][process_anonymous_hits]'>
			<input type='checkbox' id='pmw_setting_tiktok_eapi_process_anonymous_hits'
				   name='wgact_plugin_options[tiktok][eapi][process_anonymous_hits]'
				   value='1'
				<?php 
        checked( $this->options['tiktok']['eapi']['process_anonymous_hits'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Send Events API hits for anonymous visitors who likely have blocked the TikTok pixel.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['tiktok']['eapi']['process_anonymous_hits'], $this->options['tiktok']['pixel_id'] && $this->options['tiktok']['eapi']['token'], true );
        $this->get_documentation_html_by_key( 'tiktok_eapi_process_anonymous_hits' );
        $this->html_pro_feature();
        
        if ( $this->options['tiktok']['eapi']['process_anonymous_hits'] && !$this->options['tiktok']['pixel_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate the TikTok pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function pmw_setting_tiktok_advanced_matching()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0'
				   name='wgact_plugin_options[tiktok][advanced_matching]'>
			<input type='checkbox' id='pmw_setting_tiktok_advanced_matching'
				   name='wgact_plugin_options[tiktok][advanced_matching]'
				   value='1'
				<?php 
        checked( $this->options['tiktok']['advanced_matching'] );
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Send events with additional visitor identifiers, such as email and phone number, if available.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['tiktok']['advanced_matching'], $this->options['tiktok']['pixel_id'] && $this->options['tiktok']['eapi']['token'], true );
        $this->get_documentation_html_by_key( 'tiktok_advanced_matching' );
        $this->html_pro_feature();
        
        if ( $this->options['tiktok']['advanced_matching'] && !$this->options['tiktok']['pixel_id'] ) {
            echo  '<p></p><span class="dashicons dashicons-info"></span>' ;
            esc_html_e( 'You need to activate the TikTok pixel', 'woocommerce-google-adwords-conversion-tracking-tag' );
            echo  '</p><br>' ;
        }
    
    }
    
    public function wpm_setting_html_order_duplication_prevention()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[shop][order_deduplication]'>
			<input type='checkbox' id='wpm_setting_order_duplication_prevention'
				   name='wgact_plugin_options[shop][order_deduplication]'
				   value='1' <?php 
        checked( $this->options['shop']['order_deduplication'] );
        ?>
			/>
			<?php 
        $this->get_order_duplication_prevention_text();
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['shop']['order_deduplication'] );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'duplication_prevention' );
        ?>

		<br>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php 
        esc_html_e( 'Only disable order duplication prevention for testing.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php 
        esc_html_e( 'Automatically reactivates 6 hours after disabling duplication prevention.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<?php 
    }
    
    public function wpm_setting_html_maximum_compatibility_mode()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[general][maximum_compatibility_mode]'>
			<input type='checkbox' id='wpm_setting_maximum_compatibility_mode'
				   name='wgact_plugin_options[general][maximum_compatibility_mode]'
				   value='1' <?php 
        checked( $this->options['general']['maximum_compatibility_mode'] );
        ?> />
			<?php 
        esc_html_e( 'Enable the maximum compatibility mode', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['general']['maximum_compatibility_mode'], true, true );
        $this->get_documentation_html_by_key( 'maximum_compatibility_mode' );
    }
    
    public function wpm_setting_html_disable_tracking_for_user_roles()
    {
        // https://semantic-ui.com/modules/dropdown.html#multiple-selection
        // https://developer.woocommerce.com/2017/08/08/selectwoo-an-accessible-replacement-for-select2/
        // https://github.com/woocommerce/selectWoo
        ?>
		<select id="wpm_setting_disable_tracking_for_user_roles" multiple="multiple"
				name="wgact_plugin_options[shop][disable_tracking_for][]"
				style="width:350px; padding-left: 10px" data-placeholder="Choose roles&hellip;" aria-label="Roles"
				class="wc-enhanced-select"
			<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
		>
			<?php 
        foreach ( get_editable_roles() as $role => $details ) {
            ?>
				<option value="<?php 
            esc_html_e( $role );
            ?>" <?php 
            esc_html_e( ( in_array( $role, $this->options['shop']['disable_tracking_for'], true ) ? 'selected' : '' ) );
            ?>><?php 
            esc_html_e( $details['name'] );
            ?></option>
			<?php 
        }
        ?>

		</select>
		<script>
			jQuery("#wpm_setting_disable_tracking_for_user_roles").select2({
				// theme: "classic"
			})
		</script>
		<?php 
        //		$this->get_documentation_html_by_key('google_consent_regions');
        $this->html_pro_feature();
    }
    
    public function wpm_info_html_acr()
    {
        esc_html_e( 'Automatic Conversion Recovery (ACR) is ', 'woocommerce-google-adwords-conversion-tracking-tag' );
        $this->get_status_icon( wpm_fs()->can_use_premium_code__premium_only() );
        $this->html_pro_feature();
        $this->get_documentation_html_by_key( 'acr' );
    }
    
    public function pmw_info_html_order_list_info()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[shop][order_list_info]'>
			<input type='checkbox' id='wpm_plugin_option_gads_dynamic_remarketing'
				   name='wgact_plugin_options[shop][order_list_info]'
				   value='1' <?php 
        checked( $this->options['shop']['order_list_info'] );
        ?> />

			<?php 
        esc_html_e( 'Display PMW related information on the order list page', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['shop']['order_list_info'] );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'order_list_info' );
    }
    
    public function pmw_info_html_scroll_tracker_thresholds()
    {
        ?>
		<input class="pmw mono"
			   id='pmw_setting_scroll_tracker_thresholds'
			   name='wgact_plugin_options[general][scroll_tracker_thresholds]'
			   size='40'
			   type='text'
			   value='<?php 
        esc_html_e( implode( ',', $this->options['general']['scroll_tracker_thresholds'] ) );
        ?>'
		/>
		<?php 
        $this->get_status_icon( !empty($this->options['general']['scroll_tracker_thresholds']), true, true );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'scroll_tracker_threshold' );
        ?>
		<?php 
        $this->html_pro_feature();
        ?>
		<div style="margin-top: 10px">
			<?php 
        esc_html_e( 'The Scroll Tracker thresholds. A comma separated list of scroll tracking thresholds in percent where the scroll tracker triggers its events.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<?php 
    }
    
    public function html_subscription_value_multiplier()
    {
        $field_width = max( strlen( $this->options['shop']['subscription_value_multiplier'] ), 3 );
        ?>
		<input class="pmw mono"
			   id='pmw_setting_subscription_value_multiplier'
			   name='wgact_plugin_options[shop][subscription_value_multiplier]'
			   size='<?php 
        esc_html_e( $field_width );
        ?>'
			   type='text'
			   style="width:<?php 
        esc_html_e( $field_width );
        ?>ch"
			   value='<?php 
        esc_html_e( $this->options['shop']['subscription_value_multiplier'] );
        ?>'
		/>
		<?php 
        $this->get_documentation_html_by_key( 'subscription_value_multiplier' );
        ?>
		<?php 
        $this->html_pro_feature();
        ?>
		<div style="margin-top: 10px">
			<?php 
        esc_html_e( 'The multiplier multiplies the conversion value output for initial subscriptions to match the CLV of a subscription more closely.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<?php 
    }
    
    public function html_lazy_load_pmw()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[general][lazy_load_pmw]'>
			<input type='checkbox' id='wpm_plugin_option_gads_dynamic_remarketing'
				   name='wgact_plugin_options[general][lazy_load_pmw]'
				   value='1' <?php 
        checked( $this->options['general']['lazy_load_pmw'] );
        ?> />

			<?php 
        esc_html_e( 'Lazy load the Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['general']['lazy_load_pmw'], true, true );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'lazy_load_pmw' );
        ?>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php 
        esc_html_e( 'Enabling this feature will give you better page speed scores. Please read the documentation to learn more about the full implications while using this feature.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<?php 
    }
    
    private function get_order_duplication_prevention_text()
    {
        esc_html_e( 'Basic order duplication prevention is ', 'woocommerce-google-adwords-conversion-tracking-tag' );
    }
    
    private function add_to_cart_requirements_fulfilled()
    {
        
        if ( $this->options['google']['ads']['conversion_id'] && $this->options['google']['ads']['conversion_label'] && $this->options['google']['ads']['aw_merchant_id'] ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function wpm_option_html_google_ads_dynamic_remarketing()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[google][ads][dynamic_remarketing]'>
			<input type='checkbox' id='wpm_plugin_option_gads_dynamic_remarketing'
				   name='wgact_plugin_options[google][ads][dynamic_remarketing]'
				   value='1' <?php 
        checked( $this->options['google']['ads']['dynamic_remarketing'] );
        ?> />

			<?php 
        esc_html_e( 'Enable dynamic remarketing audience collection', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['dynamic_remarketing'] );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'google_ads_dynamic_remarketing' );
        ?>
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php 
        esc_html_e( 'You need to choose the correct product identifier setting in order to match the product identifiers in the product feeds.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<?php 
    }
    
    public function wpm_option_html_variations_output()
    {
        // adding the hidden input is a hack to make WordPress save the option with the value zero,
        // instead of not saving it and remove that array key entirely
        // https://stackoverflow.com/a/1992745/4688612
        ?>
		<label>
			<input type='hidden' value='0' name='wgact_plugin_options[general][variations_output]'>
			<input type='checkbox' id='wpm_plugin_option_variations_output'
				   name='wgact_plugin_options[general][variations_output]'
				   value='1' <?php 
        checked( $this->options['general']['variations_output'] );
        ?>
			/>
			<?php 
        esc_html_e( 'Enable variations output', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<?php 
        $this->get_status_icon( $this->options['general']['variations_output'], $this->options['google']['ads']['dynamic_remarketing'], true );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'variations_output' );
        ?>
		<p><span class="dashicons dashicons-info"></span>
			<?php 
        esc_html_e( 'In order for this to work you need to upload your product feed including product variations and the item_group_id. Disable it, if you choose only to upload the parent product for variable products.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<?php 
    }
    
    public function wpm_plugin_option_google_business_vertical()
    {
        ?>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_0'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='0'
				<?php 
        echo  checked( 0, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Retail', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_1'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='1'
				<?php 
        echo  checked( 1, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Education', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_3'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='3'
				<?php 
        echo  checked( 3, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Hotels and rentals', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_4'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='4'
				<?php 
        echo  checked( 4, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Jobs', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_5'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='5'
				<?php 
        echo  checked( 5, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Local deals', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_6'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='6'
				<?php 
        echo  checked( 6, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Real estate', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_google_business_vertical_8'
				   name='wgact_plugin_options[google][ads][google_business_vertical]'
				   value='8'
				<?php 
        echo  checked( 8, $this->options['google']['ads']['google_business_vertical'], false ) ;
        ?>
				<?php 
        esc_html_e( $this->disable_if_demo() );
        ?>
			/>
			<?php 
        esc_html_e( 'Custom', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<?php 
    }
    
    public function wpm_plugin_setting_aw_merchant_id()
    {
        ?>
		<input class="pmw mono"
			   type="text"
			   id="wpm_plugin_aw_merchant_id"
			   name="wgact_plugin_options[google][ads][aw_merchant_id]"
			   size="40"
			   value="<?php 
        esc_html_e( $this->options['google']['ads']['aw_merchant_id'] );
        ?>"
		/>
		<?php 
        $this->get_status_icon( $this->options['google']['ads']['aw_merchant_id'] );
        ?>
		<?php 
        $this->get_documentation_html_by_key( 'aw_merchant_id' );
        ?>
		<br><br>
		<?php 
        esc_html_e( 'ID of your Google Merchant Center account. It looks like this: 12345678', 'woocommerce-google-adwords-conversion-tracking-tag' );
    }
    
    public function wpm_plugin_option_product_identifier()
    {
        ?>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_0'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='0' <?php 
        echo  checked( 0, $this->options['google']['ads']['product_identifier'], false ) ;
        ?>/>
			<?php 
        esc_html_e( 'post ID (default)', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_2'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='2' <?php 
        echo  checked( 2, $this->options['google']['ads']['product_identifier'], false ) ;
        ?>/>
			<?php 
        esc_html_e( 'SKU', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_1'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='1' <?php 
        echo  checked( 1, $this->options['google']['ads']['product_identifier'], false ) ;
        ?>/>
			<?php 
        esc_html_e( 'ID for the WooCommerce Google Product Feed. Outputs the post ID with woocommerce_gpf_ prefix *', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<label>
			<input type='radio' id='wpm_plugin_option_product_identifier_3'
				   name='wgact_plugin_options[google][ads][product_identifier]'
				   value='3' <?php 
        echo  checked( 3, $this->options['google']['ads']['product_identifier'], false ) ;
        ?>/>
			<?php 
        esc_html_e( 'ID for the WooCommerce Google Listings & Ads Plugin. Outputs the post ID with gla_ prefix **', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</label>
		<br>
		<p style="margin-top:10px">
			<?php 
        esc_html_e( 'Choose a product identifier.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</p>
		<br>
		<?php 
        esc_html_e( '* This is for users of the WooCommerce Google Product Feed Plugin', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		<a href="https://woocommerce.com/products/google-product-feed/" target="_blank">WooCommerce Google Product Feed
			Plugin</a>
		<br>
		<?php 
        esc_html_e( '** This is for users of the WooCommerce Google Listings & Ads Plugin', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		<a href="https://woocommerce.com/products/google-listings-and-ads/" target="_blank">WooCommerce Google Listings
			& Ads Plugin
			Plugin</a>

		<?php 
    }
    
    private function html_beta()
    {
        return '<div class="pmw-status-icon beta">' . esc_html__( 'beta', 'woocommerce-google-adwords-conversion-tracking-tag' ) . '</div>';
    }
    
    private function html_beta_e( $margin_top = '1px' )
    {
        ?>
		<div class="pmw-status-icon beta"
			 style="margin-top: <?php 
        esc_html_e( $margin_top );
        ?>"
		>
			<?php 
        esc_html_e( 'beta', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
		</div>
		<?php 
    }
    
    private function html_active()
    {
        return '<div class="pmw-status-icon active">' . esc_html__( 'active', 'woocommerce-google-adwords-conversion-tracking-tag' ) . '</div>';
    }
    
    private function html_active_new()
    {
        ?>
		<div class="pmw-status-icon active"><?php 
        esc_html_e( 'active', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></div>
		<?php 
    }
    
    private function html_inactive()
    {
        return '<div class="pmw-status-icon inactive">' . esc_html__( 'inactive', 'woocommerce-google-adwords-conversion-tracking-tag' ) . '</div>';
    }
    
    private function html_inactive_new()
    {
        ?>
		<div class="pmw-status-icon inactive"><?php 
        esc_html_e( 'inactive', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></div>
		<?php 
    }
    
    private function html_partially_active()
    {
        return '<div class="pmw-status-icon partially-active">' . esc_html__( 'partially active', 'woocommerce-google-adwords-conversion-tracking-tag' ) . '</div>';
    }
    
    private function html_partially_active_new()
    {
        ?>
		<div class="pmw-status-icon partially-active"><?php 
        esc_html_e( 'partially active', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></div>
		<?php 
    }
    
    private function html_pro_feature()
    {
        
        if ( !wpm_fs()->can_use_premium_code__premium_only() && $this->options['general']['pro_version_demo'] ) {
            //            if (1===1) {
            //			return '<div class="pmw-pro-feature">' . esc_html__('Pro Feature', 'woocommerce-google-adwords-conversion-tracking-tag') . '</div>';
            ?>
			<div class="pmw-pro-feature"><?php 
            esc_html_e( 'Pro Feature', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?></div>

			<?php 
        }
    
    }
    
    private function get_status_icon( $status, $requirements = true, $inactive_silent = false )
    {
        
        if ( $status && $requirements ) {
            $this->html_active_new();
        } elseif ( $status && !$requirements ) {
            $this->html_partially_active_new();
        } elseif ( false == $inactive_silent ) {
            $this->html_inactive_new();
        }
        
        return '';
    }
    
    private function disable_if_demo()
    {
        
        if ( !wpm_fs()->can_use_premium_code__premium_only() && $this->options['general']['pro_version_demo'] ) {
            return 'disabled';
        } else {
            return '';
        }
    
    }
    
    // validate the options
    public function wpm_options_validate( $input )
    {
        // validate Google Analytics Universal property ID
        if ( isset( $input['google']['analytics']['universal']['property_id'] ) ) {
            
            if ( !Validations::is_google_analytics_universal_property_id( $input['google']['analytics']['universal']['property_id'] ) ) {
                $input['google']['analytics']['universal']['property_id'] = ( isset( $this->options['google']['analytics']['universal']['property_id'] ) ? $this->options['google']['analytics']['universal']['property_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-google-analytics-universal-property-id', esc_html__( 'You have entered an invalid Google Analytics Universal property ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Google Analytics 4 measurement ID
        if ( isset( $input['google']['analytics']['ga4']['measurement_id'] ) ) {
            
            if ( !Validations::is_google_analytics_4_measurement_id( $input['google']['analytics']['ga4']['measurement_id'] ) ) {
                $input['google']['analytics']['ga4']['measurement_id'] = ( isset( $this->options['google']['analytics']['ga4']['measurement_id'] ) ? $this->options['google']['analytics']['ga4']['measurement_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-google-analytics-4-measurement-id', esc_html__( 'You have entered an invalid Google Analytics 4 measurement ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Google Analytics 4 API key
        if ( isset( $input['google']['analytics']['ga4']['api_secret'] ) ) {
            
            if ( !Validations::is_google_analytics_4_api_secret( $input['google']['analytics']['ga4']['api_secret'] ) ) {
                $input['google']['analytics']['ga4']['api_secret'] = ( isset( $this->options['google']['analytics']['ga4']['api_secret'] ) ? $this->options['google']['analytics']['ga4']['api_secret'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-google-analytics-4-measurement-id', esc_html__( 'You have entered an invalid Google Analytics 4 API key.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // Validate the Google Analytics 4 property ID
        if ( isset( $input['google']['analytics']['ga4']['data_api']['property_id'] ) ) {
            
            if ( !Validations::is_google_analytics_4_property_id( $input['google']['analytics']['ga4']['data_api']['property_id'] ) ) {
                $input['google']['analytics']['ga4']['data_api']['property_id'] = ( isset( $this->options['google']['analytics']['ga4']['data_api']['property_id'] ) ? $this->options['google']['analytics']['ga4']['data_api']['property_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-google-analytics-4-property-id', esc_html__( 'You have entered an invalid GA4 property ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['google]['ads']['conversion_id']
        if ( isset( $input['google']['ads']['conversion_id'] ) ) {
            
            if ( !Validations::is_gads_conversion_id( $input['google']['ads']['conversion_id'] ) ) {
                $input['google']['ads']['conversion_id'] = ( isset( $this->options['google']['ads']['conversion_id'] ) ? $this->options['google']['ads']['conversion_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-conversion-id', esc_html__( 'You have entered an invalid conversion ID. It only contains 8 to 10 digits.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['google]['ads']['conversion_label']
        if ( isset( $input['google']['ads']['conversion_label'] ) ) {
            
            if ( !Validations::is_gads_conversion_label( $input['google']['ads']['conversion_label'] ) ) {
                $input['google']['ads']['conversion_label'] = ( isset( $this->options['google']['ads']['conversion_label'] ) ? $this->options['google']['ads']['conversion_label'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-conversion-label', esc_html__( 'You have entered an invalid conversion label.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['google]['ads']['phone_conversion_label']
        if ( isset( $input['google']['ads']['phone_conversion_label'] ) ) {
            
            if ( !Validations::is_gads_conversion_label( $input['google']['ads']['phone_conversion_label'] ) ) {
                $input['google']['ads']['phone_conversion_label'] = ( isset( $this->options['google']['ads']['phone_conversion_label'] ) ? $this->options['google']['ads']['phone_conversion_label'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-conversion-label', esc_html__( 'You have entered an invalid conversion label.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['google]['ads']['aw_merchant_id']
        if ( isset( $input['google']['ads']['aw_merchant_id'] ) ) {
            
            if ( !Validations::is_gads_aw_merchant_id( $input['google']['ads']['aw_merchant_id'] ) ) {
                $input['google']['ads']['aw_merchant_id'] = ( isset( $this->options['google']['ads']['aw_merchant_id'] ) ? $this->options['google']['ads']['aw_merchant_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-aw-merchant-id', esc_html__( 'You have entered an invalid merchant ID. It only contains 6 to 12 digits.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Google Optimize container ID
        if ( isset( $input['google']['optimize']['container_id'] ) ) {
            
            if ( !Validations::is_google_optimize_measurement_id( $input['google']['optimize']['container_id'] ) ) {
                $input['google']['optimize']['container_id'] = ( isset( $this->options['google']['optimize']['container_id'] ) ? $this->options['google']['optimize']['container_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-google-optimize-container-id', esc_html__( 'You have entered an invalid Google Optimize container ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['facebook']['pixel_id']
        if ( isset( $input['facebook']['pixel_id'] ) ) {
            
            if ( !Validations::is_facebook_pixel_id( $input['facebook']['pixel_id'] ) ) {
                $input['facebook']['pixel_id'] = ( isset( $this->options['facebook']['pixel_id'] ) ? $this->options['facebook']['pixel_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-facebook-pixel-id', esc_html__( 'You have entered an invalid Meta (Facebook) pixel ID. It only contains 14 to 16 digits.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['facebook']['capi']['token']
        if ( isset( $input['facebook']['capi']['token'] ) ) {
            
            if ( !Validations::is_facebook_capi_token( $input['facebook']['capi']['token'] ) ) {
                $input['facebook']['capi']['token'] = ( isset( $this->options['facebook']['capi']['token'] ) ? $this->options['facebook']['capi']['token'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-facebook-pixel-id', esc_html__( 'You have entered an invalid Meta (Facebook) CAPI token.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate ['facebook']['capi']['test_event_code']
        if ( isset( $input['facebook']['capi']['test_event_code'] ) ) {
            
            if ( !Validations::is_facebook_capi_test_event_code( $input['facebook']['capi']['test_event_code'] ) ) {
                $input['facebook']['capi']['test_event_code'] = ( isset( $this->options['facebook']['capi']['test_event_code'] ) ? $this->options['facebook']['capi']['test_event_code'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-facebook-capi-test-event-code', esc_html__( 'You have entered an invalid Meta (Facebook) CAPI test_event_code.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Bing Ads UET tag ID
        if ( isset( $input['bing']['uet_tag_id'] ) ) {
            
            if ( !Validations::is_bing_uet_tag_id( $input['bing']['uet_tag_id'] ) ) {
                $input['bing']['uet_tag_id'] = ( isset( $this->options['bing']['uet_tag_id'] ) ? $this->options['bing']['uet_tag_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-bing-ads-uet-tag-id', esc_html__( 'You have entered an invalid Bing Ads UET tag ID. It only contains 7 to 9 digits.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Twitter pixel ID
        if ( isset( $input['twitter']['pixel_id'] ) ) {
            
            if ( !Validations::is_twitter_pixel_id( $input['twitter']['pixel_id'] ) ) {
                $input['twitter']['pixel_id'] = ( isset( $this->options['twitter']['pixel_id'] ) ? $this->options['twitter']['pixel_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-twitter-pixel-id', esc_html__( 'You have entered an invalid Twitter pixel ID. It only contains 5 to 7 lowercase letters and numbers.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        function validate_twitter_event( $event, $input, $options )
        {
            
            if ( isset( $input['twitter']['event_ids'][$event] ) ) {
                $input['twitter']['event_ids'][$event] = trim( $input['twitter']['event_ids'][$event] );
                
                if ( !Validations::is_twitter_event_id( $input['twitter']['event_ids'][$event] ) ) {
                    $input['twitter']['event_ids'][$event] = ( isset( $options['twitter']['event_ids'][$event] ) ? $options['twitter']['event_ids'][$event] : '' );
                    add_settings_error( 'wgact_plugin_options', 'invalid-twitter-event-id', esc_html__( 'You have entered an invalid Twitter event ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
                    return $input;
                }
                
                return $input;
            }
            
            return $input;
        }
        
        // Validate Twitter event add_to_cart
        $input = validate_twitter_event( 'add_to_cart', $input, $this->options );
        $input = validate_twitter_event( 'add_to_wishlist', $input, $this->options );
        $input = validate_twitter_event( 'view_content', $input, $this->options );
        $input = validate_twitter_event( 'search', $input, $this->options );
        $input = validate_twitter_event( 'initiate_checkout', $input, $this->options );
        //		$input = validate_twitter_event('add_payment_info', $input, $this->options);
        $input = validate_twitter_event( 'purchase', $input, $this->options );
        // validate Pinterest pixel ID
        if ( isset( $input['pinterest']['pixel_id'] ) ) {
            
            if ( !Validations::is_pinterest_pixel_id( $input['pinterest']['pixel_id'] ) ) {
                $input['pinterest']['pixel_id'] = ( isset( $this->options['pinterest']['pixel_id'] ) ? $this->options['pinterest']['pixel_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-pinterest-pixel-id', esc_html__( 'You have entered an invalid Pinterest pixel ID. It only contains 13 digits.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Snapchat pixel ID
        if ( isset( $input['snapchat']['pixel_id'] ) ) {
            
            if ( !Validations::is_snapchat_pixel_id( $input['snapchat']['pixel_id'] ) ) {
                $input['snapchat']['pixel_id'] = ( isset( $this->options['snapchat']['pixel_id'] ) ? $this->options['snapchat']['pixel_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-snapchat-pixel-id', esc_html__( 'You have entered an invalid Snapchat pixel ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate TikTok pixel ID
        if ( isset( $input['tiktok']['pixel_id'] ) ) {
            
            if ( !Validations::is_tiktok_pixel_id( $input['tiktok']['pixel_id'] ) ) {
                $input['tiktok']['pixel_id'] = ( isset( $this->options['tiktok']['pixel_id'] ) ? $this->options['tiktok']['pixel_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-tiktok-pixel-id', esc_html__( 'You have entered an invalid TikTok pixel ID.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // Validate TikTok Events API access token
        if ( isset( $input['tiktok']['eapi']['token'] ) ) {
            
            if ( !Validations::is_tiktok_eapi_access_token( $input['tiktok']['eapi']['token'] ) ) {
                $input['tiktok']['eapi']['token'] = ( isset( $this->options['tiktok']['eapi']['token'] ) ? $this->options['tiktok']['eapi']['token'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-tiktok-eapi-access-token', esc_html__( 'You have entered an invalid TikTok Events API access token.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // Validate ['tiktok']['eapi']['test_event_code']
        if ( isset( $input['tiktok']['eapi']['test_event_code'] ) ) {
            
            if ( !Validations::is_tiktok_eapi_test_event_code( $input['tiktok']['eapi']['test_event_code'] ) ) {
                $input['tiktok']['eapi']['test_event_code'] = ( isset( $this->options['tiktok']['eapi']['test_event_code'] ) ? $this->options['tiktok']['eapi']['test_event_code'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-tiktok-eapi-test-event-code', esc_html__( 'You have entered an invalid TikTok EAPI test_event_code.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // validate Hotjar site ID
        if ( isset( $input['hotjar']['site_id'] ) ) {
            
            if ( !Validations::is_hotjar_site_id( $input['hotjar']['site_id'] ) ) {
                $input['hotjar']['site_id'] = ( isset( $this->options['hotjar']['site_id'] ) ? $this->options['hotjar']['site_id'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-hotjar-site-id', esc_html__( 'You have entered an invalid Hotjar site ID. It only contains 6 to 9 digits.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        // Sanitize and validate scroll tracker thresholds
        
        if ( isset( $input['general']['scroll_tracker_thresholds'] ) ) {
            $scroll_tracker_thresholds = $input['general']['scroll_tracker_thresholds'];
            // remove all spaces
            $scroll_tracker_thresholds = str_replace( ' ', '', $scroll_tracker_thresholds );
            // remove leading and trailing commas
            $scroll_tracker_thresholds = trim( $scroll_tracker_thresholds, ',' );
            // remove duplicate commas and replace with single comma
            $scroll_tracker_thresholds = preg_replace( '/,+/', ',', $scroll_tracker_thresholds );
            // remove quotes
            $scroll_tracker_thresholds = str_replace( '"', '', $scroll_tracker_thresholds );
            // remove single quotes
            $scroll_tracker_thresholds = str_replace( "'", '', $scroll_tracker_thresholds );
            
            if ( !Validations::is_scroll_tracker_thresholds( $scroll_tracker_thresholds ) ) {
                $input['general']['scroll_tracker_thresholds'] = ( isset( $this->options['general']['scroll_tracker_thresholds'] ) ? $this->options['general']['scroll_tracker_thresholds'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-scroll-tracker-thresholds', esc_html__( 'You have entered the Scroll Tracker thresholds in the wrong format. It must be a list of comma separated percentages, like this "25,50,75,100"', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            } else {
                // If $scroll_tracker_thresholds not empty string error log
                
                if ( '' !== $scroll_tracker_thresholds ) {
                    $input['general']['scroll_tracker_thresholds'] = explode( ',', $scroll_tracker_thresholds );
                } else {
                    $input['general']['scroll_tracker_thresholds'] = [];
                }
            
            }
        
        }
        
        // Validate the subscription value multiplier
        
        if ( isset( $input['shop']['subscription_value_multiplier'] ) ) {
            // Remove all spaces and newlines
            $input['shop']['subscription_value_multiplier'] = Helpers::settings_trim( $input['shop']['subscription_value_multiplier'] );
            
            if ( !Validations::is_subscription_value_multiplier( $input['shop']['subscription_value_multiplier'] ) ) {
                $input['shop']['subscription_value_multiplier'] = ( isset( $this->options['shop']['subscription_value_multiplier'] ) ? $this->options['shop']['subscription_value_multiplier'] : 1 );
                add_settings_error( 'wgact_plugin_options', 'invalid-subscription-value-multiplier', esc_html__( 'You have entered an invalid subscription value multiplier. It must be a number and at least 1.00', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
            
            // Count decimal places
            $decimal_places = strlen( substr( strrchr( $input['shop']['subscription_value_multiplier'], '.' ), 1 ) );
            $input['shop']['subscription_value_multiplier'] = wc_format_decimal( $input['shop']['subscription_value_multiplier'], max( $decimal_places, 2 ) );
        }
        
        // Validate the Google Ads Conversion Adjustments Conversion Name
        if ( isset( $input['google']['ads']['conversion_adjustments']['conversion_name'] ) ) {
            
            if ( !Validations::is_conversion_adjustments_conversion_name( $input['google']['ads']['conversion_adjustments']['conversion_name'] ) ) {
                $input['google']['ads']['conversion_adjustments']['conversion_name'] = ( isset( $this->options['google']['ads']['conversion_adjustments']['conversion_name'] ) ? $this->options['google']['ads']['conversion_adjustments']['conversion_name'] : '' );
                add_settings_error( 'wgact_plugin_options', 'invalid-conversion-adjustments-conversion-name', esc_html__( 'You have entered an invalid conversion name. Special characters, quotes and single quotes are not allowed due to security reasons.', 'woocommerce-google-adwords-conversion-tracking-tag' ) );
            }
        
        }
        self::deduplication_check( $input );
        /**
         * Merging with the existing options and overwriting old values
         * since disabling a checkbox doesn't send a value,
         * we need to set one to overwrite the old value
         */
        return array_replace_recursive( $this->non_form_keys( $input ), $input );
    }
    
    private static function deduplication_check( $input )
    {
        // Check if deduplication has been turned off.
        // If so, set an action with the action scheduler to automatically reactivate deduplication in 6 hours from now.
        
        if ( !$input['shop']['order_deduplication'] ) {
            
            if ( !as_next_scheduled_action( 'pmw_reactivate_duplication_prevention' ) ) {
                as_schedule_single_action( time() + 6 * HOUR_IN_SECONDS, 'pmw_reactivate_duplication_prevention' );
            } else {
                // If the action is already scheduled, update the timestamp to 6 hours from now.
                as_unschedule_all_actions( 'pmw_reactivate_duplication_prevention' );
                as_schedule_single_action( time() + 6 * HOUR_IN_SECONDS, 'pmw_reactivate_duplication_prevention' );
            }
        
        } else {
            // If set, remove the scheduled action for reactivating deduplication
            if ( as_next_scheduled_action( 'pmw_reactivate_duplication_prevention' ) ) {
                as_unschedule_action( 'pmw_reactivate_duplication_prevention' );
            }
        }
    
    }
    
    public function deduper_enable()
    {
        $this->options['shop']['order_deduplication'] = true;
        update_option( 'wgact_plugin_options', $this->options );
    }
    
    // Recursively go through the array and merge (overwrite old values with new ones
    // if a value is missing in the input array, set it to value zero in the options array
    // Omit key like 'db_version' since they would be overwritten with zero.
    protected function merge_options( $array_existing, $array_input )
    {
        $array_output = [];
        foreach ( $array_existing as $key => $value ) {
            
            if ( array_key_exists( $key, $array_input ) ) {
                
                if ( is_array( $value ) ) {
                    $array_output[$key] = $this->merge_options( $value, $array_input[$key] );
                } else {
                    $array_output[$key] = $array_input[$key];
                }
            
            } else {
                
                if ( is_array( $value ) ) {
                    $array_output[$key] = $this->set_array_value_to_zero( $value );
                } else {
                    $array_output[$key] = 0;
                }
            
            }
        
        }
        return $array_output;
    }
    
    /**
     * Place here what could be overwritten when a form field is missing
     * and what should not be re-set to the default value
     * but should be preserved
     */
    protected function non_form_keys( $input )
    {
        $non_form_keys = [
            'db_version' => $this->options['db_version'],
            'shop'       => [
            'disable_tracking_for' => [],
        ],
            'google'     => [
            'analytics' => [
            'ga4' => [
            'data_api' => [
            'credentials' => $this->options['google']['analytics']['ga4']['data_api']['credentials'],
        ],
        ],
        ],
        ],
        ];
        // in case the form field input is missing
        //        if (!array_key_exists('google_business_vertical', $input['google']['ads'])) {
        //            $non_form_keys['google']['ads']['google_business_vertical'] = $this->options['google']['ads']['google_business_vertical'];
        //        }
        return $non_form_keys;
    }
    
    private function set_array_value_to_zero( $array )
    {
        array_walk_recursive( $array, function ( &$leafnode ) {
            $leafnode = 0;
        } );
        return $array;
    }
    
    private function pro_version_demo_active()
    {
        
        if ( $this->options['general']['pro_version_demo'] ) {
            return true;
        } else {
            return false;
        }
    
    }

}