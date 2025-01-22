<?php

/**
 * Plugin Name:          Pixel Manager for WooCommerce
 * Description:          Visitor and conversion value tracking for WooCommerce. Highly optimized for data accuracy.
 * Author:               SweetCode
 * Plugin URI:           https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/
 * Author URI:           https://sweetcode.com
 * Developer:            SweetCode
 * Developer URI:        https://sweetcode.com
 * Text Domain:          woocommerce-google-adwords-conversion-tracking-tag
 * Domain path:          /languages
 * * Version:              1.30.3
 *
 * WC requires at least: 3.7
 * WC tested up to:      7.3
 *
 * License:              GNU General Public License v3.0
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/
const  WPM_CURRENT_VERSION = '1.30.3' ;
// TODO add option checkbox on uninstall and ask if user wants to delete options from db

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

use  WCPM\Classes\Admin\Admin ;
use  WCPM\Classes\Admin\Admin_REST ;
use  WCPM\Classes\Admin\Debug_Info ;
use  WCPM\Classes\Admin\Environment ;
use  WCPM\Classes\Admin\Notifications ;
use  WCPM\Classes\Admin\Order_Columns ;
use  WCPM\Classes\Database ;
use  WCPM\Classes\Deprecated_Filters ;
use  WCPM\Classes\Helpers ;
use  WCPM\Classes\Pixels\Pixel_Manager ;
use  WCPM\Classes\Options ;
use  WCPM\Classes\Shop ;
use  WCPM\Classes\Admin\Ask_For_Rating ;

if ( function_exists( 'wpm_fs' ) ) {
    wpm_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'wpm_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wpm_fs()
        {
            global  $wpm_fs ;
            
            if ( !isset( $wpm_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_7498_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_7498_MULTISITE', false );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
                $wpm_fs = fs_dynamic_init( [
                    'navigation'     => 'tabs',
                    'id'             => '7498',
                    'slug'           => 'woocommerce-google-adwords-conversion-tracking-tag',
                    'premium_slug'   => 'pixel-manager-pro-for-woocommerce',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_d4182c5e1dc92c6032e59abbfdb91',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => [
                    'days'               => 14,
                    'is_require_payment' => true,
                ],
                    'menu'           => [
                    'slug'           => 'wpm',
                    'override_exact' => true,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => [
                    'slug' => 'woocommerce',
                ],
                ],
                    'is_live'        => true,
                ] );
            }
            
            return $wpm_fs;
        }
        
        // Init Freemius.
        wpm_fs();
        // Signal that SDK was initiated.
        do_action( 'wpm_fs_loaded' );
        function wpm_fs_settings_url()
        {
            
            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                return admin_url( 'admin.php?page=wpm&section=main&subsection=google-ads' );
            } else {
                return admin_url( 'options-general.php?page=wpm&section=main&subsection=google-ads' );
            }
        
        }
        
        wpm_fs()->add_filter( 'connect_url', 'wpm_fs_settings_url' );
        wpm_fs()->add_filter( 'after_skip_url', 'wpm_fs_settings_url' );
        wpm_fs()->add_filter( 'after_connect_url', 'wpm_fs_settings_url' );
        wpm_fs()->add_filter( 'after_pending_connect_url', 'wpm_fs_settings_url' );
    }
    
    class WCPM
    {
        protected  $options ;
        public function __construct()
        {
            define( 'WPM_PLUGIN_PREFIX', 'wpm_', false );
            define( 'WPM_DB_VERSION', '3', false );
            define( 'WPM_DB_OPTIONS_NAME', 'wgact_plugin_options', false );
            define( 'WPM_DB_NOTIFICATIONS_NAME', 'wgact_notifications', false );
            define( 'WPM_PLUGIN_DIR_PATH', plugin_dir_url( __FILE__ ), false );
            define( 'WPM_PLUGIN_BASENAME', plugin_basename( __FILE__ ), false );
            define( 'WPM_DB_RATINGS', 'wgact_ratings', false );
            require_once dirname( __FILE__ ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
            // check if WooCommerce is running
            // currently this is the most reliable test for single and multisite setups
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            
            if ( $this->are_requirements_met() ) {
                // autoloader
                require_once 'lib/autoload.php';
                if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
                    require __DIR__ . '/vendor/autoload.php';
                }
                $this->setup_freemius_environment();
                // running the DB updater
                if ( get_option( WPM_DB_OPTIONS_NAME ) ) {
                    Database::run_options_db_upgrade();
                }
                // load the options
                $this->wpm_options_init();
                // run environment workflows
                add_action( 'admin_notices', [ $this, 'show_admin_notifications' ] );
                Environment::get_instance()->disable_third_party_plugin_features();
                if ( $this->options['general']['maximum_compatibility_mode'] ) {
                    Environment::get_instance()->enable_compatibility_mode();
                }
                Environment::get_instance()->flush_cache_on_plugin_changes();
                register_activation_hook( __FILE__, [ $this, 'plugin_activated' ] );
                register_deactivation_hook( __FILE__, [ $this, 'plugin_deactivated' ] );
                register_deactivation_hook( __FILE__, function () {
                    $timestamp = wp_next_scheduled( 'pmw_tracking_accuracy_analysis' );
                    wp_unschedule_event( $timestamp, 'pmw_tracking_accuracy_analysis' );
                } );
                Deprecated_Filters::load_deprecated_filters();
                
                if ( $this->is_woocommerce_active() ) {
                    add_action( 'before_woocommerce_init', function () {
                        if ( wp_doing_ajax() ) {
                            return;
                        }
                        if ( class_exists( 'Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) && method_exists( 'Automattic\\WooCommerce\\Utilities\\FeaturesUtil', 'declare_compatibility' ) ) {
                            // TODO: https://app.asana.com/0/1110999795232049/1203086190142026
                            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WPM_PLUGIN_BASENAME, true );
                        }
                    } );
                    add_action(
                        'init',
                        [ $this, 'register_hooks_for_woocommerce' ],
                        10,
                        2
                    );
                    add_action(
                        'init',
                        [ $this, 'run_woocommerce_reports' ],
                        10,
                        2
                    );
                    add_action( 'woocommerce_init', [ $this, 'init' ] );
                } else {
                    add_action( 'init', [ $this, 'init' ] );
                }
            
            } else {
                add_action( 'admin_menu', [ $this, 'add_empty_admin_page' ], 99 );
                add_action( 'admin_notices', [ $this, 'requirements_error' ] );
            }
        
        }
        
        public function register_hooks_for_woocommerce()
        {
            add_action( 'pmw_reactivate_duplication_prevention', function () {
                Admin::get_instance()->deduper_enable();
            } );
            add_action( 'pmw_tracking_accuracy_analysis', function () {
                Debug_Info::get_instance()->run_tracking_accuracy_analysis();
            } );
        }
        
        public function run_woocommerce_reports()
        {
            if ( wp_doing_ajax() ) {
                return;
            }
            // Don't run on the frontend
            if ( !is_admin() ) {
                return;
            }
            // Necessary as some older implementation had a bug that would cause the action to be scheduled multiple times
            // TODO Remove end of 2023
            $this->fix_scheduled_multiple_actions_for_pmw_tracking_accuracy_analysis();
            // Only run reports if the Pixel Manager settings are being accessed
            if ( !Environment::get_instance()->is_pmw_settings_page() ) {
                return;
            }
            // Schedule the tracking accuracy analysis
            if ( !wp_next_scheduled( 'pmw_tracking_accuracy_analysis' ) ) {
                wp_schedule_event( strtotime( '03:25:00' ), 'daily', 'pmw_tracking_accuracy_analysis' );
            }
            // If the tracking accuracy has not been run yet, run it immediately in the background.
            // https://github.com/woocommerce/action-scheduler/issues/839
            if ( function_exists( 'as_enqueue_async_action' ) && function_exists( 'as_has_scheduled_action' ) && !as_has_scheduled_action( 'pmw_tracking_accuracy_analysis' ) && !get_transient( 'pmw_tracking_accuracy_analysis' ) ) {
                as_enqueue_async_action( 'pmw_tracking_accuracy_analysis' );
            }
        }
        
        protected function fix_scheduled_multiple_actions_for_pmw_tracking_accuracy_analysis()
        {
            if ( $this->is_pmw_tracking_accuracy_analysis_scheduled_more_than_once() ) {
                as_unschedule_all_actions( 'pmw_tracking_accuracy_analysis' );
            }
        }
        
        protected function is_pmw_tracking_accuracy_analysis_scheduled_more_than_once()
        {
            $as_args = [
                'hook'   => 'pmw_tracking_accuracy_analysis',
                'status' => ActionScheduler_Store::STATUS_PENDING,
            ];
            return count( as_get_scheduled_actions( $as_args, 'ids' ) ) > 1;
        }
        
        protected function is_woocommerce_active()
        {
            return is_plugin_active( 'woocommerce/woocommerce.php' );
        }
        
        protected function are_requirements_met()
        {
            
            if ( $this->is_wpm_woocommerce_requirement_disabled() ) {
                return true;
            } else {
                return $this->is_woocommerce_active();
            }
        
        }
        
        protected function is_wpm_woocommerce_requirement_disabled()
        {
            
            if ( defined( 'WPM_EXPERIMENTAL_DISABLE_WOOCOMMERCE_REQUIREMENT' ) && true === WPM_EXPERIMENTAL_DISABLE_WOOCOMMERCE_REQUIREMENT ) {
                return true;
            } else {
                return false;
            }
        
        }
        
        public function add_empty_admin_page()
        {
            add_submenu_page(
                'woocommerce',
                esc_html__( 'Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                esc_html__( 'Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                'manage_options',
                'wpm',
                function () {
            }
            );
        }
        
        // https://github.com/iandunn/WordPress-Plugin-Skeleton/blob/master/views/requirements-error.php
        public function requirements_error()
        {
            ?>

			<div class="error">
				<p>
					<strong>
						<?php 
            esc_html_e( 'Pixel Manager for WooCommerce error', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
					</strong>:
					<?php 
            esc_html_e( "Your environment doesn't meet all the system requirements listed below.", 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				</p>

				<ul class="ul-disc">
					<li><?php 
            esc_html_e( 'The WooCommerce plugin needs to be activated', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?></li>
				</ul>
			</div>
			<style>
                .fs-tab {
                    display: none !important;
                }
			</style>

			<?php 
        }
        
        public function plugin_activated()
        {
            Environment::get_instance()->flush_cache_of_all_cache_plugins();
        }
        
        public function plugin_deactivated()
        {
            Environment::get_instance()->flush_cache_of_all_cache_plugins();
        }
        
        public function environment_check_admin_notices()
        {
            //			if (apply_filters('wpm_show_admin_alerts', apply_filters_deprecated('wooptpm_show_admin_alerts', [true], '1.13.0', 'wpm_show_admin_alerts'))) {
            //				// Add admin alerts that can be disabled by the user with a filter
            //			}
            // https://developer.wordpress.org/reference/hooks/admin_notices/#comment-5163
            //			if (defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES) {
            //				// do some stuff
            //			}
        }
        
        // startup all functions
        public function init()
        {
            Admin_REST::get_instance();
            
            if ( is_admin() ) {
                // display admin views
                Admin::get_instance();
                // ask visitor for rating
                Ask_For_Rating::get_instance();
                // Load admin notification handlers
                Notifications::get_instance();
                // Show PMW information on the order list page
                if ( Environment::get_instance()->is_woocommerce_active() && $this->options['shop']['order_list_info'] ) {
                    Order_Columns::get_instance();
                }
                // add a settings link on the plugins page
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wpm_settings_link' ] );
            }
            
            Deprecated_Filters::load_deprecated_filters();
            // inject pixels into front end
            //			add_action('after_setup_theme', [$this, 'inject_pixels']);
            $this->inject_pixels();
        }
        
        public function inject_pixels()
        {
            // TODO Remove the cookie prevention filters by January 2023
            $cookie_prevention = apply_filters_deprecated(
                'wgact_cookie_prevention',
                [ false ],
                '1.10.4',
                'wooptpm_cookie_prevention'
            );
            $cookie_prevention = apply_filters_deprecated(
                'wooptpm_cookie_prevention',
                [ $cookie_prevention ],
                '1.12.1',
                '',
                'This filter has been replaced by a much more robust cookie consent handing in the plugin. Please read more about it in the documentation.'
            );
            if ( false === $cookie_prevention ) {
                // inject pixels
                Pixel_Manager::get_instance();
            }
        }
        
        public function show_admin_notifications()
        {
            //			Notifications::payment_gateway_accuracy_warning();
            /**
             * Run compatibility checks for the admin
             */
            Environment::get_instance()->run_checks();
            /**
             * Check for incompatible plugins
             */
            Environment::get_instance()->run_incompatible_plugins_checks();
            /**
             * Show admin notices
             */
            //			if (apply_filters('wpm_show_admin_alerts', apply_filters_deprecated('wooptpm_show_admin_alerts', [true], '1.13.0', 'wpm_show_admin_alerts'))) {
            //				// Add admin alerts that can be disabled by the user with a filter
            //			}
            //
            //			https://developer.wordpress.org/reference/hooks/admin_notices/#comment-5163
            //			if (defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES) {
            //				// do some stuff
            //			}
        }
        
        // initialise the options
        private function wpm_options_init()
        {
            // set options equal to defaults
            $this->options = get_option( WPM_DB_OPTIONS_NAME );
            
            if ( false === $this->options ) {
                // if no options have been set yet, initiate default options
                $this->options = Options::get_default_options();
            } else {
                // Check if each single option has been set. If not, set them. That is necessary when new options are introduced.
                // add new default options to the options db array
                $this->options = Options::update_with_defaults( $this->options, Options::get_default_options() );
            }
            
            update_option( WPM_DB_OPTIONS_NAME, $this->options );
        }
        
        /**
         * Adds a link on the plugins page for the settings
         * ! It can't be required. Must be in the main plugin file!
         */
        public function wpm_settings_link( $links )
        {
            
            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                $admin_page = 'admin.php';
            } else {
                $admin_page = 'options-general.php';
            }
            
            $links[] = '<a href="' . admin_url( $admin_page . '?page=wpm' ) . '">Settings</a>';
            return $links;
        }
        
        protected function setup_freemius_environment()
        {
            wpm_fs()->add_filter( 'show_trial', function () {
                
                if ( $this->is_development_install() ) {
                    return false;
                } else {
                    return apply_filters( 'wpm_show_admin_trial_promo', apply_filters_deprecated(
                        'wooptpm_show_admin_trial_promo',
                        [ true ],
                        '1.13.0',
                        'wpm_show_admin_trial_promo'
                    ) ) && apply_filters( 'wpm_show_admin_notifications', apply_filters_deprecated(
                        'wooptpm_show_admin_notifications',
                        [ true ],
                        '1.13.0',
                        'wpm_show_admin_notifications'
                    ) );
                }
            
            } );
            // re-show trial message after n seconds
            wpm_fs()->add_filter( 'reshow_trial_after_every_n_sec', function () {
                return MONTH_IN_SECONDS * 6;
            } );
        }
        
        protected function is_development_install()
        {
            
            if ( class_exists( 'FS_Site' ) ) {
                return FS_Site::is_localhost_by_address( get_site_url() );
            } else {
                return false;
            }
        
        }
    
    }
    new WCPM();
}
