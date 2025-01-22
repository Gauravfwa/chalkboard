<?php
/**
 * This file contains the code to display metabox for EDD Admin Orders Page.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to add metabox to EDD admin order page.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_EDD_Metabox extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Current Provider Name.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $provider = 'edd';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'edd_view_order_details_main_after', array( $this, 'add_user_journey_metabox' ), 10, 1 );
	}

	/**
	 * Check if we are on EDD edit order screen.
	 *
	 * @return bool
	 * @since 1.0.2
	 *
	 */
	public function is_edd_order_screen() {
		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'page', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'view', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'id', true ) ) {
			return false;
		}
		
		if ( 'edd-payment-history' !== $_GET['page'] && 'view-order-details' !== $_GET['view'] ) { // phpcs:ignore
			return false;
		}

		return true;
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
	public function add_user_journey_metabox( $order_id ) {
		if ( ! $this->is_edd_order_screen() ) {
			return;
		}

		$this->display_meta_box( $order_id );
	}

	/**
	 * Display metabox HTML.
	 *
	 * @param object $post EDD Order custom post
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function display_meta_box( $order_id ) {
		$order = $this->get_provider_order_data( $order_id );

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

	/**
	 * Metabox title/heading.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	protected function metabox_title() {
		?>
		<div class="postbox-header">
			<h2><?php esc_html_e( 'User Journey by MonsterInsights', 'monsterinsights-user-journey' ); ?></h2>
		</div>
		<?php
	}
}

if ( MonsterInsights_User_Journey_Helper::is_edd_active() ) {
	new MonsterInsights_User_Journey_EDD_Metabox();
}
