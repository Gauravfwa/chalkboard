<?php
/**
 * User Journey frontend related functionality.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Frontend Class
 *
 * @since 1.0.0
 */
class MonsterInsights_User_Journey_Frontend_Tracking {

	/**
	 * Initialize.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {

		// Skip tracking if not a trackable user.
		if ( ! function_exists( 'monsterinsights_debug_output' ) ) {
			$do_not_track = ! monsterinsights_track_user();
			if ( $do_not_track ) {
				return;
			}
		}

		$this->hooks();
	}

	/**
	 * Frontend hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'wp_head', array( $this, 'enqueues' ) );
		add_action( 'init', array( $this, 'remove_unwanted_uj_cookies' ) );
	}

	/**
	 * Frontend enqueues.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueues() {
		$enable = apply_filters( 'monsterinsights_user_journey_enqueue_js', true );

		if ( ! $enable ) {
			return;
		}

		$url = monsterinsights_user_journey()->url . 'assets/js/frontend/user-journey.js';

		wp_enqueue_script(
			'monsterinsights-user-journey',
			esc_url( $url ),
			array(),
			MONSTERINSIGHTS_USER_JOURNEY_VERSION
		);

		$data = array(
			'is_ssl' => is_ssl(),
		);

		if ( is_singular() ) {
			$data['page_id'] = get_the_ID();
		}

		wp_localize_script( 'monsterinsights-user-journey', 'monsterinsights_user_journey', $data );
	}

	/**
	 * Remove unwamted UserJourney cookies to prevent
	 * 403 browser cookie limit error.
	 *
	 * @uses WP 'init' hook.
	 *
	 * @since 8.8.2
	 *
	 * @return void
	 */
	public function remove_unwanted_uj_cookies() {
		$cookie_keys = array();

		foreach ( $_COOKIE as $cookie_key => $cookie_value ) {
			// Match cookies with name like monsterinsights_uj_1, monsterinsights_uj_2 and so on.
			$pattern = '/_monsterinsights_uj_\d{1}/i';
			if ( preg_match( $pattern, $cookie_key ) ) {
				$cookie_keys[] = $cookie_key;
			}
		}

		if ( ! empty( $cookie_keys ) ) {
			foreach ( $cookie_keys as $keys ) {
				// Delete unwanted cookies.
				setcookie( $keys, '', time() - 3600 );
			}
		}
	}
}
