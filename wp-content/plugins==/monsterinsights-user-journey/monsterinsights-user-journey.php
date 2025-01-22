<?php
/**
 * Plugin Name:       MonsterInsights - User Journey Addon
 * Plugin URI:        https://www.monsterinsights.com
 * Description:       See the steps your customers take before purchasing from your website.
 * Author:            MonsterInsights Team
 * Author URI:        https://www.monsterinsights.com
 * Version:           1.1.1
 * Requires at least: 5.6.0
 * Requires PHP:      7.2
 * Text Domain:       monsterinsights-user-journey
 * Domain Path:       languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 *
 * @author  MonsterInsights
 */
class MonsterInsights_User_Journey {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.1.1';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'MonsterInsights User Journey';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'monsterinsights-user-journey';

	/**
	 * Holds the database instance.
	 *
	 * @var MonsterInsights_User_Journey_DB
	 */
	public $db;

	/**
	 * Holds the helper class instance.
	 *
	 * @since 1.0.2
	 *
	 * @var MonsterInsights_User_Journey_Helper
	 */
	public $helper;

	/**
	 * URL to a plugin directory. Used for assets.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * Plugin File.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = '';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->file = __FILE__;

		if ( ! $this->check_compatibility() ) {
			return;
		}

		// Define Addon Constant
		if ( ! defined( 'MONSTERINSIGHTS_USER_JOURNEY_VERSION' ) ) {
			define( 'MONSTERINSIGHTS_USER_JOURNEY_VERSION', $this->version );
		}

		if ( ! defined( 'MONSTERINSIGHTS_USER_JOURNEY_FILE' ) ) {
			define( 'MONSTERINSIGHTS_USER_JOURNEY_FILE', __FILE__ );
		}

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the updater
		add_action( 'monsterinsights_updater', array( $this, 'updater' ) );

		// Load the plugin.
		add_action( 'monsterinsights_load_plugins', array( $this, 'init' ), 99 );

		if ( ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) ) {
			// Make sure plugin is listed in Auto-update Disabled view
			add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update' ), 10, 2 );

			// Display call-to-action to get Pro in order to enable auto-update
			add_filter( 'plugin_auto_update_setting_html', array( $this, 'modify_autoupdater_setting_html' ), 11, 2 );
		}

		$this->install_hooks();
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The MonsterInsights_User_Journey object.
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MonsterInsights_User_Journey ) ) {
			self::$instance = new MonsterInsights_User_Journey();
		}

		return self::$instance;
	}

	/**
	 * Check compatibility with PHP and WP, and display notices if necessary
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	private function check_compatibility() {
		if ( defined( 'MONSTERINSIGHTS_FORCE_ACTIVATION' ) && MONSTERINSIGHTS_FORCE_ACTIVATION ) {
			return true;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/compatibility-check.php';
		$compatibility = MonsterInsights_User_Journey_Compatibility_Check::get_instance();
		$compatibility->maybe_display_notice();

		return $compatibility->is_php_compatible() && $compatibility->is_wp_compatible();
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) ) {
			// admin notice, MI not installed
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights' ) );

			return;
		}

		if ( version_compare( MONSTERINSIGHTS_VERSION, '8.5.1', '<' ) ) {
			// MonsterInsights version not supported
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights_version' ) );

			return;
		}

		if ( ! $this->is_monsterinsights_ecommerce_addon_active() ) {
			// MonsterInsights eCommerce Addon is not installed or activated.
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights_ecommerce_addon' ) );

			return;
		}

		$this->loader();
	}

	/**
	 * Initializes the addon updater.
	 *
	 * @param string $key The user license key.
	 *
	 * @since 1.0.0
	 *
	 */
	function updater( $key ) {
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://www.monsterinsights.com/',
			'version'     => $this->version,
			'key'         => $key
		);

