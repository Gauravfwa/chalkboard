<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Feed_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The ID of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string $plugin_basename The ID of this plugin.
     */
    private $plugin_basename;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Cron Handler
     *
     * @since    1.3.2
     * @access   private
     * @var      object $cron The current cron of this plugin.
     */
    private $cron;

    /**
     * Google merchant page
     *
     * @since    1.3.2
     * @access   private
     * @var      string
     */
    private $google_screen_hook_suffix = null;

    /**
     * Category Mapping page
     *
     * @since    1.3.2
     * @access   private
     * @var      string
     */
    private $category_mapping_screen_hook_suffix = null;

    /**
     * Dashboard
     *
     * @since    1.3.2
     * @access   private
     * @var      string
     */
    private $dashboard_screen_hook_suffix = null;

    /**
     * WPFM pro
     *
     * @since    3.0
     * @access   private
     * @var      string
     */
    private $wpfm_pro_submenu = null;

    /**
     * Setup Wizard menu
     *
     * @since    7.3.0
     * @access   private
     * @var      string
     */
    private $setup_wizard_hook_suffix = null;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name     = $plugin_name;
        $this->plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_name . '.php' );
        $this->version         = $version;
        $this->cron            = new Rex_Feed_Scheduler();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @param string $hook Hook.
     *
     * @since    1.0.0
     */
    public function enqueue_styles( $hook ) {

        // Global CSS file.
        wp_enqueue_style( $this->plugin_name . '-global', WPFM_PLUGIN_ASSETS_FOLDER . 'css/global.css', array(), $this->version );

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Rex_Product_Feed_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Rex_Product_Feed_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();
        if ( 'edit.php' === $hook ) {
            return;
        }
        $pages = array( $this->category_mapping_screen_hook_suffix, $this->dashboard_screen_hook_suffix, $this->google_screen_hook_suffix, $this->setup_wizard_hook_suffix, $this->wpfm_pro_submenu );
        $pages = apply_filters( 'wpfm_page_hooks', $pages );
        if ( 'product-feed' === $screen->post_type || in_array( $screen->id, $pages, true ) ) {
            wp_enqueue_style( $this->plugin_name . '-font-awesome', WPFM_PLUGIN_ASSETS_FOLDER . 'css/font-awesome.min.css', array(), $this->version );
            wp_enqueue_style( $this->plugin_name . '-wpfm-vendor', WPFM_PLUGIN_ASSETS_FOLDER . 'css/vendor.min.css', array(), $this->version );
            wp_enqueue_style( $this->plugin_name . '-select2', WPFM_PLUGIN_ASSETS_FOLDER . 'css/select2.min.css', array(), $this->version );

            $_get = rex_feed_get_sanitized_get_post();
            $_get = !empty( $_get[ 'get' ] ) ? $_get[ 'get' ] : array();

            if ( !empty( $_get ) && isset( $_get[ 'tour_guide' ] ) && 1 === (int) $_get[ 'tour_guide' ] ) {
                wp_enqueue_style( $this->plugin_name . '-shepherd', WPFM_PLUGIN_ASSETS_FOLDER . 'css/shepherd.css', array(), $this->version );
            }

            wp_enqueue_style( $this->plugin_name . '-style-css', WPFM_PLUGIN_ASSETS_FOLDER . 'css/style.css', array(), $this->version );
            wp_style_add_data( $this->plugin_name . '-style-css', 'rtl', 'replace' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @param string $hook Hook.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook ) {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Rex_Product_Feed_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Rex_Product_Feed_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $db_version = get_option( 'rex_wpfm_db_version' );
        $data       = function_exists( 'rex_feed_get_sanitized_get_post' ) ? rex_feed_get_sanitized_get_post() : array();
        $get_data   = !empty( $data[ 'get' ] ) ? $data[ 'get' ] : array();
        if ( $db_version < 3 ) {
            $current_screen = get_current_screen();

            if ( gettype( $current_screen ) === 'object' && property_exists( $current_screen, 'base' ) && property_exists( $current_screen, 'post_type' ) ) {
                if ( 'post' === $current_screen->base && 'product-feed' === $current_screen->post_type ) {
                    if ( 'add' === $current_screen->action ) {
                        $current_screen = 'add';
                    } elseif ( isset( $get_data[ 'action' ] ) && 'edit' === $get_data[ 'action' ] ) {
                        $current_screen = 'rex_feed_edit';
                    } else {
                        $current_screen = '';
                    }
                } elseif ( 'product-feed_page_wpfm_dashboard' === $current_screen->base ) {
                    $current_screen = $current_screen->base;
                }
            } else {
                $current_screen = '';
            }

            wp_enqueue_script( 'rex-wpfm-global-js', WPFM_PLUGIN_ASSETS_FOLDER . 'js/rex-product-feed-global-admin.js', array( 'jquery' ), $this->version, true );
            $wp_time_zone = new DateTimeZone( wp_timezone_string() );
            $current_date = new DateTime( 'now', $wp_time_zone );

            wp_localize_script(
                'rex-wpfm-global-js',
                'rex_wpfm_ajax',
                array(
                    'ajax_url'             => admin_url( 'admin-ajax.php' ),
                    'ajax_nonce'           => wp_create_nonce( 'rex-wpfm-ajax' ),
                    'is_premium'           => apply_filters( 'wpfm_is_premium', false ),
                    'feed_id'              => get_the_ID(),
                    'category_mapping_url' => admin_url( 'admin.php?page=category_mapping' ),
                    'current_screen'       => $current_screen,
                    'current_date'         => $current_date->format( 'm/d/Y H:i:s' ),
                )
            );
        }

        $screen = get_current_screen();
        if ( 'edit.php' === $hook ) {
            return;
        }
        $pages = array( $this->dashboard_screen_hook_suffix, $this->google_screen_hook_suffix, $this->wpfm_pro_submenu, $this->setup_wizard_hook_suffix );
        $pages = apply_filters( 'wpfm_page_hooks', $pages );
        if ( 'product-feed' === $screen->post_type || in_array( $screen->id, $pages, true ) ) {
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_script(
                'jquery-stop-watch',
                WPFM_PLUGIN_ASSETS_FOLDER . 'js/jquery.stopwatch.js',
                array( 'jquery' ),
                $this->version,
                true
            );
            wp_enqueue_script(
                'jquery-nice-select',
                WPFM_PLUGIN_ASSETS_FOLDER . 'js/jquery.nice-select.min.js',
                array( 'jquery' ),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->plugin_name . '-select2',
                WPFM_PLUGIN_ASSETS_FOLDER . 'js/select2.min.js',
                array( 'jquery' ),
                $this->version
            );
            wp_enqueue_script(
                $this->plugin_name,
                WPFM_PLUGIN_ASSETS_FOLDER . 'js/rex-product-feed-admin.js',
                array( 'jquery' ),
                $this->version,
                true
            );
            wp_localize_script(
                $this->plugin_name,
                'rex_wpfm_admin_translate_strings',
                array(
                    'google_cat_map_btn'    => __( 'Configure Category Mapping', 'rex-product-feed' ),
                    'optimize_pr_title_btn' => __( 'Optimize Product Title', 'rex-product-feed' ),
                )
            );
            wp_enqueue_script(
                'jquery-cookie',
                WPFM_PLUGIN_ASSETS_FOLDER . 'js/js.cookie.min.js',
                array( 'jquery' ),
                $this->version,
                true
            );

            $_get = rex_feed_get_sanitized_get_post();
            $_get = !empty( $_get[ 'get' ] ) ? $_get[ 'get' ] : array();

            if ( !empty( $_get ) && isset( $_get[ 'tour_guide' ] ) && 1 === (int) $_get[ 'tour_guide' ] ) {
                wp_enqueue_script(
                    $this->plugin_name . '-shepherd',
                    WPFM_PLUGIN_ASSETS_FOLDER . 'js/shepherd.min.js',
                    array( 'jquery' ),
                    $this->version,
                    true
                );
                wp_enqueue_script(
                    $this->plugin_name . '-on-boarding',
                    WPFM_PLUGIN_ASSETS_FOLDER . 'js/rex-product-feed-on-boarding.js',
                    array( 'jquery', $this->plugin_name . '-shepherd' ),
                    $this->version,
                    true
                );
            }
        }

        if ( $screen->id === $this->category_mapping_screen_hook_suffix ) {
            wp_enqueue_script(
                'category-map',
                WPFM_PLUGIN_ASSETS_FOLDER . 'js/category-mapper.js',
                array( 'jquery', 'jquery-ui-autocomplete' ),
                $this->version,
                true
            );
            wp_localize_script(
                'category-map',
                'rex_wpfm_cat_map_translate_strings',
                array(
                    'update_btn'           => __( 'Update', 'rex-product-feed' ),
                    'update_and_close_btn' => __( 'Update & Close', 'rex-product-feed' ),
                    'delete_btn'           => __( 'Delete', 'rex-product-feed' ),
                )
            );
        }
    }

    /**
     * Register Plugin Admin Pages
     *
     * @since    1.0.0
     */
    public function load_admin_pages() {
        $this->category_mapping_screen_hook_suffix = add_submenu_page(
            'edit.php?post_type=product-feed',
            __( 'Category Mapping', 'rex-product-feed' ),
            __( 'Category Mapping', 'rex-product-feed' ),
            'manage_woocommerce',
            'category_mapping',
            function() {
                require_once plugin_dir_path( __FILE__ ) . '/partials/category_mapping.php';
            }
        );
        $this->google_screen_hook_suffix           = add_submenu_page(
            'edit.php?post_type=product-feed',
            __( 'Google Merchant Settings', 'rex-product-feed' ),
            __( 'Google Merchant Settings', 'rex-product-feed' ),
            'manage_woocommerce',
            'merchant_settings',
            function() {
                require_once plugin_dir_path( __FILE__ ) . '/partials/merchant_settings.php';
            }
        );
        $this->dashboard_screen_hook_suffix        = add_submenu_page(
            'edit.php?post_type=product-feed',
            __( 'Settings', 'rex-product-feed' ),
            __( 'Settings', 'rex-product-feed' ),
            'manage_woocommerce',
            'wpfm_dashboard',
            function() {
                require_once plugin_dir_path( __FILE__ ) . '/partials/on_boarding.php';
            }
        );
        $is_premium                                = apply_filters( 'wpfm_is_premium_activate', false );
        add_submenu_page( 'edit.php?post_type=product-feed', __( 'Support', 'rex-product-feed' ), '<span id="rex-feed-support-submenu">' . __( 'Support', 'rex-product-feed' ) . '</span>', 'manage_woocommerce', esc_url( 'https://wordpress.org/support/plugin/best-woocommerce-feed/#new-topic-0' ) );

        if ( !$is_premium ) {
            $this->wpfm_pro_submenu = add_submenu_page(
                'edit.php?post_type=product-feed',
                '',
                '<span id="rex-feed-gopro-submenu" class="dashicons dashicons-star-filled" style="font-size: 17px; color:#1fb3fb;"></span> ' . __( 'Go Pro', 'rex-product-feed' ),
                'manage_woocommerce',
                esc_url( 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=go_pro_button&utm_medium=plugin&utm_campaign=pfm_pro&utm_id=pfm_pro' )
            );
        } else {
            $this->wpfm_pro_submenu = apply_filters( 'rex_feed_license_submenu', array() );
        }

        $this->setup_wizard_hook_suffix = add_submenu_page(
            'edit.php?post_type=product-feed',
            __( 'Get Started', 'rex-product-feed' ),
            __( 'Get Started', 'rex-product-feed' ),
            'manage_woocommerce',
            'setup-wizard',
            function() {
                require_once plugin_dir_path( __FILE__ ) . '/partials/setup-wizard.php';
            },
            10
        );

        // PFM actions.
        add_filter( 'plugin_action_links_' . $this->plugin_basename, array( new Rex_Product_Feed_Actions(), 'plugin_action_links' ) );
    }

    /**
     * Modifies the placeholder text for the title field on the 'product-feed' post type editor screen.
     * Checks the current screen's post type, and if it matches 'product-feed', changes the title placeholder.
     * Returns the updated placeholder text.
     *
     * @since 7.3.19
     */
    public function change_feed_title_placeholder( $title ) {
        $screen = get_current_screen();
        if ( 'product-feed' == $screen->post_type ) {
            $title = 'Enter your feed title';
        }
        return $title;
    }

    /**
     * Admin Footer Styles
     *
     * @return void
     */
    public function rex_admin_footer_style() {
        echo '<style>

                .wpfm-bf-wrapper {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    max-width: 1510px;
                    margin: 0 auto;
                }
                .wpfm-bf-wrapper .wpfm-logo,
                .wpfm-bf-wrapper .wpfm-bf-button{
                    flex: 0 0 25%;
                    margin: 10px;
                }
                .wpfm-bf-wrapper .wpfm-bf-text{
                    flex: 0 0 40%;
                }
                .wpfm-bf-text p,
                .wpfm-bf-text h3{
                    color: #fff;
                    
                }
                .wpfm-bf-text p{
                    font-size: 18px;
                    margin: 0;
                }
                
                .wpfm-bf-text h3{
                    font-size: 32px;
                    font-weight: 700;
                    margin: 15px 0;
                    line-height: 1.1;
                }
                .wpfm-bf-button p {
                    font-size: 18px;
                    color: #fff;
                    margin-bottom: 25px;
                } 
                .wpfm-bf-button a {
                    background-color: #fff;
                    padding: 10px 20px;
                    color: #00b4ff;
                    font-size: 30px;
                    border-radius: 4px;
                    margin: 15px 0;
                    text-decoration: none;
                }
                p.wpfm-bf-coupon {
                    margin-top: 25px;
                }
                
                
                .wpfm-black-friday-notice {
                    position: relative;
                    padding: 0;
                    margin: 0!important;
                    border: none;
                    background: transparent;
                    box-shadow: none;
                }
                .wpfm-black-friday-notice img{
                    display: block;
                    max-width: 100%;
                }
                .wpfm-black-friday-notice .notice-dismiss {
                    top: 8px;
                    right: 10px;
                    padding: 0;
                }
                .wpfm-black-friday-notice .notice-dismiss:before {
                    color: #fff;
                    font-size: 22px;
                }
                @media  (max-width: 1199px) {
                    .wpfm-bf-wrapper {
                        flex-direction: column;
                        text-align: center;
                        padding-top: 20px;
                    }
                  .wpfm-bf-wrapper .wpfm-logo,
                    .wpfm-bf-wrapper .wpfm-bf-button{
                        flex: 0 0 100%;
                    }
                    .wpfm-bf-wrapper .wpfm-bf-text{
                        flex: 0 0 100%;
                    }
                }
                .wpfm-db-update-loader {
                  display: none;
                  width: 20px;
                  height: 20px;
                }
                .blink span {
                  font-size: 35px;
                  animation-name: blink;
                  animation-duration: 1.4s;
                  animation-iteration-count: infinite;
                  animation-fill-mode: both;
                }
                
                .blink span:first-child {
                  margin-left: 5px;
                }
                
                .blink span:nth-child(2) {
                  animation-delay: .2s;
                }
                
                .blink span:nth-child(3) {
                  animation-delay: .4s;
                }
                
                @keyframes blink {
                  0% {
                    opacity: .2;
                  }
                  20% {
                    opacity: 1;
                  }
                  100% {
                    opacity: .2;
                  }
                }
                #woocommerce-product-data ul.wc-tabs li.wpfm_wc_custom_tabs a:before { 
                    font-family: WooCommerce; 
                    content: \'\e006\'; 
                 }
                 #wpfm_product_meta strong{
                    color: #1FB3FB;
                    padding: 10px;
                 }
                .bwfm-review-notice {
                  display: flex;
                  flex-flow: row wrap;
                  align-items: center;
                  padding: 20px; }
                  .bwfm-review-notice .wpfm-logo {
                    width: 80px; }
                    .bwfm-review-notice .wpfm-logo img {
                      display: block; }
                  .bwfm-review-notice .wpfm-notice-content {
                    width: calc(100% - 110px);
                    padding-left: 30px; }
                    .bwfm-review-notice .wpfm-notice-content .wpfm-notice-title {
                      font-size: 24px;
                      color: #222; }

            .rextheme-black-friday-offer {
                padding: 0!important;
                border: 0;
            }
            .rextheme-black-friday-offer img {
                display: block;
                width: 100%;
            }
            .rextheme-black-friday-offer .notice-dismiss {
                top: 4px;
                right: 6px;
                padding: 4px;
                background: #fff;
                border-radius: 100%;
            }
            .rextheme-black-friday-offer .notice-dismiss:before {
                content: "\f335";
                font-size: 20px;
            }
        </style>';
    }

    /**
     * Loads custom styles for setup wizard
     *
     * @return void
     * @since 7.2.5
     */
    public function load_custom_styles() {
        if ( !is_plugin_active( 'best-woocommerce-feed-pro/rex-product-feed-pro.php' ) ) {
            ?>
            <style>

                .rex-setup-wizard-cta-area {
                    padding: 370px 10px 125px;
                }

                @media (max-width: 1399px) {
                    .rex-setup-wizard-cta-area {
                        padding: 200px 10px 100px;
                    }
                }

                @media (max-width: 1199px) {
                    .rex-setup-wizard-cta-area {
                        padding: 150px 10px 100px;
                    }
                }

                @media (max-width: 991px) {
                    .rex-setup-wizard-cta-area {
                        padding: 120px 10px 80px;
                    }
                }
            </style>
            <?php
        } else {
            ?>
            <style>
                .rex-setup-wizard-cta-area {
                    padding: 125px 10px 125px;
                }

                @media (max-width: 1399px) {
                    .rex-setup-wizard-cta-area {
                        padding: 100px 10px 100px;
                    }
                }

                @media (max-width: 991px) {
                    .rex-setup-wizard-cta-area {
                        padding: 80px 10px 80px;
                    }
                }
            </style>
            <?php
        }
    }

    /**
     * Delete cached data for WooCommerce shipping methods.
     *
     * This function is designed to clear cached data related to WooCommerce shipping methods. It utilizes the wpfm_purge_cached_data function to perform the cleanup.
     *
     * @since 7.3.16
     */
    public function delete_shipping_transient() {
        if ( function_exists( 'wpfm_purge_cached_data' ) ) {
            // Use wpfm_purge_cached_data to remove cached data related to WooCommerce shipping methods.
            wpfm_purge_cached_data( 'wc_shipping_methods_', true );
        }
    }

    /**
     * Defines custom update messages for the 'product-feed' custom post type.
     *
     * @param array $messages An array of post updated messages.
     *
     * @return array Modified array containing custom messages for 'product-feed' post type updates.
     * @since 7.3.20
     */
    public function post_updated_messages( $messages ) {
        $messages[ 'product-feed' ] = [
            0  => '', // Unused. Messages start at index 1.
            1  => __( 'Product feed updated.', 'rex-product-feed' ),
            2  => __( 'Custom field updated.', 'rex-product-feed' ),
            3  => __( 'Custom field deleted.', 'rex-product-feed' ),
            4  => __( 'Product feed updated.', 'rex-product-feed' ),
            5  => __( 'Revision restored.', 'rex-product-feed' ),
            6  => __( 'Product feed published.', 'rex-product-feed' ),
            7  => __( 'Product feed saved.', 'rex-product-feed' ),
            8  => '',
            9  => '',
            10 => __( 'Product feed draft updated.', 'rex-product-feed' )
        ];

        return $messages;
    }
}