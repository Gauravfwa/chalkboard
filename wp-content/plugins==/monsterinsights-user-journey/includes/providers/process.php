<?php
/**
 * User Journey processing.
 *
 * Class in this file should be extended by other classes to process
 * the data for User Journey.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Base class to process user journey data.
 *
 * @since 1.0.0
 */
abstract class MonsterInsights_User_Journey_Process {

	/**
	 * Get Currently loaded Provider Name
	 *
	 * @since 1.0.2
	 */
	abstract protected function get_provider();

	/**
	 * Process User Journey via eCommerce Hooks
	 *
	 * @since 1.0.0
	 */
	abstract protected function process_user_journey( $payment_info );

	/**
	 * Check if user journey data present and process.
	 *
	 * @param string $entry_id Entry ID.
	 * @param object $transaction_object Entry ID.
	 * @param array  $journey Entry ID.
	 *
	 * @requires void
	 * @since 1.0.0
	 */
	protected function process_entry_meta( $entry_id, $transaction_object = '', $journey = array() ) {

		if ( empty( $journey ) ) {
			if ( empty( $_COOKIE['_monsterinsights_uj'] ) ) {
				return;
			}

			$journey = json_decode( wp_unslash( $_COOKIE['_monsterinsights_uj'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		if ( ! is_array( $journey ) || empty( $journey ) ) {
			return;
		}

		$count          = 1;
		$timestamp_prev = 0;

		foreach ( $journey as $timestamp => $record ) {

			$item = $this->get_record_data( $record, $timestamp, $count );
			$count ++;
			if ( empty( $item ) ) {
				continue;
			}

			$item = array_merge(
				array(
					'entry_id' => absint( $entry_id ),
					'duration' => ! empty( $timestamp_prev ) ? absint( $timestamp ) - absint( $timestamp_prev ) : 0,
				),
				$item
			);
			monsterinsights_user_journey()->db->add( $item );
			$timestamp_prev = $timestamp;
		}

		$this->update_provider_order_meta( $entry_id, $transaction_object );

		$cookie_path = defined( 'SITECOOKIEPATH' ) ? SITECOOKIEPATH : '/';
		setcookie( '_monsterinsights_uj', '', time() - 3600, $cookie_path ); // Reset the cookie.
	}

	/**
	 * Get record from the string.
	 *
	 * @param string $record Record string.
	 * @param int    $timestamp Timestamp.
	 * @param int    $step Current step.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function get_record_data( $record, $timestamp, $step ) {

		if ( empty( $record ) || strpos( $record, '|#|' ) === false ) {
			return array();
		}

		$parts = explode( '|#|', $record );
		$url   = esc_url_raw( strtok( $parts[0], '?' ) );

		if ( 1 !== $step && false === strpos( $url, home_url() ) ) {
			return array();
		}

		$item = array(
			'post_id'    => ! empty( $parts[2] ) ? absint( $parts[2] ) : 0,
			'url'        => $url,
			'parameters' => '',
			'title'      => ! empty( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '',
			'external'   => strpos( $parts[0], home_url() ) === false,
			'step'       => $step,
			'date'       => gmdate( 'Y-m-d H:i:s', absint( $timestamp ) ),
		);

		parse_str( wp_parse_url( $parts[0], PHP_URL_QUERY ), $params );

		if ( ! empty( $params ) ) {
			$parameters = array();
			foreach ( $params as $key => $value ) {
				$parameters[ sanitize_key( $key ) ] = sanitize_text_field( $value );
			}
			$item['parameters'] = wp_json_encode( $parameters );
		}

		if ( $step === 1 && strpos( $item['title'], '{ReferrerPageTitle}' ) !== false ) {
			$title         = MonsterInsights_User_Journey_Helper::get_html_page_title( $url );
			$item['title'] = ! empty( $title )
				? sanitize_text_field( $title )
				: str_replace( '{ReferrerPageTitle}', __( 'Referrer', 'monsterinsights-user-journey' ), $item['title'] );
		}

		return $item;
	}

	/**
	 * Update order meta values as per provider.
	 *
	 * @param int    $entry_id Order ID.
	 * @param object $transaction_object Transaction Object.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	private function update_provider_order_meta( $entry_id, $transaction_object = '' ) {

		if ( ! $this->get_provider() ) {
			return;
		}

		$provider = $this->get_provider();
		$meta_key = '_monsterinsights_user_journey_completed';

		switch ( $provider ) {
			case 'memberpress':
				if ( is_object( $transaction_object ) && ! empty( $transaction_object ) ) {
					$transaction_object->update_meta( $meta_key, 'yes' );
				}
				break;
			case 'givewp':
				give_update_payment_meta( $entry_id, $meta_key, 'yes' );
				break;
			case 'restrict-content-pro':
				$rcp_payments = MonsterInsights_User_Journey_Helper::rcp_payments();
				$rcp_payments->update_meta( $entry_id, $meta_key, 'yes' );
				break;
			default:
				update_post_meta( $entry_id, $meta_key, 'yes' );
		}
	}

	/**
	 * Get sanitized user journey.
	 */
	protected function get_sanitized_user_journey() {
		if ( empty( $_COOKIE['_monsterinsights_uj'] ) ) {
			return false;
		}

		$journies = json_decode( wp_unslash( $_COOKIE['_monsterinsights_uj'] ), true ); // phpcs:ignore

		if ( ! is_array( $journies ) || empty( $journies ) ) {
			return false;
		}

		return array_map( 'sanitize_text_field', $journies );
	}
}
