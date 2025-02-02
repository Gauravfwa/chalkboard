<?php
namespace Objectiv\Plugins\Checkout\Managers;

use Objectiv\Plugins\Checkout\Managers\Helpers\EDD_SL_Plugin_Updater;
use Objectiv\Plugins\Checkout\Managers\Helpers\EDD_Theme_Updater;

/**
 * EDD Software Licensing Magic
 *
 * A drop-in class that magically manages your EDD SL plugin licensing.
 *
 * @author Clifton H. Griffin II
 * @version 0.6.1
 * @copyright Clif Griffin Development, Inc. 2014
 * @license GNU GPL version 3 (or later) {@see license.txt}
 **/
class UpdatesManager {
	var $menu_slug; // parent menu slug to attach "License" submenu to
	var $prefix; // prefix for internal settings
	var $url; // plugin host site URL for EDD SL
	var $version; // plugin version for EDD SL
	var $name; // plugin name for EDD SL
	var $key_statuses; // store list of key statuses and messages
	var $bad_key_statuses; // store list of key statuses and messages
	var $activate_errors; // store list of activation errors and error messages
	var $last_activation_error; // because we can't pass variables directly to admin_notice
	var $plugin_file; // we need to pass this in so it maps to WP
	var $theme    = false; // do we have a theme or a plugin?
	var $beta     = false; // is this a beta?
	var $home_url = false;
	var $updater;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string|bool $prefix A prefix that keeps your setting separate for this instance. Short and no spaces. (default: false)
	 * @param string|bool $menu_slug The menu slug of the parent menu you want to attach the License submenu item to. Same syntax as add_submenu_page()  (default: false)
	 * @param string|bool $url The URL of your host site (default: false)
	 * @param string|bool $version The plugin version (default: false)
	 * @param string|bool $name The plugin name (default: false)
	 * @param string $author The author of the plugin.
	 * @param string|bool $plugin_file The plugin file
	 * @param string|bool $theme True if updating a theme.
	 * @param string|bool $beta True if beta versions are enabled
	 * @param string|bool $home_url True if we should use home_url() instead of get_site_url()
	 * @return void
	 */
	public function __construct( $prefix = false, $menu_slug = false, $url = false, $version = false, $name = false, $author, $plugin_file = false, $theme = false, $beta = false, $home_url = false ) {
		if ( false === $prefix ) {
			error_log( 'CGD_EDDSL_Magic: No prefix specified. Aborting.' );
			return;
		}

		if ( false === $url || false === $version || false === $name ) {
			error_log( 'CGD_EDDSL_Magic: url, version, plugin file, or name parameter was false. Aborting.' );
			return;
		}

		if ( false === $home_url ) {
			$this->home_url = get_home_url();
		} else {
			$this->home_url = $home_url;
		}

		$this->url         = trailingslashit( $url );
		$this->version     = $version;
		$this->name        = $name;
		$this->author      = $author;
		$this->menu_slug   = $menu_slug;
		$this->prefix      = $prefix . '_';
		$this->plugin_file = $plugin_file;
		$this->theme       = $theme;
		$this->beta        = $beta;

		$this->key_statuses = array(
			'invalid'       => 'The entered license key is not valid.',
			'expired'       => 'Your key has expired and needs to be renewed.',
			'inactive'      => 'Your license key is valid, but is not active.',
			'disabled'      => 'Your license key is currently disabled. Please contact support.',
			'site_inactive' => 'Your license key is valid, but not active for this site.',
			'valid'         => 'Your license key is valid and active for this site.',
			'nolicensekey'  => 'Your license key is missing.',
		);

		$this->bad_key_statuses = array(
			'expired',
			'disabled',
			'invalid',
			'nolicensekey', // we made up this status
		);

		$this->activate_errors = array(
			'missing'             => 'The provided license key does not seem to exist.',
			'revoked'             => 'The provided license key has been revoked. Please contact support.',
			'no_activations_left' => 'This license key has been activated the maximum number of times.',
			'expired'             => 'This license key has expired.',
			'key_mismatch'        => 'An unknown error has occurred: key_mismatch',
		);

		// Instantiate EDD_SL_Plugin_Updater
		add_action( 'admin_init', array( $this, 'updater_init' ), 0 ); // run first

		// Add License settings page to menu
		if ( $this->menu_slug !== false ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
		}

		// Form Handler
		add_action( 'admin_init', array( $this, 'save_settings' ) );

		// Cron action
		add_action( $this->prefix . '_check_license', array( $this, 'check_license' ) );

		// Delayed license status update
		add_action( $this->prefix . '_edd_sl_delayed_license_status_update', array( $this, 'delayed_license_update' ) );
	}

