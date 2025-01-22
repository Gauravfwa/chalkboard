<?php
/**
 *
 * Maintain list of email logs
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

if ( ! class_exists( 'AF_Email_Log_Table' ) ) {
	/**
	 * Class for table of abandoned cart
	 */
	class AF_Email_Log_Table extends WP_List_Table {

		/** Class constructor */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => __( 'Email Log', 'addify_acr' ), // singular name of the listed records.
					'plural'   => __( 'Email Logs', 'addify_acr' ), // plural name of the listed records.
					'screen'   => get_current_screen(),
					'ajax'     => false, // does this table support ajax?
				)
			);

		}


		/**
		 * Retrieve logs data from the database.
		 *
		 * @param int $per_page Per page.
		 * @param int $page_number Page number.
		 *
		 * @return mixed
		 */
		public static function get_email_logs( $per_page = 20, $page_number = 1 ) {

			$args = array(
				'post_type'      => 'addify_acr_logs',
				'post_status'    => 'publish',
				'nopaging'       => false,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
				'order_by'       => 'post_date',
			);

			$the_query = new WP_Query( $args );
			$data      = array();

			if ( $the_query->have_posts() ) {

				$logs = $the_query->get_posts();

				foreach ( $logs as $log ) {

					$row = array();

					$row['id']            = $log->ID;
					$row['log_content']   = $log->post_content;
					$row['user_id']       = get_post_meta( $log->ID, 'user_id', true );
					$row['user_email']    = get_post_meta( $log->ID, 'user_email', true );
					$row['email_type']    = get_post_meta( $log->ID, 'email_type', true );
					$row['cart_subtotal'] = get_post_meta( $log->ID, 'subtotal', true );
					$row['status']        = get_post_meta( $log->ID, 'status', true );
					$row['date']          = $log->post_date;

					$data[] = $row;
				}

				return $data;

			} else {

				return array();
			}
		}


		/**
		 * Delete an email log.
		 *
		 * @param int $id cart ID.
		 */
		public static function delete_log( $id ) {

			wp_delete_post( $id, true );

		}


		/**
		 * Returns the count of records in the database.
		 *
		 * @return null|string
		 */
		public static function record_count() {

			$args = array(
				'post_type'      => 'addify_acr_logs',
				'post_status'    => 'publish',
				'posts_per_page' => '-1',
				'order_by'       => 'post_date',
				'fields'         => 'ids',
			);

			$the_query = new WP_Query( $args );

			return count( $the_query->get_posts() );
		}


		/** Text displayed when no email log data is available */
		public function no_items() {
			esc_html_e( 'No email log available.', 'addify_acr' );
		}


		/**
		 * Render a column when no column specific method exist.
		 *
		 * @param array  $item Row data.
		 * @param string $column_name Column name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'email':
					return $item['user_email'];
				case 'display_name':
					$user = get_user_by( 'id', intval( $item['user_id'] ) );
					if ( ! is_object( $user ) ) {
						return 'Guest';
					}
					return $user->display_name;
				case 'user_name':
					$user = get_user_by( 'id', intval( $item['user_id'] ) );
					return $user->user_login;
				case 'cart_status':
					return $item['cart_status'];
				case 'cart_content':
					return $item['cart_content'];
				case 'date':
					return $item['date'];
				case 'cart_subtotal':
					return wc_price( $item['cart_subtotal'] );
				case 'email_type':
					return $item['email_type'];
				case 'cart_total':
					return wc_price( $item['cart_total'] );
				default:
					return implode( ',', $item ); // Show the whole array for troubleshooting purposes.
			}

		}

		/**
		 * Render the bulk edit checkbox.
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
		 * Method for name column
		 *
		 * @param array $item an array of DB data.
		 *
		 * @return string
		 */
		public function column_display_name( $item ) {

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			$tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '';
			$id   = $item['id'];

			$actions = array(
				'delete' => sprintf( '<a href="?page=%s&tab=%s&&action=delete&id=%s">%s</a>', $page, $tab, $id, __( 'Delete', 'addify_acr' ) ),
			);

			$user = get_user_by( 'id', intval( $item['user_id'] ) );

			if ( ! is_object( $user ) ) {
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
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'            => '<input type="checkbox" />',
				'display_name'  => __( 'Customer Name', 'addify_acr' ),
				'email'         => __( 'Email Address', 'addify_acr' ),
				'email_type'    => __( 'Email Type', 'addify_acr' ),
				'cart_subtotal' => __( 'Subtotal', 'addify_acr' ),
				'date'          => __( 'Last Updated', 'addify_acr' ),
			);

			return $columns;
		}


		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name'  => 'user_email',
				'email' => 'user_name',
			);

			return $sortable_columns;
		}

		/**
		 * Returns an associative array containing the bulk action
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

			$per_page     = $this->get_items_per_page( 'cart_per_page', 20 );
			$current_page = $this->get_pagenum();

			$this->items = self::get_email_logs( $per_page, $current_page );

			$total_items = $this->record_count();

			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // WE have to calculate the total number of items.
					'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				)
			);

		}

		/**
		 * Process actions of table
		 */
		public function process_bulk_action() {

			// Detect when a Delete action is being triggered...
			if ( 'delete' === $this->current_action() ) {

				if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ), '_afacr_wpnonce' ) ) {

					die( 'Nonce not verified.' );

				} else {

					$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : 0;

					self::delete_log( absint( $id ) );

					echo wp_kses_post( '<div id="message" class="updated notice notice-success is-dismissible"><p>Email log has been deleted successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
				}
			}

			// If the delete bulk action is triggered.
			if ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) || ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) ) ) {

				$delete_ids = isset( $_REQUEST['bulk-delete'] ) ? sanitize_meta( '', wp_unslash( $_REQUEST['bulk-delete'] ), '' ) : array();
				// loop over the array of record IDs and delete them.
				foreach ( $delete_ids as $id ) {
					self::delete_log( $id );

				}
				echo wp_kses_post( sprintf( '<div id="message" class="updated notice notice-success is-dismissible"><p>%s email logs have been deleted successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', count( $delete_ids ) ) );
			}
		}

	}
}
