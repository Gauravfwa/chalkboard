<?php

/**
 * The file for all order based event handling.
 *
 * @link       https://www.activecampaign.com/
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/events
 */

use Activecampaign_For_Woocommerce_Logger as Logger;
use Activecampaign_For_Woocommerce_Ecom_Order_Repository as Order_Repository;
use Activecampaign_For_Woocommerce_Api_Client as Api_Client;

/**
 * The Order_Finished Event Class.
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/events
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Order_Action_Events {
	use Activecampaign_For_Woocommerce_Order_Data_Gathering;

	public function execute_order_created( $args ) {
		$logger = new Logger();
		if ( ! isset( $args ) ) {
			return;
		}

		$logger->debug(
			'Order created triggered',
			[
				'order' => $args,
			]
		);
		if ( isset( $args ) ) {
			wp_schedule_single_event(
				time() + 30,
				'activecampaign_for_woocommerce_ready_new_order',
				$args
			);
		}
	}

	public function execute_order_updated( $order_id ) {
		$logger = new Logger();

		if ( isset( $order_id ) && ! empty( $order_id ) ) {
			$logger->debug(
				'Order update triggered',
				[
					'order' => $order_id,
				]
			);
			$post_type = get_post_type( $order_id );

			// If it's not an order just ignore it, this could be anything
			if ( 'shop_order' !== $post_type ) {
				return;
			}

			$wc_order = $this->get_wc_order( $order_id );

			// Check if order is valid
			if ( self::validate_object( $wc_order, 'get_data' ) ) {
				// This will sync it immediately but also blindly
				wp_schedule_single_event(
					time() + 30,
					'activecampaign_for_woocommerce_admin_sync_single_order_active',
					[
						'wc_order_id' => $order_id,
					]
				);
			} else {
				$logger->warning(
					'The updated order does not appear to be valid for sync to AC.',
					[
						'order_id' => $order_id,
					]
				);
			}
		}
	}

	/**
	 * Execute AC steps for an order deleted event. Triggered by action.
	 *
	 * @param string|int $order_id The arguments.
	 */
	public function execute_order_deleted( $order_id ) {
		$logger = new Logger();
		try {
			if ( isset( $order_id ) && ! empty( $order_id ) ) {
				$post_type = get_post_type( $order_id );

				// If it's not an order just ignore it, this could be anything
				if ( 'shop_order' !== $post_type ) {
					return;
				}

				$logger->debug(
					'Order delete triggered',
					[
						'order' => $order_id,
					]
				);
				// Delete an order for deepdata
				$settings = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_SETTINGS_NAME );

				$api_uri = isset( $settings['api_url'] ) ? $settings['api_url'] : null;
				$api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : null;

				$order_repository = new Order_Repository( new Api_Client( $api_uri, $api_key, $logger ) );

				// Try to find the order in AC
				$ac_order = $order_repository->find_by_externalid( $order_id );
				if ( isset( $ac_order ) && self::validate_object( $ac_order, 'get_id' ) ) {
					$ac_order_id = $ac_order->get_id();

					// If the order exists delete it from AC
					$order_repository->delete( $ac_order_id );

					// Delete a local order for COFE
					$this->delete_order_from_local_table( $order_id );
				}
			}
		} catch ( Throwable $t ) {
			$logger->warning(
				'There was an issue deleting the order from AC.',
				[
					'order_id' => $order_id,
					'message'  => $t->getMessage(),
				]
			);
		}
	}

	/**
	 * Removes a WC deleted order from the AC table.
	 * This can be used for new or historical sync orders.
	 *
	 * @param     mixed ...$args The passed arguments.
	 */
	public function delete_order_from_local_table( ...$args ) {
		if ( isset( $args[0] ) ) {
			$order_id = $args[0];

			// Find the post type in
			$post_type = get_post_type( $order_id );

			// Make sure it's a shop order
			if ( 'shop_order' !== $post_type ) {
				return;
			}

			global $wpdb;
			$wpdb->delete( $wpdb->prefix . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_TABLE_NAME, [ 'wc_order_id' => $order_id ] );
		}
	}
}
