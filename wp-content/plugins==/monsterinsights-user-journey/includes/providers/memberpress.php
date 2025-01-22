<?php
/**
 * User Journey MemberPress Processing.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to process user journey for MemberPress.
 *
 * This class extends MonsterInsights_User_Journey_Process base class for interactivity with
 * databse.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_Process_MemberPress extends MonsterInsights_User_Journey_Process {

	/**
	 * MemberPress Payment Statues to Process
	 *
	 * @var array
	 */
	public $hooks_to_process = array( 'mepr-txn-status-complete', 'mepr-txn-status-confirmed' );

	/**
	 * Initialize.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		// Hooks to process.
		foreach ( $this->hooks_to_process as $hook ) {
			add_action( $hook, array( $this, 'process_user_journey' ), 11 );
		}

		add_action( 'mepr-txn-status-pending', array( $this, 'store_temp_user_journey' ), 10 );
	}

	/**
	 * Provider name/slug.
	 *
	 * @return string
	 * @since 1.0.2
	 *
	 */
	protected function get_provider() {
		return 'memberpress';
	}

	/**
	 * Process User Journey data once the order has been placed by the user.
	 *
	 * @param MeprTransaction $txn MemberPress Payment Transaction Object.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function process_user_journey( $txn ) {
		// Don't report transactions that are not payments.
		if ( ! empty( $txn->txn_type ) && MeprTransaction::$payment_str !== $txn->txn_type ) {
			return;
		}

		$payment_id = $txn->id;

		$is_in_ga = $txn->get_meta( '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_memberpress_transaction_skip_user_journey', false, $txn );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $skip_ga || 'yes' !== $is_in_ga ) {
			return;
		}

		$already_completed = $txn->get_meta( '_monsterinsights_user_journey_completed', true );

		if ( $already_completed && 'yes' === $already_completed ) {
			return;
		}

		// Get user journey from meta.
		$temp_journey = $txn->get_meta( '_monsterinsights_temporary_user_journey', true );

		if ( $temp_journey ) {
			$this->process_entry_meta( $payment_id, $txn, $temp_journey );
		} else if ( $txn_sub = $txn->subscription() ) {
			// For subscription sometimes we do not get meta-data from real transaction.
			$temp_journey = $txn_sub->first_txn()->get_meta( '_monsterinsights_temporary_user_journey', true );

			if ( $temp_journey ) {
				$this->process_entry_meta( $payment_id, $txn, $temp_journey );
			}
		}
	}

	/**
	 * Store transaction journey to meta.
	 *
	 * @param MeprTransaction $txn MemberPress Payment Transaction Object.
	 *
	 * @return void
	 * @since 1.0.7
	 */
	public function store_temp_user_journey( $txn ) {
		if ( empty( $_COOKIE['_monsterinsights_uj'] ) ) {
			return;
		}

		$journey = json_decode( wp_unslash( $_COOKIE['_monsterinsights_uj'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $journey ) || empty( $journey ) ) {
			return;
		}

		$txn->update_meta( '_monsterinsights_temporary_user_journey', $journey );

		// Reset the cookie.
		$cookie_path = defined( 'SITECOOKIEPATH' ) ? SITECOOKIEPATH : '/';
		setcookie( '_monsterinsights_uj', '', time() - 3600, $cookie_path );
	}
}

if ( MonsterInsights_User_Journey_Helper::is_memberpress_active() ) {
	new MonsterInsights_User_Journey_Process_MemberPress();
}
