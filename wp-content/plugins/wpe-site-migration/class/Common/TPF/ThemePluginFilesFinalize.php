<?php

namespace DeliciousBrains\WPMDB\Common\TPF;

use DeliciousBrains\WPMDB\Common\Debug;
use DeliciousBrains\WPMDB\Common\Error\ErrorLog;
use DeliciousBrains\WPMDB\Common\Filesystem\Filesystem;
use DeliciousBrains\WPMDB\Common\FormData\FormData;
use DeliciousBrains\WPMDB\Common\Http\Http;
use DeliciousBrains\WPMDB\Common\MigrationPersistence\Persistence;
use DeliciousBrains\WPMDB\Common\MigrationState\MigrationStateManager;
use DeliciousBrains\WPMDB\Common\MigrationState\StateDataContainer;
use DeliciousBrains\WPMDB\Common\Queue\Manager;
use DeliciousBrains\WPMDB\Common\Transfers\Files\PluginHelper;
use DeliciousBrains\WPMDB\Common\Transfers\Files\Util;
use DeliciousBrains\WPMDB\Common\Util\Util as CommonUtil;
use DeliciousBrains\WPMDB\Data\Stage;
use Exception;
use WP_Error;

class ThemePluginFilesFinalize {
	/**
	 * @var FormData
	 */
	private $form_data;

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @var Util
	 */
	private $transfer_helpers;

	/**
	 * @var ErrorLog
	 */
	private $error_log;

	/**
	 * @var Http
	 */
	private $http;

	/**
	 * @var StateDataContainer
	 */
	private $state_data_container;

	/**
	 * @var Manager
	 */
	private $manager;

	/**
	 * @var MigrationStateManager
	 */
	private $migration_state_manager;

	/**
	 * @var PluginHelper
	 */
	private $plugin_helper;

	public function __construct(
		FormData $form_data,
		Filesystem $filesystem,
		Util $transfer_helpers,
		ErrorLog $error_log,
		Http $http,
		StateDataContainer $state_data_container,
		Manager $manager,
		MigrationStateManager $migration_state_manager,
		PluginHelper $plugin_helper
	) {
		$this->form_data               = $form_data;
		$this->filesystem              = $filesystem;
		$this->transfer_helpers        = $transfer_helpers;
		$this->error_log               = $error_log;
		$this->http                    = $http;
		$this->state_data_container    = $state_data_container;
		$this->manager                 = $manager;
		$this->migration_state_manager = $migration_state_manager;
		$this->plugin_helper           = $plugin_helper;
	}

	/**
	 * Maybe finalize theme and plugins file transfers.
	 *
	 * @param string $intent
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function maybe_finalize_tp_migration( $intent ) {
		$state_data = $intent === 'push' ? Persistence::getRemoteStateData() : Persistence::getStateData();

		if ( ! isset( $state_data['stage'] ) ) {
			return false;
		}

		$current_migration = $this->form_data->getCurrentMigrationData();

		// Check whether any of the handled stages were in the migration.
		if (
			empty( array_intersect(
				[ Stage::THEME_FILES, Stage::PLUGIN_FILES, Stage::MUPLUGIN_FILES, Stage::OTHER_FILES, Stage::ROOT_FILES ],
				$current_migration['stages']
			) )
		) {
			return false;
		}

		// Check that the number of files transferred is correct, throws exception
		$verified = $this->verify_file_transfer( $state_data );

		if ( is_wp_error( $verified ) ) {
			return $verified;
		}

		/**
		 * Note that we don't use the Stage:: constants here for stagenames (yet)
		 * the knock-on effects haven't been dealt with yet.
		 */
		$files_to_migrate = array(
			'themes'    => isset( $current_migration['stages'], $state_data['theme_folders'] )
			               && in_array( Stage::THEME_FILES, $current_migration['stages'] )
				? $state_data['theme_folders']
				: [],
			'plugins'   => isset( $current_migration['stages'], $state_data['plugin_folders'] )
			               && in_array( Stage::PLUGIN_FILES, $current_migration['stages'] )
				? $state_data['plugin_folders']
				: [],
			'muplugins' => isset( $current_migration['stages'], $state_data['muplugin_folders'] )
			               && in_array( Stage::MUPLUGIN_FILES, $current_migration['stages'] )
				? $state_data['muplugin_folders']
				: [],
			'others'    => isset( $current_migration['stages'], $state_data['other_folders'] )
			               && in_array( Stage::OTHER_FILES, $current_migration['stages'] )
				? $state_data['other_folders']
				: [],
			'root'      => isset( $current_migration['stages'], $state_data['root_folders'] )
			               && in_array( Stage::ROOT_FILES, $current_migration['stages'] )
				? $state_data['root_folders']
				: [],
		);

