<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backend logic
 *
 * @package Play_HT
 */
class Backend extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// avoid injecting our content on learnPress plugin pages
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'lp_course' ) {
			return false;
		}

		// hook to add plugin admin menu page
		add_action( 'admin_menu', [ &$this, 'add_admin_menu_page' ] );

		// add activation processes needed
		register_activation_hook( WPPP_MAIN_FILE, [ &$this, 'on_activation_process' ] );

		// on plugin activation redirect to welcome page
		add_action( 'activated_plugin', [ &$this, 'play_activation_redirect' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets() {
		wp_enqueue_script( 'initialize_firebase' );
		wp_enqueue_script( 'helpers' );
		wp_enqueue_style( 'playht-backend' );

		if ( 1 == get_option( 'wppp_status_flag' ) ) {
			wp_enqueue_script( 'retrieve_user_data' );
			wp_enqueue_script( 'promise_polyfill' );
			wp_enqueue_script( 'analytics' );
			wp_enqueue_script( 'welcome_page_voices' );
		}

		if ( get_post_type() == 'post' ) {
			wp_enqueue_script( 'sweetalert' );
			wp_enqueue_style( 'sweetalert' );
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'play-welcome-page' ) {

			wp_enqueue_script( 'bootstrap_js' );
			wp_enqueue_script( 'dashboard_wppp_Chart' );
			wp_enqueue_script( 'dashboard_wppp_exporting' );
			wp_enqueue_script( 'dashboard_wppp_export_data' );
			wp_enqueue_script( 'dashboard_wppp_access' );
			wp_enqueue_script( 'jquery_dropdown_wpplay' );
			wp_enqueue_script( 'dashboard_wppp' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'play_ht_settings' );
			wp_enqueue_script( 'switchery' );
		}
	}

	/**
	 * Add admin menu page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_admin_menu_page() {
		// adding menu page.
		add_menu_page(
			__( 'Play.ht', 'play-ht' ),
			__( 'Play.ht', 'play-ht' ),
			'manage_options',
			'play-welcome-page',
			[
				&$this,
				'play_main_view',
			],
			'dashicons-controls-play',
			'90.321'
		);
	}

	/**
	 * Processes needs to be done upon activation
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function on_activation_process() {

		// add new option
		// option might be 0(New User),1(connected to play.ht/no articles yet),2(subscribed user/ show articles)
		if ( false === get_option( 'wppp_status_flag' ) ) {
			add_option( 'wppp_status_flag', 0 );
		}
	}

	/**
	 * Redirect Plugin after activation
	 *
	 * @param $plugin
	 *
	 * @return void
	 */
	public function play_activation_redirect( $plugin ) {
		if ( $plugin == plugin_basename( WPPP_MAIN_FILE ) ) {
			exit( wp_redirect( admin_url( 'admin.php?page=play-welcome-page' ) ) );
		}
	}

	/**
	 * load welcome page view
	 *
	 * @since 1.0.0.
	 * @return void
	 */
	public function play_main_view() {
		// option might be 0(New User),1(connected to play.ht/no articles yet),2(subscribed user/ show articles)
		$play_flag = get_option( 'wppp_status_flag' );
		$user_data = get_option( 'wppp_play_user_data' );
		// load view.
		if ( $play_flag == 0 ) {
			// New User/not connected to play.ht yet
			playht_load_view( 'back-end/play_main_view', [] );
		} else {
			playht_load_view(
				'back-end/main_view_subscribed',
				[
					'user_data' => ( false === $user_data ) ? $user_data : maybe_unserialize( $user_data ),
					'settings'  => WPPP_IMAGES_PATH . 'settings.png',
				]
			);
		}
	}
}
