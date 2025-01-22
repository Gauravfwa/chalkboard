<?php

namespace WCPM\Classes\Admin;

use WCPM\Classes\Default_Options;
use WCPM\Classes\Helpers;

class Validations {

	public static function validate_imported_options( $options ) {

		$options_to_check = [
			'google'     => [
				'ads'          => [
					'conversion_id'    => '',
					'conversion_label' => '',
				],
				'analytics'    => [
					'universal'        => [
						'property_id' => '',
					],
					'ga4'              => [
						'measurement_id' => '',
					],
					'link_attribution' => false,
				],
				'optimize'     => [
					'container_id' => '',
				],
				'consent_mode' => [
					'active'  => false,
					'regions' => [],
				],
				'user_id'      => false,
			],
			'facebook'   => [
				'pixel_id' => '',
			],
			'shop'       => [
				'order_total_logic' => 0,
			],
			'general'    => [
				'variations_output' => true,
			],
			'db_version' => WPM_DB_VERSION,
		];

		return self::do_all_keys_exist_recursive($options_to_check, $options);
	}

	private static function do_all_keys_exist_recursive( $partial_array, $full_array ) {

		foreach ($partial_array as $key => $value) {
			if (!array_key_exists($key, $full_array)) {
				error_log('key not found: ' . $key);
				return false;
			}
			if (is_array($value)) {
				if (!self::do_all_keys_exist_recursive($value, $full_array[$key])) {
					return false;
				}
			}
		}

		return true;
	}


	public static function validate_ga4_data_api_credentials( $credentials ) {

		// If $credentials is an empty array (thus the default empty value), return true
		if (empty($credentials)) {
			return true;
		}

		if (isset($credentials['type']) && 'service_account' !== $credentials['type']) {
			return false;
		}

		// Abort if $credentials['project_id'] is not regular string
		if (isset($credentials['project_id']) && !is_string($credentials['project_id'])) {
			return false;
		}

		// Abort if $credentials['private_key_id'] is not a private key ID
		if (isset($credentials['private_key_id']) && !is_string($credentials['private_key_id'])) {
			return false;
		}

		// Abort if $credentials['private_key'] is not a private key
		if (isset($credentials['private_key']) && !is_string($credentials['private_key'])) {
			return false;
		}

		// Abort if $credentials['client_email'] is not a client email
		if (isset($credentials['client_email']) && !Helpers::is_email($credentials['client_email'])) {
			return false;
		}

		// Abort if $credentials['client_id'] is not only numbers
		if (isset($credentials['client_id']) && !is_numeric($credentials['client_id'])) {
			return false;
		}

		// Abort if $credentials['auth_uri'] is not a valid URL
		if (isset($credentials['auth_uri']) && !Helpers::is_url($credentials['auth_uri'])) {
			return false;
		}

		// Abort if $credentials['token_uri'] is not a valid URL
		if (isset($credentials['token_uri']) && !Helpers::is_url($credentials['token_uri'])) {
			return false;
		}

		// Abort if $credentials['auth_provider_x509_cert_url'] is not a valid URL
		if (isset($credentials['auth_provider_x509_cert_url']) && !Helpers::is_url($credentials['auth_provider_x509_cert_url'])) {
			return false;
		}

		// Abort if $credentials['client_x509_cert_url'] is not a valid URL
		if (isset($credentials['client_x509_cert_url']) && !Helpers::is_url($credentials['client_x509_cert_url'])) {
			return false;
		}

		return true;
	}

	public static function is_gads_conversion_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{8,11}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_hotjar_site_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{6,9}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_scroll_tracker_thresholds( $string ) {
		if (empty($string)) {
			return true;
		}

		// https://regex101.com/r/4haInV/1
		$re = '/^([\d]|[\d][\d]|100)(,([\d]|[\d][\d]|100))*$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_facebook_capi_token( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-zA-Z\d_-]{150,250}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_facebook_capi_test_event_code( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^TEST\d{3,7}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_gads_conversion_label( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[-a-zA-Z_0-9]{17,20}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_gads_aw_merchant_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{6,12}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_google_optimize_measurement_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^(GTM|OPT)-[A-Z0-9]{6,8}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_google_analytics_universal_property_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^UA-\d{6,10}-\d{1,2}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_google_analytics_4_measurement_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^G-[A-Z0-9]{10,12}$/m';

		return self::validate_with_regex($re, $string);
	}


	public static function is_google_analytics_4_api_secret( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-zA-Z\d_-]{18,26}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_google_analytics_4_property_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{6,12}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_facebook_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{14,16}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_bing_uet_tag_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{7,9}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_twitter_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-z0-9]{5,7}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_twitter_event_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^tw-[a-z0-9]{5}-[a-z0-9]{5}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_pinterest_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^\d{13}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_snapchat_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[a-z0-9\-]*$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_tiktok_pixel_id( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[A-Z0-9]{20,20}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function is_tiktok_eapi_access_token( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^[\da-z]{30,50}$/m';

		return self::validate_with_regex($re, $string);
	}


	public static function is_tiktok_eapi_test_event_code( $string ) {
		if (empty($string)) {
			return true;
		}

		$re = '/^TEST\d{3,7}$/m';

		return self::validate_with_regex($re, $string);
	}

	public static function validate_with_regex( $re, $string ) {

		// Validate if string matches the regex $re
		if (preg_match($re, $string)) {
			return true;
		}

		return false;

//		preg_match_all($re, $string, $matches, PREG_SET_ORDER, 0);
//
//		if (isset($matches[0])) {
//			return true;
//		} else {
//			return false;
//		}
	}

	public static function is_conversion_adjustments_conversion_name( $string ) {

		return filter_var($string, FILTER_SANITIZE_STRING) === $string;
	}

	public static function is_subscription_value_multiplier( $string ) {

		// Return true if $string is a float or integer
		if (!is_numeric($string)) {
			return false;
		}

		// The value must be at least 1.00
		if (floatval($string) < 1.00) {
			return false;
		}

		return true;
	}
}
