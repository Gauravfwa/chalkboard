<?php
/**
 * This file contains the code to display metabox for LifterLMS Admin Orders Page.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to add metabox to LifterLMS admin order page.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_LifterLMS_Metabox extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Current Provider Name.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $provider = 'lifterlms';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_user_journey_metabox' ) );
	}

	/**
	 * Provider name.
	 *
	 * @return string
	 * @since 1.0.2
	 *
	 */
	protected function get_provider() {
		return $this->provider;
	}

	/**
	 * Add metabox
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 * @uses add_meta_boxes WP Hook
	 *
	 */
	public function add_user_journey_metabox() {
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}

		$post         = get_post( absint( $_GET['post'] ) );
		$user_journey = array();

		if ( is_object( $post ) && ! empty( $post ) ) {
			$user_journey = monsterinsights_user_journey()->db->get_user_journey( $post->ID );
		}

		if ( empty( $user_journey ) ) {
			return;
		}

		add_meta_box(
			'lifterlms-monsterinsights-user-journey-metabox',
			esc_html__( 'User Journey by MonsterInsights', 'monsterinsights-user-journey' ),
			array( $this, 'display_meta_box' ),
			'llms_order',
			'normal',
			'core'
		);
	}


	/**
	 * Display metabox HTML.
	 *
	 * @param object $post LifterLMS Order custom post
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function display_meta_box( $post ) {
		$order = $this->get_provider_order_data( $post->ID );

		if ( empty( $order ) ) {
			return;
		}

		$user_journey = monsterinsights_user_journey()->db->get_user_journey(
			$order['id'],
			array(
				'offset' => $this->db_offset(),
				'number' => $this->db_limit()
			)
		);

		if ( ! empty( $user_journey ) ) {
			$this->metabox_html( $user_journey, $order['id'], $order['date'] );
		}
	}
}

if ( MonsterInsights_User_Journey_Helper::is_lifter_lms_active() ) {
	new MonsterInsights_User_Journey_LifterLMS_Metabox();
}
