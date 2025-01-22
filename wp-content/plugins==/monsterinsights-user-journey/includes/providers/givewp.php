<?php
/**
 * User Journey GiveWP Processing.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to process user journey for GiveWP.
 *
 * This class extends MonsterInsights_User_Journey_Process base class for interactivity with
 * database.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_Process_GiveWP extends MonsterInsights_User_Journey_Process {

	/**
	 * Initialize.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'give_complete_donation', array( $this, 'process_user_journey' ), 11 );
		add_action( 'give_insert_payment', array( $this, 'create_donation' ) );
	}

	/**
	 * Provider name/slug.
	 *
	 * @return string
	 * @since 1.0.2
	 *
	 */
	protected function get_provider() {
		return 'givewp';
	}

	/**
	 * Process User Journey data once the order has been placed by the user.
	 *
	 * @param int $payment_id $payment_id GiveWP Payment ID.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function process_user_journey( $payment_id ) {

		$is_in_ga = give_get_payment_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_givewp_transaction_skip_user_journey', false, $payment_id );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $skip_ga || 'yes' !== $is_in_ga ) {
			return;
		}

		$already_completed = give_get_payment_meta( $payment_id, '_monsterinsights_user_journey_completed', true );

		if ( $already_completed && 'yes' === $already_completed ) {
			return;
		}

		// Get user journey from meta.
		$temp_journey = give_get_payment_meta( $payment_id, '_monsterinsights_temporary_user_journey' );

		if ( $temp_journey ) {
			$this->process_entry_meta( $payment_id, '', $temp_journey );
		}
	}

	/**
	 * Call after donation create.
	 *
	 * @param int $payment_ID Donation ID.
	 *
	 * @return void
	 */
	public function create_donation( $payment_ID ) {
		if ( empty( $_COOKIE['_monsterinsights_uj'] ) ) {
			return;
		}

		$journey = json_decode( wp_unslash( $_COOKIE['_monsterinsights_uj'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $journey ) || empty( $journey ) ) {
			return;
		}

		// Store to metadata.
		give_update_payment_meta( $payment_ID, '_monsterinsights_temporary_user_journey', $journey );

		// Reset the cookie.
		$cookie_path = defined( 'SITECOOKIEPATH' ) ? SITECOOKIEPATH : '/';
		setcookie( '_monsterinsights_uj', '', time() - 3600, $cookie_path );
	}
}

if ( MonsterInsights_User_Journey_Helper::is_givewp_active() ) {
	new MonsterInsights_User_Journey_Process_GiveWP();
}
