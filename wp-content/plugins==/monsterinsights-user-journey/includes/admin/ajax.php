<?php
/**
 * This file contains all the ajax calls used for Admin side of the addon.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * MonsterInsights_User_Journey_Ajax class
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_Ajax extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Provider Name.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * class constructor
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function __construct() {
		add_action( 'wp_ajax_monsterinsights_paginate_user_journey', array(
			$this,
			'monsterinsights_paginate_user_journey'
		) );

		$license_type = MonsterInsights()->license->get_license_type();

		// If it is pro licensed.
		if ( $license_type === 'master' || $license_type === 'pro' ) {
			add_action( 'wp_ajax_monsterinsights_user_journey_report', array(
				$this,
				'monsterinsights_user_journey_report'
			) );

			add_action( 'wp_ajax_monsterinsights_user_journey_report_filter_params', array(
				$this,
				'monsterinsights_user_journey_report_filter_params'
			) );
		}
	}

	/**
	 * Display Metabox
	 * This is an abstract function in the base class.
	 *
	 * @param Object|Array $order_info Order Information
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function display_meta_box( $order_info ) {
	}

	/**
	 * Get current provider name.
	 *
	 * @return string
	 * @since 1.0.2
	 *
	 */
	protected function get_provider() {
		return $this->provider;
	}

	/**
	 * Ajax Result for pagination.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function monsterinsights_paginate_user_journey() {

		if ( !isset($_POST['nonce'])) {
			return;
		}
		
		if ( ! wp_verify_nonce( $_POST['nonce'], 'monsterinsights-uj-admin-ajax' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		if ( ! isset( $_POST['page'] ) ) {
			return;
		}

		if ( ! isset( $_POST['id'] ) ) {
			return;
		}

		$id       = absint( sanitize_text_field( $_POST['id'] ) );
		$page     = absint( sanitize_text_field( $_POST['page'] ) );
		$provider = (isset($_POST['provider'])) ? sanitize_text_field( $_POST['provider'] ) : false;
		$page     -= 1;
		$offset   = $page * $this->db_limit();
		$order    = array();

		$this->provider = $provider;

		$order = $this->get_provider_order_data( $id );

		if ( empty( $order ) ) {
			$order['id']   = 0;
			$order['date'] = '';
		}

		$user_journey = monsterinsights_user_journey()->db->get_user_journey(
			$order['id'],
			array(
				'offset' => $offset,
				'number' => $this->db_limit()
			)
		);

		ob_start();
		$this->metabox_html( $user_journey, $order['id'], $order['date'] );
		wp_die();
	}

	/**
	 * Metabox Title.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	protected function metabox_title() {
		if ( 'woocommerce' !== $this->get_provider() && 'lifterlms' !== $this->get_provider() ) {
			?>
			<div class="postbox-header">
				<h2><?php esc_html_e( 'User Journey by MonsterInsights', 'monsterinsights-user-journey' ); ?></h2>
			</div>
			<?php
		}
	}

	/**
	 * Ajax callback for report page.
	 *
	 * @return void
	 * @since 1.0.7
	 */
	public function monsterinsights_user_journey_report() {
		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'monsterinsights_save_settings' ) ) {
			return;
		}

		if ( $demo_report = monsterinsights_user_journey()->db->show_demo_report() ) {
			wp_send_json( array(
				'items' => $demo_report,
				'demo'  => true,
			) );
		}

		$report = monsterinsights_user_journey()->db->get_paginated_report( array(
			'search'     => isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '',
			'start_date' => isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '',
			'end_date'   => isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '',
			'sources'    => isset( $_POST['sources'] ) ? sanitize_text_field( $_POST['sources'] ) : '',
			'mediums'    => isset( $_POST['mediums'] ) ? sanitize_text_field( $_POST['mediums'] ) : '',
			'campaigns'  => isset( $_POST['campaigns'] ) ? sanitize_text_field( $_POST['campaigns'] ) : '',
			'page'       => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
		) );

		wp_send_json( $report );
	}

	/**
	 * Callback for report filter parameters like campaign, source, medium.
	 *
	 * @return void
	 * @since 1.0.7
	 */
	public function monsterinsights_user_journey_report_filter_params() {
		check_ajax_referer( 'mi-admin-nonce', 'nonce' );

		if ( ! current_user_can( 'monsterinsights_save_settings' ) ) {
			return;
		}

		$parameters = monsterinsights_user_journey()->db->get_all_parameters();

		wp_send_json_success( $parameters );
	}
}

// Initialize the class.
new MonsterInsights_User_Journey_Ajax();
