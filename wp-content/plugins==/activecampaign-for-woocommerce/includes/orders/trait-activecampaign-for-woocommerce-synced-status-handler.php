<?php

/**
 * The Ecom Product Factory file.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/
 */

use Activecampaign_For_Woocommerce_Logger as Logger;

trait Activecampaign_For_Woocommerce_Synced_Status_Handler {

	/**
	 * @var mixed The readable status mapping.
	 */
	private $readable_status_mapping = [
		0  => [
			'title' => 'Unsynced',
			'help'  => 'This record has not been synced to ActiveCampaign.',
		],
		1  => [
			'title' => 'Synced',
			'help'  => 'This record has been synced to ActiveCampaign.',
		],
		2  => [
			'title' => 'On Hold',
			'help'  => 'This is an on hold record or on hold order. Skip it for now until an event marks it available to sync again.',
		],
		3  => [
			'title' => 'Historical Sync Pending',
			'help'  => 'Historical sync is pending for this record. It is in a scheduled state.',
		],
		4  => [
			'title' => 'Historical Sync Preparation',
			'help'  => 'This record is ready for historical sync preparation. It has been marked to sync but has not yet been scheduled.',
		],
		5  => [
			'title' => 'Historical Sync Finished',
			'help'  => 'Historical sync has finished this record but the sync may still be running.',
		],
		6  => [
			'title' => 'Records Incompatible',
			'help'  => 'Historical sync could not collect required data or could not find the customer/order records.',
		],
		7  => [
			'title' => 'Subscription, unable to currently sync',
			'help'  => 'This is a subscription record. ActiveCampaign is unable to consume data of this type right now.',
		],
		8  => [
			'title' => 'Refund order, unable to currently sync',
			'help'  => 'One or more items on this record have been refunded. ActiveCampaign is unable to process them currently.',
		],
		9  => [
			'title' => 'Sync Failed, will not try again',
			'help'  => 'Sync for this record has permanently failed. Please check logs for explanation. This record will not be synced again until marked otherwise.',
		],
		86 => [
			'title' => 'Record deleted from WooCommerce',
			'help'  => 'This record may have been deleted from WooCommerce. It will be removed from the table.',
		],
	];

	public function get_readable_sync_status( $status_ref ) {
		$mappings = $this->readable_status_mapping;

		foreach ( $mappings as $local_numeric => $readable ) {
			if ( $status_ref == $local_numeric ) {
				return $readable;
			}
		}
	}

	public function get_readable_sync_status_title( $status_ref ) {
		$return = $this->get_readable_sync_status( $status_ref );
		return $return['title'];
	}

	public function get_readable_sync_status_help( $status_ref ) {
		$return = $this->get_readable_sync_status( $status_ref );
		return $return['help'];
	}

	/**
	 * Mark this order as failed in our database table.
	 *
	 * @param int $wc_order_id
	 */
	public function mark_order_as_failed( $wc_order_id ) {
		global $wpdb;
		$data = [ 'synced_to_ac' => self::STATUS_FAIL ];
		$wpdb->update(
			$wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME,
			$data,
			[
				'wc_order_id' => $wc_order_id,
			]
		);
	}

	/**
	 * Mark this order as failed in our database table.
	 *
	 * @param int $wc_order_id
	 */
	public function mark_order_as_incompatible( $wc_order_id ) {
		global $wpdb;
		$data = [ 'synced_to_ac' => self::STATUS_HISTORICAL_SYNC_INCOMPATIBLE ];
		$wpdb->update(
			$wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME,
			$data,
			[
				'wc_order_id' => $wc_order_id,
			]
		);
	}

	/**
	 * Mark this order as unsynced because the overall request failed, it will have to be synced again.
	 *
	 * @param int $wc_order_id
	 */
	public function mark_order_as_pending( $wc_order_id ) {
		global $wpdb;
		$data = [ 'synced_to_ac' => self::STATUS_HISTORICAL_SYNC_PEND ];
		$wpdb->update(
			$wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME,
			$data,
			[
				'wc_order_id' => $wc_order_id,
			]
		);
	}

