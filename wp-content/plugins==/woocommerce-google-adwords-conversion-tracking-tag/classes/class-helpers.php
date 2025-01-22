<?php

namespace WCPM\Classes;

use  libphonenumber\NumberParseException ;
use  libphonenumber\PhoneNumberFormat ;
use  libphonenumber\PhoneNumberUtil ;
use  WC_Geolocation ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Helpers
{
    public static function get_input_vars_sanitized( $type )
    {
        $input_vars = filter_input_array( $type, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        return self::generic_sanitization( $input_vars );
    }
    
    public static function generic_sanitization( $input )
    {
        
        if ( is_array( $input ) ) {
            array_walk_recursive( $input, function ( &$value, $key ) {
                $value = filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            } );
        } else {
            $input = filter_var( $input, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        }
        
        return $input;
    }
    
    public static function is_allowed_notification_page( $page )
    {
        $allowed_pages = [ 'page_wpm', 'index.php', 'dashboard' ];
        foreach ( $allowed_pages as $allowed_page ) {
            if ( strpos( $page, $allowed_page ) !== false ) {
                return true;
            }
        }
        return false;
    }
    
    public static function is_wc_hpos_enabled()
    {
        return class_exists( 'Automattic\\WooCommerce\\Utilities\\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }
    
    public static function is_orders_page()
    {
        global  $pagenow ;
        $_get = self::get_input_vars( INPUT_GET );
        return 'edit.php' === $pagenow && isset( $_get['post_type'] ) && 'shop_order' === $_get['post_type'] || isset( $_get['page'] ) && 'wc-orders' === $_get['page'];
    }
    
    // If is single order page return true
    // TODO Check if it works with HPOS enabled
    public static function is_edit_order_page()
    {
        //		global $pagenow;
        //
        //		$_get = self::get_input_vars(INPUT_GET);
        //
        //		error_log('current screen id: ' . get_current_screen()->id);
        return 'shop_order' === get_current_screen()->id;
        //		return
        //			'post.php' === $pagenow
        //			&& isset($_get['post'])
        //			&& 'shop_order' === get_post_type($_get['post'])
        //			&& isset($_get['action'])
        //			&& 'edit' === $_get['action'];
    }
    
    public static function get_input_vars( $type )
    {
        $input_vars = filter_input_array( $type, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        return self::generic_sanitization( $input_vars );
    }
    
    public static function is_email( $email )
    {
        return filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
    
    public static function is_url( $url )
    {
        return filter_var( $url, FILTER_VALIDATE_URL );
    }
    
    public static function get_options_object( $options )
    {
        // TODO find out why I did this weird transformation and simplify it (I think it had something to do with correctly representing objects)
        $options_obj = json_decode( wp_json_encode( $options ) );
        if ( function_exists( 'get_woocommerce_currency' ) ) {
            $options_obj->shop->currency = get_woocommerce_currency();
        }
        return $options_obj;
    }
    
    public static function clean_product_name_for_output( $name )
    {
        return html_entity_decode( $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    }
    
    public static function get_e164_formatted_phone_number( $number, $country )
    {
        try {
            $phone_util = PhoneNumberUtil::getInstance();
            $number_parsed = $phone_util->parse( $number, $country );
            return $phone_util->format( $number_parsed, PhoneNumberFormat::E164 );
        } catch ( NumberParseException $e ) {
            /**
             * Don't error log the exception. It leads to more confusion than it helps:
             * https://wordpress.org/support/topic/php-errors-in-version-1-27-0/
             */
            return $number;
        }
    }
    
    /**
     * Check if the current visitor is on localhost.
     *
     * @return bool
     */
    public static function is_localhost()
    {
        // If the IP is local, return true, else false
        // https://stackoverflow.com/a/13818647/4688612
        return !filter_var( WC_Geolocation::get_ip_address(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }
    
    /**
     * Get the (external) IP address of the current visitor.
     *
     * @return array|string|string[]
     */
    public static function get_user_ip()
    {
        
        if ( self::is_localhost() ) {
            $ip = WC_Geolocation::get_external_ip_address();
        } else {
            $ip = WC_Geolocation::get_ip_address();
        }
        
        // only set the IP if it is a public address
        $ip = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
        // Remove the IPv6 to IPv4 mapping in case the IP contains one
        // and return the IP plain public IPv4 or IPv6 IP
        // https://en.wikipedia.org/wiki/IPv6_address
        return str_replace( '::ffff:', '', $ip );
    }
    
    public static function get_visitor_country()
    {
        $location = WC_Geolocation::geolocate_ip( self::get_user_ip() );
        return $location['country'];
    }
    
    // https://stackoverflow.com/a/60199374/4688612
    public static function is_iframe()
    {
        
        if ( isset( $_SERVER['HTTP_SEC_FETCH_DEST'] ) && 'iframe' === $_SERVER['HTTP_SEC_FETCH_DEST'] ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public static function get_percentage( $counter, $denominator )
    {
        return ( $denominator > 0 ? round( $counter / $denominator * 100 ) : 0 );
    }
    
    public static function get_user_country_code( $user )
    {
        // If the country code is set on the user, return it
        if ( isset( $user->billing_country ) ) {
            return $user->billing_country;
        }
        // Geolocate the user IP and return the country code
        return self::get_visitor_country();
    }
    
    public static function is_dashboard()
    {
        global  $pagenow ;
        // Don't check for the plugin settings page. Notifications have to be handled there.
        $allowed_pages = [ 'index.php', 'dashboard' ];
        foreach ( $allowed_pages as $allowed_page ) {
            if ( strpos( $pagenow, $allowed_page ) !== false ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Trim a settings page input string by removing all whitespace and newlines.
     * Source: https://stackoverflow.com/a/3760830/4688612
     *
     * @return string
     * @since 1.27.10
     */
    public static function settings_trim( $string )
    {
        return trim( preg_replace( '/\\s\\s+/', ' ', $string ) );
    }
    
    /**
     * Filter to return the Facebook fbevents.js script URL.
     *
     * It allows to either return the latest version or a specific version.
     *
     * @return string
     * @since 1.30.0
     */
    public static function get_facebook_fbevents_js_url()
    {
        $fbevents_standard_url = 'https://connect.facebook.net/en_US/fbevents.js';
        if ( apply_filters( 'pmw_facebook_fbevents_script_version', '' ) ) {
            return $fbevents_standard_url . '?v=' . apply_filters( 'pmw_facebook_fbevents_script_version', '' );
        }
        if ( apply_filters( 'pmw_facebook_fbevents_script_url', '' ) ) {
            return apply_filters( 'pmw_facebook_fbevents_script_url', '' );
        }
        return $fbevents_standard_url;
    }

}