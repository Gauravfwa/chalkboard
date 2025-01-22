<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class ApplyCouponAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class ApplyCouponAction extends CFWAction {

	/**
	 * ApplyCouponAction constructor.
	 *
	 * @param $id
	 * @param $no_privilege
	 * @param $action_prefix
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * Applies the coupon discount and returns the new totals
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		ob_start();

		if ( empty( $_POST['coupon_code'] ) || ! WC()->cart->apply_coupon( sanitize_text_field( $_POST['coupon_code'] ) ) ) {
			/**
			 * Filters apply coupon response error
			 *
			 * @since 3.0.0
			 *
			 * @param array $response The response object
			 */
			$response = apply_filters(
				'cfw_apply_coupon_response_error',
				array(
					'result' => false,
					'code'   => false,
				)
			);

		} else {
			WC()->cart->calculate_totals();

			/**
			 * Filters apply coupon successful response object
			 *
			 * @since 3.0.0
			 *
			 * @param array $response The response object
			 */
			$response = array(
				'result' => true,
				'code'   => $_POST['coupon_code'],
			);
		}

		$response['html'] = ob_get_clean();

		/**
		 * Filters apply coupon response object
		 *
		 * @since 3.0.0
		 *
		 * @param array $response The response object
		 */
		$response = apply_filters( 'cfw_apply_coupon_response', $response );

		$this->out(
			$response
		);
	}
}
