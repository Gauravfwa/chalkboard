<?php
/**
 * This file contains the code to display metabox for GiveWP Admin Orders Page.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to add metabox to GiveWP admin order page.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_GiveWP_Metabox extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Current Provider Name.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $provider = 'givewp';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'give_view_donation_details_billing_after', array( $this, 'add_user_journey_metabox' ), 10, 1 );
	}

	/**
	 * Check if we are on GiveWP order screen.
	 *
	 * @return array
	 * @since 1.0.2
	 *
	 */
	public function is_givewp_order_screen() {
		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'view', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'id', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'post_type', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'page', true ) ) {
			return false;
		}
		
		if ( 'give_forms' !== $_GET['post_type'] && 'give-payment-history' !== $_GET['page'] && 'view-payment-details' !== $_GET['view'] ) { // phpcs:ignore
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
	public function add_user_journey_metabox( $payment_id ) {

		if ( ! $this->is_givewp_order_screen() ) {
			return;
		}

		$this->display_meta_box( $payment_id );
	}

	/**
	 * Display metabox HTML.
	 *
	 * @param object $post GiveWP Order custom post
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function display_meta_box( $payment_id ) {
		$order = $this->get_provider_order_data( $payment_id );

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

		if ( empty( $user_journey ) ) {
			return;
		}

		?>
		<tr>
			<td colspan="2">
				<?php $this->metabox_html( $user_journey, $order['id'], $order['date'] ); ?>
			</td>
		</tr>
		<?php
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

if ( MonsterInsights_User_Journey_Helper::is_givewp_active() ) {
	new MonsterInsights_User_Journey_GiveWP_Metabox();
}
