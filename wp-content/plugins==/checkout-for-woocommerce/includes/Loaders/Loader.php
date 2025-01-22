<?php

namespace Objectiv\Plugins\Checkout\Loaders;

/**
 * Helps load pages
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */

abstract class Loader {
	public static function checkout() {}
	public static function order_pay() {}
	public static function order_received() {}

	/**
	 * @return array The global parameters
	 */
	public static function init_checkout() {
		/**
		 * Set Checkout Constant
		 */
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		/**
		 * Add body classes
		 */
		add_filter(
			'body_class',
			function( $css_classes ) {
				if ( ! cfw_show_shipping_tab() ) {
					$css_classes[] = 'cfw-hide-shipping';
				}

				return $css_classes;
			}
		);

		// This seems to be a 3.5 requirement
		// Ensure gateways and shipping methods are loaded early.
		WC()->payment_gateways();
		WC()->shipping();

		// When on the checkout with an empty cart, redirect to cart page
		if ( WC()->cart->is_empty() ) {
			wc_add_notice( cfw__( 'Checkout is not available whilst your cart is empty.', 'woocommerce' ), 'notice' );
			wp_redirect( wc_get_cart_url() );
			exit;
		}

		// Check cart contents for errors
		do_action( 'woocommerce_check_cart_items' );

		// Calc totals
		WC()->cart->calculate_totals();

		/**
		 * Filters global template parameters available to templates
		 *
		 * @since 3.0.0
		 *
		 * @param array $global_params The global template parameters
		 */
		return apply_filters( 'cfw_template_global_params', array() );
	}

