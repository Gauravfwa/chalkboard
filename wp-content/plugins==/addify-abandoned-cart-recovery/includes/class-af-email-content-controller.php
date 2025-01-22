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

if ( ! class_exists( 'AF_Email_Content_Controller' ) ) {
	/**
	 * Front class of module.
	 */
	class AF_Email_Content_Controller {

		/**
		 * Store ID of current coupon.
		 *
		 * @var int
		 */
		private $coupon_id;

		/**
		 * Constructor of class AF_Abandoned_Cart_Admin.
		 */
		public function __construct() {

		}

		/**
		 * Send emails for carts.
		 *
		 * @param int $email_template_id Email Template ID.
		 * @param int $order_id Order ID.
		 */
		public function send_email_for_order( $email_template_id, $order_id ) {

			$from_name  = get_option( 'woocommerce_email_from_name' );
			$from_email = get_option( 'woocommerce_email_from_address' );
			// More headers.
			$headers  = 'MIME-Version: 1.0' . "\n";
			$headers .= 'Content-type:text/html' . "\n";
			$headers .= 'From: ' . $from_name . ' < ' . $from_email . ' > ' . "\r\n";

			$subject = get_post_meta( $email_template_id, 'afacr_email_subject', true );

			$order = wc_get_order( $order_id );

			$billing_email = $order->get_billing_email();

			$order_date = $order->get_date_created();

			$last_email = (array) json_decode( get_post_meta( $order_id, 'last_email_send', true ), true );

			$email_content = $this->get_content_for_email_order( $email_template_id, $order );
			$email_html    = $this->afacr_email_create_template( $subject, $email_content );

			if ( wp_mail( $billing_email, $subject, $email_html, $headers ) ) {

				array_push( $last_email, $email_template_id );
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

					if ( is_user_logged_in() ) {

						update_post_meta( $post_id, 'user_id', $order->get_customer_id() );
						update_post_meta( $post_id, 'user_email', $billing_email );
						update_post_meta( $post_id, 'email_type', 'pending_order' );
						update_post_meta( $post_id, 'subtotal', $order->get_subtotal() );
						update_post_meta( $post_id, 'total', $order->get_total() );
						update_post_meta( $post_id, 'status', 'pending_order' );

					}
				} else {

					wc_add_notice( implode( ',', $post_id->get_error_messages() ), 'error' );
					return;
				}
			} else {
				return false;
			}

		}

		/**
		 * Send emails for carts.
		 *
		 * @param int $email_template_id Email Template ID.
		 * @param int $cart_id Cart ID.
		 */
		public function send_email_for_cart( $email_template_id, $cart_id ) {

			$from_name  = get_option( 'woocommerce_email_from_name' );
			$from_email = get_option( 'woocommerce_email_from_address' );
			// More headers.
			$headers  = 'MIME-Version: 1.0' . "\n";
			$headers .= 'Content-type:text/html' . "\n";
			$headers .= 'From: ' . $from_name . ' < ' . $from_email . ' > ' . "\r\n";

			$subject = get_post_meta( $email_template_id, 'afacr_email_subject', true );

			$last_email = (array) json_decode( get_post_meta( $cart_id, 'last_email_send', true ), true );

			$user_email = get_post_meta( $cart_id, 'user_email', true );

			$email_content = $this->get_content_for_email_cart( $email_template_id, $cart_id );
			$email_html    = $this->afacr_email_create_template( $subject, $email_content );

			if ( wp_mail( $user_email, $subject, $email_html, $headers ) ) {

				// Total Abandoned Cart Emails.
				array_push( $last_email, $email_template_id );

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

					if ( is_user_logged_in() ) {

						update_post_meta( $post_id, 'user_id', get_post_meta( $cart_id, 'user_id', true ) );
						update_post_meta( $post_id, 'user_email', get_post_meta( $cart_id, 'user_email', true ) );
						update_post_meta( $post_id, 'email_type', 'abandoned_cart' );
						update_post_meta( $post_id, 'subtotal', get_post_meta( $cart_id, 'cart_subtotal', true ) );
						update_post_meta( $post_id, 'status', get_post_meta( $cart_id, 'cart_status', true ) );

					}
				} else {

					wc_add_notice( implode( ',', $post_id->get_error_messages() ), 'error' );
					return;
				}
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Create contents for abandoned carts emails.
		 *
		 * @param int $email_template_id Email Template ID.
		 * @param int $cart_id Cart ID.
		 */
		public function get_content_for_email_cart( $email_template_id, $cart_id ) {

			$user_id       = get_post_meta( $cart_id, 'user_id', true );
			$user_email    = get_post_meta( $cart_id, 'user_email', true );
			$cart_subtotal = get_post_meta( $cart_id, 'cart_subtotal', true );
			$cart_status   = get_post_meta( $cart_id, 'cart_status', true );
			$user          = get_user_by( 'id', $user_id );
			$template      = get_post( $email_template_id );
			$content       = wpautop( wptexturize( $template->post_content ) );

			if ( is_a( $user, 'WP_User') ) {

				$first_name = $user->user_firstname;
				$last_name  = $user->user_lastname;
				$full_name  = $user->display_name;

			} else {

				$first_name = empty( get_post_meta( $cart_id, 'first_name', true ) ) ? '' : get_post_meta( $cart_id, 'first_name', true );
				$last_name  = empty( get_post_meta( $cart_id, 'last_name', true ) ) ? '' : get_post_meta( $cart_id, 'last_name', true );
				$full_name  = empty( get_post_meta( $cart_id, 'full_name', true ) ) ? '' : get_post_meta( $cart_id, 'full_name', true );
			}

			$cart_table    = $this->get_cart_table_html_cart( $cart_id );
			$coupon_value  = get_post_meta( $email_template_id, 'afacr_coupon_value', true );
			$coupon_active = get_post_meta( $email_template_id, 'afacr_enable_coupon', true );

			if ( 'yes' === $coupon_active && floatval( $coupon_value ) > 0 ) {
				$coupon        = $this->create_coupon_for_email( $email_template_id, $cart_id );
				$coupon_expiry = date_format( date_create( $coupon->get_date_expires() ), 'M d,Y' );
				$coupon_code   = $coupon->get_code();
			} else {
				$coupon        = '';
				$coupon_expiry = '';
				$coupon_code   = '';
			}

			$recovery_link    = wc_get_cart_url() . '?afacr_cart_id=' . $cart_id;
			$recovery_button  = '<a href=" ' . wc_get_cart_url() . '?afacr_cart_id=' . $cart_id . ' " style="
							    display: block;
							    width: 100%;
							    background-color: #8BC34A;
							    color: white;
							    text-align: center;
							    width: 100%;
							    max-width: 125px;
							    margin: 0 auto;
							    padding: 5px 10px;
							    font-size: 16px;
							    line-height: 26px;
							    height: 25px;
							    max-height: 25px;
							    text-decoration: none;
							    border-radius: 3px;
							">' . get_option( 'afacr_recover_cart_button_text' ) . '</a>';
			$key_words_values = array( $first_name, $last_name, $full_name, wc_price( $cart_subtotal ), '<b>' . $coupon_code . '</b>', $coupon_expiry, $cart_table, $recovery_link, $recovery_button );
			$key_words        = array( '{first-name}', '{last-name}', '{full-name}', '{cart-subtotal}', '{coupon}', '{coupon-expiry}', '{cart-table}', '{recovery-link}', '{recovery-button}' );

			return str_replace( $key_words, $key_words_values, $content );
		}

		/**
		 * Create coupons for abandoned carts emails.
		 *
		 * @param int $email_template_id Email Template ID.
		 * @param int $cart_id Cart ID.
		 */
		public function create_coupon_for_email( $email_template_id, $cart_id ) {

			$user_id       = get_post_meta( $cart_id, 'user_id', true );
			$user_email    = get_post_meta( $cart_id, 'user_email', true );
			$cart_subtotal = get_post_meta( $cart_id, 'cart_subtotal', true );
			$cart_status   = get_post_meta( $cart_id, 'cart_status', true );

			$cart         = get_post( $cart_id );
			$cart_content = json_decode( $cart->post_content, true );
			$product_ids  = array();

			foreach ( (array) $cart_content as $key => $item ) {

				if ( ! isset( $item['product_id'] ) || empty( $item['product_id'] ) ) {
					continue;
				}

				$product_ids[] = $item['product_id'];
			}

			$coupon_value    = get_post_meta( $email_template_id, 'afacr_coupon_value', true );
			$discount_type   = 'percentage' === get_post_meta( $email_template_id, 'afacr_coupon_type', true ) ? 'percent' : 'fixed_cart';
			$coupon_validity = get_post_meta( $email_template_id, 'afacr_coupon_validity', true );
			$coupen_prefix   = get_option( 'afacr_coupons_prefix' );

			// Generate Coupon Code of 8 characters.
			$coupon_code = $coupen_prefix . strtoupper( substr( str_shuffle( md5( $cart_id ) ), 0, 5 ) );

			$description = __( 'Abandoned Cart coupon for email ', 'addify_acr' ) . $user_email;

			$coupon = array(
				'post_title'   => $coupon_code,
				'post_content' => '',
				'post_excerpt' => $description,
				'post_status'  => 'publish',
				'post_type'    => 'shop_coupon',
			);

			$post_id = wp_insert_post( $coupon );

			$this->coupon_id = $post_id;

			$coupon = new WC_Coupon( $post_id );

			
			$coupon->set_props(
				array(
					'code'                        => $coupon_code,
					'discount_type'               => wc_clean( $discount_type ),
					'amount'                      => wc_format_decimal( $coupon_value ),
					'date_expires'                => wc_clean( gmdate( 'd-m-Y H:i:s', strtotime( '+' . $coupon_validity . ' days' ) ) ),
					'individual_use'              => true,
					'product_ids'                 => $product_ids,
					'excluded_product_ids'        => array(),
					'usage_limit'                 => absint( 1 ),
					'usage_limit_per_user'        => absint( 1 ),
					'limit_usage_to_x_items'      => absint( '' ),
					'free_shipping'               => true,
					'product_categories'          => array(),
					'excluded_product_categories' => array(),
					'exclude_sale_items'          => false,
					'minimum_amount'              => wc_format_decimal( '' ),
					'maximum_amount'              => wc_format_decimal( '' ),
					'email_restrictions'          => array_filter( array_map( 'trim', explode( ',', wc_clean( $user_email ) ) ) ),
				)
			);

			if ( $coupon->save() ) {
				update_post_meta( $post_id, 'coupon_type', 'afacr_coupon');
			}

			return $coupon;
		}

		/**
		 * Create content for pending orders emails.
		 */
		public function delete_coupon_created() {

			wp_delete_post( $this->coupon_id, true );
		}

		/**
		 * Create content for pending orders emails.
		 *
		 * @param int $email_template_id Email Template ID.
		 * @param int $order Order Object.
		 */
		public function get_content_for_email_order( $email_template_id, $order ) {

			$user_id        = $order->get_customer_id();
			$order_subtotal = $order->get_subtotal();
			$user           = get_user_by( 'id', $user_id );
			$template       = get_post( $email_template_id );
			$content        = wpautop( wptexturize( $template->post_content ) );

			$first_name = $order->get_billing_first_name();
			$last_name  = $order->get_billing_last_name();
			$full_name  = $first_name . ' ' . $last_name;

			$cart_table      = $this->get_cart_table_html_order( $order );
			$recovery_link   = $order->get_checkout_payment_url();
			$coupon_id       = '';
			$coupon_expiry   = '';
			$recovery_button = '<a href=" ' . $order->get_checkout_payment_url() . ' " style="
							    display: block;
							    width: 100%;
							    background-color: #8BC34A;
							    color: white;
							    text-align: center;
							    width: 100%;
							    max-width: 125px;
							    margin: 0 auto;
							    padding: 5px 10px;
							    font-size: 16px;
							    line-height: 26px;
							    height: 25px;
							    max-height: 25px;
							    text-decoration: none;
							    border-radius: 3px;
							">' . get_option( 'afacr_pay_order_button_text' ) . '</a>';

			$key_words_values = array( $first_name, $last_name, $full_name, wc_price( $order_subtotal ), $coupon_id, $coupon_expiry, $cart_table, $recovery_link, $recovery_button );
			$key_words        = array( '{first-name}', '{last-name}', '{full-name}', '{cart-subtotal}', '{coupon}', '{coupon-expiry}', '{cart-table}', '{recovery-link}', '{recovery-button}' );

			return str_replace( $key_words, $key_words_values, $content );
		}

		/**
		 * Create content for pending orders emails.
		 *
		 * @param int $subject Subject of email.
		 * @param int $content Content of email.
		 */
		public function afacr_email_create_template( $subject, $content = '' ) {

			$af_footer_data = get_option( 'woocommerce_email_footer_text' );
			$new_footer     = str_replace( '{site_address}', get_option( 'home' ), $af_footer_data );
			$new_footer     = str_replace( '{site_title}', get_option( 'blogname' ), $af_footer_data );
			$content        = wpautop( wptexturize( $content ) );
			$new_footer     = str_replace( '{WooCommerce}', '<a href="https://woocommerce.com" style=" font-weight: normal; text-decoration: underline;">WooCommerce</a>', $new_footer );

			$html = '
			<style>
				a { color: ' . esc_attr( get_option( 'woocommerce_email_base_color' ) ) . ';}
				h2 { color: ' . esc_attr( get_option( 'woocommerce_email_base_color' ) ) . ';}
			</style>

			<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>
					<div id="wrapper" dir="ltr" style="background-color: ' . esc_attr( get_option( 'woocommerce_email_background_color' ) ) . '; margin: 0; padding: 70px 0; width: 100%; -webkit-text-size-adjust: none;">
						<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
							<tbody>
								<tr>
									<td valign="top" align="center">
										<div id="template_header_image">
											<p style="margin-top: 0;"><img src="' . esc_url( get_option( 'woocommerce_email_header_image' ) ) . '" alt="" style="border: none; display: inline-block; font-size: 14px; font-weight: bold; height: auto; outline: none; text-decoration: none; text-transform: capitalize; vertical-align: middle; max-width: 100%; margin-left: 0; margin-right: 0;"></p>
										</div>
										<table id="template_container" style="background-color: ' . esc_attr( get_option( 'woocommerce_email_body_background_color' ) ) . '; border: 0px solid #cd3333; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); border-radius: 3px;" width="600" cellspacing="0" cellpadding="0" border="0">
											<tbody>
												<tr>
													<td valign="top" align="center">
													<!-- Header -->
														<table id="template_header" style="background-color: ' . esc_attr( get_option( 'woocommerce_email_base_color' ) ) . '; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; border-radius: 3px 3px 0 0;" width="100%" cellspacing="0" cellpadding="0" border="0">
															<tbody>
																<tr>
																	<td id="header_wrapper" style="padding: 36px 48px; display: block;">
																		<h1 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #6a7d3a; color: #ffffff;">' . $subject . '</h1>
																	</td>
																</tr>
															</tbody>
														</table>
													<!-- End Header -->
													</td>
												</tr>
												<tr>
													<td valign="top" align="center">
													<!-- Body -->
														<table id="template_body" width="600" cellspacing="0" cellpadding="0" border="0">
															<tbody>
																<tr>
																	<td id="body_content" style="background-color: ' . esc_attr( get_option( 'woocommerce_email_body_background_color' ) ) . ';" valign="top">
																	<!-- Content -->
																		<table width="100%" cellspacing="0" cellpadding="20" border="0">
																			<tbody>
																				<tr>
																					<td style="padding: 48px 48px 32px;" valign="top">
																						<div id="body_content_inner" style="color: ' . esc_attr( get_option( 'woocommerce_email_text_color' ) ) . '; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;">
																							<p style="margin: 0 0 16px;">' . $content . '</p>
																							
																						</div>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	<!-- End Content -->
																	</td>
																</tr>
															</tbody>
														</table>
													<!-- End Body -->
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td valign="top" align="center">
									<!-- Footer -->
										<table id="template_footer" width="600" cellspacing="0" cellpadding="10" border="0">
											<tbody>
												<tr>
													<td style="padding: 0; border-radius: 6px;" valign="top">
														<table width="100%" cellspacing="0" cellpadding="10" border="0">
															<tbody>
																<tr>
																	<td colspan="2" id="credit" style="border-radius: 6px; border: 0; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 12px; line-height: 150%; text-align: center; padding: 24px 0;" valign="middle">
																		<p style="margin: 0 0 16px;">' . $new_footer . '</p>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									<!-- End Footer -->
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</body>
			</html>';

			return $html;

		}

		/**
		 * Create table for cart.
		 *
		 * @param int $cart_id Cart ID.
		 *
		 * @return string
		 */
		public function get_cart_table_html_cart( $cart_id ) {

			$cart          = get_post( $cart_id );
			$cart_content  = json_decode( $cart->post_content, true );
			$cart_subtotal = get_post_meta( $cart_id, 'cart_subtotal', true );
			$cart_totals   = get_post_meta( $cart_id, 'cart_totals', true );

			ob_start();
			?>
			<div style="margin-top: 10px; margin-bottom: 10px;">
				<table style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif" cellspacing="0" cellpadding="6" border="1">
					<thead>
						<tr>
							<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html__( 'Product', 'addify_acr' ); ?>
							</th>
							<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html__( 'Quantity', 'addify_acr' ); ?>
							</th>
							<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html__( 'Price', 'addify_acr' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( (array) $cart_content as $key => $item ) :

							$product_id = isset( $item['variation_id'] ) && ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];

							$product = wc_get_product( $product_id );

							if ( is_bool( $product ) ) {
								continue;
							}
							?>
							<tr>
								<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word">
								<?php echo esc_html( $product->get_title() ); ?>
								</td>
								<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
								<?php echo esc_attr( $item['quantity'] ); ?>
								</td>
								<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
								<?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?>
								</td>
							</tr>
							<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
								<?php echo esc_html__( 'Subtotal', 'addify_acr' ); ?>:</th>
							<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
								<?php echo wp_kses_post( wc_price( $cart_subtotal ) ); ?>
							</td>
						</tr>
						<?php if ( !empty( floatval( $cart_totals['subtotal_tax'] ) ) ) : ?>
							<tr>
								<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
									<?php echo esc_html__( 'Subtotal Tax', 'addify_acr' ); ?>:</th>
								<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
									<?php echo wp_kses_post( wc_price( $cart_totals['subtotal_tax'] ) ); ?>
								</td>
							</tr>
						<?php endif; ?>
						<?php if ( !empty( floatval( $cart_totals['shipping_total'] ) ) ) : ?>
							<tr>
								<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
									<?php echo esc_html__( 'Shipping', 'addify_acr' ); ?>:</th>
								<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
									<?php echo wp_kses_post( wc_price( $cart_totals['shipping_total'] ) ); ?>
								</td>
							</tr>
						<?php endif; ?>
						<?php if ( !empty( floatval( $cart_totals['total'] ) ) ) : ?>
							<tr>
								<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
									<?php echo esc_html__( 'Total', 'addify_acr' ); ?>:</th>
								<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
									<?php echo wp_kses_post( wc_price( $cart_totals['total'] ) ); ?>
								</td>
							</tr>
						<?php endif; ?>
					</tfoot>
				</table>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Create table for Object items.
		 *
		 * @param int $order Order Object.
		 *
		 * @return string
		 */
		public function get_cart_table_html_order( $order ) {

			$cart_content = $order->get_items();

			ob_start();
			?>
			<div style="margin-top: 10px; margin-bottom: 10px;">
				<table style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif" cellspacing="0" cellpadding="6" border="1">
					<thead>
						<tr>
							<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html__( 'Product', 'addify_acr' ); ?>
							</th>
							<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html__( 'Quantity', 'addify_acr' ); ?>
							</th>
							<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html__( 'Subtotal', 'addify_acr' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( (array) $cart_content as $key => $item ) : ?>
							<tr>
								<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word">
									<?php echo esc_html( $item->get_name() ); ?>
								</td>
								<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
									<?php echo esc_attr( $item->get_quantity() ); ?>
								</td>
								<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
									<?php echo wp_kses_post( wc_price( $item->get_subtotal() ) ); ?>
								</td>
							</tr>
							<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
								<?php echo esc_html__( 'Subtotal', 'addify_acr' ); ?>:</th>
							<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">
								<?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php echo esc_html__( 'Shipping', 'addify_acr' ); ?>:</th>
							<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php echo esc_html__( 'Payment method', 'addify_acr' ); ?>:</th>
							<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo esc_html( $order->get_payment_method_title() ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><?php echo esc_html__( 'Total', 'addify_acr' ); ?>:</th>
							<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
								<?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
			<?php
			return ob_get_clean();
		}
	}
}
