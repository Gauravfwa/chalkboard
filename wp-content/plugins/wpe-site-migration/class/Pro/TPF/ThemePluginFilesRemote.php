<?php

namespace DeliciousBrains\WPMDB\Pro\TPF;

use DeliciousBrains\WPMDB\Common\Filesystem\Filesystem;
use DeliciousBrains\WPMDB\Common\Http\Helper;
use DeliciousBrains\WPMDB\Common\Http\Http;
use DeliciousBrains\WPMDB\Common\Http\Scramble;
use DeliciousBrains\WPMDB\Common\MigrationState\MigrationStateManager;
use DeliciousBrains\WPMDB\Common\Properties\Properties;
use DeliciousBrains\WPMDB\Common\Settings\Settings;
use DeliciousBrains\WPMDB\Common\Queue\Manager;
use DeliciousBrains\WPMDB\Common\Transfers\Files\FileProcessor;
use DeliciousBrains\WPMDB\Data\Stage;
use DeliciousBrains\WPMDB\Pro\Transfers\Files\PluginHelper;
use DeliciousBrains\WPMDB\Common\Transfers\Files\Util;
use DeliciousBrains\WPMDB\Pro\Transfers\Files\TransferManager;
use DeliciousBrains\WPMDB\Pro\Transfers\Receiver;
use DeliciousBrains\WPMDB\Pro\Transfers\Sender;
use Exception;

class ThemePluginFilesRemote {
	/**
	 * @var Util
	 */
	public $transfer_util;

	/**
	 * @var TransferManager
	 */
	public $transfer_manager;

	/**
	 * @var FileProcessor
	 */
	public $file_processor;

	/**
	 * @var Manager
	 */
	public $queueManager;

	/**
	 * @var Receiver
	 */
	public $receiver;

	/**
	 * @var Http
	 */
	private $http;

	/**
	 * @var Helper
	 */
	private $http_helper;

	/**
	 * @var MigrationStateManager
	 */
	private $migration_state_manager;

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var Properties
	 */
	private $properties;

	/**
	 * @var Sender
	 */
	private $sender;

	/**
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @var Scramble
	 */
	private $scrambler;

	/**
	 * @var PluginHelper
	 */
	private $plugin_helper;

	public function __construct(
		Util $util,
		FileProcessor $file_processor,
		Manager $queue_manager,
		TransferManager $transfer_manager,
		Receiver $receiver,
		Http $http,
		Helper $http_helper,
		MigrationStateManager $migration_state_manager,
		Settings $settings,
		Properties $properties,
		Sender $sender,
		Filesystem $filesystem,
		Scramble $scramble,
		PluginHelper $plugin_helper
	) {
		$this->queueManager            = $queue_manager;
		$this->transfer_util           = $util;
		$this->file_processor          = $file_processor;
		$this->transfer_manager        = $transfer_manager;
		$this->receiver                = $receiver;
		$this->http                    = $http;
		$this->http_helper             = $http_helper;
		$this->migration_state_manager = $migration_state_manager;
		$this->settings                = $settings->get_settings();
		$this->properties              = $properties;
		$this->sender                  = $sender;
		$this->filesystem              = $filesystem;
		$this->scrambler               = $scramble;
		$this->plugin_helper           = $plugin_helper;
	}

	public function register() {
		add_action(
			'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_themes',
			array( $this, 'ajax_tp_respond_to_get_remote_themes' )
		);
		add_action(
			'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_plugins',
			array( $this, 'ajax_tp_respond_to_get_remote_plugins' )
		);
		add_action(
			'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_muplugins',
			array( $this, 'ajax_tp_respond_to_get_remote_muplugins' )
		);
		add_action(
			'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_others',
			array( $this, 'ajax_tp_respond_to_get_remote_others' )
		);
		add_action(
			'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_root',
			array( $this, 'ajax_tp_respond_to_get_remote_root' )
		);

		add_action( 'wp_ajax_nopriv_wpmdbtp_transfers_send_file', array( $this, 'ajax_tp_respond_to_request_files', ) );
		add_action( 'wp_ajax_nopriv_wpmdbtp_transfers_receive_file', array( $this, 'ajax_tp_respond_to_post_file' ) );
	}

	public function ajax_tp_respond_to_get_remote_themes() {
		$this->respond_to_get_remote_folders( Stage::THEMES );
	}

	public function ajax_tp_respond_to_get_remote_plugins() {
		$this->respond_to_get_remote_folders( Stage::PLUGINS );
	}

	public function ajax_tp_respond_to_get_remote_muplugins() {
		$this->respond_to_get_remote_folders( Stage::MUPLUGINS );
	}

	public function ajax_tp_respond_to_get_remote_others() {
		$this->respond_to_get_remote_folders( Stage::OTHERS );
	}

	/**
	 * Respond to get remote folders.
	 *
	 * @param string $stage
	 *
	 * @return void
	 */
	public function respond_to_get_remote_folders( $stage ) {
		$this->plugin_helper->respond_to_get_remote_folders( $stage );
	}

	/**
	 *
	 * Fired off a nopriv AJAX hook that listens to pull requests for file batches
	 *
	 * @return void
	 */
	public function ajax_tp_respond_to_request_files() {
		$this->plugin_helper->respond_to_request_files();
	}

	/**
	 * Respond to post of a file.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function ajax_tp_respond_to_post_file() {
		$this->plugin_helper->respond_to_post_file();
	}
}
