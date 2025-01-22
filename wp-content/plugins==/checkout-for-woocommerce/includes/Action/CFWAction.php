<?php

namespace Objectiv\Plugins\Checkout\Action;

use Objectiv\BoosterSeat\Base\Action;

/**
 * Class CFWAction
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
abstract class CFWAction extends Action {

	/**
	 * AccountExistsAction constructor.
	 *
	 * @param $id
	 * @param $no_privilege
	 * @param $action_prefix
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		/**
		 * PHP Warning / Notice Suppression
		 */
		if ( ! defined( 'CFW_DEV_MODE' ) || ! CFW_DEV_MODE ) {
			ini_set( 'display_errors', 'Off' );
		}

		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * @since 1.0.0
	 * @access protected
	 * @param $out
	 */
	protected function out( $out ) {
		// TODO: Execute and out (in Action) should be final and not overrideable. Action needs to NOT force JSON as an object. Could use a parameter to flip JSON to object
		if ( ! defined( 'OBJECTIV_BOOSTER_NO_BUFFER' ) ) {
			@ob_end_clean(); // @phpcs:ignore
		}

		echo json_encode( $out );
		wp_die();
	}
}
