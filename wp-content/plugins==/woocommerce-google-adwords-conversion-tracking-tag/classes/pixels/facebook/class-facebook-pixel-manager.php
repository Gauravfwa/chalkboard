<?php

namespace WCPM\Classes\Pixels\Facebook;

use  WCPM\Classes\Http\Facebook_CAPI ;
use  WCPM\Classes\Shop ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Facebook_Pixel_Manager
{
    protected  $facebook_capi ;
    public function __construct( $options )
    {
        
        if ( wpm_fs()->can_use_premium_code__premium_only() && $options['facebook']['capi']['token'] ) {
            $this->facebook_capi = new Facebook_CAPI( $options );
            // Save the Facebook session identifiers on the order so that we can use them later when the order gets paid or completed
            // https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-checkout.html#source-view.403
            add_action( 'woocommerce_checkout_order_created', [ $this, 'facebook_save_session_identifiers_on_order__premium_only' ] );
            // Process the purchase through Facebook CAPI when they are paid,
            // or when they are manually completed.
            add_action( 'woocommerce_order_status_on-hold', [ $this, 'facebook_capi_report_purchase__premium_only' ] );
            add_action( 'woocommerce_order_status_processing', [ $this, 'facebook_capi_report_purchase__premium_only' ] );
            add_action( 'woocommerce_payment_complete', [ $this, 'facebook_capi_report_purchase__premium_only' ] );
            add_action( 'woocommerce_order_status_completed', [ $this, 'facebook_capi_report_purchase__premium_only' ] );
            /**
             * Process WooCommerce Subscription renewals
             * https://docs.woocommerce.com/document/subscriptions/develop/action-reference/
             * https://github.com/wp-premium/woocommerce-subscriptions/blob/master/includes/class-wc-subscription.php
             * https://developers.facebook.com/docs/marketing-api/conversions-api/subscription-lifecycle-events/
             * */
            add_action( 'woocommerce_subscription_payment_complete', [ $this, 'facebook_capi_report_subscription_payment_complete__premium_only' ] );
            if ( $this->track_facebook_capi_subscription_renewal() ) {
                add_action(
                    'woocommerce_subscription_renewal_payment_complete',
                    [ $this, 'facebook_capi_report_subscription_purchase_renewal__premium_only' ],
                    10,
                    2
                );
            }
            add_action( 'woocommerce_subscription_status_cancelled', [ $this, 'facebook_capi_report_subscription_cancellation__premium_only' ] );
            add_action(
                'woocommerce_subscription_status_updated',
                [ $this, 'facebook_capi_report_subscription_update__premium_only' ],
                10,
                3
            );
        }
    
    }
    
    public function track_facebook_capi_subscription_renewal()
    {
        return apply_filters( 'pmw_facebook_subscription_renewal_tracking', true );
    }
    
    public function do_not_track_facebook_capi_subscription_renewal()
    {
        return !$this->track_facebook_capi_subscription_renewal();
    }

}