	private function mark_abandoned_cart_network_failed( $abandoned_cart ) {
		global $wpdb;
		$logger = new Logger();

		$fail_step = self::STATUS_ABANDONED_CART_FAILED_WAIT;

		$wpdb->update(
			$wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME,
			[
				'synced_to_ac'   => $fail_step,
				'abandoned_date' => $abandoned_cart->last_access_time,
			],
			[
				'id' => $abandoned_cart->id,
			]
		);

		if ( $wpdb->last_error ) {
			$logger->error(
				'A database error was encountered attempting to update a record in the' . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME . ' table.',
				[
					'wpdb_last_error'  => $wpdb->last_error,
					'suggested_action' => 'Please check the message for explanation and contact ActiveCampaign support if the issue repeats.',
					'ac_code'          => 'TSSH_239',
					'order_id'         => $abandoned_cart->id,
				]
			);
		}
	}

	private function mark_abandoned_cart_failed( $abandoned_cart ) {
		global $wpdb;
		$logger = new Logger();

		try {
			$fail_step = self::STATUS_ABANDONED_CART_FAILED_WAIT;

			// First fail
			if ( self::STATUS_ABANDONED_CART_UNSYNCED == $abandoned_cart->synced_to_ac ) {
				$fail_step = self::STATUS_ABANDONED_CART_FAILED_WAIT;
			}

			// Second fail
			if ( self::STATUS_ABANDONED_CART_FAILED_WAIT == $abandoned_cart->synced_to_ac ) {
				$fail_step = self::STATUS_ABANDONED_CART_FAILED_2;
			}

			// If it failed three times mark it perm fail
			if ( self::STATUS_ABANDONED_CART_FAILED_2 == $abandoned_cart->synced_to_ac ) {
				$fail_step = self::STATUS_ABANDONED_CART_NETWORK_FAIL_PERM;
			}

			$wpdb->update(
				$wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME,
				[
					'synced_to_ac'   => $fail_step,
					'abandoned_date' => $abandoned_cart->last_access_time,
				],
				[
					'id' => $abandoned_cart->id,
				]
			);

			if ( $wpdb->last_error ) {
				$logger->error(
					'A database error was encountered attempting to update a record in the ' . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME . ' table',
					[
						'wpdb_last_error'  => $wpdb->last_error,
						'suggested_action' => 'Please check the message for explanation and contact ActiveCampaign support if the issue repeats.',
						'order_id'         => $abandoned_cart->id,
						'ac_code'          => 'TSSH_284',
					]
				);
			}
		} catch ( Throwable $t ) {
			$logger->error(
				'An exception was thrown while attempting to mark an abandoned cart as failed.',
				[
					'message'          => $t->getMessage(),
					'suggested_action' => 'Please check the message for explanation and contact ActiveCampaign support if the issue repeats.',
					'ac_code'          => 'TSSH_295',
					'trace'            => $t->getTrace(),
				]
			);
		}
	}

	private function clean_all_old_historical_syncs() {
		global $wpdb;
		$logger = new Logger();
		try {
			$wipe_time    = 40320;
			$expire_4week = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $wipe_time . ' minutes' ) );

			$wipe_time    = 20160;
			$expire_2week = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $wipe_time . ' minutes' ) );

			$synced_to_ac_implode = implode(
				',',
				[
					self::STATUS_HISTORICAL_SYNC_FINISH,
					self::STATUS_HISTORICAL_SYNC_INCOMPATIBLE,
					self::STATUS_HISTORICAL_SYNC_PREP,
					self::STATUS_HISTORICAL_SYNC_PEND,
				]
			);

			// phpcs:disable
			$delete_count = $wpdb->query(
				'DELETE FROM ' . $wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME .
				' WHERE (order_date < "' . $expire_2week . '" AND synced_to_ac IN (' . $synced_to_ac_implode . ') ) OR (order_date < "' . $expire_4week . '" AND synced_to_ac = 1) OR (order_date IS NULL AND abandoned_date IS NULL AND last_access_time IS NULL)'
			);

			// phpcs:enable
			if ( ! empty( $delete_count ) ) {
				$logger->debug( $delete_count . ' old historical sync records deleted.' );

				if ( $wpdb->last_error ) {
					$logger->error(
						'A database error was encountered while attempting to delete old historical sync records.',
						[
							'wpdb_last_error' => $wpdb->last_error,
							'ac_code'         => 'HCU_118',
						]
					);
				}
			}
		} catch ( Throwable $t ) {
			$logger->error(
				'An exception was encountered while preparing or getting historical sync results.',
				[
					'message' => $t->getMessage(),
					'ac_code' => 'HCU_133',
					'trace'   => $logger->clean_trace( $t->getTrace() ),
				]
			);
		}
	}
}
