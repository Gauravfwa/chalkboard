<?php
/**
 * Manage cron jobs to send emails and generate coupons
 *
 * @package  addify-abandoned-cart-recovery/includes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AF_Automatic_Cron_Jobs' ) ) {
	/**
	 * Front class of module.
	 */
	class AF_Automatic_Cron_Jobs {
		/**
		 * Store all email templates
		 *
		 * @var array
		 */
		private $email_templates;
		/**
		 * Store All Abandoned Carts
		 *
		 * @var array
		 */
		private $abandoned_carts;

		/**
		 * Store All Abandoned Carts in awaiting
		 *
		 * @var array
		 */
		private $abandoned_awaiting_carts;

		/**
		 * Store All Abandoned Carts in awaiting
		 *
		 * @var array
		 */
		private $pending_orders;

		/**
		 * Store All Abandoned Carts in awaiting
		 *
		 * @var array
		 */
		private $shop_coupons;

		/**
		 * Email Content Controller.
		 *
		 * @var array
		 */
		private $email_controller;

		/**
		 * Constructor of class AF_Automatic_Cron_Jobs.
		 */
		public function __construct() {

			add_filter( 'cron_schedules', array( $this, 'afacr_cron_schedules' ), 100, 2 );

			if ( 'yes' !== get_option( 'afacr_enable' ) ) {
				return;
			}

			add_action( 'addify_consider_abanoned_cart', array( $this, 'addify_consider_abanoned_cart_callback' ), 10 );

			add_action( 'addify_automatic_emails', array( $this, 'afacr_send_automatic_emails' ), 10 );

			add_action( 'addify_automatic_delete', array( $this, 'afacr_delete_carts_orders_and_coupens' ), 10 );
		}

		/**
		 * Consider it as abandoned cart
		 */
		public function afacr_delete_carts_orders_and_coupens() {

			// Load all email templates.

			$this->afacr_load_abandoned_carts();

			foreach ( (array) $this->abandoned_carts as $cart_id ) {

				if ( intval( $cart_id ) < 1 ) {
					continue;
				}

				$cart = get_post( $cart_id );

				$delete_time = get_option( 'afacr_delete_abandoned_cart_time' );

				if ( ! isset( $delete_time[0] ) || ! isset( $delete_time[1] ) ) {
					break;
				}

				if ( 0 !== intval( $delete_time[0] ) && ( $delete_time[0] <= $this->afacr_date_difference( $cart->post_date, gmdate( 'd-m-Y H:i:s' ), $delete_time[1] ) ) ) {

					wp_delete_post( $cart_id, true );
					continue;
				}
			}

			// Delete all expired coupons.

			// Delete Coupons when used.

			$this->afacr_load_shop_coupons();

			foreach ( (array) $this->shop_coupons as $coupon_id ) {

				$coupon = new WC_Coupon( $coupon_id );

				if ( 'yes' === get_option( 'afacr_delete_once_used' ) ) {

					if ( $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {

						wp_delete_post( $coupon_id, true );
						continue;
					}
				}

				if ( 'yes' === get_option( 'afacr_delete_expired' ) ) {

					$expiry_date = $coupon->get_date_expires();

					if ( ( 0 <= $this->afacr_date_difference( $expiry_date, gmdate( 'd-m-Y H:i:s' ), 'days' ) ) ) {

						wp_delete_post( $coupon_id, true );
						continue;
					}
				}
			}

		}


		/**
		 * Consider it as abandoned cart.
		 */
		public function addify_consider_abanoned_cart_callback() {

			$this->afacr_load_abandoned_awaiting_carts();

			foreach ( (array) $this->abandoned_awaiting_carts as $cart_id ) {

				if ( intval( $cart_id ) < 1 ) {
					continue;
				}

				$cart = get_post( $cart_id );

				$abandoned_time = get_option( 'afacr_consider_abandoned_time' );

				if ( ! isset( $abandoned_time[0] ) || ! isset( $abandoned_time[1] ) ) {
					break;
				}

				if ( $abandoned_time[0] <= $this->afacr_date_difference( $cart->post_date, gmdate( 'd-m-Y H:i:s' ), $abandoned_time[1] ) ) {

					if ( empty( get_post_meta( $cart_id, 'user_email', true ) ) ) {
						wp_delete_post( $cart_id, true );
						continue;
					}

					update_post_meta( $cart_id, 'cart_status', 'abandoned' );

					$value = intval( get_option( 'afacr_total_abandoned_carts' ) ) + 1;

					update_option( 'afacr_total_abandoned_carts', $value );

					$cart_amount = get_post_meta( $cart_id, 'cart_subtotal', true );

					$total_amount = floatval( get_option( 'afacr_total_abandoned_cart_amount' ) ) + $cart_amount;

					update_option( 'afacr_total_abandoned_cart_amount', $total_amount );
				}
			}
		}

		/**
		 * Add custom schedule for cron job.
		 *
		 * @param array $schedules Schedules of cron jobs.
		 *
		 * @return array
		 */
		public function afacr_cron_schedules( $schedules ) {

			$_type = get_option( 'afacr_time_type' );
			$_time = get_option( 'afacr_cron_time' );

			$cron_time = 5 * 60;

			switch ( $_type ) {
				case 'minutes':
					$cron_time = $_time * 60;
					break;
				case 'hours':
					$cron_time = $_time * 60 * 60;
					break;
				case 'days':
					$cron_time = $_time * 60 * 60 * 24;
					break;
				default:
					$cron_time = $_time;
					break;
			}

			$schedules['afacr_cron_schedule'] = array(
				'interval' => $cron_time,
				'display'  => 'Abandoned Cart Cron Schedule',
			);

			return $schedules;
		}

		/**
		 * Send Automatic emails.
		 */
		public function afacr_send_automatic_emails() {

			$this->afacr_load_abandoned_carts();
			$this->afacr_load_pending_orders();
			$this->afacr_load_email_templates();

			// Return if emails templates are empty.
			// Return if abandoned carts are empty.
			if ( empty( $this->email_templates ) ) {
				return;
			}

			$this->email_controller = new AF_Email_Content_Controller();
			$from_name              = get_option( 'woocommerce_email_from_name' );
			$from_email             = get_option( 'woocommerce_email_from_address' );
			// More headers.
			$headers  = 'MIME-Version: 1.0' . "\n";
			$headers .= 'Content-type:text/html' . "\n";
			$headers .= 'From: ' . $from_name . ' < ' . $from_email . ' > ' . "\r\n";

			foreach ( $this->email_templates as $template_id ) {

				$email_type = get_post_meta( $template_id, 'afacr_email_type', true );

				$subject = get_post_meta( $template_id, 'afacr_email_subject', true );

				$email_time = json_decode( get_post_meta( $template_id, 'afacr_time', true ), true );

				if ( empty( $email_time ) || ! isset( $email_time[1] ) || ! isset( $email_time[0] ) ) {
					continue;
				}

				switch ( $email_type ) {

					case 'cart':
						foreach ( (array) $this->abandoned_carts as $cart_id ) {

							$cart = get_post( $cart_id );

							$last_email = (array) json_decode( get_post_meta( $cart_id, 'last_email_send', true ), true );
							$user_email = get_post_meta( $cart_id, 'user_email', true );

							if ( ! empty( $last_email ) && in_array( $template_id, $last_email ) ) {
								continue;
							}

							$user_id     = get_post_meta( $cart_id, 'user_id', true );
							$email_roles = (array) json_decode( get_post_meta( $template_id, 'afacr_customer_roles', true ) );
							$user        = get_user_by( 'id', $user_id );
							$user_role   = is_a( $user, 'WP_User' ) ? current( $user->roles ) : 'guest';

							if ( ! empty( $email_roles ) && ! in_array( $user_role, $email_roles ) ) {
								return;
							}

							if ( empty( get_post_meta( $cart_id, 'user_email', true ) ) ) {
								wp_delete_post( $cart_id, true );
								continue;
							}

							$abandoned_time = get_option( 'afacr_consider_abandoned_time' );

							$abandoned_time = gmdate( 'd-m-Y H:i:s', strtotime( $cart->post_date . ' + ' . $abandoned_time[0] . ' ' . $abandoned_time[1] ) );

							if ( ( $email_time[0] > $this->afacr_date_difference( $abandoned_time, gmdate( 'd-m-Y H:i:s' ), $email_time[1] ) ) ) {
								continue;
							}

							$email_content = $this->email_controller->get_content_for_email_cart( $template_id, $cart_id );
							$email_html    = $this->email_controller->afacr_email_create_template( $subject, $email_content );

							if ( wp_mail( $user_email, $subject, $email_html, $headers ) ) {

								// Total Abandoned Cart Emails.
								if ( empty( $last_email ) ) {
									$last_email = array();
								}
								array_push( $last_email, $template_id );
								update_post_meta( $cart_id, 'last_email_send', wp_json_encode( $last_email ) );

								$total_emails = intval( get_option( 'afacr_total_abandoned_carts_emails' ) ) + 1;
								update_option( 'afacr_total_abandoned_carts_emails', $total_emails );

								$post_data = array(
									'post_title'  => $user_email,
									'post_type'   => 'addify_acr_logs',
									'post_status' => 'publish',
								);

								$post_id = wp_insert_post( $post_data, true );

								if ( ! is_wp_error( $post_id ) ) {

									update_post_meta( $post_id, 'user_id', get_post_meta( $cart_id, 'user_id', true ) );
									update_post_meta( $post_id, 'user_email', get_post_meta( $cart_id, 'user_email', true ) );
									update_post_meta( $post_id, 'email_type', 'abandoned_cart' );
									update_post_meta( $post_id, 'subtotal', get_post_meta( $cart_id, 'cart_subtotal', true ) );
									update_post_meta( $post_id, 'status', get_post_meta( $cart_id, 'cart_status', true ) );

								} else {

									wc_add_notice( implode( ',', $post_id->get_error_messages() ), 'error' );
									return;
								}
							} else {

								$this->email_controller->delete_coupon_created();
							}

							$value = intval( get_option( 'afacr_cron_emails_test' ) ) + 1;

							update_option( 'afacr_cron_emails_test', $value );
						}
						break;

					case 'order':
						if ( 'yes' !== get_option( 'afacr_enable_pending_order' ) ) {
							continue 2;
						}

						foreach ( (array) $this->pending_orders as $order ) {

							// check Enable for user.
							$user          = get_user_by( 'id', $order->get_customer_id() );
							$user_role     = is_a( $user, 'WP_User' ) ? current( $user->roles ) : 'guest';
							$setting_roles = (array) get_option( 'afacr_user_roles' );

							if ( ! empty( $setting_roles ) && ! in_array( $user_role, $setting_roles ) ) {
								continue;
							}

							$email_roles = (array) json_decode( get_post_meta( $template_id, 'afacr_customer_roles', true ) );

							if ( ! empty( $email_roles ) && ! in_array( $user_role, $email_roles ) ) {
								return;
							}

							$billing_email = $order->get_billing_email();

							$order_date = $order->get_date_created();

							$last_email = json_decode( get_post_meta( $order->get_id(), 'last_email_send', true ), true );

							if ( ! empty( $last_email ) && in_array( $template_id, $last_email ) ) {
								continue;
							}

							if ( intval( $email_time[0] ) > $this->afacr_date_difference( $order_date->date( 'd-m-Y H:i:s' ), gmdate( 'd-m-Y H:i:s' ), $email_time[1] ) ) {
								continue;
							}

							$email_content = $this->email_controller->get_content_for_email_order( $template_id, $order );
							$email_html    = $this->email_controller->afacr_email_create_template( $subject, $email_content );

							if ( wp_mail( $billing_email, $subject, $email_html, $headers ) ) {

								if ( empty( $last_email ) ) {
									$last_email = array();
								}
								array_push( $last_email, $template_id );
								update_post_meta( $order->get_id(), 'last_email_send', wp_json_encode( $last_email ) );
								// Total Abandoned Cart Emails.

								$total_emails = intval( get_option( 'afacr_total_pending_orders_emails' ) ) + 1;
								update_option( 'afacr_total_pending_orders_emails', $total_emails );

								$post_data = array(
									'post_title'  => $billing_email,
									'post_type'   => 'addify_acr_logs',
									'post_status' => 'publish',
								);

								$post_id = wp_insert_post( $post_data, true );

								if ( ! is_wp_error( $post_id ) ) {

									update_post_meta( $post_id, 'user_id', $order->get_customer_id() );
									update_post_meta( $post_id, 'user_email', $billing_email );
									update_post_meta( $post_id, 'email_type', 'pending_order' );
									update_post_meta( $post_id, 'subtotal', $order->get_subtotal() );
									update_post_meta( $post_id, 'total', $order->get_total() );
									update_post_meta( $post_id, 'status', 'pending_order' );

								} else {

									wc_add_notice( implode( ',', $post_id->get_error_messages() ), 'error' );
									return;
								}
							}
						}
						break;
				}
			}
		}

		/**
		 * Calculate difference in dates and format it.
		 *
		 * @param date   $date_1 date one.
		 * @param date   $date_2 date two.
		 * @param string $difference_in different in seconds, minutes, hours, days.
		 *
		 * @return string
		 */
		public function afacr_date_difference( $date_1, $date_2, $difference_in = '' ) {

			switch ( $difference_in ) {

				case 'seconds':
					$difference_format = '%R%s';
					break;
				case 'minutes':
					$difference_format = '%R%i';
					break;
				case 'hours':
					$difference_format = '%R%h';
					break;
				case 'days':
					$difference_format = '%R%d';
					break;
				default:
					$difference_format = '%R%a';
					break;
			}

			$datetime1 = date_create( $date_1 );
			$datetime2 = date_create( $date_2 );

			$interval = date_diff( $datetime1, $datetime2 );

			return $interval->format( $difference_format );
		}

		/**
		 * Load all abandoned carts.
		 */
		public function afacr_load_abandoned_carts() {

			// Load all abandoned carts.
			$args = array(
				'post_type'      => 'addify_acr_carts',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cart_status',
						'value'   => 'abandoned',
						'compare' => '=',
					),
				),
				'order_by'       => 'post_date',
				'fields'         => 'ids',
			);

			$the_query             = new WP_Query( $args );
			$this->abandoned_carts = $the_query->get_posts();
		}

		/**
		 * Load all email templates.
		 */
		public function afacr_load_email_templates() {

			// Load all email templates.
			$args = array(
				'post_type'      => 'addify_acr_emails',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'afacr_automatic',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'key'     => 'afacr_enable',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
				'fields'         => 'ids',
			);

			$the_query             = new WP_Query( $args );
			$this->email_templates = $the_query->get_posts();
		}

		/**
		 * Load all pending emails.
		 */
		public function afacr_load_pending_orders() {
			// Load all email templates.
			$status               = array_unique( array_merge( array( 'wc-pending' ), (array) get_option( 'afacr_pending_order_status' ) ) );
			$args                 = array(
				'limit'          => -1,
				'status'         => $status,
			);
			$this->pending_orders = wc_get_orders( $args );
		}

		/**
		 * Load all abandoned awaiting carts.
		 */
		public function afacr_load_abandoned_awaiting_carts() {
			// Load all abandoned carts.
			$args = array(
				'post_type'      => 'addify_acr_carts',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cart_status',
						'value'   => 'abandoned-awaiting',
						'compare' => '=',
					),
				),
				'order_by'       => 'post_date',
				'fields'         => 'ids',
			);

			$the_query                      = new WP_Query( $args );
			$this->abandoned_awaiting_carts = $the_query->get_posts();
		}

		/**
		 * Load all coupons.
		 */
		public function afacr_load_shop_coupons() {
			// Load all abandoned carts.
			$args = array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'order_by'       => 'post_date',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'coupon_type',
						'value'   => 'afacr_coupon',
						'compare' => '=',
					),
				),
			);

			$the_query          = new WP_Query( $args );
			$this->shop_coupons = $the_query->get_posts();
		}
	}
	new AF_Automatic_Cron_Jobs();
}
