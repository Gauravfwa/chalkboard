<?php

/**
 * Plugin Name:       Quiz Customizer for LearnDash
 * Description:       Replace text for buttons, labels & messages, as well as apply a custom design to many elements of LearnDash quizzes.
 * Version:           1.3.2
 * Author:            Escape Creative
 * Author URI:        https://escapecreative.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       learndash-quiz-customizer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * LearnDash Dependency Check
 * Must have LearnDash active. Otherwise, deactivate plugin.
 * @link https://wordpress.stackexchange.com/questions/127818/how-to-make-a-plugin-require-another-plugin
 */
add_action( 'admin_init', 'lqc_learndash_check' );

function lqc_learndash_check() {

	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! class_exists( 'SFWD_LMS' ) ) {

		add_action( 'admin_notices', 'lqc_learndash_activate_plugin_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	} // end if is_plugin_active
} // end lqc_learndash_check()

function lqc_learndash_activate_plugin_notice() { ?>
	<div class="notice notice-error is-dismissible">
		<p><strong>Error:</strong> Please install &amp; activate the LearnDash plugin before you can use the Quiz Customizer for LearnDash.</p>
	</div>
<?php }


/**
 * Current plugin version.
 * Start at version 1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LQC_QUIZ_CUSTOMIZER_VERSION', '1.3.2' );

/**
 * Define Constants
 */
define( 'LQC_QUIZ_CUSTOMIZER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );



/**
 * Adds <body> class when plugin is active.
 *
 * @since 1.0
 */
include_once plugin_dir_path( __FILE__ ) . 'inc/body-class.php';


/**
 * Adds theme compatibility.
 *
 * @since 1.3
 */
include_once plugin_dir_path( __FILE__ ) . 'inc/theme-compat.php';


/**
 * Override LearnDash Templates
 *
 * @since  1.0
 */
function lqc_learndash_template_override( $filepath, $name, $args, $echo, $return_file_path ) {

	if ( 'learndash_quiz_messages' == $name ) {
		$filepath = LQC_QUIZ_CUSTOMIZER_PLUGIN_DIR . 'templates/legacy/learndash_quiz_messages.php';
	}

	return $filepath;
 
}
add_filter( 'learndash_template', 'lqc_learndash_template_override', 10, 5 );


/**
 * Add improved LearnDash quiz styles to the front-end.
 * This also adds inline styles based on Customizer options chosen.
 *
 * @since 1.0
 * @return void
 */
function lqc_learndash_quiz_customizer_css() {

	// Add main stylesheet that cleans up LD styles
	wp_enqueue_style( 'lqc-learndash-quiz-customizer', plugins_url( 'assets/css/quiz.css', __FILE__ ), array(), '1.3.2' );

	// Add inline styles via the Customizer
	wp_add_inline_style( 'lqc-learndash-quiz-customizer', lqc_learndash_quiz_customizer_inline_css() );

}

// Priority of 11 should load this after Design Upgrade styles (priority 10)
add_action( 'wp_enqueue_scripts', 'lqc_learndash_quiz_customizer_css', 11 );


/**
 * Include Customizer settings
 */
require LQC_QUIZ_CUSTOMIZER_PLUGIN_DIR . 'inc/customizer/class-learndash-quiz-customizer.php';
new LQC_Learndash_Quiz_Customizer_Setup();


/**
 * Add Plugin Action Links to Customizer & License Key pages.
 *
 * @since 1.0
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
 */
add_filter( 'plugin_action_links', 'lqc_add_plugin_action_links', 10, 5 );

function lqc_add_plugin_action_links( $actions, $plugin_file ) {
	static $plugin;

	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);

	if ($plugin == $plugin_file) {

		$open_license_key_page = array('license_key' => '<a href="' . esc_url( admin_url( '/options-general.php?page=learndash-quiz-customizer-license' ) ) . '">' . __( 'License Key', 'learndash-quiz-customizer' ) . '</a>');
		$open_customizer = array('customizer' => '<a href="' . esc_url( admin_url( '/customize.php?autofocus[panel]=lqc_learndash_quiz_panel' ) ) . '">' . __( 'Customize', 'learndash-quiz-customizer' ) . '</a>');

		$actions = array_merge($open_license_key_page, $actions);
		$actions = array_merge($open_customizer, $actions);

	}

	return $actions;
}


/**
 * Setup EDD Software Licensing & Automatic Updates
 *
 * @since 1.0
 * @link https://docs.easydigitaldownloads.com/article/383-automatic-upgrades-for-wordpress-plugins
 */
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'LQC_EDD_STORE_URL', 'https://escapecreative.com' );

// the download ID for the product in Easy Digital Downloads
define( 'LQC_EDD_ITEM_ID', 8165 );

// the name of our product
define( 'LQC_EDD_ITEM_NAME', 'Quiz Customizer for LearnDash' );

// the name of the settings page for the license input to be displayed
define( 'LQC_EDD_PLUGIN_LICENSE_PAGE', 'learndash-quiz-customizer-license' );

