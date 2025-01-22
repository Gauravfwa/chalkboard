<?php
/**
 * This file contains reuseable class and methods helpful in
 * User Journey processing.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */


/**
 * Class MonsterInsights_User_Journey_Helper
 *
 * @since 1.0.0
 */
class MonsterInsights_User_Journey_Helper {

	/**
	 * Check for current screen and match it with the screen on which we will be
	 * displaying the metabox for the providers we support.
	 *
	 * @param array $screen_id The id/name of the screen to check.
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public static function is_supported_provider_screen( $screens ) {

		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$current_screen = get_current_screen();

		if ( is_object( $current_screen ) ) {
			if ( in_array( $current_screen->id, $screens ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the page <title> from a given URL.
	 *
	 * @param string $url Page URL.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function get_html_page_title( $url ) {

		if ( ! apply_filters( 'monsterinsights_user_journey_process_referrer_page_title', true ) ) {
			return '';
		}

		$request = wp_remote_get( $url );

		if ( 'OK' !== wp_remote_retrieve_response_message( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return '';
		}

		$response = wp_remote_retrieve_body( $request );

		preg_match( '/<title>(.*)<\/title>/i', $response, $matches );

		return ! empty( $matches[1] ) ? trim( $matches[1] ) : '';
	}

	/**
	 * Check if LifterLMS is active
	 *
	 * @return bool
	 * @since 1.0.2
	 */
	public static function is_lifter_lms_active() {
		return function_exists( 'llms' );
	}

	/**
	 * Check if GiveWP is active.
	 *
	 * @return boolean
	 * @since 1.0.2
	 *
	 * @since 1.0.2
	 */
	public static function is_givewp_active() {
		return function_exists( 'give' );
	}

	/**
	 * Get Donation ID which is different from Donation Post ID/Actual Donation ID.
	 *
	 * @param int $payment_id ID of the payment.
	 *
	 * @return int
	 * @since 1.0.2
	 */
	public static function givewp_donation_id( $payment_id ) {
		$payment = new Give_Payment( $payment_id );

		return isset( $payment->number ) ? $payment->number : $payment_id;
	}

	/**
	 * Check if EDD is active.
	 *
	 * @return boolean
	 * @since 1.0.2
	 */
	public static function is_edd_active() {
		return class_exists( 'Easy_Digital_Downloads' );
	}

	/**
	 * Check if Restrict Content Pro is active.
	 *
	 * @return boolean
	 * @since 1.0.2
	 */
	public static function is_rcp_active() {
		return class_exists( 'Restrict_Content_Pro' );
	}

	/**
	 * Restrict Content Pro - Return instance of class RCP_Payments.
	 *
	 * @return object
	 * @since 1.0.2
	 */
	public static function rcp_payments() {
		return new RCP_Payments();
	}