	/**
	 * @return array The global template parameters
	 */
	public static function init_order_pay() {
		global $wp;

		/**
		 * Filters global template parameters available to templates
		 *
		 * @since 3.0.0
		 *
		 * @param array $global_template_parameters The global template parameters
		 */
		$global_template_parameters = apply_filters(
			'cfw_template_global_params',
			array(
				'call_receipt_hook'  => false,
				'order_button_text'  => apply_filters( 'woocommerce_pay_order_button_text', cfw__( 'Pay for order', 'woocommerce' ) ),
				'available_gateways' => array(),
			)
		);

		do_action( 'before_woocommerce_pay' );

		$order_id = absint( $wp->query_vars['order-pay'] );

		// Pay for existing order.
		if ( isset( $_GET['pay_for_order'], $_GET['key'] ) && $order_id ) { // WPCS: input var ok, CSRF ok.
			try {
				$order_key          = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
				$order              = wc_get_order( $order_id );
				$hold_stock_minutes = (int) get_option( 'woocommerce_hold_stock_minutes', 0 );

				// Order or payment link is invalid.
				if ( ! $order || $order->get_id() !== $order_id || ! hash_equals( $order->get_order_key(), $order_key ) ) {
					throw new \Exception( cfw__( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ) );
				}

				if ( ! current_user_can( 'pay_for_order', $order->get_id() ) && ! is_user_logged_in() ) {
					wc_add_notice( cfw__( 'Please log in to your account below to continue to the payment form.', 'woocommerce' ), 'error' );
				}

				// Add notice if logged in customer is trying to pay for guest order.
				if ( ! $order->get_user_id() && is_user_logged_in() ) {
					// If order has does not have same billing email then current logged in user then show warning.
					if ( $order->get_billing_email() !== wp_get_current_user()->user_email ) {
						wc_add_notice( cfw__( 'You are paying for a guest order. Please continue with payment only if you recognize this order.', 'woocommerce' ), 'notice' );
					}
				}

				// Logged in customer trying to pay for someone else's order.
				if ( ! current_user_can( 'pay_for_order', $order_id ) && is_user_logged_in() ) {
					throw new \Exception( cfw__( 'This order cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ) );
				}

				// Does not need payment.
				if ( ! $order->needs_payment() ) {
					/* translators: %s: order status */
					throw new \Exception( sprintf( cfw__( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ) );
				}

				// Ensure order items are still stocked if paying for a failed order. Pending orders do not need this check because stock is held.
				if ( ! $order->has_status( wc_get_is_pending_statuses() ) ) {
					$quantities = array();

					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							$quantities[ $product->get_stock_managed_by_id() ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $item->get_quantity() : $item->get_quantity();
						}
					}

					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							if ( ! apply_filters( 'woocommerce_pay_order_product_in_stock', $product->is_in_stock(), $product, $order ) ) {
								/* translators: %s: product name */
								throw new \Exception( sprintf( cfw__( 'Sorry, "%s" is no longer in stock so this order cannot be paid for. We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name() ) );
							}

							// We only need to check products managing stock, with a limited stock qty.
							if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
								continue;
							}

							// Check stock based on all items in the cart and consider any held stock within pending orders.
							$held_stock     = ( $hold_stock_minutes > 0 ) ? wc_get_held_stock_quantity( $product, $order->get_id() ) : 0;
							$required_stock = $quantities[ $product->get_stock_managed_by_id() ];

							if ( $product->get_stock_quantity() < ( $held_stock + $required_stock ) ) {
								/* translators: 1: product name 2: quantity in stock */
								throw new \Exception( sprintf( cfw__( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) ) );
							}
						}
					}
				}

				WC()->customer->set_props(
					array(
						'billing_country'  => $order->get_billing_country() ? $order->get_billing_country() : null,
						'billing_state'    => $order->get_billing_state() ? $order->get_billing_state() : null,
						'billing_postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : null,
					)
				);
				WC()->customer->save();

				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

				if ( count( $available_gateways ) ) {
					current( $available_gateways )->set_current();
				}

				$global_template_parameters['order']              = $order;
				$global_template_parameters['available_gateways'] = $available_gateways;
			} catch ( \Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		} elseif ( $order_id ) {

			// Pay for order after checkout step.
			$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
			$order     = wc_get_order( $order_id );

			if ( $order && $order->get_id() === $order_id && hash_equals( $order->get_order_key(), $order_key ) ) {

				if ( $order->needs_payment() ) {

					$global_template_parameters['order']             = $order;
					$global_template_parameters['call_receipt_hook'] = true;

				} else {
					/* translators: %s: order status */
					wc_add_notice( sprintf( cfw__( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ), 'error' );
				}
			} else {
				wc_add_notice( cfw__( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ), 'error' );
			}
		} else {
			wc_add_notice( cfw__( 'Invalid order.', 'woocommerce' ), 'error' );
		}

		return $global_template_parameters;
	}

	/**
	 * @return array The global template parameters
	 * @throws \WC_Data_Exception
	 */
	public static function init_thank_you() {
		$order            = cfw_get_main()->get_order_received_order();
		$settings_manager = cfw_get_main()->get_settings_manager();

		/**
		 * Filters global template parameters available to templates
		 *
		 * @since 3.0.0
		 *
		 * @param array $global_template_parameters The global template parameters
		 */
		$global_template_parameters = apply_filters( 'cfw_template_global_params', array() );

		// Empty awaiting payment session.
		unset( WC()->session->order_awaiting_payment );

		// In case order is created from admin, but paid by the actual customer, store the ip address of the payer.
		if ( $order ) {
			$order->set_customer_ip_address( \WC_Geolocation::get_ip_address() );
			$order->save();
		} else {
			return $global_template_parameters;
		}

		$valid_order_statuses = array_flip( array_intersect_key( array_flip( (array) $settings_manager->get_setting( 'thank_you_order_statuses' ) ), wc_get_order_statuses() ) );

		$global_template_parameters['order']          = $order;
		$global_template_parameters['order_statuses'] = str_replace( 'wc-', '', $valid_order_statuses );
		$global_template_parameters['show_downloads'] = $order->has_downloadable_item() && $order->is_download_permitted();
		$global_template_parameters['downloads']      = $order->get_downloadable_items();

		do_action( 'cfw_checkout_loaded_pre_head' );

		// Empty current cart.
		if ( ! isset( $_GET['view'] ) ) {
			wc_clear_cart_after_payment();
		}

		return $global_template_parameters;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param array $global_template_parameters
	 * @param string $template_file
	 */
	public static function display( array $global_template_parameters, string $template_file ) {
		/**
		 * Fires before template pieces are loaded
		 *
		 * @since 3.0.0
		 *
		 * @param string $template_file The template file
		 */
		do_action( 'cfw_template_before_load', $template_file );

		// Load content template
		cfw_get_main()->get_templates_manager()->get_active_template()->view( $template_file, $global_template_parameters );

		/**
		 * Fires after template pieces are loaded
		 *
		 * @since 3.0.0
		 *
		 * @param string $template_file The template file
		 */
		do_action( 'cfw_template_after_load', $template_file );
	}

	/**
	 *
	 */
	public static function output_meta_tags() {
		?>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width">
		<?php
	}

	/**
	 * Output content of Settings > General > Header Scripts
	 */
	public static function output_custom_header_scripts() {
		echo cfw_get_main()->get_settings_manager()->get_setting( 'header_scripts' );
	}

	/**
	 * Output content of Settings > General > Footer Scripts
	 */
	public static function output_custom_footer_scripts() {
		echo cfw_get_main()->get_settings_manager()->get_setting( 'footer_scripts' );
	}

	public static function output_page_title() {
		// We use this instead of _wp_render_title_tag because it requires the theme support title-tag capability.
		echo '<title>' . wp_get_document_title() . '</title>' . "\n";
	}

	public static function output_wp_styles() {
		wp_print_styles();
	}

	/**
	 * Output custom styles
	 */
	public static function output_custom_styles() {
		$settings_manager  = cfw_get_main()->get_settings_manager();
		$templates_manager = cfw_get_main()->get_templates_manager();

		// Get logo attachment ID if available
		$logo_attachment_id    = $settings_manager->get_setting( 'logo_attachment_id' );
		$active_theme          = $templates_manager->get_active_template()->get_slug();
		$supports              = $templates_manager->get_active_template()->get_supports();
		$body_background_color = $settings_manager->get_setting( 'body_background_color', array( $active_theme ) );
		$body_text_color       = $settings_manager->get_setting( 'body_text_color', array( $active_theme ) );
		$body_font             = $settings_manager->get_setting( 'body_font' );
		$heading_font          = $settings_manager->get_setting( 'heading_font' );

		if ( ! empty( $body_font ) && 'System Font Stack' !== $body_font ) {
			echo '<link href="https://fonts.googleapis.com/css?family=' . urlencode( $body_font ) . '&display=swap" rel="stylesheet">';
		}

		if ( ! empty( $heading_font ) && 'System Font Stack' !== $heading_font ) {
			echo '<link href="https://fonts.googleapis.com/css?family=' . urlencode( $heading_font ) . '&display=swap" rel="stylesheet">';
		}
		?>
		<style>
			html, body.checkout-wc {
			<?php if ( ! empty( $body_background_color ) ) : ?>
				background: <?php echo $body_background_color; ?> !important;
			<?php endif; ?>

			<?php if ( ! empty( $body_text_color ) ) : ?>
				color: <?php echo $body_text_color; ?>;
			<?php endif; ?>

			<?php if ( ! empty( $body_font ) ) : ?>
				font-family: <?php echo 'System Font Stack' === $body_font ? '' : "'" . $body_font . "'"; ?>,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
			<?php endif; ?>
			}

			.pay-button-separator span, #cfw-payment-request-buttons h2 {
			<?php if ( ! empty( $body_background_color ) ) : ?>
				background: <?php echo $body_background_color; ?>;
			<?php endif; ?>

			<?php if ( ! empty( $body_text_color ) ) : ?>
				color: <?php echo $body_text_color; ?>;
			<?php endif; ?>
			}

			<?php if ( ! empty( $heading_font ) ) : ?>
			h1, h2, h3, h4, h5, h6 {
				font-family: <?php echo 'System Font Stack' === $heading_font ? '' : "'" . $heading_font . "'"; ?>,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
			}
			<?php endif; ?>

			<?php if ( ! empty( $body_background_color ) ) : ?>
			#cfw.context-thank-you ul.status-steps i, #cfw.context-thank-you ul.status-steps li:first-child:after {
				background: <?php echo $body_background_color; ?>;
			}
			<?php endif; ?>
			<?php if ( in_array( 'header-background', $supports, true ) ) : ?>
			#cfw-header {
				background: <?php echo $settings_manager->get_setting( 'header_background_color', array( $active_theme ) ); ?>;

				<?php if ( strtolower( $settings_manager->get_setting( 'header_background_color', array( $active_theme ) ) ) !== '#ffffff' ) : ?>
				margin-bottom: 2em;
			<?php endif; ?>
			}
			<?php endif; ?>

			<?php if ( in_array( 'footer-background', $supports, true ) ) : ?>
			#cfw-footer {
				color: <?php echo $settings_manager->get_setting( 'footer_color', array( $active_theme ) ); ?>;
				background: <?php echo $settings_manager->get_setting( 'footer_background_color', array( $active_theme ) ); ?>;

				<?php if ( strtolower( $settings_manager->get_setting( 'footer_background_color', array( $active_theme ) ) ) !== '#ffffff' ) : ?>
				margin-top: 2em;
			<?php endif; ?>
			}
			<?php endif; ?>

			<?php if ( in_array( 'summary-background', $supports, true ) ) : ?>
			#cfw-cart-summary, #cfw-cart-summary:before {
				background: <?php echo $settings_manager->get_setting( 'summary_background_color', array( $active_theme ) ); ?> !important;
			}
			<?php endif; ?>

			#arrow, .lost_password a {
				color: <?php echo $settings_manager->get_setting( 'link_color', array( $active_theme ) ); ?> !important;
				fill: <?php echo $settings_manager->get_setting( 'link_color', array( $active_theme ) ); ?> !important;
			}