if( !class_exists( 'LQC_Quiz_Customizer_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/LQC_Quiz_Customizer_Plugin_Updater.php' );
}

function lqc_edd_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'lqc_edd_license_key' ) );

	// setup the updater
	$edd_updater = new LQC_Quiz_Customizer_Plugin_Updater( LQC_EDD_STORE_URL, __FILE__,
		array(
			'version' => '1.3.2',           // current version number
			'license' => $license_key,      // license key (used get_option above to retrieve from DB)
			'item_id' => LQC_EDD_ITEM_ID,   // ID of the product
			'author'  => 'Escape Creative', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'admin_init', 'lqc_edd_plugin_updater', 0 );


/************************************
* the code below is just a standard
* options page. Substitute with
* your own.
*************************************/

function lqc_edd_license_menu() {
	add_options_page( 'Quiz Customizer for LearnDash License Key', 'Quiz Customizer for LearnDash License', 'manage_options', LQC_EDD_PLUGIN_LICENSE_PAGE, 'lqc_edd_license_page' );
}
add_action('admin_menu', 'lqc_edd_license_menu');

function lqc_edd_license_page() {
	$license = get_option( 'lqc_edd_license_key' );
	$status  = get_option( 'lqc_edd_license_status' );
	?>
	<div class="wrap">
		<h2><?php _e('Quiz Customizer for LearnDash - License Key'); ?></h2>
		<ol>
			<li><?php _e('Enter license key'); ?></li>
			<li><?php _e('Click <b>Save Changes</b>'); ?></li>
			<li><?php _e('<b>*IMPORTANT*</b> After the page reloads, you must click <b>Activate License</b> to finalize activation'); ?></li>
		</ol>
		<form method="post" action="options.php">

			<?php settings_fields('lqc_edd_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('License Key'); ?>
						</th>
						<td>
							<input id="lqc_edd_license_key" name="lqc_edd_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<p><?php _e( 'Your license key is in your purchase receipt email, or you can find it on your <a href="https://escapecreative.com/account/">account page</a>.' ); ?></p>
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Activate License'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									Status: <span style="color:green; font-weight:bold;"><?php _e('Active'); ?></span>
									<?php wp_nonce_field( 'lqc_edd_nonce', 'lqc_edd_nonce' ); ?>
									<input type="submit" class="button-secondary" name="lqc_edd_license_deactivate" value="<?php _e('Deactivate License'); ?>" style="display:block; margin-top:8px;"/>
								<?php } else {
									wp_nonce_field( 'lqc_edd_nonce', 'lqc_edd_nonce' ); ?>
									<input type="submit" class="button-secondary" name="lqc_edd_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function lqc_edd_register_option() {
	// creates our settings in the options table
	register_setting('lqc_edd_license', 'lqc_edd_license_key', 'lqc_edd_sanitize_license' );
}
add_action('admin_init', 'lqc_edd_register_option');

function lqc_edd_sanitize_license( $new ) {
	$old = get_option( 'lqc_edd_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'lqc_edd_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}



/************************************
* this illustrates how to activate
* a license key
*************************************/

function lqc_edd_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['lqc_edd_license_activate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'lqc_edd_nonce', 'lqc_edd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'lqc_edd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_id'    => LQC_EDD_ITEM_ID,
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( LQC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'learndash-quiz-customizer' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'disabled' :
					case 'revoked' :

						$message = __( 'Your license key has been disabled.', 'learndash-quiz-customizer' );
						break;

					case 'missing' :

						$message = __( 'Invalid license.', 'learndash-quiz-customizer' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this site.', 'learndash-quiz-customizer' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), LQC_EDD_ITEM_NAME );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.', 'learndash-quiz-customizer' );
						break;

					default :

						$message = __( 'An error occurred, please try again.', 'learndash-quiz-customizer' );
						break;
				}

			}

		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'options-general.php?page=' . LQC_EDD_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'lqc_edd_license_status', $license_data->license );
		wp_redirect( admin_url( 'options-general.php?page=' . LQC_EDD_PLUGIN_LICENSE_PAGE ) );
		exit();
	}
}
add_action('admin_init', 'lqc_edd_activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/

function lqc_edd_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['lqc_edd_license_deactivate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'lqc_edd_nonce', 'lqc_edd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'lqc_edd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_id'    => LQC_EDD_ITEM_ID,
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( LQC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'learndash-quiz-customizer' );
			}

			$base_url = admin_url( 'options-general.php?page=' . LQC_EDD_PLUGIN_LICENSE_PAGE );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' ) {
			delete_option( 'lqc_edd_license_status' );
		}

		wp_redirect( admin_url( 'options-general.php?page=' . LQC_EDD_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action('admin_init', 'lqc_edd_deactivate_license');

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer.
 */
function lqc_edd_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they wish.
				break;

		}
	}
}
add_action( 'admin_notices', 'lqc_edd_admin_notices' );


/**
 * Generate CSS based on the Customizer settings.
 *
 * @since 1.0
 */
include_once plugin_dir_path( __FILE__ ) . 'inc/inline-css.php';
