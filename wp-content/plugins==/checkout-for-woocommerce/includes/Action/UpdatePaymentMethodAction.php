<?php

namespace Objectiv\Plugins\Checkout\Action;

/**
 * Class LogInAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class UpdatePaymentMethodAction extends CFWAction {

	/**
	 * LogInAction constructor.
	 *
	 * @param $id
	 * @param $no_privilege
	 * @param $action_prefix
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		WC()->session->set( 'chosen_payment_method', empty( $_POST['payment_method'] ) ? '' : $_POST['payment_method'] );

		$this->out( array( 'payment_method' => WC()->session->get( 'chosen_payment_method' ) ) );
	}
}
