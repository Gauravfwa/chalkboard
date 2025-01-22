<?php

namespace OM4\WooCommerceZapier\API\Controller;

use OM4\WooCommerceZapier\API\API;
use OM4\WooCommerceZapier\Logger;
use WC_REST_Controller;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * REST API controller class that gives WooCommerce Zapier app a performant way of
 * ensuring authentication credentials are still valid.
 *
 * @since 2.0.0
 */
class PingController extends WC_REST_Controller {

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Constructor.
	 *
	 * @param Logger $logger Logger instance.
	 */
	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'ping';

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = API::REST_NAMESPACE;

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args' => array(),
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Check whether a given request has permission to ping.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'webhooks', 'read' ) ) {
			if ( ! is_ssl() ) {
				// Log this because WooCommerce's REST API does not perform Basic Authentication if is_ssl() is false: https://github.com/woocommerce/woocommerce/blob/4.0.1/includes/class-wc-rest-authentication.php#L81.
				$this->logger->critical( 'WooCommerce REST API Basic Authentication was not performed during ping because is_ssl() returned false. A HTTP %d response occurred.', (string) rest_authorization_required_code() );
			}
			return new WP_Error(
				'woocommerce_rest_cannot_view',
				__( 'Sorry, you cannot list resources.', 'woocommerce-zapier' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * (Fast) ping response.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 *
	 * @return array|WP_Error
	 */
	public function get_items( $request ) {
		return rest_ensure_response( array() );
	}
}
