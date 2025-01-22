<?php
/**
 * User Journey Charitable Processing.
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to process user journey data for charitable.
 *
 * This class extends MonsterInsights_User_Journey_Process base class for interactivity with
 * databse.
 */
class MonsterInsights_User_Journey_Process_Charitable extends MonsterInsights_User_Journey_Process {

	/**
	 * Meta key to store temporary data.
	 */
	private $temp_meta_key = '_monsterinsights_temporary_user_journey';

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		// Temporary store user journey.
		add_action( 'charitable_after_save_donation', array( $this, 'after_save_donation' ), 12, 1 );

		// Store user journey to permanent.
		add_action( 'charitable_donation_status_charitable-completed', array( $this, 'process_user_journey' ), 12, 1 );
	}

	/**
	 * Provider name/slug.
	 *
	 * @return string
	 * @since 1.0.2
	 */
	protected function get_provider() {
		return 'charitable';
	}

	/**
	 * After the donation has been saved in the database.
	 * Store user joruney in post meta as temporary.
	 *
	 * @param int $donation_id
	 *
	 * @return void
	 */
	public function after_save_donation( $donation_id ) {
		$journey = $this->get_sanitized_user_journey();

		if ( ! $journey ) {
			return;
		}

		update_post_meta( $donation_id, $this->temp_meta_key, $journey );

		// Reset the cookie.
		$cookie_path = defined( 'SITECOOKIEPATH' ) ? SITECOOKIEPATH : '/';
		setcookie( '_monsterinsights_uj', '', time() - 3600, $cookie_path );
	}

	/**
	 * Process User Journey data once the donation has been completed.
	 *
	 * @param Charitable_Donation $donation
	 *
	 * @return void
	 */
	public function process_user_journey( $donation ) {
		$is_in_ga = get_post_meta( $donation->ID, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_charitable_transaction_skip_user_journey', false, $donation->ID );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $skip_ga || 'yes' !== $is_in_ga ) {
			return;
		}

		$already_completed = get_post_meta( $donation->ID, '_monsterinsights_user_journey_completed', true );

		if ( $already_completed && 'yes' === $already_completed ) {
			return;
		}

		// Get user journey from post meta.
		$temp_journey = get_post_meta( $donation->ID, $this->temp_meta_key, true );

		if ( $temp_journey ) {
			$this->process_entry_meta( $donation->ID, '', $temp_journey );
		}
	}

}

if ( MonsterInsights_User_Journey_Helper::is_charitable_active() ) {
	new MonsterInsights_User_Journey_Process_Charitable();
}