		$updater = new MonsterInsights_Updater( $args );
	}

	/**
	 * Display MonsterInsights Pro CTA on Plugins -> autoupdater setting column
	 *
	 * @param string $html
	 * @param string $plugin_file
	 *
	 * @return string
	 */
	public function modify_autoupdater_setting_html( $html, $plugin_file ) {
		if ( plugin_basename( __FILE__ ) === $plugin_file &&
			 // If main plugin (free) happens to be enabled and already takes care of this, then bail
			 ! apply_filters( "monsterinsights_is_autoupdate_setting_html_filtered_" . $plugin_file, false )
		) {
			$html = sprintf(
				'<a href="%s">%s</a>',
				'https://www.monsterinsights.com/docs/go-lite-pro/?utm_source=liteplugin&utm_medium=plugins-autoupdate&utm_campaign=upgrade-to-autoupdate&utm_content=monsterinsights-user-journey',
				__( 'Enable the MonsterInsights PRO plugin to manage auto-updates', 'monsterinsights-user-journey' )
			);
		}

		return $html;
	}

	/**
	 * Disable auto-update.
	 *
	 * @param $update
	 * @param $item
	 *
	 * @return bool
	 */
	public function disable_auto_update( $update, $item ) {
		// If this is multisite and is not on the main site, return early.
		if ( is_multisite() && ! is_main_site() ) {
			return $update;
		}

		if ( isset( $item->id ) && plugin_basename( __FILE__ ) === $item->id ) {
			return false;
		}

		return $update;
	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 *
	 * @return    void
	 * @since 1.0.0
	 *
	 */
	public function requires_monsterinsights() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install MonsterInsights Pro to use the MonsterInsights User Journey addon', 'monsterinsights-user-journey' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user does not have MI version installed
	 *
	 * @return    void
	 * @since 1.0.0
	 *
	 */
	public function requires_monsterinsights_version() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or update MonsterInsights Pro with version 8.5.1 or newer to use the MonsterInsights User Journey addon', 'monsterinsights-user-journey' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Check if MonsterInsights eCommerce addon is installed or activated.
	 *
	 * @return boolean
	 * @since 1.0.0
	 *
	 */
	public function is_monsterinsights_ecommerce_addon_active() {
		if ( class_exists( 'MonsterInsights_eCommerce' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Output a nag notice if the user does not have MonsterInsights eCommerce Addon installed.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function requires_monsterinsights_ecommerce_addon() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or activate MonsterInsights eCommerce Addon to use the MonsterInsights User Journey Addon', 'monsterinsights-user-journey' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Load Register, Uninstall and MU New blog hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function install_hooks() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/DB.php';

		register_activation_hook( $this->file, array(
			'MonsterInsights_User_Journey_DB',
			'install',
		) );

		// Add tables to new blogs.
		add_action( 'wpmu_new_blog', array( 'MonsterInsights_User_Journey_DB', 'new_multi_site_blog' ), 10, 6 );
	}

	/**
	 * Load the necessary functions, classes and functionality.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function loader() {
		$this->url = plugin_dir_url( __FILE__ );
		$this->load_database();
		$this->load_helpers();
		$this->load_frontend();
		$this->load_providers();
		$this->load_admin();
	}

	/**
	 * Load Database.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	private function load_database() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/DB.php';

		$this->db = new MonsterInsights_User_Journey_DB();
	}

	/**
	 * Load Frontend functionality.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	private function load_frontend() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/frontend/tracking.php';

		$frontend_tracking = new MonsterInsights_User_Journey_Frontend_Tracking();

		if ( ! is_admin() ) {
			$frontend_tracking->init();
		}
	}

	/**
	 * Load Helper Class.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	private function load_helpers() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-journey-helper.php';

		$this->helper = new MonsterInsights_User_Journey_Helper();
	}

	/**
	 * Load eCommerce Providers.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function load_providers() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/providers/process.php'; // Load base class file

		$providers = MonsterInsights_User_Journey_Helper::ecommerce_providers();

		foreach ( $providers as $provider ) {
			$file = plugin_dir_path( __FILE__ ) . 'includes/providers/' . $provider . '.php';
			if ( file_exists( $file ) ) {
				require_once wp_normalize_path( $file );
			}
		}
	}

	/**
	 * Load eCommerce Providers.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function load_admin() {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! MonsterInsights_User_Journey_Helper::can_view_user_journey() ) {
			return;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/admin/init.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-abstract-user-journey-metabox.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/admin/ajax.php';

		$providers = MonsterInsights_User_Journey_Helper::ecommerce_providers();

		if ( ! empty( $providers ) ) {
			foreach ( $providers as $provider ) {
				$file = plugin_dir_path( __FILE__ ) . 'includes/admin/providers/' . $provider . '.php';
				if ( file_exists( $file ) ) {
					require_once wp_normalize_path( $file );
				}
			}
		}
	}
}

/**
 * Return plugin main class instance.
 *
 * @return object
 * @since 1.0.0
 *
 */
function monsterinsights_user_journey() {
	return MonsterInsights_User_Journey::get_instance();
}

monsterinsights_user_journey();