			a {
				color: <?php echo $settings_manager->get_setting( 'link_color', array( $active_theme ) ); ?>;
			}

			main.checkoutwc .cfw-bottom-controls .cfw-primary-btn, main.checkoutwc .place-order .cfw-primary-btn, main.checkoutwc #info_payment button {
				background-color: <?php echo $settings_manager->get_setting( 'button_color', array( $active_theme ) ); ?>;
				color: <?php echo $settings_manager->get_setting( 'button_text_color', array( $active_theme ) ); ?>;
			}

			main.checkoutwc .cfw-bottom-controls .cfw-primary-btn:hover, main.checkoutwc .place-order .cfw-primary-btn:hover, main.checkoutwc #info_payment button:hover {
				background-color: <?php echo $settings_manager->get_setting( 'button_hover_color', array( $active_theme ) ); ?>;
				color: <?php echo $settings_manager->get_setting( 'button_text_hover_color', array( $active_theme ) ); ?>;
			}

			main.checkoutwc .cfw-secondary-btn, main.checkoutwc .woocommerce-button {
				background-color: <?php echo $settings_manager->get_setting( 'secondary_button_color', array( $active_theme ) ); ?>;
				color: <?php echo $settings_manager->get_setting( 'secondary_button_text_color', array( $active_theme ) ); ?>;
			}

			main.checkoutwc .cfw-secondary-btn:hover, main.checkoutwc .woocommerce-button:hover {
				background-color: <?php echo $settings_manager->get_setting( 'secondary_button_hover_color', array( $active_theme ) ); ?>;
				color: <?php echo $settings_manager->get_setting( 'secondary_button_text_hover_color', array( $active_theme ) ); ?>;
			}


			<?php if ( ! empty( $logo_attachment_id ) ) : ?>
			.cfw-logo .logo {
				background: transparent url( <?php echo wp_get_attachment_url( $logo_attachment_id ); ?> ) no-repeat;
				background-size: contain;
				background-position: left center;
			}
			<?php else : ?>
			.cfw-logo .logo {
				<?php if ( in_array( 'header-background', $supports, true ) ) : ?>
				background: <?php echo $settings_manager->get_setting( 'header_background_color', array( $active_theme ) ); ?>;
			<?php endif; ?>
				height: auto !important;
				width: auto;
				margin: 20px auto;
				color: <?php echo $settings_manager->get_setting( 'header_text_color', array( $active_theme ) ); ?>;
			}
			.cfw-logo .logo:after {
				content: "<?php echo html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES ); ?>";
				font-size: 2em;
			}
			<?php endif; ?>

			.cfw-input-wrap > input[type="text"]:focus, .cfw-input-wrap > input[type="email"]:focus, .cfw-input-wrap > input[type="tel"]:focus, .cfw-input-wrap > input[type="number"]:focus, .cfw-input-wrap > input[type="password"]:focus, .cfw-input-wrap select:focus, .cfw-input-wrap textarea:focus {
				box-shadow: 0 0 0 2px <?php echo $settings_manager->get_setting( 'button_color', array( $active_theme ) ); ?> !important;
			}

			#cfw-cart .cart-item-row .cfw-cart-item-image .cfw-cart-item-quantity-bubble {
				background-color: <?php echo $settings_manager->get_setting( 'cart_item_quantity_color', array( $active_theme ) ); ?>;
				color: <?php echo $settings_manager->get_setting( 'cart_item_quantity_text_color', array( $active_theme ) ); ?>;
			}

			<?php if ( in_array( 'accent-color', $supports, true ) ) : ?>
				main.checkoutwc .cfw-shipping-methods-list li.active, main.checkoutwc .cfw-radio-reveal-li.cfw-active .cfw-radio-reveal-title-wrap  {
					background-color: <?php echo $settings_manager->get_setting( 'accent_color', array( $active_theme ) ); ?> !important;
				}
			<?php endif; ?>

			<?php if ( $settings_manager->get_setting( 'show_logos_mobile' ) === 'yes' ) : ?>
			@media(max-width: 900px) {
				#checkout #cfw-billing-methods .payment_method_icons {
					display: flex !important;
				}
			}
			<?php endif; ?>

			<?php echo $settings_manager->get_setting( 'custom_css', array( $active_theme ) ); ?>;
		</style>
		<?php
	}
}
