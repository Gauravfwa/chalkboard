<?php

/**
 * The admin historical sync page specific functionality of the plugin.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.8.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin
 */

use Activecampaign_For_Woocommerce_Logger as Logger;
use Activecampaign_For_Woocommerce_Historical_Sync_Prep as Historical_Prep;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin/historical_sync
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
trait Activecampaign_For_Woocommerce_Admin_Historical_Sync {
	use Activecampaign_For_Woocommerce_Admin_Utilities,
		Activecampaign_For_Woocommerce_Synced_Status_Handler,
		Activecampaign_For_Woocommerce_Historical_Utilities;
	/**
	 * Fetches the historical sync page view.
	 *
	 * @since 1.5.0
	 */
	public function fetch_historical_sync_page() {
		wp_enqueue_script( $this->plugin_name . 'historical-sync' );

		require_once plugin_dir_path( __FILE__ )
					 . 'views/activecampaign-for-woocommerce-historical-sync.php';

	}

	/**
	 * Schedules the bulk historical sync to run as a background job.
	 *
	 * @since 1.6.0
	 */
	public function schedule_bulk_historical_sync() {
		$logger = new Logger();
		try {
			delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_RUNNING_STATUS_NAME );
			$sync_contacts = self::get_request_data( 'syncContacts' );

			if ( isset( $sync_contacts ) && $sync_contacts ) {
				// Sync all the contacts from the orders

				do_action( 'activecampaign_for_woocommerce_run_historical_sync_contacts' );

				update_option(
					ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME,
					[
						'orders'   => 'scheduled',
						'contacts' => 'scheduled',
					]
				);
			} else {
				update_option(
					ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME,
					[
						'orders'   => 'scheduled',
						'contacts' => 'not selected',
					]
				);
			}

			do_action( 'activecampaign_for_woocommerce_ready_existing_historical_data' );

			wp_schedule_single_event(
				time() + 10,
				'activecampaign_for_woocommerce_prep_historical_data',
				[]
			);
		} catch ( Throwable $t ) {
			$logger->warning(
				'There was an issue running the historical sync prep.',
				[
					'message'  => $t->getMessage(),
					'trace'    => $t->getTrace(),
					'function' => 'schedule_bulk_historical_sync',
				]
			);
		}
	}

	private function update_sync_running_status( $type, $status ) {
		$run_sync          = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME );
		$run_sync[ $type ] = $status;
		update_option(
			ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME,
			$run_sync
		);
	}

	/**
	 * Checks the status of historical sync and returns the result.
	 *
	 * @since 1.5.0
	 */
	public function check_historical_sync_status() {
		$logger = new Logger();
		global $wpdb;
		try {
			$status      = json_decode( get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_RUNNING_STATUS_NAME ), 'array' );
			$run_sync    = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME );
			$stop_status = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_STOP_CHECK_NAME );

			if ( is_null( $status ) ) {
				$status = [];
			}

			// status types
			$status['synced']       = 0;
			$status['prepared']     = 0;
			$status['pending']      = 0;
			$status['error']        = 0;
			$status['incompatible'] = 0;

			// conditionals
			$status['stuck']      = false;
			$status['is_running'] = false;

			$status['run_sync'] = $run_sync;

			// counts
			$status['total_orders'] = $this->get_sync_ready_order_count();

			if ( isset( $status['incompatible_order_id_array'] ) ) {
				$status['incompatible'] = count( $status['incompatible_order_id_array'] );
			}
		} catch ( Throwable $t ) {
			$logger->warning(
				'There was an issue getting the historical sync status',
				[
					'message'  => $t->getMessage(),
					'function' => 'check_historical_sync_status',
				]
			);
		}

		try {
			// phpcs:disable
			$sync_counts = $wpdb->get_row(
				'SELECT 
                count(id) as total,
                count(if(synced_to_ac = 4,1,null)) as prepared,
                count(if(synced_to_ac = 3,1,null)) as pending,
                count(if(synced_to_ac = 1,1,null)) as synced,
       			count(if(synced_to_ac = 6,1,null)) as incompatible,
                count(if(synced_to_ac = 9,1,null)) as error
                FROM `' . $wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME . '`;',
				ARRAY_A
			);
			// phpcs:enable

			if ( isset( $sync_counts ) ) {
				$status['synced']       = $sync_counts['synced'];
				$status['prepared']     = $sync_counts['prepared'];
				$status['incompatible'] = $sync_counts['incompatible'];
				$status['pending']      = $sync_counts['pending'];
				$status['error']        = $sync_counts['error'];
			}
		} catch ( Throwable $t ) {
			$this->logger->debug(
				'There was an issue collecting the historical sync counts from the ActiveCampaign table.',
				[
					'message' => $t->getMessage(),
				]
			);
		}

		try {
			// If there is pending and the last update was more than 30 minutes ago then this is a stuck sync
			if ( ( $status['pending'] > 0 || $status['prepared'] > 0 ) && empty( $stop_status ) ) {
				$status['is_running'] = true;
				if (
						isset( $status['last_update'], $status['pending'], $status['prepared'] )
				) {
					$last_update = time() - $status['last_update'];
					if (
						$last_update > 100 &&
						(
							! isset( $status['stop_type_name'] ) ||
							$status['pending'] > 0 ||
							$status['prepared'] > 0
						)
					) {
						$status['stuck']      = true;
						$status['is_running'] = false;
						$run_sync['orders']   = 'stuck';
					}
				}
			}

			if (
				isset( $run_sync['orders'] ) &&
				'stuck' !== $run_sync['orders'] &&
				'syncing' === $run_sync['orders'] &&
				empty( $sync_counts['pending'] ) &&
				empty( $sync_counts['prepared'] )
			) {
				$run_sync['orders'] = 'finished';
			}

			if ( $stop_status ) {
				$status['stop_status'] = true;
			}

			update_option(
				ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME,
				$run_sync
			);

			// If the sync is scheduled but has not run
			$data = (object) [
				'status' => $status,
			];
		} catch ( Throwable $t ) {
			$logger->warning(
				'There was an issue getting the historical sync status',
				[
					'message'  => $t->getMessage(),
					'function' => 'check_historical_sync_status',
				]
			);
		}

		if ( isset( $data ) ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Sets a stop for the historical sync with a condition of cancel or pause.
	 *
	 * @since 1.5.0
	 */
	public function stop_historical_sync() {
		$logger = new Logger();
		try {
			$stop_type = self::get_request_data( 'type' );
			$user      = wp_get_current_user();

			if ( ! empty( $stop_type ) ) {
				if ( in_array( $stop_type, [ 2, '2' ], true ) ) {
					delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_STOP_CHECK_NAME );
					$status                = json_decode( get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_RUNNING_STATUS_NAME ), 'array' );
					$status['stuck']       = false;
					$status['is_running']  = true;
					$status['last_update'] = time();
					update_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_RUNNING_STATUS_NAME, wp_json_encode( $status ) );
					wp_send_json_success( 'Continue requested...' );
				}

				$logger->alert(
					'Historical sync stop requested',
					[
						'stop_type'         => $stop_type,
						'requested by user' => [
							'user_id'    => isset( $user->ID ) ? $user->ID : null,
							'user_email' => isset( $user->data->user_email ) ? $user->data->user_email : null,
						],
					]
				);

				update_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_STOP_CHECK_NAME, true, false );
				wp_send_json_success( 'Stop requested...' );
			} else {
				wp_send_json_success( 'No argument provided' );
			}
		} catch ( Throwable $t ) {
			$logger->warning(
				'There was an issue stopping the historical sync.',
				[
					'message'  => $t->getMessage(),
					'function' => 'stop_historical_sync',
				]
			);
		}
	}

	/**
	 * Resets the historical sync if it gets in a stuck position.
	 *
	 * @since 1.5.0
	 */
	public function reset_historical_sync() {
		delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_RUNNING_STATUS_NAME );
		delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_SCHEDULED_STATUS_NAME );
		delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_HISTORICAL_SYNC_LAST_STATUS_NAME );
		$this->clean_bad_data_from_table();
		$this->clean_all_old_historical_syncs();
		wp_send_json_success( 'Sync statuses cleared.' );
	}
}
