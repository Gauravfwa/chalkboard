<?php
/**
 * Front file of Module
 *
 * Manage actions of front
 *
 * @package  addify-abandoned-cart-recovery/includes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AF_Abandoned_Cart_Front' ) ) {
	/**
	 * Front class of module.
	 */
	class AF_Abandoned_Cart_Front {

		/**
		 * Store ID of current user abandoned cart ID
		 *
		 * @var array
		 */
		private $afacr_id;

		/**
		 * Constructor of class.
		 */
		public function __construct() {

			if ( 'yes' !== get_option( 'afacr_enable' ) ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'afacr_front_scripts' ), 10 );

			add_action( 'wp_footer', array( $this, 'afacr_guest_modal_for_email' ), 10 );

			add_action( 'init', array( $this, 'afacr_save_guest_email_address' ), 10 );

			add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'afacr_load_abandoned_cart' ), 10, 1 );
			add_action( 'woocommerce_cart_updated', array( $this, 'afacr_record_abandoned_cart' ), 10, 1 );
			add_action( 'woocommerce_cart_emptied', array( $this, 'afacr_remove_abandoned_cart' ), 100 );
			add_action( 'woocommerce_checkout_order_created', array( $this, 'afacr_order_created' ), 10, 1 );
			add_filter( 'woocommerce_login_redirect', array( $this, 'afacr_redirect_to_cart_page' ), 10, 1 );
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'afacr_capture_cart_from_checkout' ), 10, 1 );
		}

		public function afacr_remove_abandoned_cart() {

			if ( is_checkout() ) {
				return;
			}

			if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {
				$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();
			} 

			if ( !empty( $this->afacr_id ) ) {
				wp_delete_post( $this->afacr_id );
			}
		}

		/**
		 * Redirect to cart page
		 *
		 * @param string $post_data serialized checkout form data.
		 */
		public function afacr_capture_cart_from_checkout( $post_data ) {

			if ( is_user_logged_in() || 'checkout' !== get_option( 'afacr_enable_guest' ) ) {
				return;
			}

			parse_str( $post_data, $params );

			if ( ! isset( $params['billing_email'] ) || empty( $params['billing_email'] ) || !is_email( $params['billing_email'] ) ) {
				return;
			}

			if ( isset( $this->afacr_id ) && ! empty( $this->afacr_id ) ) {
				return;
			}

			if ( ! empty( $this->afacr_get_user_abandoned_cart_id() ) ) {
				return;
			}

			$post_data = array(
				'post_title'   => 'Abandoned Cart',
				'post_type'    => 'addify_acr_carts',
				'post_content' => wp_json_encode( wc()->cart->get_cart_contents() ),
				'post_status'  => 'publish',
			);

			$post_id = wp_insert_post( $post_data, true );

			if ( ! is_wp_error( $post_id ) ) {

				$this->afacr_id = $post_id;
				
				$billing_first_name = isset( $params['billing_first_name'] ) ? $params['billing_first_name'] : '';
				$billing_last_name  = isset( $params['billing_last_name'] ) ? $params['billing_last_name'] : '';
				$customer_name      = $billing_first_name . ' ' . $billing_last_name;
				$customer 	        = wc()->session->get_customer_id();

				update_post_meta( $this->afacr_id, 'user_id', $customer );
				update_post_meta( $this->afacr_id, 'user_name', $customer_name );
				update_post_meta( $this->afacr_id, 'first_name', $billing_first_name );
				update_post_meta( $this->afacr_id, 'last_name', $billing_last_name );
				update_post_meta( $this->afacr_id, 'full_name', $customer_name );
				update_post_meta( $this->afacr_id, 'user_email', $params['billing_email'] );
				update_post_meta( $this->afacr_id, 'cart_subtotal', wc()->cart->get_subtotal() );
				update_post_meta( $this->afacr_id, 'cart_total', wc()->cart->get_total('edit') );
				update_post_meta( $this->afacr_id, 'cart_totals', wc()->cart->get_totals() );
				update_post_meta( $this->afacr_id, 'cart_status', 'abandoned-awaiting' );
			}
		}

		/**
		 * Redirect to cart page
		 *
		 * @param string $redirect_to URL to redirect.
		 */
		public function afacr_redirect_to_cart_page( $redirect_to ) {

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			if ( isset( $_GET['redirect_to'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) ) ) {
				return esc_url( sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) );
			}

			return $redirect_to;
		}

		/**
		 * Enqueue Scripts and style sheets
		 */
		public function afacr_front_scripts() {

			wp_enqueue_style( 'frontcss', plugins_url( '../assets/css/front.css', __FILE__ ), false, '3.4.1' );
			wp_enqueue_script( 'jquery' );
			// Enqueue Scripts.
			wp_enqueue_script( 'frontjs', plugins_url( '../assets/js/front.js', __FILE__ ), array(), '1.0.0', false );

			if ( is_user_logged_in() ) {
				return;
			}

			if ( 'checkout' === get_option( 'afacr_enable_guest' ) || 'never' === get_option( 'afacr_enable_guest' ) || 0 === WC()->cart->get_cart_contents_count() ) {
				return;
			}

			if ( isset( wc()->session ) ) {
				if ( ! empty( wc()->session->get( 'afacr_email_stored' ) ) ) {
					return;
				}
			}

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			if ( isset( $_GET['afacr_cart_id'] ) && ! empty( $_GET['afacr_cart_id'] ) ) {
				return;
			}

			wp_enqueue_style( 'bootstrapcss', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css', false, '3.4.1' );
			wp_enqueue_script( 'bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js', array(), '3.4.1', false );
		}

		/**
		 * Load abandoned cart via link
		 *
		 * @param object $cart Cart Object.
		 */
		public function afacr_load_abandoned_cart( $cart ) {

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			if ( is_user_logged_in() ) {
				return;
			}

			if ( isset( $_GET['afacr_cart_id'] ) && ! empty( $_GET['afacr_cart_id'] ) ) {

				$post_id = sanitize_text_field( wp_unslash( $_GET['afacr_cart_id'] ) );

				if ( ! empty( $post_id ) && empty( $cart->get_cart_contents() ) ) {

					$cart = get_post( $post_id );

					if ( ! is_a( $cart, 'WP_Post' ) ) {
						return;
					}

					$user_id = get_post_meta( $cart->ID, 'user_id', true );
					$user    = get_user_by( 'id', $user_id );

					if ( is_a( $user, 'WP_User' ) && ! is_user_logged_in() ) {
						wc_add_notice( __( 'Your abandoned cart has been loaded successfully.', 'addify_acr' ), 'success' );
						wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '?redirect_to=' . wc_get_cart_url() );
						exit;
					}

					$cart_contents = json_decode( $cart->post_content, true );

					foreach ( $cart_contents as $cart_item ) {

						$product_id   = $cart_item['product_id'];
						$quantity     = $cart_item['quantity'];
						$variation_id = $cart_item['variation_id'];
						$variation    = (array) $cart_item['variation'];

						wc()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

					}

					wc_add_notice( __( 'Your abandoned cart has been loaded successfully.', 'addify_acr' ), 'success' );
				}
			}
		}

		/**
		 * Save guest email address.
		 */
		public function afacr_save_guest_email_address() {

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			if ( isset( $_REQUEST['modal_cancel'] ) ) {
				if ( isset( wc()->session ) ) {
					wc()->session->set( 'afacr_email_stored', 'false' );
				}
				return;
			}

			if ( isset( $_POST['afacr_save_email'] ) ) {

				if ( isset( wc()->session ) ) {
					wc()->session->set( 'afacr_email_stored', 'true' );
				}

				if ( isset( $_POST['afacr_agree_terms'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['afacr_agree_terms'] ) ) ) {

					if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

						$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();

						if ( isset( $_POST['afacr_email_address'] ) ) {

							$user_email = sanitize_text_field( wp_unslash( $_POST['afacr_email_address'] ) );
							update_post_meta( $this->afacr_id, 'user_email', $user_email );

						}
					}

				} elseif ( 'always' === get_option( 'afacr_enable_guest' ) ) {

					if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

						$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();

						if ( isset( $_POST['afacr_email_address'] ) ) {

							$user_email = sanitize_text_field( wp_unslash( $_POST['afacr_email_address'] ) );
							update_post_meta( $this->afacr_id, 'user_email', $user_email );

						}
					}

				} elseif ( 'ask' === get_option( 'afacr_enable_guest' ) &&  ! isset( $_POST['afacr_agree_terms'] ) ) {

					if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

						$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();
					}

					if ( !empty( $this->afacr_id ) ) {

						if ( is_a( get_post($this->afacr_id), 'WP_Post') ) {
							wp_delete_post( $this->afacr_id );
							unset( $this->afacr_id );
						}
					}
				}
			}
		}

		/**
		 * Show modal for guest to get email address
		 */
		public function afacr_guest_modal_for_email() {

			if ( is_user_logged_in() ) {
				return;
			}

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			if ( isset( $_GET['afacr_cart_id'] ) && ! empty( $_GET['afacr_cart_id'] ) ) {
				return;
			}

			if ( 'checkout' === get_option( 'afacr_enable_guest' ) || 'never' === get_option( 'afacr_enable_guest' ) || 0 === WC()->cart->get_cart_contents_count() ) {
				return;
			}

			if ( isset( wc()->session ) ) {
				if ( ! empty( wc()->session->get( 'afacr_email_stored' ) ) ) {
					return;
				}
			}

			$modal_title = (string) get_option( 'afacr_popup_title' );

			?>
			<!-- Trigger the modal with a button -->
			<button id="afacr-btn-ask-guest" type="button" class="afacr-btn-ask-guest btn btn-info btn-lg hidden" data-toggle="modal" data-target="#myModal12345"></button>
			<!-- Modal -->
			<div id="myModal12345" class="myModal12345 modal fade" role="dialog">
				<div class="modal-dialog">
					<form method="POST">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php echo esc_html( ! empty( $modal_title ) ? $modal_title : 'Email Required to Store Abandoned Cart' ); ?></h4> </div>
							<div class="modal-body">
								<table>
									<tr>
										<th>
											<label class="">
												<?php echo esc_html__( 'Email Address', 'addify_acr' ); ?>
											</label>
										</th>
										<td>
											<input class="w-100" type="email" name="afacr_email_address" required> </td>
									</tr>
									<?php
									if ( 'ask' === get_option( 'afacr_enable_guest' ) ) {
										$privacy = (array) get_option( 'afacr_text_for_terms' );
										?>
										<tr>
											<th colspan="2">
												<input type="checkbox" name="afacr_agree_terms" value="yes">
											<?php echo esc_html( isset( $privacy[1] ) ? $privacy[1] : 'I agree to terms and conditions and privacy policy of site.' ); ?>
											</th>
										</tr>
										<?php } ?>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" name="afacr_cancel_modal" id="afacr_cancel_modal" class="btn btn-default">
									<?php echo esc_html__( 'Cancel', 'addify_acr' ); ?>
								</button>
								<button type="submit" name="afacr_save_email" value="save" class="btn btn-default ">
									<?php echo esc_html__( 'Save', 'addify_acr' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<?php
		}


		/**
		 * Get user abandoned cart id.
		 */
		public function afacr_get_user_abandoned_cart_id() {

			$customer = wc()->session->get_customer_id();
			$args     = array(
				'post_type'   => 'addify_acr_carts',
				'post_status' => 'publish',
				'meta_query'  => array(
					array(
						'key'     => 'user_id',
						'value'   => $customer,
						'compare' => '=',
						'type'    => 'INT',
					),
					array(
						'key'     => 'cart_status',
						'value'   => array( 'abandoned', 'abandoned-awaiting' ),
						'compare' => 'IN',
					),
				),
				'fields'      => 'ids',
			);

			$the_query = new WP_Query( $args );

			if ( $the_query->have_posts() ) {

				$carts = $the_query->get_posts();
				return current( $carts );
			}

			return null;
		}


		/**
		 * Order status change call back.
		 *
		 * @param object $order Order object.
		 *
		 * @return void
		 */
		public function afacr_order_created( $order ) {

			if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

				$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();

				if ( empty( $this->afacr_id ) ) {
					return;
				}
			}

			$cart_status = get_post_meta( $this->afacr_id, 'cart_status', true );

			if ( 'abandoned' === $cart_status ) {

				// Increase recovered cart quantity.
				$total_carts = intval( get_option( 'afacr_total_recovered_carts' ) ) + 1;

				update_option( 'afacr_total_recovered_carts', $total_carts );

				// Add amount in total recovery.
				$cart_amount = get_post_meta( $this->afacr_id, 'cart_subtotal', true );

				$total_amount = floatval( get_option( 'afacr_total_recovered_cart_amount' ) ) + $cart_amount;

				update_option( 'afacr_total_recovered_cart_amount', $total_amount );

				// Update cart Status.
				update_post_meta( $this->afacr_id, 'cart_status', 'recovered' );

			} else {
				// Delete cart.
				wp_delete_post( $this->afacr_id, true );

			}
		}


		/**
		 * Update abandoned cart.
		 *
		 * @param object $item_key Key of removed item.
		 * @param object $cart WC_Cart object.
		 *
		 * @return void
		 */
		public function afacr_update_abandoned_cart( $item_key, $cart ) {

			if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

				$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();

				if ( empty( $this->afacr_id ) ) {
					return;
				}
			}

			if ( $cart->get_cart_contents_count() < 1 ) {
				wp_delete_post( $this->afacr_id, true );
			}

		}

		/**
		 * Record abandoned cart.
		 *
		 * @param object $cart WC_Cart object.
		 *
		 * @return void
		 */
		public function afacr_record_abandoned_cart( $cart ) {

			if ( ! is_user_logged_in() && 'checkout' === get_option( 'afacr_enable_guest' )  ) {
				return;
			}

			if ( ! is_user_logged_in() && 'never' === get_option( 'afacr_enable_guest' )  ) {
				return;
			}

			if ( is_checkout() ) {
				return;
			}

			if ( 0 == WC()->cart->get_cart_contents_count() ) {

				if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

					$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();
				}

				if ( !empty( $this->afacr_id ) ) {
					wp_delete_post( $this->afacr_id );
				}
				return;
			}

			if ( ! isset( $this->afacr_id ) || empty( $this->afacr_id ) ) {

				$this->afacr_id = $this->afacr_get_user_abandoned_cart_id();
			}

			if ( ! empty( $this->afacr_id ) ) {

				$post_data = array(
					'ID'           => $this->afacr_id,
					'post_content' => wp_json_encode( wc()->cart->get_cart_contents() ),
				);

				$post_id = wp_update_post( $post_data, true );

				if ( ! is_wp_error( $post_id ) ) {

					$this->afacr_id = $post_id;

					update_post_meta( $this->afacr_id, 'cart_subtotal', wc()->cart->get_subtotal() );
					update_post_meta( $this->afacr_id, 'cart_total', wc()->cart->get_total() );
					update_post_meta( $this->afacr_id, 'cart_totals', wc()->cart->get_totals() );

					return;

				} else {

					wc_add_notice( implode( ',', $post_id->get_error_messages() ), 'error' );
					return;
				}
			} else {

				$post_data = array(
					'post_title'   => 'Abandoned Cart',
					'post_type'    => 'addify_acr_carts',
					'post_content' => wp_json_encode( wc()->cart->get_cart_contents() ),
					'post_status'  => 'publish',
				);

				$post_id = wp_insert_post( $post_data, true );

				if ( ! is_wp_error( $post_id ) ) {

					$this->afacr_id = $post_id;

					if ( is_user_logged_in() ) {

						$current_user = wp_get_current_user();
						update_post_meta( $this->afacr_id, 'user_id', $current_user->ID );
						update_post_meta( $this->afacr_id, 'user_email', $current_user->user_email );
						update_post_meta( $this->afacr_id, 'cart_subtotal', wc()->cart->get_subtotal() );
						update_post_meta( $this->afacr_id, 'cart_total', wc()->cart->get_total() );
						update_post_meta( $this->afacr_id, 'cart_totals', wc()->cart->get_totals() );
						update_post_meta( $this->afacr_id, 'cart_status', 'abandoned-awaiting' );

					} else {

						$customer = wc()->session->get_customer_id();
						update_post_meta( $this->afacr_id, 'user_id', $customer );
						update_post_meta( $this->afacr_id, 'cart_subtotal', wc()->cart->get_subtotal() );
						update_post_meta( $this->afacr_id, 'cart_total', wc()->cart->get_total() );
						update_post_meta( $this->afacr_id, 'cart_totals', wc()->cart->get_totals() );
						update_post_meta( $this->afacr_id, 'cart_status', 'abandoned-awaiting' );
					}
				} else {

					wc_add_notice( implode( ',', $post_id->get_error_messages() ), 'error' );
					return;
				}

				return;
			}
		}
	}
	new AF_Abandoned_Cart_Front();
}
