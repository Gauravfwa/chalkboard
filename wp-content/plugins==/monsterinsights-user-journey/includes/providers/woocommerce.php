<?php
/**
 * User Journey WooCommerce Processing.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to process user journey for WooCommerce.
 *
 * This class extends MonsterInsights_User_Journey_Process base class for interactivity with
 * databse.
 *
 * @since 1.0.0
 */
class MonsterInsights_User_Journey_Process_WooCommerce extends MonsterInsights_User_Journey_Process {

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_processing', array( $this, 'process_user_journey' ), 10 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'process_user_journey' ), 10 );

		add_action( 'woocommerce_new_order', array( $this, 'woocommerce_new_order_created' ) );
	}

	/**
	 * Provider name/slug.
	 *
	 * @return string
	 * @since 1.0.2
	 */
	protected function get_provider() {
		return 'woocommerce';
	}

	/**
	 * Process User Journey data once the order has been placed by the user.
	 *
	 * @param [type] $payment_id WooCommerce Payment ID.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function process_user_journey( $payment_id ) {
		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_woo_transaction_skip_user_journey', false, $payment_id );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $skip_ga || 'yes' !== $is_in_ga ) {
			return;
		}

		$order = wc_get_order( $payment_id );

		$already_completed = get_post_meta( $order->get_order_number(), '_monsterinsights_user_journey_completed', true );

		if ( $already_completed && 'yes' === $already_completed ) {
			return;
		}

		// Get user journey from post meta.
		$temp_journey = get_post_meta( $payment_id, '_monsterinsights_temporary_user_journey', true );

		if ( $temp_journey ) {
			$this->process_entry_meta( $payment_id, '', $temp_journey );
		}
	}

	/**
	 * Trigger after order has been created.
	 * Store user journey to order meta.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since 1.0.6
	 */
	public function woocommerce_new_order_created( $order_id ) {
		if ( empty( $_COOKIE['_monsterinsights_uj'] ) ) {
			return;
		}

		$journey = json_decode( wp_unslash( $_COOKIE['_monsterinsights_uj'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $journey ) || empty( $journey ) ) {
			return;
		}

		$meta_key = '_monsterinsights_temporary_user_journey';

		update_post_meta( $order_id, $meta_key, $journey );

		// Reset the cookie.
		$cookie_path = defined( 'SITECOOKIEPATH' ) ? SITECOOKIEPATH : '/';
		setcookie( '_monsterinsights_uj', '', time() - 3600, $cookie_path );
	}
}

if ( MonsterInsights_User_Journey_Helper::is_woocommerce_active() ) {
	new MonsterInsights_User_Journey_Process_WooCommerce();
}