	/**
	 * Is MemberPress active.
	 *
	 * @return boolean
	 * @since 1.0.2
	 */
	public static function is_memberpress_active() {
		return defined( 'MEPR_VERSION' );
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public static function is_woocommerce_active() {
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * eComm Providers to integrate.
	 *
	 * @since 1.0.2
	 *
	 * @var array
	 */
	public static function ecommerce_providers() {
		return array(
			'woocommerce',
			'lifterlms',
			'memberpress',
			'edd',
			'givewp',
			'restrict-content-pro',
			'charitable',
		);
	}

	/**
	 * Check if an array is a valid array and not empty.
	 * This will also check if a key exists inside an array
	 * if the param is set to true.
	 *
	 * @param array   $array Array to check.
	 * @param string  $key Array key to check.
	 * @param boolean $check_key Wether to check the key or not.
	 *
	 * @return boolean
	 * @since 1.0.2
	 */
	public static function is_valid_array( $array, $key, $check_key = false ) {
		if ( is_array( $array ) ) {
			if ( ! empty( $array ) ) {
				if ( $check_key ) {
					if ( array_key_exists( $key, $array ) ) {
						return true;
					} else {
						return false;
					}
				}

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Hide User Journey reports/metabox if reports are disabled
	 * in the settings and also if current user role does not
	 * have permission to view reports.
	 *
	 * @return bool
	 * @since 1.0.2
	 */
	public static function can_view_user_journey() {
		if ( monsterinsights_get_option( 'dashboards_disabled' ) ) {
			if ( 'dashboard_widget' === monsterinsights_get_option( 'dashboards_disabled' ) || 'disabled' === monsterinsights_get_option( 'dashboards_disabled' ) ) {
				return false;
			}
		}

		$view_reports       = monsterinsights_get_option( 'view_reports' );
		$current_user_roles = wp_get_current_user()->roles;
		$in_roles           = array();

		if ( is_array( $view_reports ) && is_array( $current_user_roles ) ) {
			$in_roles = array_intersect( $current_user_roles, $view_reports );

			if ( empty( $in_roles ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get WooCommerce Order ID from the Order Object
	 *
	 * @since 1.0.3
	 *
	 * @param int $order_post_id Post ID of the order
	 *
	 * @return mixed
	 */
	public static function get_woo_order_id( $order_post_id ) {
		$order    = wc_get_order( $order_post_id );
		$order_id = $order->get_order_number();

		return $order_id;
	}

	/**
	 * Get EDD Order ID to display
	 *
	 * @since 1.0.3
	 *
	 * @param int $order_post_id Post or Payment ID of the order
	 *
	 * @return mixed
	 */
	public static function get_edd_order_id( $order_post_id ) {
		$payment = function_exists( 'edd_get_order' ) ? edd_get_order( $order_post_id ) : edd_get_payment( $order_post_id );

		if ( ! is_object( $payment ) && empty( $payment ) ) {
			return '';
		}

		if ( isset( $payment->order_number ) ) {
			return $payment->order_number;
		}

		return $payment->ID;
	}

	/**
	 * Get Order data based on the provider being loaded.
	 *
	 * @param int $id Order ID.
	 *
	 * @return array
	 * @since 1.0.7
	 */
	public static function get_provider_order_data( $id ) {
		$provider = self::get_provider();

		$order_data = array();

		if ( ! $provider ) {
			return $order_data;
		}

		switch ( $provider ) {
			case 'woocommerce':
				$order = wc_get_order($id);

				if (!$order) {
					return $order_data;
				}

				$order_data['id']                  = $order->get_id();
				$order_data['date']                = $order->get_date_created()->setTimezone( new DateTimeZone( "UTC" ) )->format( 'Y-m-d H:i:s' );
				$order_data['total']               = $order->get_total();
				$order_data['total_with_currency'] = get_woocommerce_currency_symbol() . $order->get_total();
				$order_data['edit_order_url']      = $order->get_edit_order_url();
				break;
			case 'rcp':
				$rcp_payments = new RCP_Payments();
				$payment      = $rcp_payments->get_payment($id);

				if (!$payment) {
					return $order_data;
				}

				$order_data['id']                  = $payment->id;
				$order_data['date']                = $payment->date;
				$order_data['total']               = $payment->amount;
				$order_data['total_with_currency'] = rcp_currency_filter( $payment->amount );
				$order_data['edit_order_url']      = add_query_arg( array( 'payment_id' => urlencode( $payment->id ), 'view' => 'edit-payment' ), admin_url( 'admin.php?page=rcp-payments' ) );
				break;
			case 'memberpress':
				$txn     = new MeprTransaction();
				$payment = $txn->get_one($id);

				if (!$payment) {
					return $order_data;
				}

				$mepr_options = MeprOptions::fetch();

				$order_data['id']                  = $payment->id;
				$order_data['date']                = $payment->created_at;
				$order_data['total']               = $payment->total;
				$order_data['total_with_currency'] = $mepr_options->currency_symbol . $payment->total;
				$order_data['edit_order_url']      = admin_url( 'admin.php?page=memberpress-trans&action=edit&id=' . $payment->id );
				break;
			case 'lifterlms':
				$order = llms_get_post($id);

				if (!$order || !is_a($order, 'LLMS_Order')) {
					return $order_data;
				}

				$order_data['id']                  = $order->get( 'id' );
				$order_data['date']                = $order->get( 'date_gmt' );
				$order_data['total']               = $order->get( 'total' );
				$order_data['total_with_currency'] = $order->get( 'total' );
				$order_data['edit_order_url']      = admin_url( 'post.php?action=edit&post=' . $order_data['id'] );
				break;
			case 'givewp':
				$payment_id = absint($id);
				$payment    = new Give_Payment($payment_id);

				$payment_exists = $payment->ID;

				if (!$payment_exists) {
					return $order_data;
				}

				$order_data['id']                  = $payment->ID;
				$order_data['date']                = get_gmt_from_date( $payment->date );
				$order_data['total']               = $payment->total;
				$order_data['total_with_currency'] = give_currency_symbol( $payment->currency ) . $payment->total;
				$order_data['edit_order_url']      = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $payment->ID );
				break;
			case 'edd':
				$payment = function_exists('edd_get_order') ? edd_get_order($id) : edd_get_payment($id);

				if (!is_object($payment) && empty($payment)) {
					return $order_data;
				}

				$order_data['id'] = $payment->ID;

				if (isset($payment->date_completed)) {
					$order_data['date'] = $payment->date_completed;
				} else if (isset($payment->completed_date)) {
					$order_data['date'] = $payment->completed_date;
				}

				$order_data['total']               = $payment->total;
				$order_data['total_with_currency'] = edd_currency_filter( edd_format_amount( $payment->total, true, '', 'typed' ) );
				$order_data['edit_order_url']      = edd_get_admin_url( array( 'page' => 'edd-payment-history', 'view' => 'view-order-details', 'id' => $payment->ID ) );
				break;

			case 'charitable':
				$donation = charitable_get_donation( $id );

				if ( ! $donation ) {
					return $order_data;
				}

				$order_data['id']                  = $donation->ID;
				$order_data['date']                = $donation->get_date( 'Y-m-d H:i:s' );
				$order_data['total']               = $donation->get_total();
				$order_data['total_with_currency'] = charitable_format_money( $donation->get_total() );
				$order_data['edit_order_url']      = add_query_arg( array( 'post' => $donation->ID, 'action' => 'edit' ), admin_url( 'post.php' ) );
				break;

			default:
				return $order_data;
		}

		return $order_data;
	}

	/**
	 * Get active provider name.
	 *
	 * @return false|string
	 */
	public static function get_provider() {
		if ( self::is_woocommerce_active() ) {
			return 'woocommerce';
		}

		if ( self::is_edd_active() ) {
			return 'edd';
		}

		if ( self::is_memberpress_active() ) {
			return 'memberpress';
		}

		if ( self::is_rcp_active() ) {
			return 'rcp';
		}

		if ( self::is_lifter_lms_active() ) {
			return 'lifterlms';
		}

		if ( self::is_givewp_active() ) {
			return 'givewp';
		}

		if ( self::is_charitable_active() ) {
			return 'charitable';
		}

		return false;
	}

	/**
	 * Check charitable plguin is active or not.
	 *
	 * @return bool
	 */
	public static function is_charitable_active() {
		return class_exists( 'Charitable' );
	}
}
