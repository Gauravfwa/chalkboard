<?php
/**
 * This file contains the code to display metabox for Restrict Content Pro Admin Orders Page.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to add metabox to Restrict Content Pro admin order page.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_Restrict_Content_Pro_Metabox extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Current Provider Name.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $provider = 'rcp';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'rcp_edit_payment_after', array( $this, 'add_user_journey_metabox' ), 10, 3 );
	}

	/**
	 * Check if we are on RCP Edit Order page.
	 *
	 * @return boolean
	 * @since 1.0.2
	 *
	 */
	public function is_rcp_order_screen() {
		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'page', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'payment_id', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'view', true ) ) {
			return false;
		}
		
		if ( 'rcp-payments' !== $_GET['page'] && 'edit-payment' !== $_GET['view'] ) { // phpcs:ignore
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
	 * @param object $payment RCP Payment Object
	 * @param object $membership_level RCP Membership level Object
	 * @param object $uer WordPress User Info from RCP
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function add_user_journey_metabox( $payment, $membership_level, $user ) {
		if ( ! $this->is_rcp_order_screen() ) {
			return;
		}

		$this->display_meta_box( $payment );
	}

	/**
	 * Display metabox HTML.
	 *
	 * @param object $post RCP Payment Object
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function display_meta_box( $payment ) {
		$order = $this->get_provider_order_data( $payment->id );

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

if ( MonsterInsights_User_Journey_Helper::is_rcp_active() ) {
	new MonsterInsights_User_Journey_Restrict_Content_Pro_Metabox();
}
