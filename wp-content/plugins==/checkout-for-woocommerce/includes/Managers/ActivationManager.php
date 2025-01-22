<?php

namespace Objectiv\Plugins\Checkout\Managers;

/**
 * Class ActivationManager
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Managers
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class ActivationManager {
	private $checks = array();

	public function __construct( $checks ) {
		$this->checks = $checks;
	}

	public function activate(): bool {
		$errors = 0;

		foreach ( $this->checks as $check ) {
			if ( ! function_exists( $check['function'] ) || ! $check['function']( $check['value'] ) ) {
				add_option( $check['id'], array( $check['message'] ) );
				$errors++;
			}
		}

		return 0 !== $errors;
	}

	/**
	 * Method to be run on unsuccessful plugin activation. The function that generates the error admin notice for plugin
	 * activation
	 *
	 * @param ExtendedPathManager $path_manager
	 * @since 1.0.0
	 * @access public
	 */
	public function activate_admin_notice( ExtendedPathManager $path_manager ) {

		foreach ( $this->checks as $check ) {
			$notice_name = $check['id'];

			$activation_error = get_option( $notice_name );

			if ( ! empty( $activation_error ) ) {

				// Get rid of "Plugin Activated" message on error.
				unset( $_GET['activate'] );

				foreach ( $activation_error as $error ) {
					if ( ! $error['success'] ) {
						// Print the error notice
						printf( "<div class='%s'><p>%s</p></div>", $error['class'], $error['message'] );
					}
				}

				// Remove the option after all error messages displayed
				delete_option( $notice_name );

				// Deactivate the plugin
				deactivate_plugins( $path_manager->get_path_main_file() );
			}
		}
	}
}
