<?php
/**
 * Dashboard of Module
 *
 * Generate report of dashboard
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

if ( ! class_exists( 'AF_Cart_List_Table' ) ) {
	/**
	 * Class for table of abandoned cart.
	 */
	class AF_Cart_List_Table extends WP_List_Table {

		/** Class constructor. */
		public function __construct() {

			parent::__construct(
				array(
					'singular' => __( 'Abandoned Cart', 'addify_acr' ), // singular name of the listed records.
					'plural'   => __( 'Abandoned Carts', 'addify_acr' ), // plural name of the listed records.
					'screen'   => get_current_screen(),
					'ajax'     => false, // does this table support ajax?
				)
			);

		}


		/**
		 * Retrieve carts data from the database.
		 *
		 * @param int $per_page Per page.
		 * @param int $page_number Page Number.
		 *
		 * @return mixed
		 */
		public static function get_abanoned_carts( $per_page = 20, $page_number = 1 ) {

			$args = array(
				'post_type'      => 'addify_acr_carts',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
				'meta_query'     => array(
					array(
						'key'     => 'cart_status',
						'value'   => array( 'abandoned', 'abandoned-awaiting' ),
						'compare' => 'IN',
					),
				),
				'orderby'       => 'post_date',
			);

			$the_query = new WP_Query( $args );
			$data      = array();

			if ( $the_query->have_posts() ) {

				$carts = $the_query->get_posts();

				foreach ( $carts as $cart ) {

					$row = array();

					$row['id']            = $cart->ID;
					$row['cart_content']  = $cart->post_content;
					$row['user_id']       = get_post_meta( $cart->ID, 'user_id', true );
					$row['user_email']    = get_post_meta( $cart->ID, 'user_email', true );
					$row['cart_status']   = get_post_meta( $cart->ID, 'cart_status', true );
					$row['cart_subtotal'] = get_post_meta( $cart->ID, 'cart_subtotal', true );
					$row['cart_total']    = get_post_meta( $cart->ID, 'cart_total', true );
					$row['last_email']    = (array) json_decode( get_post_meta( $cart->ID, 'last_email_send', true ) );
					$row['date']          = $cart->post_modified;

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
		public static function delete_cart( $id ) {
			wp_delete_post( $id, true );
		}


		/**
		 * Returns the count of records in the database.
		 *
		 * @return null|string
		 */
		public static function record_count() {

			$args = array(
				'post_type'      => 'addify_acr_carts',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cart_status',
						'value'   => array( 'abandoned', 'abandoned-awaiting' ),
						'compare' => 'IN',
					),
				),
				'fields'         => 'ids',
				'order_by'       => 'post_date',
			);

			$the_query = new WP_Query( $args );

			return count( $the_query->get_posts() );
		}


		/**
		 * Text displayed when no customer data is available.
		 */
		public function no_items() {
			esc_html_e( 'No abandoned cart available.', 'addify_acr' );
		}


		/**
		 * Render a column when no column specific method exist.
		 *
		 * @param array  $item Data of row.
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

				case 'last_email':
					$email = array();

					foreach ( $item['last_email'] as $value ) {

						$email[] = get_the_title( $value );
					}

					if ( empty( $email ) ) {
						return __( 'No email sent', 'addify_acr' );
					}

					return implode( ',<br>', $email );

				case 'cart_total':
					return wc_price( $item['cart_total'] );
				default:
					return implode( ',', $item ); // Show the whole array for troubleshooting purposes.
			}

		}

		/**
		 * Render the bulk edit check box.
		 *
		 * @param array $item Data of row.
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
		 * @param array $item Data of row.
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

			$actions = array(
				'edit'   => sprintf( '<a href="?page=%s&tab=%s&action=edit&id=%s">%s</a>', $page, $tab, $id, __( 'Edit', 'addify_acr' ) ),
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
		 * Method for name column.
		 *
		 * @param array $item Data of row.
		 *
		 * @return string
		 */
		public function column_email_send( $item ) {

			$args = array(
				'post_type'   => 'addify_acr_emails',
				'post_status' => 'publish',
				'meta_query'  => array(
					array(
						'key'     => 'afacr_email_type',
						'value'   => 'cart',
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

				$user = get_user_by( 'id', intval( $item['user_id'] ) );

				$user_roles = is_object( $user ) ? $user->roles : array( 'guest' );

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
		 * Associative array of columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'            => '<input type="checkbox" />',
				'display_name'  => __( 'Customer Name', 'addify_acr' ),
				'email'         => __( 'Email Address', 'addify_acr' ),
				'cart_status'   => __( 'Cart Status', 'addify_acr' ),
				'cart_subtotal' => __( 'Cart Subtotal', 'addify_acr' ),
				'last_email'    => __( 'Sent Emails', 'addify_acr' ),
				'email_send'    => __( 'Send Email', 'addify_acr' ),
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

			/** Process bulk action. */

			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'per_page', 10 );
			$current_page = $this->get_pagenum();

			$this->items = self::get_abanoned_carts( $per_page, $current_page );

			$total_items = $this->record_count();

			$this->set_pagination_args(
				array(
					'total_items' => $total_items, // WE have to calculate the total number of items.
					'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				)
			);

		}

		/**
		 * Method for name column.
		 */
		public function process_bulk_action() {

			// Detect when a delete action is triggered.
			if ( 'delete' === $this->current_action() ) {

				// In our file that handles the request, verify the nonce.

				if ( isset( $_REQUEST['_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) ), '_wpnonce' ) ) {
					die( 'Nonce not verified.' );
				}

				$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : 0;

				self::delete_cart( absint( $id ) );

				echo wp_kses_post( '<div id="message" class="updated notice notice-success is-dismissible"><p>Abandoned cart has been deleted successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
				return;
			}

			// If the delete bulk action is triggered.
			if ( ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'bulk-delete' ) || ( isset( $_REQUEST['action2'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) === 'bulk-delete' ) ) {

				$delete_ids = isset( $_REQUEST['bulk-delete'] ) ? sanitize_meta( '', wp_unslash( $_REQUEST['bulk-delete'] ), '' ) : array();

				// loop over the array of record IDs and delete them.
				foreach ( $delete_ids as $id ) {
					self::delete_cart( $id );

				}
				echo wp_kses_post( sprintf( '<div id="message" class="updated notice notice-success is-dismissible"><p>%s abandoned carts have been deleted successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>', count( $delete_ids ) ) );
			}
		}

	}
}
