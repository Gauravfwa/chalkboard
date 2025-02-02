<?php

namespace DeliciousBrains\WPMDB\Common\TPF;

use DeliciousBrains\WPMDB\Common\Error\ErrorLog;
use DeliciousBrains\WPMDB\Common\FormData\FormData;
use DeliciousBrains\WPMDB\Common\Http\Http;
use DeliciousBrains\WPMDB\Common\MigrationPersistence\Persistence;
use DeliciousBrains\WPMDB\Data\Stage;
use WP_Error;

class TransferCheck {
	/**
	 * @var FormData
	 */
	private $form_data;

	/**
	 * @var Http
	 */
	private $http;

	/**
	 * @var ErrorLog
	 */
	private $error_log;

	public function __construct(
		FormData $form_data,
		Http $http,
		ErrorLog $error_log
	) {
		$this->form_data = $form_data;
		$this->http      = $http;
		$this->error_log = $error_log;
	}

	/**
	 * Fires on `wpmdb_initiate_migration`
	 *
	 * @param array $state_data
	 *
	 * @return array|WP_Error
	 */
	public function transfer_check( $state_data ) {
		if ( is_wp_error( $state_data ) ) {
			return $state_data;
		}

		$message = null;

		// ***+=== @TODO - revisit usage of parse_migration_form_data
		$form_data = $this->form_data->parse_and_save_migration_form_data( $state_data['form_data'] );
		if (
			empty( array_intersect(
				[ Stage::THEME_FILES, Stage::PLUGIN_FILES, Stage::MUPLUGIN_FILES, Stage::OTHER_FILES, Stage::ROOT_FILES ],
				$form_data['current_migration']['stages']
			) )
		) {
			return $state_data;
		}

		if ( ! isset( $state_data['intent'] ) ) {
			$this->error_log->log_error( 'Unable to determine migration intent - $state_data[\'intent\'] empty' );

			return new WP_Error(
				'wpmdb_error',
				__( 'A problem occurred starting the Themes & Plugins migration.', 'wp-migrate-db' )
			);
		}

		$key                 = 'push' === $state_data['intent'] ? 'remote' : 'local';
		$site_details        = $state_data['site_details'][ $key ];
		$tmp_folder_writable = isset( $site_details['local_tmp_folder_writable'] ) ? $site_details['local_tmp_folder_writable'] : null;

		// $tmp_folder_writable is `null` if remote doesn't have T&P addon installed
		if ( false !== $tmp_folder_writable || false !== Persistence::getRemoteWPECookie() ) {
			return $state_data;
		}

		$tmp_folder_error_message = isset( $site_details['local_tmp_folder_check']['message'] ) ? $site_details['local_tmp_folder_check']['message'] : '';

		$error_message = __(
			'Unfortunately it looks like we can\'t migrate your themes or plugins. However, running a migration without themes and plugins should work. Please uncheck the Themes checkbox, uncheck the Plugins checkbox, and try your migration again.',
			'wp-migrate-db'
		);

		$link = 'https://deliciousbrains.com/wp-migrate-db-pro/doc/theme-plugin-files-errors/';
		$more = __( 'More Details »', 'wp-migrate-db' );

		$message = sprintf(
			'<p class="t-p-error">%s</p><p class="t-p-error">%s <a href="%s" target="_blank">%s</a></p>',
			$error_message,
			$tmp_folder_error_message,
			$link,
			$more
		);

		return new WP_Error( 'wpmdb_error', $message );
	}
}