	// Form Saving Stuff

	/**
	 * the_nonce
	 * Creates a nonce for the license page form.
	 *
	 * @access public
	 * @return void
	 */
	public function the_nonce() {
		wp_nonce_field( "save_{$this->prefix}_mb_settings", "{$this->prefix}_mb_save" );
	}

	/**
	 * get_field_name
	 * Generates a field name from a setting value for the license page form.
	 *
	 * @access public
	 * @param string $setting The key for the setting you're saving.
	 * @return string The field name
	 */
	public function get_field_name( string $setting ): string {
		return "{$this->prefix}_mb_setting[$setting]";
	}

	/**
	 * get_field_value
	 * Retrieves value from the database for specified setting.
	 *
	 * @access public
	 * @param string $setting The setting key you're retrieving (default: false)
	 * @return string The field value
	 */
	public function get_field_value( $setting = false ) {
		if ( false === $setting ) {
			return false;
		}

		return get_option( $this->prefix . '_' . $setting );
	}

	/**
	 * set_field_value
	 * Set value for the specified setting.
	 *
	 * @access public
	 * @param string $setting (default: false)
	 * @param mixed $value
	 * @return void
	 */
	public function set_field_value( $setting = false, $value ): bool {
		if ( false === $setting ) {
			return false;
		}

		return update_option( $this->prefix . '_' . $setting, $value );
	}

	/**
	 * save_settings
	 * Save license settings.  Listens for settings form submit. Also handles activation / deactivation.
	 *
	 * @access public
	 * @return void
	 */
	public function save_settings() {
		if ( isset( $_REQUEST[ "{$this->prefix}_mb_setting" ] ) && check_admin_referer( "save_{$this->prefix}_mb_settings", "{$this->prefix}_mb_save" ) ) {
			$settings = $_REQUEST[ "{$this->prefix}_mb_setting" ];
			foreach ( $settings as $setting => $value ) {
				$this->set_field_value( $setting, $value );
			}

			// Handle activation if applicable
			if ( isset( $_REQUEST['activate_key'] ) || isset( $_REQUEST['deactivate_key'] ) ) {
				$this->manage_license_activation();
			} else {
				$this->check_license();
			}

			add_action( 'admin_notices', array( $this, 'notice_settings_saved_success' ) );
		}
	}

	/**
	 * updater_init
	 * Sets up the EDD_SL_Plugin_Updater object.
	 *
	 * @access public
	 * @return void
	 */
	function updater_init() {
		// retrieve our license key from the DB
		$license_key = $this->get_license_key();

		if ( ! $license_key ) {
			return;
		}

		// setup the updater
		$this->updater = new EDD_SL_Plugin_Updater(
			$this->url,
			$this->plugin_file,
			array(
				'version'   => $this->version,  // current version number
				'license'   => $license_key,    // license key (used get_option above to retrieve from DB)
				'item_name' => $this->name,     // name of this plugin
				'author'    => $this->author,   // author of this plugin
				'beta'      => $this->beta,     // beta or not (default false)
			)
		);
	}

	/**
	 * Adds "License" page to specified parent menu. Only attached if menu_slug is not false.
	 *
	 * @access public
	 * @return void
	 */
	function admin_menu() {
		add_submenu_page( $this->menu_slug, "{$this->name} License Settings", 'License', 'manage_options', $this->prefix . 'menu', array( $this, 'admin_page' ) );
	}

	/**
	 * admin_page
	 * Generates license page form.
	 *
	 * @access public
	 * @return void
	 */
	function admin_page() {
		$key_status = $this->get_field_value( 'key_status' );
		?>
		<div class="wrap">
			<h2><?php echo $this->name; ?> License Settings</h2>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
				<?php $this->the_nonce(); ?>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" valign="top">
							<label for="<?php echo $this->get_field_name( 'license_key' ); ?>">License Key</label>
						</th>
						<td>
							<input type="password" class="regular-text" id="<?php echo $this->get_field_name( 'license_key' ); ?>" name="<?php echo $this->get_field_name( 'license_key' ); ?>" value="<?php echo $this->get_field_value( 'license_key' ); ?>" /><br />
							<span>Your <?php echo $this->name; ?> license key.</span>
						</td>
					</tr>

					<?php if ( ! empty( $key_status ) ) : ?>
						<tr>
							<th scope="row" valign="top">
								<label>Key Status</label>
							</th>
							<td>
								<?php if ( 'inactive' === $key_status || 'site_inactive' === $key_status ) : ?>
									<input type="submit" name="activate_key" class="button-secondary" value="Activate Site" />
									<p style="color:red;"><?php echo $this->key_statuses[ $key_status ]; ?></p>
								<?php elseif ( 'valid' === $key_status ) : ?>
									<input type="submit" name="deactivate_key" class="button-secondary" value="Deactivate Site" />
									<p style="color:green;"><?php echo $this->key_statuses[ $key_status ]; ?></p>
								<?php else : ?>
									<p style="color:red;"><?php echo $this->key_statuses[ $key_status ]; ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>

					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>

		<?php
	}

