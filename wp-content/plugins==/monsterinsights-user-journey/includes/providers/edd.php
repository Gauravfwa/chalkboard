<?php
/**
 * User Journey EDD Processing.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to process user journey for EDD.
 *
 * This class extends MonsterInsights_User_Journey_Process base class for interactivity with
 * database.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_Process_EDD extends MonsterInsights_User_Journey_Process {

	/**
	 * Initialize.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'edd_update_payment_status', array( $this, 'process_user_journey' ), 10 );
	}

	/**
	 * Provider name/slug.
	 *
	 * @return string
	 * @since 1.0.2
	 *
	 */
	protected function get_provider() {
		return 'edd';
	}

	/**
	 * Process User Journey data once the order has been placed by the user.
	 *
	 * @param int $payment_id EDD Payment ID.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function process_user_journey( $payment_id ) {

		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_edd_transaction_skip_user_journey', false, $payment_id );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $skip_ga || 'yes' !== $is_in_ga ) {
			return;
		}

		$already_completed = get_post_meta( $payment_id, '_monsterinsights_user_journey_completed', true );

		if ( $already_completed && 'yes' === $already_completed ) {
			return;
		}

		$this->process_entry_meta( $payment_id );
	}
}

if ( MonsterInsights_User_Journey_Helper::is_edd_active() ) {
	new MonsterInsights_User_Journey_Process_EDD();
}