		$migration_id = $intent === 'push' ? $state_data['remote_state_id'] : $state_data['migration_state_id'];

		$paths = [
			'themes'    => CommonUtil::get_stage_base_dir( Stage::THEME_FILES ),
			'plugins'   => CommonUtil::get_stage_base_dir( Stage::PLUGIN_FILES ),
			'muplugins' => CommonUtil::get_stage_base_dir( Stage::MUPLUGIN_FILES ),
			'others'    => CommonUtil::get_stage_base_dir( Stage::OTHER_FILES ),
			'root'      => CommonUtil::get_stage_base_dir( Stage::ROOT_FILES ),
		];

		foreach ( $files_to_migrate as $stage => $stage_folder ) {
			$dest_path = trailingslashit( $paths[ $stage ] );
			$tmp_path  = Util::get_temp_dir( $stage );

			if ( 'pull' === $intent ) {
				$file_folders_list = $this->get_files_for_stage( $stage, $state_data, $current_migration );
			}

			$stage_folder = array_unique( $stage_folder );

			foreach ( $stage_folder as $file_folder ) {
				if ( 'pull' === $intent && ! $this->in_file_folders_list( $file_folders_list, $file_folder ) ) {
					continue;
				}
				if ( in_array( $stage, [ 'plugins', 'muplugins', 'others', 'root' ] ) ) {
					$folder_name = basename( str_replace( '\\', '/', $file_folder ) );
				} else { //Themes
					$folder_name = $this->transfer_helpers->get_theme_folder_name(
						$file_folder,
						$tmp_path
					);

					if ( ! $folder_name ) {
						return new WP_Error(
							'wpmdb_no_folder_name',
							sprintf( __( 'Unable to determine folder name for theme %s' ), $file_folder )
						);
					}
				}

				$dest_folder = $dest_path . $folder_name;
				$tmp_source  = $tmp_path . $folder_name;

				if ( is_link( $dest_folder ) ) {
					$dest_folder = readlink( $dest_folder );
				}

				$return = $this->move_folder_into_place( $tmp_source, $dest_folder, $stage );

				if ( is_wp_error( $return ) ) {
					return $this->transfer_helpers->log_and_return_error( $return->get_error_message() );
				}
			}
		}