	/**
	 * manage_license_activation
	 * Handles license activation and deactivation
	 *
	 * @access public
	 * @return void
	 */
	function manage_license_activation() {
		$action = isset( $_REQUEST['activate_key'] ) ? 'activate_license' : 'deactivate_license';

		// data to send in our API request
		$api_params = array(
			'edd_action' => $action,
			'license'    => $this->get_field_value( 'license_key' ),
			'item_name'  => urlencode( $this->name ), // the name of our product in EDD
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'activate_license' === $action ) {
			// Front end notice only
			// $license_data->license will be either "valid" or "invalid"

			if ( isset( $license_data->error ) ) {
				$this->last_activation_error = $license_data->error;
				add_action( 'admin_notices', array( $this, 'notice_license_activate_error' ) );
			} elseif ( 'invalid' === $license_data->license ) {
				add_action( 'admin_notices', array( $this, 'notice_license_invalid' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'notice_license_valid' ) );
			}
		} else {
			// $license_data->license will be either "deactivated" or "failed"
			if ( 'failed' === $license_data->license ) {
				// warn user
				add_action( 'admin_notices', array( $this, 'notice_license_deactivate_failed' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'notice_license_deactivate_success' ) );
			}
		}

		// Set detailed key_status
		$this->cancel_delayed_license_update();
		$this->set_field_value( 'key_status', $this->get_license_status() );
	}

	/**
	 * get_license_status
	 * Retrieve status of license key for current site.
	 *
	 * @access public
	 * @return string|bool The license status|false on error
	 */
	function get_license_status() {
		$license = $this->get_license_key();

		if ( ! $license ) {
			return 'nolicensekey';
		}

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->name ),
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Updates option that stores the license limit
		if ( ! isset( $license_data->license_limit ) ) {
			$limit = 0;
		} else {
			$limit = $license_data->license_limit;
		}

		update_option( 'cfw_license_activation_limit', $limit );

		if ( isset( $license_data->license ) ) {
			return $license_data->license;
		}

		var_dump( $response );

