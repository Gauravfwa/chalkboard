<?php

use Activecampaign_For_Woocommerce_Api_Client_Graphql as Api_Client_Graphql;
use Activecampaign_For_Woocommerce_Interacts_With_Api as Interacts_With_Api;
use Activecampaign_For_Woocommerce_Simple_Graphql_Serializer as GraphqlSerializer;
use Activecampaign_For_Woocommerce_Cofe_Sync_Connection as Cofe_Sync_Connection;
use Activecampaign_For_Woocommerce_Logger as Logger;

/**
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/repositories
 */
class Activecampaign_For_Woocommerce_Cofe_Order_Repository {
	use Interacts_With_Api;

	/**
	 * The API client.
	 *
	 * @var Api_Client_Graphql
	 */
	private $client;

	/**
	 * Ecom_Order Repository constructor.
	 *
	 * @param Api_Client_Graphql $client The api client.
	 */
	public function __construct( Api_Client_Graphql $client ) {
		$this->client = $client;
		// Prod/Staging:
		$this->client->configure_client( null, 'ecom/graphql' );
		$this->client->set_max_retries( 2 );
	}
	/**
	 * Creates a remote resource and updates the model with the returned data.
	 *
	 * @param array $model The model to be created remotely.
	 * @return string object from graphql or null
	 */
	public function upsert_order( $model ) {
		$logger = new Logger();

		try {
			// GraphqlSerializer::graphql_serialize( 'orders', $model );

			if ( $model ) {
				$response = $this->client->mutation(
					'upsertOrder',
					'order',
					$model,
					array(
						'id',
					)
				);

				return $response;
			} else {
				$logger->warning(
					'No valid models were provided to the single order sync for upsert order.',
					[
						'models'  => $model,
						'ac_code' => 'COR_58',
					]
				);

				return null;
			}
		} catch ( Throwable $t ) {
			$split = explode( 'Response:', $t->getMessage() );
			if ( isset( $split[1] ) ) {
				$ob = maybe_unserialize( $split[1] );
			}
			$logger->warning(
				'Order repository failed to send graphql data. Process must be ended.',
				[
					'message' => $t->getMessage(),
					'code'    => $t->getCode(),
					'trace'   => $logger->clean_trace( $t->getTrace() ),
					'ac_code' => 'COR_73',
				]
			);
			return null;
		}
	}

	/**
	 * Creates a remote resource and updates the model with the returned data.
	 *
	 * @param array $models The model to be created remotely.
	 * @return mixed object from graphql or null
	 */
	public function create_bulk( $models ) {
		$logger = new Logger();

		try {
			// GraphqlSerializer::graphql_serialize( 'orders', $models );

			if ( $models ) {
				$response = $this->client->mutation(
					'bulkUpsertOrdersAsync',
					'orders',
					$models,
					array(
						'recordId',
					)
				);

				return $response;
			} else {
				$logger->warning(
					'No valid models were provided to the order bulk sync.',
					[
						'models'  => $models,
						'ac_code' => 'COR_114',
					]
				);

				return null;
			}
		} catch ( Throwable $t ) {
			$split = explode( 'Response: ', $t->getMessage() );

			if ( isset( $split[1] ) ) {
				$dec = json_decode( $split[1] );
				if ( isset( $dec->errors ) ) {
					if (
						isset( $dec->errors[0]->message, $dec->errors[0]->extensions ) &&
						'Validation errors' === $dec->errors[0]->message
					) {
						$data = [
							'type'   => 'validation_error',
							'errors' => $this->collect_error_records( $dec->errors[0]->extensions ),
						];

						return $data;
					}
				}
			}

			$logger->warning(
				'The order repository failed processing GraphQL data.',
				[
					'message' => $t->getMessage(),
					'code'    => $t->getCode(),
					'ac_code' => 'COR_145',
					'trace'   => $logger->clean_trace( $t->getTrace() ),
				]
			);

			return null;
		}
	}

	private function collect_error_records( $errors ) {
		$error_id_array = [];

		foreach ( $errors as $key => $error_text ) {
			$error_id_array[] = $this->get_error_record( $key );
		}

		if ( count( $error_id_array ) > 0 ) {
			return $error_id_array;
		}

		return null;
	}

	private function get_error_record( $line ) {
		// "currency-storeOrderId-7362"
		$groups = explode( '-', $line );

		if ( isset( $groups[1], $groups[2] ) && 'storeOrderId' === $groups[1] ) {
			return $groups[2];
		}
	}

}
