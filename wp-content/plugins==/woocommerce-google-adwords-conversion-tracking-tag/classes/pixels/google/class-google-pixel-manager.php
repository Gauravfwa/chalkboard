<?php

namespace WCPM\Classes\Pixels\Google;

use  WCPM\Classes\Helpers ;
use  WCPM\Classes\Shop ;
use  WCPM\Classes\Http\Google_MP_GA4 ;
use  WCPM\Classes\Http\Google_MP_UA ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Google_Pixel_Manager
{
    private  $google_pixel ;
    private  $google_analytics_ua_http_mp ;
    private  $google_analytics_4_http_mp ;
    private  $cid_key_ga_ua ;
    private  $cid_key_ga4 ;
    protected  $options_obj ;
    public function __construct( $options )
    {
        $this->google_pixel = new Google( $options );
        $this->options_obj = Helpers::get_options_object( $options );
    }
    
    public function wpm_woocommerce_order_status_changed(
        $order_id,
        $old_status,
        $new_status,
        $order
    )
    {
        /**
         * If admin sends a payment link to a client
         * we want to set the clients cid
         */
        if ( 'on-hold' === $new_status && !is_admin() ) {
            $this->google_analytics_save_cid_on_order__premium_only( $order );
        }
    }
    
    // https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-order.html#source-view.364
    protected function get_order_paid_statuses()
    {
        $statuses = wc_get_is_paid_statuses();
        // Add additional custom order statuses to trigger the Measurement Protocol purchase hit
        return apply_filters_deprecated(
            'wpm_register_custom_order_confirmation_statuses',
            [ $statuses ],
            '1.30.3',
            'Use the woocommerce_order_is_paid_statuses filter instead'
        );
    }
    
    protected function log_prevented_order_report_for_user( $order )
    {
        if ( !is_user_logged_in() ) {
            return;
        }
        $user_info = get_user_by( 'id', Shop::wpm_get_order_user_id( $order ) );
        if ( !is_object( $user_info ) ) {
            return;
        }
        wc_get_logger()->debug( 'Prevented order ID ' . $order->get_id() . ' to be reported through the Measurement Protocol for user ' . $user_info->user_login . ' (roles: ' . implode( ', ', $user_info->roles ) . ')', [
            'source' => 'PMW',
        ] );
    }
    
    public function inject_order_received_page_dedupe( $order, $order_total, $is_new_customer )
    {
        if ( $this->google_pixel->is_google_ads_active() && wpm_fs()->can_use_premium_code__premium_only() ) {
            $this->save_gclid_in_order__premium_only( $order );
        }
    }
    
    public function inject_everywhere()
    {
        // $this->google_pixel->inject_everywhere();
    }
    
    public function inject_product_category()
    {
        // all handled on front-end
    }
    
    public function inject_product_tag()
    {
        // all handled on front-end
    }
    
    public function inject_shop_top_page()
    {
        // all handled on front-end
    }
    
    public function inject_search()
    {
        // all handled on front-end
    }
    
    public function inject_product( $product, $product_attributes )
    {
        // handled on front-end
    }
    
    public function inject_cart( $cart, $cart_total )
    {
        // all handled on front-end
    }
    
    protected function inject_opening_script_tag()
    {
    }
    
    protected function inject_closing_script_tag()
    {
    }

}