		return false;
	}

	/**
	 * notice_license_invalid function.
	 *
	 * @access public
	 * @return void
	 */
	function notice_license_invalid() {
		?>
		<div class="error">
			<p><?php echo $this->name; ?> license activation was not successful. Please check your key status below for more information.</p>
		</div>
		<?php
	}

	/**
	 * notice_license_valid function.
	 *
	 * @access public
	 * @return void
	 */
	function notice_license_valid() {
		?>
		<div class="updated">
			<p><?php echo $this->name; ?> license successfully activated.</p>
		</div>
		<?php
	}

	/**
	 * notice_license_deactivate_failed function.
	 *
	 * @access public
	 * @return void
	 */
	function notice_license_deactivate_failed() {
		?>
		<div class="error">
			<p><?php echo $this->name; ?> license deactivation failed. Please try again, or contact support.</p>
		</div>
		<?php
	}

	/**
	 * notice_license_deactivate_success function.
	 *
	 * @access public
	 * @return void
	 */
	function notice_license_deactivate_success() {
		?>
		<div class="updated">
			<p><?php echo $this->name; ?> license deactivated successfully.</p>
		</div>
		<?php
	}

	function notice_settings_saved_success() {
		?>
		<div class="updated">
			<p><?php echo $this->name; ?> license settings saved successfully.</p>
		</div>
		<?php
	}

	/**
	 * notice_license_activate_error function.
	 *
	 * @access public
	 * @param mixed $error
	 * @return void
	 */
	function notice_license_activate_error( $error ) {
		?>
		<div class="error">
			<p><?php echo $this->name; ?> license activation failed: <?php echo $this->activate_errors[ $this->last_activation_error ]; ?></p>
		</div>
		<?php
	}

	/**
	 * set_license_check_cron
	 * Create cron for license check
	 *
	 * @access public
	 * @return void
	 */
	public function set_license_check_cron() {
		$this->unset_license_check_cron();
		wp_schedule_event( time(), 'daily', $this->prefix . '_check_license' );
	}

	/**
	 * unset_license_check_cron
	 * Clear cron for license check.
	 *
	 * @access public
	 * @return void
	 */
	public function unset_license_check_cron() {
		wp_clear_scheduled_hook( $this->prefix . '_check_license' );
	}

	/**
	 * check_license
	 * Retrieve license status for current site and store in key_status setting.
	 *
	 * @access public
	 * @return void
	 */
	function check_license() {
		$current_key_status = $this->get_field_value( 'key_status' );
		$new_status         = $this->get_license_status();

		// If doing cron and the key is currently valid and the new status is invalid, add 3 day delay to updating to prevent immediate deactivation
		if ( defined( 'DOING_CRON' ) && 'valid' === $current_key_status && ( in_array( $new_status, $this->bad_key_statuses, true ) || false === $new_status ) ) {
			$this->cancel_delayed_license_update();
			$this->schedule_delayed_license_check();
		} else {
			$this->cancel_delayed_license_update();
			$this->set_field_value( 'key_status', $new_status );
		}
	}

	/**
	 * Update the key status to the current status
	 */
	function delayed_license_update() {
		$new_status = $this->get_license_status();

		if ( false !== $new_status ) {
			$this->set_field_value( 'key_status', $new_status );
		} else {
			// Bump check another three days
			$this->cancel_delayed_license_update();
			$this->schedule_delayed_license_check();
		}
	}

	function schedule_delayed_license_check() {
		if ( ! wp_next_scheduled( $this->prefix . '_edd_sl_delayed_license_status_update' ) ) {
			wp_schedule_single_event( time() + ( DAY_IN_SECONDS * 3 ), $this->prefix . '_edd_sl_delayed_license_status_update' );
		}
	}

	function cancel_delayed_license_update() {
		wp_clear_scheduled_hook( $this->prefix . '_edd_sl_delayed_license_status_update' );
	}

	/**
	 * get_license_data
	 * Retrieve license data for current site.
	 *
	 * @access public
	 * @return bool|\stdClass License data
	 */
	function get_license_data() {
		$license = $this->get_license_key();

		if ( ! $license ) {
			return false;
		}

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->name ),
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			// Notify us of issue
			wp_mail( 'support@checkoutwc.com', 'CheckoutWC License Check Error', join( ', ', $response->get_error_messages() ) . ' License key: ' . $license );
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * get_license_activation_limit function.
	 *
	 * @access public
	 *
	 * @param bool $force
	 *
	 * @return int The license activation limit
	 */
	function get_license_activation_limit( $force = false ) {
		$limit = get_option( 'cfw_license_activation_limit', false );

		if ( $force || false === $limit ) {
			$license = $this->get_license_key();

			if ( ! $license ) {
				return 0;
			}

			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_name'  => urlencode( $this->name ),
				'url'        => $this->home_url,
				'bypass'     => 'true',
			);

			// Call the custom API.
			$response = wp_remote_get(
				add_query_arg( $api_params, $this->url ),
				array(
					'timeout'   => 15,
					'sslverify' => false,
				)
			);

			if ( is_wp_error( $response ) ) {
				$limit = 0;
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( ! isset( $license_data->license_limit ) ) {
					$limit = 0;
				} else {
					$limit = $license_data->license_limit;
				}
			}

			update_option( 'cfw_license_activation_limit', $limit );
		}

		return $limit;
	}

	/**
	 * get_license_upgrades function.
	 *
	 * @access public
	 * @return int
	 */
	function get_license_upgrades() {
		$license = $this->get_license_key();

		if ( ! $license ) {
			return 0;
		}

		$api_params = array(
			'edd_action' => 'get_license_upgrades',
			'license'    => $license,
			'item_name'  => urlencode( $this->name ),
			'url'        => $this->home_url,
			'bypass'     => 'true',
		);

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->url ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$upgrades = json_decode( wp_remote_retrieve_body( $response ) );
		if ( is_null( $upgrades ) || ! $upgrades ) {
			return 0;
		}

		return $upgrades;
	}

	/**
	 * Get trimmed license key
	 *
	 * @return bool|string
	 */
	function get_license_key() {
		$license = trim( $this->get_field_value( 'license_key' ) );

		if ( ! empty( $license ) && 32 === strlen( $license ) ) {
			return $license;
		}

		return false;
	}

}