		return true;
	}

	/**
	 * Get files for stage.
	 *
	 * @param string $stage
	 * @param array  $state_data
	 * @param array  $current_migration
	 *
	 * @return array
	 */
	public function get_files_for_stage( $stage, $state_data, array $current_migration ) {
		if ( 'pull' === $current_migration['intent'] ) {
			return $state_data['site_details']['remote'][ $stage ];
		}

		return $state_data[ $stage . '_folders' ];
	}

	/**
	 * @param array  $file_folders_list
	 * @param string $file_folder
	 *
	 * @return bool
	 */
	public function in_file_folders_list( $file_folders_list, $file_folder ) {
		foreach ( $file_folders_list as $list_item ) {
			if ( $list_item[0]['path'] === $file_folder ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $source
	 * @param string $dest
	 * @param string $stage
	 *
	 * @return bool|WP_Error
	 */
	public function move_folder_into_place(
		$source,
		$dest,
		$stage
	) {
		Debug::log( 'Finalizing ' . $stage . ': Moving “' . $source . '” to “' . $dest . '”.' );

		$fs              = $this->filesystem;
		$dest_backup     = false;

		if ( ! $fs->file_exists( $source ) ) {
			$message = sprintf(
				__( 'Temporary file not found when finalizing %s Files migration: %s ', 'wp-migrate-db' ),
				ucwords( Stage::singular_name( $stage ) ),
				$source
			);
			$this->error_log->log_error( $message );

			return new WP_Error( 'wpmdbpro_theme_plugin_files_error', $message );
		}

		//Remove auto-generated .htaccess file
		if ( is_file( $source . DIRECTORY_SEPARATOR . '.htaccess' ) ) {
			unlink( $source . DIRECTORY_SEPARATOR . '.htaccess' );
		}

		if ( $fs->file_exists( $dest ) ) {
			Debug::log( 'Destination folder "' . $dest . '" exists.' );

			$backup_dir = Util::get_temp_dir( $stage ) . 'backups' . DIRECTORY_SEPARATOR;
			if ( ! $fs->is_dir( $backup_dir ) ) {
				if ( ! $fs->mkdir( $backup_dir ) ) {
					$message = sprintf(
						__(
							'Unable to create backup directory when finalizing Theme & Plugin Files migration: %s',
							'wp-migrate-db'
						),
						$backup_dir
					);
					$this->error_log->log_error( $message );
					error_log( $message );

					return new WP_Error( 'finalize-backup-dir-error', $message );
				}
			}

			$dest_backup = $backup_dir . basename( $dest ) . '.' . time() . '.bak';

			if ( ! $fs->move( $dest, $dest_backup ) ) {
				$message = sprintf(
					__(
						'Unable to backup destination file when finalizing Theme & Plugin Files migration: %s',
						'wp-migrate-db'
					),
					$dest
				);
				$this->error_log->log_error( $message );
				error_log( $message );

				return new WP_Error( 'finalize-backup-dest-error', $message );
			}
		}

		if ( ! $fs->move( $source, $dest ) ) {
			$message = sprintf(
				__(
					'Unable to move file into place when finalizing Theme & Plugin Files migration. Source: %s | Destination: %s',
					'wp-migrate-db'
				),
				$source,
				$dest
			);
			$this->error_log->log_error( $message );
			error_log( $message );

			// attempt to restore backup
			if ( $dest_backup ) {
				$fs->move( $dest_backup, $dest );
			}

			return new WP_Error( 'wpmdbpro_theme_plugin_files_error', $message );
		}

		Debug::log( 'Finalizing ' . $stage . ': Moved “' . $source . '” to “' . $dest . '”.' );

		return true;
	}

	/**
	 * Runs on local site
	 */
	public function cleanup_transfer_migration() {
		$stages = $this->form_data->getMigrationStages();

		if ( ! $stages ) {
			return;
		}

		if (
			! empty( array_intersect(
				[ Stage::THEME_FILES, Stage::PLUGIN_FILES, Stage::MUPLUGIN_FILES, Stage::OTHER_FILES, Stage::CORE_FILES, Stage::ROOT_FILES ],
				$stages
			) )
		) {
			$this->plugin_helper->cleanup_transfer_migration( 'themes' );
		}
	}

	/**
	 * Runs on remote site, on `wpmdb_respond_to_push_cancellation` hook
	 */
	public function remove_tmp_files_remote() {
		$stages = $this->form_data->getMigrationStages();

		if ( ! $stages ) {
			return;
		}
		// Only run if a media files stage was processed
		if (
			! empty( array_intersect(
				[ Stage::THEME_FILES, Stage::PLUGIN_FILES, Stage::MUPLUGIN_FILES, Stage::OTHER_FILES, Stage::ROOT_FILES ],
				$stages
			) )
		) {
			$this->plugin_helper->remove_tmp_files( 'themes', 'remote' );
		}
	}

	/**
	 *
	 * Fires on the `wpmdb_before_finalize_migration` hook
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	public function verify_file_transfer( $state_data ) {
		$stages            = array();
		$current_migration = $this->form_data->getCurrentMigrationData();

		if (
			isset( $current_migration['stages'] ) &&
			in_array( Stage::THEME_FILES, $current_migration['stages'] ) &&
			isset( $state_data['theme_folders'] )
		) {
			$stages[] = Stage::THEMES;
		}

		if (
			isset( $current_migration['stages'] ) &&
			in_array( Stage::PLUGIN_FILES, $current_migration['stages'] ) &&
			isset( $state_data['plugin_folders'] )
		) {
			$stages[] = Stage::PLUGINS;
		}

		if (
			isset( $current_migration['stages'] ) &&
			in_array( Stage::MUPLUGIN_FILES, $current_migration['stages'] ) &&
			isset( $state_data['muplugin_folders'] )
		) {
			$stages[] = Stage::MUPLUGINS;
		}

		if (
			isset( $current_migration['stages'] ) &&
			in_array( Stage::OTHER_FILES, $current_migration['stages'] ) &&
			isset( $state_data['other_folders'] )
		) {
			$stages[] = Stage::OTHERS;
		}

		if (
			isset( $current_migration['stages'] ) &&
			in_array( Stage::ROOT_FILES, $current_migration['stages'] ) &&
			isset( $state_data['root_folders'] )
		) {
			$stages[] = Stage::ROOT;
		}

		$migration_key = isset( $state_data['type'] ) && 'push' === $state_data['type'] ? $state_data['remote_state_id'] : $state_data['migration_state_id'];

		foreach ( $stages as $stage ) {
			try {
				// Throws exception
				$this->transfer_helpers->check_stage_directory( $stage );
			} catch ( Exception $e ) {
				return new WP_Error( 'wpmdb_error', $e->getMessage() );
			}
		}

		return true;
	}
}
