<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.checkoutwc.com
 * @since             1.0.0
 * @package           Objectiv\Plugins\Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       CheckoutWC
 * Plugin URI:        https://www.CheckoutWC.com
 * Description:       Beautiful, conversion optimized checkout templates for WooCommerce.
 * Version:           4.1.1
 * Author:            Objectiv
 * Author URI:        https://objectiv.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       checkout-wc
 * Domain Path:       /languages
 * Tested up to: 5.6.2
 * WC tested up to: 5.0.0
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CFW_NAME', 'Checkout for WooCommerce' );
define( 'CFW_UPDATE_URL', 'https://www.checkoutwc.com' );
define( 'CFW_VERSION', '4.1.1' );
define( 'CFW_PATH', dirname( __FILE__ ) );
define( 'CFW_URL', plugins_url( '/', __FILE__ ) );
define( 'CFW_MAIN_FILE', __FILE__ );

use Objectiv\Plugins\Checkout\Main;
use Objectiv\Plugins\Checkout\Core\Admin;

/**
 * Our language function wrappers that we only use for
 * external translation domains
 *
 * This has to run here or we can't use these functions in the PHP warning which short circuits everything else.
 */
require_once CFW_PATH . '/sources/php/language-wrapper-functions.php';

/*
 * Protect our gentle, out of date users from our fancy modern code
 */
if ( version_compare( phpversion(), '7.0.0', '<' ) ) {
	add_action(
		'admin_notices',
		function() {
			$important = '';
			if ( isset( $_GET['page'] ) && 'cfw-settings' === $_GET['page'] ) {
				$important = "style='display:block !important'";
			}
			?>
		<div <?php echo $important; ?> class="notice notice-error checkout-wc">
			<?php echo sprintf( cfw__( '<h3><strong>%s</strong>: PHP 7.0 or greater is required to use CheckoutWC.</h3>', 'checkout-wc' ), 'CheckoutWC' ); ?>
			<p><?php echo sprintf( cfw__( 'Newer versions of PHP are both faster and more secure, so updating will have a positive effect on your site&#8217;s performance.', 'checkout-wc' ), 'CheckoutWC' ); ?></p>
			<p><?php echo sprintf( cfw__( 'To prevent fatal errors, we have loaded %s into <strong>%s</strong>, which disables all features and functionality. In most cases, upgrading only takes a few minutes. Contact your hosting provider for more information.', 'checkout-wc' ), 'CheckoutWC', cfw__( 'safe mode', 'checkout-wc' ) ); ?></p>
			<p class="button-container">
				<?php
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					esc_url( wp_get_update_php_url() ),
					cfw__( 'Learn more about updating PHP' ),
					/* translators: Accessibility text. */
					cfw__( '(opens in a new tab)' )
				);
				?>
			</p>
		</div>
			<?php
		}
	);

	return;
}

/**
 * Auto-loader (composer)
 */
require_once CFW_PATH . '/vendor/autoload.php';

/**
 * General Functions
 */
require_once CFW_PATH . '/sources/php/constants.php';

if ( CFW_BYPASS_LOAD ) {
	return;
}

/**
 * General Functions
 */
require_once CFW_PATH . '/sources/php/functions.php';

/**
 * Template Hooks and Functions
 */
require_once CFW_PATH . '/sources/php/template-functions.php';
require_once CFW_PATH . '/sources/php/template-hooks.php';

/**
 * Debugging
 */
if ( class_exists( '\Kint' ) && property_exists( '\Kint', 'enabled_mode' ) ) {
	/**
	 * Kint disabled by default. Enable by enabling developer mode (see docs)
	 */
	\Kint::$enabled_mode = false;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function cfw_plugin_init() {
	Main::instance()->run( CFW_MAIN_FILE );
}
cfw_plugin_init();

/**
 * @return Main|null
 */
function cfw_get_main() {
	return Main::instance();
}

/**
 * Activation hook
 */
register_activation_hook( __FILE__, array( cfw_get_main(), 'activation' ) );

/**
 * Deactivation hook
 */
register_deactivation_hook( __FILE__, array( cfw_get_main(), 'deactivation' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/
if ( is_admin() ) {
	$cfw_admin = new Admin( cfw_get_main() );
	$cfw_admin->run();
}
