<?php
/**
 * This file contains the code to display metabox for MemberPress Admin Orders Page.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */

/**
 * Class to add metabox to MemberPress admin order page.
 *
 * @since 1.0.2
 */
class MonsterInsights_User_Journey_MemberPress_Metabox extends MonsterInsights_User_Journey_Metabox {

	/**
	 * Current Provider Name.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $provider = 'memberpress';

	/**
	 * Class constructor.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {
		add_action( 'mepr_edit_transaction_table_after', array( $this, 'add_user_journey_metabox' ), 10 );
	}

	/**
	 * Check if we are on MemberPress edit order screen.
	 *
	 * @return boolean
	 * @since 1.0.2
	 *
	 */
	public function is_memberpress_order_screen() {
		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'page', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'action', true ) ) {
			return false;
		}

		if ( ! MonsterInsights_User_Journey_Helper::is_valid_array( $_GET, 'id', true ) ) {
			return false;
		}
		
		if ( 'memberpress-trans' !== $_GET['page'] && 'edit' !== $_GET['action'] ) { // phpcs:ignore
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
	 * @param object $txn Transaction Object.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function add_user_journey_metabox( $txn ) {
		if ( ! $this->is_memberpress_order_screen() ) {
			return;
		}

		$this->display_meta_box( $txn );
	}

	/**
	 * Display metabox HTML.
	 *
	 * @param object $post MemberPress Transaction Object.
	 *
	 * @return void
	 * @since 1.0.2
	 *
	 */
	public function display_meta_box( $txn ) {
		$order = $this->get_provider_order_data( $txn->id );

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

if ( MonsterInsights_User_Journey_Helper::is_memberpress_active() ) {
	new MonsterInsights_User_Journey_MemberPress_Metabox();
}
