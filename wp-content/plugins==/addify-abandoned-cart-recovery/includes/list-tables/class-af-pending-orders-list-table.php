<?php
/**
 * Maintain list of pending orders
 *
 * @package  addify-abandoned-cart-recovery/includes/list-tables
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'AF_Pending_Orders_List_Table' ) ) {
	/**
	 * Class for table of abandoned cart
	 */
	class AF_Pending_Orders_List_Table extends WP_List_Table {

		/** Class constructor */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => __( 'Pending Order', 'addify_acr' ), // singular name of the listed records.
					'plural'   => __( 'Pending Orders', 'addify_acr' ), // plural name of the listed records.
					'screen'   => get_current_screen(),
					'ajax'     => false, // does this table support ajax?
				)
			);

		}


		/**
		 * Retrieve carts data from the database.
		 *
		 * @param int $per_page per page.
		 * @param int $page_number page number.
		 *
		 * @return mixed
		 */
		public static function get_pending_orders( $per_page = 10, $page_number = 1 ) {

			$status = array_unique( array_merge( array( 'wc-pending' ), (array) get_option( 'afacr_pending_order_status' ) ) );
			$args   = array(
				'limit'          => -1,
				'status'         => $status,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
			);

			$customer_orders = wc_get_orders( $args );

			$data = array();
			
			if ( ! empty( $customer_orders ) ) {

				foreach ( $customer_orders as $order ) {

					$row = array();

					$user_id = $order->get_customer_id();

					$user = get_user_by('id', $user_id );

					$user_role = is_a($user, 'WP_User') ? current( $user->roles ) : 'guest';

					$pending_order_roles = get_option('afacr_user_roles');

					if ( !empty( $pending_order_roles ) && !in_array( $user_role , $pending_order_roles) ) {
						continue;
					}

					$row['id']             = $order->get_id();
					$row['order_items']    = wp_json_encode( $order->get_items() );
					$row['user_id']        = $order->get_customer_id();
					$row['user_email']     = $order->get_billing_email();
					$row['order_status']   = wc_get_order_status_name( $order->get_status() );
					$row['order_subtotal'] = $order->get_subtotal();
					$row['order_total']    = $order->get_total();
					$row['last_email']     = (array) json_decode( get_post_meta( $order->get_id(), 'last_email_send', true ) );
					$row['order_date']     = $order->get_date_created();

					$data[] = $row;
				}

				return $data;

			} else {

				return array();
			}
		}


		/**
		 * Delete a customer record.
		 *
		 * @param int $id customer ID.
		 */
		public static function delete_order( $id ) {
			wp_delete_post( $id, true );
		}


		/**
		 * Returns the count of records in the database.
		 *
		 * @return null|string
		 */
		public static function record_count() {
			$status          = array_unique( array_merge( array( 'wc-pending' ), (array) get_option( 'afacr_pending_order_status' ) ) );
			$customer_orders = wc_get_orders(
				array(
					'limit'  => -1,
					'status' => $status,
				)
			);

			$data[] = array();

			foreach ( $customer_orders as $order ) {

				$user_id = $order->get_customer_id();

				$user = get_user_by('id', $user_id );

				$user_role = is_a($user, 'WP_User') ? current( $user->roles ) : 'guest';

				$pending_order_roles = get_option('afacr_user_roles');

				if ( !empty( $pending_order_roles ) && !in_array( $user_role , $pending_order_roles) ) {
					continue;
				}

				$data[] = $order;
			}

			return count( array_filter( $data ) );
		}


		/** Text displayed when no customer data is available */
		public function no_items() {
			esc_html_e( 'No pending order available', 'addify_acr' );
		}


		/**
		 * Render a column when no column specific method exist.
		 *
		 * @param array  $item Row data.
		 * @param string $column_name Column Name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'email':
					return $item['user_email'];

				case 'display_name':
					$user = get_user_by( 'id', intval( $item['user_id'] ) );

					if ( ! is_a( $user, 'WP_User' ) ) {
						return 'Guest';
					}

					return $user->display_name;

				case 'user_name':
					$user = get_user_by( 'id', intval( $item['user_id'] ) );
					return $user->user_login;

				case 'order_status':
					return $item['order_status'];

				case 'order_content':
					return $item['order_content'];

				case 'order_date':
					$date = $item['order_date']->date( 'd-m-yy' );
					$time = $item['order_date']->date( 'H:i:s' );
					return $date . ' at ' . $time;

				case 'order_subtotal':
					return wc_price( $item['order_subtotal'] );
				case 'order_total':
					return wc_price( $item['order_total'] );

				case 'last_email':
					$email = array();
					foreach ( $item['last_email'] as $value ) {

						$email[] = get_the_title( $value );
					}

					if ( empty( $email ) ) {
						return __( 'No email sent', 'addify_acr' );
					}

					return implode( ',<br>', $email );

				case 'last_email':
					return $item['last_email'];

				default:
					return implode( ',', $item ); // Show the whole array for troubleshooting purposes.
			}

		}

		/**
		 * Render the bulk edit check box
		 *
		 * @param array $item Row data.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="bulk-delete[]" value="%s" />',
				$item['id']
			);
		}


		/**
		 * Method for name column.
		 *
		 * @param array $item an array of DB data.
		 *
		 * @return string
		 */
		public function column_display_name( $item ) {

			if ( isset( $_REQUEST['_afacr__wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr__wpnonce'] ) ) ), '_afacr__wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			$tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '';
			$id   = $item['id'];

			$order = wc_get_order( $item['id'] );

			$actions = array(
				'edit'   => sprintf( '<a href="%s">%s</a>', $order->get_edit_order_url(), __( 'Edit', 'addify_acr' ) ),
				'delete' => sprintf( '<a href="?page=%s&tab=%s&action=delete&id=%s">%s</a>', $page, $tab, $id, __( 'Delete', 'addify_acr' ) ),

			);

			$user = get_user_by( 'id', intval( $item['user_id'] ) );

			if ( ! is_a( $user, 'WP_User' ) ) {
				$customer_name = 'Guest';
			} else {
				$customer_name = $user->display_name;
			}

			return sprintf(
				'%s %s',
				$customer_name,
				$this->row_actions( $actions )
			);
		}

		/**
		 * Method for email Send column.
		 *
		 * @param array $item an array of DB data.
		 *
		 * @return string
		 */
		public function column_email_send( $item ) {

			$args = array(
				'post_type'      => 'addify_acr_emails',
				'post_status'    => 'publish',
				'posts_per_page' => '-1',
				'meta_query'     => array(
					array(
						'key'     => 'afacr_email_type',
						'value'   => 'order',
						'compare' => '=',

					),
				),
			);
			$row       = '<select name="afacr_email_send">';
			$the_query = new WP_Query( $args );

			$template = false;

			if ( !$the_query->have_posts() ) {
				return __('No email template', 'addify_acr');
			}

			foreach ( $the_query->get_posts() as $post ) {

				$user        = get_user_by( 'id', intval( $item['user_id'] ) );
				$user_roles  = is_a( $user, 'WP_User' ) ? $user->roles : array( 'guest' );
				$email_roles = (array) json_decode( get_post_meta( $post->ID, 'afacr_customer_roles', true ) );

				if ( ! empty( $email_roles ) && count( array_intersect( $user_roles, $email_roles ) ) <= 0 ) {
					continue;
				}

				$template = true;
				$row     .= sprintf( '<option value="%s"> %s </option>', $post->ID, $post->post_title );
			}

			$row .= '</select>';
			$row .= sprintf( '<button type="button" data-cart="%s" class="afacr_send_email">%s</button>', $item['id'], __( 'Send Email', 'addify_acr' ) );

			if ( !$template ) {
				return __('No email template', 'addify_acr');
			}
			return $row;
		}

		/**
		 *  Associative array of columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'             => '<input type="checkbox" />',
				'display_name'   => __( 'Customer Name', 'addify_acr' ),
				'email'          => __( 'Billing Email', 'addify_acr' ),
				'order_status'   => __( 'Order Status', 'addify_acr' ),
				'order_subtotal' => __( 'Order Subtotal', 'addify_acr' ),
				'order_total'    => __( 'Order Total', 'addify_acr' ),
				'last_email'     => __( 'Sent Emails', 'addify_acr' ),
				'email_send'     => __( 'Send Email', 'addify_acr' ),
				'order_date'     => __( 'Order Date', 'addify_acr' ),
			);

			return $columns;
		}

		/**
		 * Gets a list of sortable columns.
		 *
		 * The format is:
		 * - `'internal-name' => 'orderby'`
		 * - `'internal-name' => array( 'orderby', 'asc' )` - The second element sets the initial sorting order.
		 * - `'internal-name' => array( 'orderby', true )`  - The second element makes the initial order descending.
		 *
		 * @since 3.1.0
		 *
		 * @return array
		 */
		protected function get_sortable_columns() {
			$sortable_columns = array(
				'display_name'   => array( 'orderby', 'asc' ),
				'order_subtotal' => array( 'orderby', 'asc' ),
			);

			return $sortable_columns;
		}

		/**
		 * Returns an associative array containing the bulk action.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array(
				'bulk-delete' => 'Delete',
			);

			return $actions;
		}


		/**
		 * Handles data query and filter, sorting, and pagination.
		 */
		public function prepare_items() {

			$this->_column_headers = array( $this->get_columns() );

			/** Process bulk action */

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'cart_per_page', 10 );
			$current_page = $this->get_pagenum();

			$this->items = self::get_pending_orders( $per_page, $current_page );

			$total_items = $this->record_count();

			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // WE have to calculate the total number of items.
					'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				)
			);

		}


		/**
		 * Process actions.
		 */
		public function process_bulk_action() {

			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {

				// In our file that handles the request, verify the nonce.

				if ( isset( $_REQUEST['_afacr__wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_afacr__wpnonce'] ) ), '_afacr__wpnonce' ) ) {

					die( 'Nonce not verified.' );

				} else {

					$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : 0;

					self::delete_order( absint( $id ) );

					echo wp_kses_post( '<div id="message" class="updated notice notice-success is-dismissible"><p>Order has been deleted successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
					return;

				}
			}

			// If the delete bulk action is triggered.
			if ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) || ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) ) ) {

				$delete_ids = isset( $_REQUEST['bulk-delete'] ) ? sanitize_meta( '', wp_unslash( $_REQUEST['bulk-delete'] ), '' ) : array();

				// loop over the array of record IDs and delete them.
				foreach ( $delete_ids as $id ) {
					self::delete_order( $id );

				}

				echo wp_kses_post( sprintf( '<div id="message" class="updated notice notice-success is-dismissible"><p>%s orders have been deleted successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', count( $delete_ids ) ) );
			}
		}

	}
}
