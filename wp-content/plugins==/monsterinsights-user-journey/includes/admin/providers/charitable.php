<?php
/**
 * This file contains the code to display metabox for charitable donation Page.
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to add metabox to charitable donation page.
 */
class MonsterInsights_User_Journey_Charitable_Metabox extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Current Provider Name.
	 *
	 * @var string
	 */
	private $provider = 'charitable';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( ! MonsterInsights_User_Journey_Helper::can_view_user_journey() ) {
			return;
		}

		add_action( 'add_meta_boxes_' . Charitable::DONATION_POST_TYPE, array( $this, 'add_user_journey_metabox' ), 21 );
	}

	/**
	 * Provider name.
	 *
	 * @return string
	 */
	protected function get_provider() {
		return $this->provider;
	}

	/**
	 * Add metabox to donation admin view page.
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
			'charitable-monsterinsights-user-journey-metabox',
			esc_html__( 'User Journey by MonsterInsights', 'monsterinsights-user-journey' ),
			array( $this, 'display_meta_box' ),
			Charitable::DONATION_POST_TYPE,
			'normal',
			'low'
		);
	}

	/**
	 * Display metabox HTML.
	 *
	 * @param object $post Charitable donation custom post
	 *
	 * @return void
	 */
	public function display_meta_box( $post ) {
		$donation = $this->get_provider_order_data( $post->ID );

		if ( empty( $donation ) ) {
			return;
		}

		$user_journey = monsterinsights_user_journey()->db->get_user_journey(
			$donation['id'],
			array(
				'offset' => $this->db_offset(),
				'number' => $this->db_limit()
			)
		);

		if ( ! empty( $user_journey ) ) {
			$this->metabox_html( $user_journey, $donation['id'], $donation['date'] );
		}
	}
}

if ( MonsterInsights_User_Journey_Helper::is_charitable_active() ) {
	new MonsterInsights_User_Journey_Charitable_Metabox();
}
