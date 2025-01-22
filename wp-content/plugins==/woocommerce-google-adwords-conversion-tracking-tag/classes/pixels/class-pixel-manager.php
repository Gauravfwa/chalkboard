<?php

namespace WCPM\Classes\Pixels;

use  Mpdf\Tag\Option ;
use  WCPM\Classes\Admin\Environment ;
use  WCPM\Classes\Admin\Validations ;
use  WCPM\Classes\Data\GA4_Data_API ;
use  WCPM\Classes\Helpers ;
use  WCPM\Classes\Options ;
use  WCPM\Classes\Shop ;
use  WCPM\Classes\Product ;
use  WCPM\Classes\Http\Facebook_CAPI ;
use  WCPM\Classes\Http\Google_MP ;
use  WCPM\Classes\Http\TikTok_EAPI ;
use  WCPM\Classes\Pixels\Facebook\Facebook_Microdata ;
use  WCPM\Classes\Pixels\Facebook\Facebook_Pixel_Manager ;
use  WCPM\Classes\Pixels\Google\Google ;
use  WCPM\Classes\Pixels\Google\Google_Pixel_Manager ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Pixel_Manager
{
    protected  $options ;
    protected  $options_obj ;
    protected  $cart ;
    protected  $facebook_active ;
    protected  $google_active ;
    protected  $google ;
    protected  $microdata_product_id ;
    protected  $order ;
    protected  $position = 1 ;
    protected  $rest_namespace = 'pmw/v1' ;
    protected  $gads_conversion_adjustments_route = '/google-ads/conversion-adjustments.csv' ;
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
        /**
         * Initialize options
         */
        $this->options = get_option( WPM_DB_OPTIONS_NAME );
        $this->options_obj = Helpers::get_options_object( $this->options );
        if ( function_exists( 'get_woocommerce_currency' ) ) {
            $this->options_obj->shop->currency = get_woocommerce_currency();
        }
        /**
         * Set a few states
         */
        $this->facebook_active = !empty($this->options_obj->facebook->pixel_id);
        //		$this->google_active   = $this->google_active();
        $this->google = new Google( $this->options );
        $this->google_active = $this->google->google_active();
        /**
         * Inject WPM snippets in head
         */
        add_action( 'wp_head', function () {
            $this->inject_wpm_opening();
            if ( wpm_fs()->can_use_premium_code__premium_only() && Environment::get_instance()->is_woocommerce_active() && is_product() ) {
                if ( $this->options_obj->facebook->microdata ) {
                    $this->microdata_product_id = ( new Facebook_Microdata( $this->options ) )->inject_schema( wc_get_product( get_the_ID() ) );
                }
            }
            // Add products to data layer from page transient
            
            if ( get_transient( 'pmw_products_for_datalayer_' . get_the_ID() ) ) {
                $products = get_transient( 'pmw_products_for_datalayer_' . get_the_ID() );
                $this->inject_products_from_transient_into_datalayer( $products );
            }
            
            $this->inject_data_layer();
        } );
        /**
         * Initialize all pixels
         */
        if ( $this->google_active ) {
            new Google_Pixel_Manager( $this->options );
        }
        if ( $this->facebook_active ) {
            new Facebook_Pixel_Manager( $this->options );
        }
        if ( wpm_fs()->can_use_premium_code__premium_only() && $this->is_tiktok_eapi_active__premium_only() ) {
            TikTok_EAPI::get_instance();
        }
        add_action( 'wp_head', function () {
            $this->inject_wpm_closing();
        } );
        /**
         * Front-end script section
         */
        if ( Shop::track_user() ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'wpm_front_end_scripts' ] );
        }
        add_action( 'wp_ajax_pmw_get_cart_items', [ $this, 'ajax_pmw_get_cart_items' ] );
        add_action( 'wp_ajax_nopriv_pmw_get_cart_items', [ $this, 'ajax_pmw_get_cart_items' ] );
        add_action( 'wp_ajax_pmw_get_product_ids', [ $this, 'ajax_pmw_get_product_ids' ] );
        add_action( 'wp_ajax_nopriv_pmw_get_product_ids', [ $this, 'ajax_pmw_get_product_ids' ] );
        add_action( 'wp_ajax_pmw_purchase_pixels_fired', [ $this, 'ajax_purchase_pixels_fired_handler' ] );
        add_action( 'wp_ajax_nopriv_pmw_purchase_pixels_fired', [ $this, 'ajax_purchase_pixels_fired_handler' ] );
        // Experimental filter ! Can be removed without further notification
        if ( apply_filters( 'wpm_experimental_defer_scripts', false ) ) {
            add_filter(
                'script_loader_tag',
                [ $this, 'experimental_defer_scripts' ],
                10,
                2
            );
        }
        /**
         * Inject pixel snippets after <body> tag
         */
        if ( did_action( 'wp_body_open' ) ) {
            add_action( 'wp_body_open', function () {
                $this->inject_body_pixels();
            } );
        }
        /**
         * Inject pixel snippets into wp_footer
         */
        add_action( 'wp_footer', [ $this, 'wpm_wp_footer' ] );
        /**
         * Process short codes
         */
        new Shortcodes( $this->options );
        
        if ( Environment::get_instance()->is_woocommerce_active() ) {
            add_action(
                'woocommerce_after_shop_loop_item',
                [ $this, 'action_woocommerce_after_shop_loop_item' ],
                10,
                1
            );
            add_filter(
                'woocommerce_blocks_product_grid_item_html',
                [ $this, 'wc_add_data_to_gutenberg_block' ],
                10,
                3
            );
            add_action( 'wp_head', [ $this, 'woocommerce_inject_product_data_on_product_page' ] );
            // do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
            add_action(
                'woocommerce_after_cart_item_name',
                [ $this, 'woocommerce_after_cart_item_name' ],
                10,
                2
            );
            add_action(
                'woocommerce_after_mini_cart_item_name',
                [ $this, 'woocommerce_after_cart_item_name' ],
                10,
                2
            );
            add_action( 'woocommerce_mini_cart_contents', [ $this, 'woocommerce_mini_cart_contents' ] );
            add_action( 'woocommerce_new_order', [ $this, 'wpm_woocommerce_new_order' ] );
        }
        
        /**
         * Run background processes
         */
        add_action( 'template_redirect', [ $this, 'run_background_processes' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        // When updating a page, delete any transient data set by the PM
        add_action(
            'save_post',
            [ $this, 'delete_pmw_products_transient' ],
            10,
            3
        );
    }
    
    public function delete_pmw_products_transient( $post_id, $post, $update )
    {
        if ( $update ) {
            delete_transient( 'pmw_products_for_datalayer_' . $post_id );
        }
    }
    
    public function inject_products_from_transient_into_datalayer( $products )
    {
        ?>
		<script>
			(window.wpmDataLayer = window.wpmDataLayer || {}).products = window.wpmDataLayer.products || {}
			window.wpmDataLayer.products                               = Object.assign(window.wpmDataLayer.products, <?php 
        echo  wp_json_encode( (object) $products ) ;
        ?>)
		</script>
		<?php 
    }
    
    public function get_google_ads_conversion_adjustments_endpoint()
    {
        /**
         * The regular /wp-json/ endpoint doesn't work if pretty permalinks are disabled.
         * https://developer.wordpress.org/rest-api/key-concepts/#routes-endpoints
         */
        return '/?rest_route=/' . $this->rest_namespace . $this->gads_conversion_adjustments_route;
    }
    
    // https://wordpress.stackexchange.com/a/377954/68337
    public function prepare_custom_rest_handlers(
        $served,
        $result,
        $request,
        $server
    )
    {
        //		error_log('prepare_custom_rest_responses');
        // error log $request->get_route();
        //		error_log($request->get_route());
        // If $request->get_route() is not /pmw/v1/google-ads/conversion-adjustments then return $served
        if ( strpos( $request->get_route(), $this->rest_namespace . $this->gads_conversion_adjustments_route ) === false ) {
            return $served;
        }
        //		if (strpos($request->get_route(), '/google-ads/conversion-adjustments/') !== 0) {
        //			return $served;
        //		}
        // Send headers
        // For production
        $server->send_header( 'Content-Type', 'text/csv' );
        // For testing
        //		$server->send_header('Content-Type', 'text/html');
        //		$server->send_header( 'Content-Type', 'text/xml' );
        //		$server->send_header( 'Content-Type', 'application/xml' );
        // Echo the XML that's returned by smg_feed().
        // Turn off phpcs because we're echoing the XML.
        esc_html_e( $result->get_data() );
        // And then exit.
        exit;
    }
    
    public function register_rest_routes()
    {
        /**
         * Testing endpoint which helps to verify if the REST API is working
         */
        register_rest_route( $this->rest_namespace, '/test/', [
            'methods'             => 'POST',
            'callback'            => function () {
            wp_send_json_success();
        },
            'permission_callback' => function () {
            return true;
        },
        ] );
        /**
         * Testing endpoint which helps to verify if the REST API is working
         */
        register_rest_route( $this->rest_namespace, '/test/', [
            'methods'             => 'GET',
            'callback'            => function () {
            wp_send_json_success();
        },
            'permission_callback' => function () {
            return true;
        },
        ] );
        register_rest_route( $this->rest_namespace, '/settings/', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'wpm_save_imported_settings' ],
            'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
        ] );
        /**
         * No nonce verification required as we only request public data
         * from the server.
         */
        register_rest_route( $this->rest_namespace, '/products/', [
            'methods'             => 'POST',
            'callback'            => function ( $request ) {
            $request_decoded = $request->get_json_params();
            $this->get_products_for_datalayer( $request_decoded );
        },
            'permission_callback' => function () {
            return true;
        },
        ] );
        register_rest_route( $this->rest_namespace, '/pixels-fired/', [
            'methods'             => 'POST',
            'callback'            => function ( $request ) {
            $data = $request->get_json_params();
            // TODO: Maybe remove the nonce verification. 1) Some merchants even cache parts of the purchase confirmation page, which lets the nonce fail. 2) Nonce checks in this endpoint are not really necessary as we CAN check the for a valid order_key which is only known to the customer who purchased a specific order.
            if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
                wp_send_json_error( 'Invalid nonce' );
            }
            $order_key = filter_var( $data['order_key'], FILTER_SANITIZE_STRING );
            $order_source = filter_var( $data['source'], FILTER_SANITIZE_STRING );
            
            if ( $order_key && $order_source ) {
                $this->save_conversion_pixels_fired_status( $order_key, $order_source );
                wp_send_json_success();
            } else {
                wp_send_json_error( 'No order key or order source provided' );
            }
        
        },
            'permission_callback' => function () {
            return true;
        },
        ] );
    }
    
    private function get_products_for_datalayer( $data )
    {
        $product_ids = Helpers::generic_sanitization( $data['productIds'] );
        if ( !$product_ids ) {
            wp_send_json_error( 'No product IDs provided.' );
        }
        if ( !is_array( $product_ids ) ) {
            wp_send_json_error( 'Product IDs must be an array.' );
        }
        // Prevent server overload if too many products are requested
        $product_ids = ( count( $product_ids ) > 50 ? array_slice( $product_ids, 0, 50 ) : $product_ids );
        $products = $this->get_products_for_datalayer_by_product_ids( $product_ids );
        // Check if a data layer products transient for this page exists
        // If it does, add the products from the transient to $products
        
        if ( get_transient( 'pmw_products_for_datalayer_' . $data['pageId'] ) ) {
            $products_in_transient = get_transient( 'pmw_products_for_datalayer_' . $data['pageId'] );
            // Merge the associative arrays with nested arrays $products and $products_in_transient preserving the keys
            $products = array_replace_recursive( $products, $products_in_transient );
        }
        
        // Set transient with products for $data['pageId']
        if ( 'cart' !== $data['pageType'] && 'checkout' !== $data['pageType'] && 'order_received_page' !== $data['pageType'] ) {
            set_transient( 'pmw_products_for_datalayer_' . $data['pageId'], $products, MONTH_IN_SECONDS );
        }
        wp_send_json_success( $products );
    }
    
    private function get_order_value_after_refunds( $order )
    {
        $refunds = $order->get_refunds();
        $refunded_amount = 0;
        foreach ( $refunds as $refund ) {
            $refunded_amount -= $refund->get_total();
        }
        $order_total = $order->get_total();
        $adjusted_value = $order_total - $refunded_amount;
        // Calculate the new order value considering the order total logic that has been applied by the user
        $adjusted_value_percentage = $adjusted_value / $order_total;
        $adjusted_value = Shop::pmw_get_order_total( $order, true ) * $adjusted_value_percentage;
        return wc_format_decimal( $adjusted_value, 2 );
    }
    
    private function get_order_details_for_acr( $data )
    {
        // If order ID or order key is not provided, return error
        if ( !isset( $data['order_id'] ) || !isset( $data['order_key'] ) ) {
            wp_send_json_error( 'No order ID or order key provided' );
        }
        $order_id = filter_var( $data['order_id'], FILTER_SANITIZE_NUMBER_INT );
        $order_key = filter_var( $data['order_key'], FILTER_SANITIZE_STRING );
        $order = wc_get_order( $order_id );
        // if order is not found, return error
        if ( !$order ) {
            wp_send_json_error( 'Order not found' );
        }
        // If order key doesn't match, return error
        if ( $order->get_order_key() !== $order_key ) {
            wp_send_json_error( 'Order key does not match' );
        }
        if ( !$this->is_order_eligible_for_acr( $order ) ) {
            wp_send_json_error( 'Order is not eligible for ACR' );
        }
        // Return the order details for the wpmDataLayer with the provided ID
        wp_send_json_success( $this->get_order_data( $order ) );
    }
    
    private function is_order_eligible_for_acr( $order )
    {
        /**
         * If the order is not in a paid state, return false.
         *
         * It seems to be better to check for paid statuses instead of not-paid statuses,
         * as there are shops that may add unpaid statuses to the status list. Those statuses
         * then can trigger the ACR, which they shouldn't. Using wc_get_is_paid_statuses()
         * will make sure that only paid statuses are checked, even additional paid statuses
         * added through the filter in wc_get_is_paid_statuses().
         *
         * https://stackoverflow.com/a/59869889
         */
        if ( !in_array( $order->get_status(), wc_get_is_paid_statuses() ) ) {
            return false;
        }
        // If order has already fired the conversion pixel, return false
        if ( Shop::has_conversion_pixel_already_fired( $order ) ) {
            return false;
        }
        return true;
    }
    
    public function capture_ajax_server_to_server_event()
    {
        $_post = Helpers::get_input_vars( INPUT_POST );
        $this->process_server_to_server_event( $_post['data'] );
        wp_send_json_success();
    }
    
    public function process_server_to_server_event( $data )
    {
        // Send Facebook CAPI event
        if ( isset( $data['facebook'] ) ) {
            ( new Facebook_CAPI( $this->options ) )->send_facebook_capi_event( $data['facebook'] );
        }
        // Send Tiktok Events API event
        if ( isset( $data['tiktok'] ) ) {
            TikTok_EAPI::send_tiktok_eapi_event( $data['tiktok'] );
        }
    }
    
    public function wpm_save_imported_settings( $request )
    {
        // Verify nonce
        if ( !wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        $options = $request->get_params();
        // Sanitize nested array $options
        $options = Helpers::generic_sanitization( $options );
        if ( !is_array( $options ) ) {
            wp_send_json_error( 'Invalid options. Not an array.' );
        }
        // Validate imported options
        if ( !Validations::validate_imported_options( $options ) ) {
            //			wc_get_logger()->error('Invalid Options. Options not saved', ['source' => 'PMW']);
            wp_send_json_error( [
                'message' => 'Invalid options. Didn\'t pass validation.',
            ] );
        }
        // All good, save options
        update_option( WPM_DB_OPTIONS_NAME, $options );
        wp_send_json_success( [
            'message' => 'Options saved',
        ] );
    }
    
    public function run_background_processes()
    {
        
        if ( wpm_fs()->can_use_premium_code__premium_only() && Environment::get_instance()->is_woocommerce_active() ) {
            
            if ( is_cart() || is_checkout() ) {
                if ( $this->options_obj->facebook->pixel_id && $this->options_obj->facebook->capi->token ) {
                    ( new Facebook_CAPI( $this->options ) )->pmw_facebook_set_session_identifiers();
                }
                if ( $this->google->is_google_analytics_active() ) {
                    ( new Google_MP( $this->options ) )->wpm_google_analytics_set_session_data();
                }
            }
            
            if ( Shop::pmw_is_order_received_page() ) {
                if ( Shop::pmw_get_current_order() ) {
                    ( new Google_Pixel_Manager( $this->options ) )->save_gclid_in_order__premium_only( Shop::pmw_get_current_order() );
                }
            }
        }
    
    }
    
    public function wpm_woocommerce_new_order( $order_id )
    {
        $order = wc_get_order( $order_id );
        /**
         * All new orders should be marked as long WPM is active,
         * so that we know we can process them later through WPM,
         * and so that we know we should not touch orders that were
         * placed before WPM was active.
         */
        $order->add_meta_data( '_wpm_process_through_wpm', true, true );
        /**
         * Set a custom user ID on the order
         * because WC sets 0 on all order created
         * manually through the back-end.
         */
        $user_id = 0;
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
        }
        $order->add_meta_data( '_wpm_customer_user', $user_id, true );
        $order->save();
    }
    
    // Thanks to: https://gist.github.com/mishterk/6b7a4d6e5a91086a5a9b05ace304b5ce#file-mark-wordpress-scripts-as-async-or-defer-php
    public function experimental_defer_scripts( $tag, $handle )
    {
        if ( 'wpm' !== $handle ) {
            return $tag;
        }
        return str_replace( ' src', ' defer src', $tag );
        // defer the script
    }
    
    public function woocommerce_mini_cart_contents()
    {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $this->woocommerce_after_cart_item_name( $cart_item, $cart_item_key );
        }
    }
    
    public function woocommerce_after_cart_item_name( $cart_item, $cart_item_key )
    {
        $data = [
            'product_id'   => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'],
        ];
        ?>
		<script>
			window.wpmDataLayer.cartItemKeys                                          = window.wpmDataLayer.cartItemKeys || {}
			window.wpmDataLayer.cartItemKeys['<?php 
        echo  esc_js( $cart_item_key ) ;
        ?>'] = <?php 
        echo  wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ;
        ?>;
		</script>

		<?php 
    }
    
    // on product page
    public function woocommerce_inject_product_data_on_product_page()
    {
        if ( !is_product() ) {
            return;
        }
        $product = wc_get_product( get_the_id() );
        
        if ( Product::is_not_wc_product( $product ) ) {
            wc_get_logger()->debug( 'woocommerce_inject_product_data_on_product_page provided no product on a product page: .' . get_the_id(), [
                'source' => 'PMW',
            ] );
            return;
        }
        
        Product::get_product_data_layer_script( $product, false, true );
        
        if ( $product->is_type( 'grouped' ) ) {
            foreach ( $product->get_children() as $product_id ) {
                $product = wc_get_product( $product_id );
                
                if ( Product::is_not_wc_product( $product ) ) {
                    Product::log_problematic_product_id( $product_id );
                    continue;
                }
                
                Product::get_product_data_layer_script( $product, false, true );
            }
        } elseif ( $product->is_type( 'variable' ) ) {
            /**
             * Stop inspection
             *
             * @noinspection PhpPossiblePolymorphicInvocationInspection
             */
            // Prevent processing of large amounts of variations
            // because get_available_variations() is very slow
            if ( 64 <= count( $product->get_children() ) ) {
                return;
            }
            foreach ( $product->get_available_variations() as $key => $variation ) {
                $variable_product = wc_get_product( $variation['variation_id'] );
                
                if ( !is_object( $variable_product ) ) {
                    Product::log_problematic_product_id( $variation['variation_id'] );
                    continue;
                }
                
                Product::get_product_data_layer_script( $variable_product, false, true );
            }
        }
    
    }
    
    // every product that's generated by the shop loop like shop page or a shortcode
    public function action_woocommerce_after_shop_loop_item()
    {
        global  $product ;
        Product::get_product_data_layer_script( $product );
    }
    
    // product views generated by a gutenberg block instead of a shortcode
    public function wc_add_data_to_gutenberg_block( $html, $data, $product )
    {
        return $html . Product::buffer_get_product_data_layer_script( $product );
    }
    
    public function wpm_wp_footer()
    {
    }
    
    // https://support.cloudflare.com/hc/en-us/articles/200169436-How-can-I-have-Rocket-Loader-ignore-specific-JavaScripts-
    private function inject_data_layer()
    {
        ?>

		<script>

			window.wpmDataLayer = window.wpmDataLayer || {}
			window.wpmDataLayer = Object.assign(window.wpmDataLayer, <?php 
        echo  wp_json_encode( $this->get_data_for_data_layer(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ;
        ?>)

		</script>

		<?php 
    }
    
    /**
     * Set up the wpmDataLayer
     *
     * @return mixed|void
     */
    protected function get_data_for_data_layer()
    {
        /**
         * Load and set some defaults.
         */
        $data = [
            'cart'                => (object) [],
            'cart_item_keys'      => (object) [],
            'orderDeduplication'  => $this->options['shop']['order_deduplication'] && !Shop::is_nodedupe_parameter_set(),
            'position'            => 1,
            'viewItemListTrigger' => $this->view_item_list_trigger_settings(),
            'version'             => [
            'number'             => WPM_CURRENT_VERSION,
            'pro'                => wpm_fs()->is__premium_only(),
            'eligibleForUpdates' => wpm_fs()->can_use_premium_code__premium_only(),
            'distro'             => 'fms',
        ],
        ];
        /**
         * Load the pixels
         */
        $data['pixels'] = $this->get_pixel_data();
        /**
         * Load remaining settings
         */
        
        if ( Environment::get_instance()->is_woocommerce_active() ) {
            $data = $this->add_order_data( $data );
            //			$data         = array_merge($data, $this->get_order_data());
            $data['shop'] = $this->get_shop_data();
        }
        
        $data['general'] = $this->get_general_data();
        $data['user'] = $this->get_user_data();
        /**
         * Load the experiment settings
         */
        //		$data['experiments'] = [
        //				'ga4_server_and_browser_tracking' => apply_filters('experimental_pmw_ga4_server_and_browser_tracking', false),
        //		];
        // Return and optionally modify the wpm data layer
        return apply_filters( 'wpm_experimental_data_layer', $data );
    }
    
    protected function get_pixel_data()
    {
        $data = [];
        if ( $this->google->is_google_active() ) {
            $data['google'] = $this->get_google_pixel_data();
        }
        if ( $this->options_obj->bing->uet_tag_id ) {
            $data['bing'] = $this->get_bing_pixel_data();
        }
        if ( $this->options_obj->facebook->pixel_id ) {
            $data['facebook'] = $this->get_facebook_pixel_data();
        }
        if ( $this->options_obj->hotjar->site_id ) {
            $data['hotjar'] = $this->get_hotjar_pixel_data();
        }
        if ( $this->options_obj->pinterest->pixel_id ) {
            $data['pinterest'] = $this->get_pinterest_pixel_data();
        }
        if ( $this->options_obj->snapchat->pixel_id ) {
            $data['snapchat'] = $this->get_snapchat_pixel_data();
        }
        if ( $this->options_obj->tiktok->pixel_id ) {
            $data['tiktok'] = $this->get_tiktok_pixel_data();
        }
        if ( $this->options_obj->twitter->pixel_id ) {
            $data['twitter'] = $this->get_twitter_pixel_data();
        }
        return $data;
    }
    
    protected function get_google_pixel_data()
    {
        $data = [
            'linker'  => [
            'settings' => $this->google->get_google_linker_settings(),
        ],
            'user_id' => (bool) $this->options_obj->google->user_id,
        ];
        if ( $this->google->is_google_ads_active() ) {
            $data['ads'] = [
                'conversionIds'            => (object) $this->google->get_google_ads_conversion_ids(),
                'dynamic_remarketing'      => [
                'status'                      => (bool) $this->options_obj->google->ads->dynamic_remarketing,
                'id_type'                     => Product::get_dyn_r_id_type( 'google_ads' ),
                'send_events_with_parent_ids' => apply_filters( 'wpm_send_events_with_parent_ids', apply_filters_deprecated(
                'wooptpm_send_events_with_parent_ids',
                [ true ],
                '1.13.0',
                'wpm_send_events_with_parent_ids'
            ) ),
            ],
                'google_business_vertical' => $this->google->get_google_business_vertical( $this->options['google']['ads']['google_business_vertical'] ),
                'phone_conversion_label'   => $this->options_obj->google->ads->phone_conversion_label,
                'phone_conversion_number'  => $this->options_obj->google->ads->phone_conversion_number,
            ];
        }
        if ( $this->google->is_google_analytics_active() ) {
            $data['analytics'] = [
                'universal' => [
                'property_id' => $this->options_obj->google->analytics->universal->property_id,
                'parameters'  => (object) $this->google->get_ga_ua_parameters( $this->options_obj->google->analytics->universal->property_id ),
                'mp_active'   => wpm_fs()->can_use_premium_code__premium_only(),
            ],
                'ga4'       => [
                'measurement_id' => $this->options_obj->google->analytics->ga4->measurement_id,
                'parameters'     => (object) $this->google->get_ga4_parameters( $this->options_obj->google->analytics->ga4->measurement_id ),
                'mp_active'      => $this->options_obj->google->analytics->ga4->api_secret && wpm_fs()->can_use_premium_code__premium_only(),
                'debug_mode'     => $this->google->is_ga4_debug_mode_active(),
            ],
                'id_type'   => $this->google->get_ga_id_type(),
                'eec'       => wpm_fs()->can_use_premium_code__premium_only() && $this->google->is_google_analytics_active(),
            ];
        }
        if ( Options::is_google_optimize_active() ) {
            $data['optimize'] = [
                'container_id' => Options::get_options_obj()->google->optimize->container_id,
            ];
        }
        return $data;
    }
    
    protected function get_bing_pixel_data()
    {
        return [
            'uet_tag_id'          => $this->options_obj->bing->uet_tag_id,
            'dynamic_remarketing' => [
            'id_type' => Product::get_dyn_r_id_type( 'bing' ),
        ],
        ];
    }
    
    protected function get_facebook_pixel_data()
    {
        $data = [
            'pixel_id'            => $this->options_obj->facebook->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => Product::get_dyn_r_id_type( 'facebook' ),
        ],
            'capi'                => (bool) $this->options_obj->facebook->capi->token,
            'advanced_matching'   => (bool) $this->options_obj->facebook->capi->user_transparency->send_additional_client_identifiers,
            'exclusion_patterns'  => apply_filters( 'pmw_facebook_tracking_exclusion_patterns', [] ),
            'fbevents_js_url'     => Helpers::get_facebook_fbevents_js_url(),
        ];
        if ( wpm_fs()->can_use_premium_code__premium_only() && Environment::get_instance()->is_woocommerce_active() && is_product() && $this->options_obj->facebook->microdata ) {
            $data['microdata_product_id'] = $this->microdata_product_id;
        }
        return $data;
    }
    
    protected function get_hotjar_pixel_data()
    {
        return [
            'site_id' => $this->options_obj->hotjar->site_id,
        ];
    }
    
    protected function get_pinterest_pixel_data()
    {
        $data = [
            'pixel_id'             => $this->options_obj->pinterest->pixel_id,
            'dynamic_remarketing'  => [
            'id_type' => Product::get_dyn_r_id_type( 'pinterest' ),
        ],
            'enhanced_match_email' => $this->get_user_email(),
        ];
        // Add Pinterest Conversion ID if available.
        $enhanced_match = (bool) $this->options_obj->pinterest->enhanced_match;
        $enhanced_match = apply_filters_deprecated(
            'wooptpm_pinterest_enhanced_match',
            [ $enhanced_match ],
            '1.13.0',
            'wpm_pinterest_enhanced_match'
        );
        $data['enhanced_match'] = apply_filters_deprecated(
            'wpm_pinterest_enhanced_match',
            [ $enhanced_match ],
            '1.22.0',
            null,
            'There is now an option in the Pinterest settings to enable/disable enhanced match.'
        );
        return $data;
    }
    
    protected function get_snapchat_pixel_data()
    {
        return [
            'pixel_id'            => $this->options_obj->snapchat->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => Product::get_dyn_r_id_type( 'snapchat' ),
        ],
        ];
    }
    
    protected function get_tiktok_pixel_data()
    {
        return [
            'pixel_id'            => $this->options_obj->tiktok->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => Product::get_dyn_r_id_type( 'tiktok' ),
        ],
            'eapi'                => (bool) $this->options_obj->tiktok->eapi->token,
            'advanced_matching'   => (bool) $this->options_obj->tiktok->advanced_matching,
        ];
    }
    
    protected function get_twitter_pixel_data()
    {
        return [
            'pixel_id'            => $this->options_obj->twitter->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => Product::get_dyn_r_id_type( 'twitter' ),
        ],
            'event_ids'           => $this->options_obj->twitter->event_ids,
        ];
    }
    
    protected function add_order_data( $data )
    {
        if ( !Shop::pmw_is_order_received_page() ) {
            return array_merge( $data, [] );
        }
        if ( !Shop::pmw_get_current_order() ) {
            return array_merge( $data, [] );
        }
        if ( !Shop::can_order_confirmation_be_processed( Shop::pmw_get_current_order() ) ) {
            return array_merge( $data, [] );
        }
        return array_merge( $data, $this->get_order_data( Shop::pmw_get_current_order() ) );
    }
    
    protected function get_order_data( $order )
    {
        $data = [];
        
        if ( $order ) {
            $data['order'] = [
                'id'               => (int) $order->get_id(),
                'number'           => (string) $order->get_order_number(),
                'key'              => (string) $order->get_order_key(),
                'affiliation'      => (string) get_bloginfo( 'name' ),
                'currency'         => (string) Shop::get_order_currency( $order ),
                'value_filtered'   => (double) Shop::pmw_get_order_total( $order, true ),
                'value_regular'    => (double) $order->get_total(),
                'discount'         => (double) $order->get_total_discount(),
                'tax'              => (double) $order->get_total_tax(),
                'shipping'         => (double) $order->get_shipping_total(),
                'coupon'           => implode( ',', $order->get_coupon_codes() ),
                'aw_merchant_id'   => ( (int) $this->options['google']['ads']['aw_merchant_id'] ? (int) $this->options['google']['ads']['aw_merchant_id'] : '' ),
                'aw_feed_country'  => (string) Helpers::get_visitor_country(),
                'aw_feed_language' => (string) $this->google->get_gmc_language(),
                'new_customer'     => Shop::is_new_customer( $order ),
                'quantity'         => (int) count( Product::wpm_get_order_items( $order ) ),
                'items'            => Product::get_front_end_order_items( $order ),
                'customer_id'      => $order->get_customer_id(),
                'user_id'          => $order->get_user_id(),
            ];
            // Process customer lifetime value
            
            if ( Shop::can_clv_query_be_run( $order->get_billing_email() ) ) {
                $data['order']['clv_order_total'] = Shop::get_clv_order_total_by_billing_email( $order->get_billing_email() );
                $data['order']['clv_order_value_filtered'] = Shop::get_clv_value_filtered_by_billing_email( $order->get_billing_email() );
            }
            
            // set em (email)
            $data['order']['billing_email'] = trim( strtolower( $order->get_billing_email() ) );
            $data['order']['billing_email_hashed'] = hash( 'sha256', trim( strtolower( $order->get_billing_email() ) ) );
            
            if ( $order->get_billing_phone() ) {
                $phone = $order->get_billing_phone();
                $phone = Helpers::get_e164_formatted_phone_number( $phone, $order->get_billing_country() );
                $data['order']['billing_phone'] = $phone;
            }
            
            if ( $order->get_billing_first_name() ) {
                $data['order']['billing_first_name'] = trim( strtolower( $order->get_billing_first_name() ) );
            }
            if ( $order->get_billing_last_name() ) {
                $data['order']['billing_last_name'] = trim( strtolower( $order->get_billing_last_name() ) );
            }
            if ( $order->get_billing_city() ) {
                $data['order']['billing_city'] = str_replace( ' ', '', trim( strtolower( $order->get_billing_city() ) ) );
            }
            if ( $order->get_billing_state() ) {
                $data['order']['billing_state'] = trim( strtolower( $order->get_billing_state() ) );
            }
            if ( $order->get_billing_postcode() ) {
                $data['order']['billing_postcode'] = $order->get_billing_postcode();
            }
            if ( $order->get_billing_country() ) {
                $data['order']['billing_country'] = trim( strtolower( $order->get_billing_country() ) );
            }
            $data['products'] = $this->get_order_products( $order );
        }
        
        return $data;
    }
    
    /**
     * We are controlling the entire output in all formats from here. Why?
     * Because each pixel has different requirements for each data field.
     * Hashed, not hashed, lower case, not lower case, phone with + or without +,
     * etc.
     *
     * @return array
     */
    protected function get_user_data()
    {
        $data = [];
        
        if ( is_user_logged_in() || Shop::pmw_is_order_received_page() ) {
            $order = null;
            if ( Shop::pmw_is_order_received_page() ) {
                $order = Shop::pmw_get_current_order();
            }
            $current_user = wp_get_current_user();
            /**
             * ID
             */
            $data['id'] = [
                'raw'    => get_current_user_id(),
                'sha256' => hash( 'sha256', get_current_user_id() ),
            ];
            /**
             * Email
             */
            $data['email'] = [
                'raw'      => $this->get_user_email(),
                'sha256'   => $this->get_user_email( 'sha256' ),
                'facebook' => $this->get_user_email( 'sha256' ),
            ];
            /**
             * First name
             */
            
            if ( $order && $order->get_billing_first_name() ) {
                $first_name = $order->get_billing_first_name();
            } else {
                $first_name = $current_user->user_firstname;
            }
            
            if ( $first_name ) {
                $data['first_name'] = [
                    'raw'      => trim( $first_name ),
                    'facebook' => trim( strtolower( $first_name ) ),
                ];
            }
            /**
             * Last name
             */
            
            if ( $order && $order->get_billing_last_name() ) {
                $last_name = $order->get_billing_last_name();
            } else {
                $last_name = $current_user->user_lastname;
            }
            
            if ( $last_name ) {
                $data['last_name'] = [
                    'raw'      => trim( $last_name ),
                    'facebook' => trim( strtolower( $last_name ) ),
                ];
            }
            /**
             * Phone
             */
            
            if ( $order && $order->get_billing_phone() ) {
                $phone = $order->get_billing_phone();
            } else {
                $phone = get_user_meta( get_current_user_id(), 'billing_phone', true );
            }
            
            if ( $phone ) {
                $data['phone'] = [
                    'raw'      => trim( $phone ),
                    'e164'     => Helpers::get_e164_formatted_phone_number( trim( strtolower( $phone ) ), Helpers::get_user_country_code( $current_user ) ),
                    'facebook' => str_replace( '+', '', trim( strtolower( $phone ) ) ),
                    'sha256'   => [
                    'raw'  => hash( 'sha256', trim( $phone ) ),
                    'e164' => hash( 'sha256', Helpers::get_e164_formatted_phone_number( trim( strtolower( $phone ) ), Helpers::get_user_country_code( $current_user ) ) ),
                ],
                ];
            }
            /**
             * Postcode
             */
            
            if ( $order && $order->get_billing_postcode() ) {
                $postcode = $order->get_billing_postcode();
            } else {
                $postcode = get_user_meta( get_current_user_id(), 'billing_postcode', true );
            }
            
            if ( $postcode ) {
                $data['postcode'] = [
                    'raw'      => trim( $postcode ),
                    'facebook' => trim( strtolower( $postcode ) ),
                ];
            }
            /**
             * City
             */
            
            if ( $order && $order->get_billing_city() ) {
                $city = $order->get_billing_city();
            } else {
                $city = get_user_meta( get_current_user_id(), 'billing_city', true );
            }
            
            if ( $city ) {
                $data['city'] = [
                    'raw'      => trim( $city ),
                    'facebook' => trim( strtolower( $city ) ),
                ];
            }
            /**
             * State
             */
            
            if ( $order && $order->get_billing_state() ) {
                $state = $order->get_billing_state();
            } else {
                $state = get_user_meta( get_current_user_id(), 'billing_state', true );
            }
            
            if ( $state ) {
                $data['state'] = [
                    'raw'      => trim( $state ),
                    'facebook' => preg_replace( '/[a-zA-Z]{2}-/', '', trim( strtolower( $state ) ) ),
                ];
            }
            /**
             * Country
             */
            
            if ( $order && $order->get_billing_country() ) {
                $country = $order->get_billing_country();
            } else {
                $country = get_user_meta( get_current_user_id(), 'billing_country', true );
            }
            
            if ( $country ) {
                $data['country'] = [
                    'raw'      => trim( $country ),
                    'facebook' => trim( strtolower( $country ) ),
                ];
            }
        }
        
        return $data;
    }
    
    protected function get_order_products( $order )
    {
        $order_products = [];
        foreach ( (array) Product::wpm_get_order_items( $order ) as $order_item ) {
            $order_item_data = $order_item->get_data();
            if ( 0 !== $order_item_data['variation_id'] ) {
                // add variation
                $order_products[$order_item_data['variation_id']] = $this->get_product_data( $order_item_data['variation_id'] );
            }
            $order_products[$order_item_data['product_id']] = $this->get_product_data( $order_item_data['product_id'] );
        }
        return $order_products;
    }
    
    protected function get_product_data( $product_id )
    {
        $product = wc_get_product( $product_id );
        
        if ( Product::is_not_wc_product( $product ) ) {
            Product::log_problematic_product_id( $product_id );
            return [];
        }
        
        $data = [
            'product_id'   => $product->get_id(),
            'name'         => $product->get_name(),
            'type'         => $product->get_type(),
            'dyn_r_ids'    => Product::get_dyn_r_ids( $product ),
            'brand'        => (string) Product::get_brand_name( $product_id ),
            'category'     => (array) Product::get_product_category( $product_id ),
            'variant_name' => ( (string) ($product->get_type() === 'variation') ? Product::get_formatted_variant_text( $product ) : '' ),
        ];
        
        if ( $product->get_type() === 'variation' ) {
            $parent_product = wc_get_product( $product->get_parent_id() );
            $data['brand'] = Product::get_brand_name( $parent_product->get_id() );
        }
        
        return $data;
    }
    
    public function view_item_list_trigger_settings()
    {
        $settings = [
            'testMode'        => false,
            'backgroundColor' => 'green',
            'opacity'         => 0.5,
            'repeat'          => true,
            'timeout'         => 1000,
            'threshold'       => 0.8,
        ];
        $settings = apply_filters_deprecated(
            'wooptpm_view_item_list_trigger_settings',
            [ $settings ],
            '1.13.0',
            'wpm_view_item_list_trigger_settings'
        );
        return apply_filters( 'wpm_view_item_list_trigger_settings', $settings );
    }
    
    public function inject_wpm_opening()
    {
        echo  PHP_EOL . '<!-- START Pixel Manager for WooCommerce -->' . PHP_EOL ;
    }
    
    public function inject_wpm_closing()
    {
        if ( Environment::get_instance()->is_woocommerce_active() && Shop::pmw_is_order_received_page() && Shop::pmw_get_current_order() ) {
            $this->increase_conversion_count_for_ratings( Shop::pmw_get_current_order() );
        }
        echo  PHP_EOL . '<!-- END Pixel Manager for WooCommerce -->' . PHP_EOL ;
    }
    
    private function increase_conversion_count_for_ratings( $order )
    {
        
        if ( Shop::can_order_confirmation_be_processed( $order ) ) {
            $ratings = get_option( WPM_DB_RATINGS );
            if ( !isset( $ratings['conversions_count'] ) ) {
                $ratings['conversions_count'] = 0;
            }
            $ratings['conversions_count'] = $ratings['conversions_count'] + 1;
            update_option( WPM_DB_RATINGS, $ratings );
        } else {
            Shop::conversion_pixels_already_fired_html();
        }
    
    }
    
    public function ajax_pmw_get_cart_items()
    {
        global  $woocommerce ;
        $cart_items = $woocommerce->cart->get_cart();
        $data = [];
        foreach ( $cart_items as $cart_item => $value ) {
            $product = wc_get_product( $value['data']->get_id() );
            
            if ( Product::is_not_wc_product( $product ) ) {
                Product::log_problematic_product_id( $value['data']->get_id() );
                continue;
            }
            
            $data['cart_item_keys'][$cart_item] = [
                'id'          => (string) $product->get_id(),
                'isVariation' => false,
            ];
            $data['cart'][$product->get_id()] = [
                'id'          => (string) $product->get_id(),
                'dyn_r_ids'   => Product::get_dyn_r_ids( $product ),
                'name'        => $product->get_name(),
                'brand'       => Product::get_brand_name( $product->get_id() ),
                'quantity'    => (int) $value['quantity'],
                'price'       => (double) $product->get_price(),
                'isVariation' => false,
            ];
            
            if ( 'variation' === $product->get_type() ) {
                $parent_product = wc_get_product( $product->get_parent_id() );
                
                if ( $parent_product ) {
                    $data['cart'][$product->get_id()]['name'] = $parent_product->get_name();
                    $data['cart'][$product->get_id()]['parentId'] = (string) $parent_product->get_id();
                    $data['cart'][$product->get_id()]['parentId_dyn_r_ids'] = Product::get_dyn_r_ids( $parent_product );
                    $data['cart'][$product->get_id()]['brand'] = Product::get_brand_name( $parent_product->get_id() );
                } else {
                    wc_get_logger()->debug( 'Variation ' . $product->get_id() . ' doesn\'t link to a valid parent product.', [
                        'source' => 'PMW',
                    ] );
                }
                
                $data['cart'][$product->get_id()]['isVariation'] = true;
                $data['cart'][$product->get_id()]['category'] = Product::get_product_category( $product->get_parent_id() );
                $variant_text_array = [];
                $attributes = $product->get_attributes();
                if ( $attributes ) {
                    foreach ( $attributes as $key => $value ) {
                        $key_name = str_replace( 'pa_', '', $key );
                        $variant_text_array[] = ucfirst( $key_name ) . ': ' . strtolower( $value );
                    }
                }
                $data['cart'][$product->get_id()]['variant'] = (string) implode( ' | ', $variant_text_array );
                $data['cart_item_keys'][$cart_item]['parentId'] = (string) $product->get_parent_id();
                $data['cart_item_keys'][$cart_item]['isVariation'] = true;
            } else {
                $data['cart'][$product->get_id()]['category'] = Product::get_product_category( $product->get_id() );
            }
        
        }
        wp_send_json_success( $data );
    }
    
    public function ajax_pmw_get_product_ids()
    {
        $data = Helpers::get_input_vars( INPUT_POST );
        // Change productIds back into an array
        $data['productIds'] = explode( ',', $data['productIds'] );
        $this->get_products_for_datalayer( $data );
    }
    
    public function get_products_for_datalayer_by_product_ids( $product_ids )
    {
        $products = [];
        foreach ( $product_ids as $key => $product_id ) {
            // validate if a valid product ID has been passed in the array
            if ( !ctype_digit( $product_id ) ) {
                continue;
            }
            $product = wc_get_product( $product_id );
            if ( Product::is_not_wc_product( $product ) ) {
                //				wc_get_logger()->debug('ajax_pmw_get_product_ids received an invalid product', ['source' => 'PMW']);
                continue;
            }
            $products[$product_id] = Product::get_product_details_for_datalayer( $product );
        }
        return $products;
    }
    
    public function ajax_purchase_pixels_fired_handler()
    {
        $_post = Helpers::get_input_vars( INPUT_POST );
        // Verify nonce
        if ( !wp_verify_nonce( $_post['nonce_ajax'], 'nonce-pmw-ajax' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }
        
        if ( isset( $_post['order_key'] ) && isset( $_post['source'] ) ) {
            $order_key = filter_var( $_post['order_key'], FILTER_SANITIZE_STRING );
            $order_source = filter_var( $_post['source'], FILTER_SANITIZE_STRING );
            $this->save_conversion_pixels_fired_status( $order_key, $order_source );
            wp_send_json_success();
        } else {
            wp_send_json_error( 'Invalid data. Missing order_key or source.' );
        }
    
    }
    
    public function save_conversion_pixels_fired_status( $order_key, $source = 'thankyou_page' )
    {
        $order = wc_get_order( wc_get_order_id_by_order_key( $order_key ) );
        $order->update_meta_data( '_wpm_conversion_pixel_trigger', $source );
        $order->update_meta_data( '_wpm_conversion_pixel_fired', true );
        // Get the time between when the order was created and now and save it in _wpm_conversion_pixel_fired_delay
        $time_diff = time() - strtotime( $order->get_date_created() );
        $order->update_meta_data( '_wpm_conversion_pixel_fired_delay', $time_diff );
        $order->save();
    }
    
    public function wpm_front_end_scripts()
    {
        $pmw_dependencies = [ 'jquery', 'wp-hooks' ];
        // enable polyfill.io with filter
        
        if ( wpm_fs()->can_use_premium_code__premium_only() && apply_filters( 'wpm_experimental_inject_polyfill_io', false ) ) {
            wp_enqueue_script(
                'polyfill-io',
                'https://cdn.polyfill.io/v2/polyfill.min.js',
                false,
                WPM_CURRENT_VERSION,
                false
            );
            $pmw_dependencies[] = 'polyfill-io';
        }
        
        wp_enqueue_script(
            'wpm',
            WPM_PLUGIN_DIR_PATH . 'js/public/wpm-public.p1.min.js',
            $pmw_dependencies,
            WPM_CURRENT_VERSION,
            $this->move_wpm_script_to_footer()
        );
        wp_localize_script(
            'wpm',
            //            'ajax_object',
            'wpm',
            [
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'root'          => esc_url_raw( rest_url() ),
                'nonce_wp_rest' => wp_create_nonce( 'wp_rest' ),
                'nonce_ajax'    => wp_create_nonce( 'nonce-pmw-ajax' ),
            ]
        );
    }
    
    protected function move_wpm_script_to_footer()
    {
        // this filter moves the wpm script to the footer
        return apply_filters( 'wpm_experimental_move_wpm_script_to_footer', false );
    }
    
    private function get_preset_version()
    {
        return '.p' . apply_filters( 'wpm_script_optimization_preset_version', 1 );
    }
    
    public function inject_order_received_page_dedupe( $order, $order_total, $is_new_customer )
    {
        // nothing to do
    }
    
    private function inject_body_pixels()
    {
        //        $this->google_pixel_manager->inject_google_optimize_anti_flicker_snippet();
    }
    
    private function get_shop_data()
    {
        $data = [];
        
        if ( is_product_category() ) {
            $data['list_name'] = 'Product Category' . Shop::get_list_name_suffix();
            $data['list_id'] = 'product_category' . Shop::get_list_id_suffix();
            $data['page_type'] = 'product_category';
        } elseif ( is_product_tag() ) {
            $data['list_name'] = 'Product Tag' . Shop::get_list_name_suffix();
            $data['list_id'] = 'product_tag' . Shop::get_list_id_suffix();
            $data['page_type'] = 'product_tag';
        } elseif ( is_search() ) {
            $data['list_name'] = 'Product Search';
            $data['list_id'] = 'search';
            $data['page_type'] = 'search';
        } elseif ( is_shop() ) {
            $data['list_name'] = 'Shop | page number: ' . $this->get_page_number();
            $data['list_id'] = 'product_shop_page_number_' . $this->get_page_number();
            $data['page_type'] = 'product_shop';
        } elseif ( is_product() ) {
            $data['list_name'] = 'Product | ' . Shop::wpm_get_the_title();
            $data['list_id'] = 'product_' . sanitize_title( get_the_title() );
            $data['page_type'] = 'product';
            $product = wc_get_product();
            $data['product_type'] = $product->get_type();
        } elseif ( is_front_page() ) {
            $data['list_name'] = 'Front Page';
            $data['list_id'] = 'front_page';
            $data['page_type'] = 'front_page';
        } elseif ( Shop::pmw_is_order_received_page() ) {
            $data['list_name'] = 'Order Received Page';
            $data['list_id'] = 'order_received_page';
            $data['page_type'] = 'order_received_page';
        } elseif ( is_cart() ) {
            $data['list_name'] = 'Cart';
            $data['list_id'] = 'cart';
            $data['page_type'] = 'cart';
        } elseif ( is_checkout() ) {
            $data['list_name'] = 'Checkout Page';
            $data['list_id'] = 'checkout';
            $data['page_type'] = 'checkout';
        } elseif ( is_page() ) {
            $data['list_name'] = 'Page | ' . Shop::wpm_get_the_title();
            $data['list_id'] = 'page_' . sanitize_title( get_the_title() );
            $data['page_type'] = 'page';
        } elseif ( is_home() ) {
            $data['list_name'] = 'Blog Home';
            $data['list_id'] = 'blog_home';
            $data['page_type'] = 'blog_post';
        } elseif ( 'post' === get_post_type() ) {
            $data['list_name'] = 'Blog Post | ' . Shop::wpm_get_the_title();
            $data['list_id'] = 'blog_post_' . sanitize_title( get_the_title() );
            $data['page_type'] = 'blog_post';
        } else {
            $data['list_name'] = '';
            $data['list_id'] = '';
            $data['page_type'] = '';
        }
        
        $data['currency'] = get_woocommerce_currency();
        //		$data['mini_cart']['track'] = apply_filters_deprecated('wooptpm_track_mini_cart', [true], '1.13.0', 'wpm_track_mini_cart');
        //		$data['mini_cart']['track'] = apply_filters('wpm_track_mini_cart', $data['mini_cart']['track']);
        $mini_cart_filter_deprecation_message = 'The filter has become obsolete since WPM now tracks cart item data using the browser cache and doesn\'t rely entirely on the server anymore.';
        apply_filters_deprecated(
            'wooptpm_track_mini_cart',
            [ true ],
            '1.13.0',
            '',
            $mini_cart_filter_deprecation_message
        );
        apply_filters_deprecated(
            'wpm_track_mini_cart',
            [ true ],
            '1.15.5',
            '',
            $mini_cart_filter_deprecation_message
        );
        $data['cookie_consent_mgmt'] = [
            'explicit_consent' => (bool) $this->options_obj->shop->cookie_consent_mgmt->explicit_consent,
        ];
        return $data;
    }
    
    protected function get_page_number()
    {
        return ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
    }
    
    private function get_general_data()
    {
        return [
            'variationsOutput'         => (bool) $this->options_obj->general->variations_output,
            'userLoggedIn'             => is_user_logged_in(),
            'scrollTrackingThresholds' => $this->options_obj->general->scroll_tracker_thresholds,
            'pageId'                   => get_the_ID(),
            'excludeDomains'           => apply_filters( 'pmw_exclude_domains_from_tracking', [] ),
            'server2server'            => [
            'active'        => Options::server_2_server_enabled(),
            'ipExcludeList' => apply_filters( 'pmw_exclude_ips_from_server2server_events', [] ),
        ],
        ];
    }
    
    protected function get_user_email( $algo = null )
    {
        $email = '';
        
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            if ( $current_user->user_email ) {
                $email = $current_user->user_email;
            }
        }
        
        // Override email if the order has a billing email
        
        if ( Shop::pmw_is_order_received_page() ) {
            $order = Shop::pmw_get_current_order();
            if ( $order && $order->get_billing_email() ) {
                $email = $order->get_billing_email();
            }
        }
        
        // Clean up formatting
        $email = strtolower( trim( $email ) );
        // encrypt email
        if ( $email && $algo && in_array( $algo, hash_algos(), true ) ) {
            $email = hash( $algo, $email );
        }
        return $email;
    }

}