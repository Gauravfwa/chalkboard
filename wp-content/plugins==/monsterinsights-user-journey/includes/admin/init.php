<?php
/**
 * Initialize Admin - User Journey.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Admin functions and init functionality.
 *
 * @since 1.0.0
 */
final class MonsterInsights_User_Journey_Admin {
	/**
	 * Screens on which we want to load the assets.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $screens = array(
		'shop_order',
		'llms_order',
		'give_forms_page_give-payment-history',
		'download_page_edd-payment-history',
		'restrict_page_rcp-payments',
		'memberpress_page_memberpress-trans',
		'donation',
	);

	/**
	 * Holds singleton instance
	 *
	 * @since 1.0.0
	 *
	 * @var MonsterInsights_User_Journey_Admin
	 */
	private static $instance;

	/**
	 * Return Singleton instance
	 *
	 * @return MonsterInsights_User_Journey_Admin
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add admin scripts.
		add_action( 'admin_head', array( $this, 'add_admin_scripts' ) );
	}

	/**
	 * Add required admin scripts.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function add_admin_scripts() {
		if ( MonsterInsights_User_Journey_Helper::is_supported_provider_screen( $this->screens ) ) {
			$css_url = monsterinsights_user_journey()->url . 'assets/css/admin/user-journey.css';
			$js_url  = monsterinsights_user_journey()->url . 'assets/js/admin/user-journey.js';

			wp_enqueue_style( 'monsterinsights-user-journey-admin', esc_url( $css_url ), MONSTERINSIGHTS_USER_JOURNEY_VERSION );
			wp_enqueue_script( 'monsterinsights-user-journey-admin-js', esc_url( $js_url ), array( 'jquery' ), MONSTERINSIGHTS_USER_JOURNEY_VERSION, true );

			/**
			 * Localize the admin JS with params
			 *
			 * @since 1.0.2
			 */
			wp_localize_script(
				'monsterinsights-user-journey-admin-js',
				'monsterinsights_uj',
				array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'admin_ajax_none' => wp_create_nonce( 'monsterinsights-uj-admin-ajax' )
				)
			);
		}
	}

}

// Initialize the class
MonsterInsights_User_Journey_Admin::get_instance();
