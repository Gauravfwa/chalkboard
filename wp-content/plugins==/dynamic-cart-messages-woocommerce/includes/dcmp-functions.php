<?php
/**
 * Display Dynamic cart message on product page.
 */
function dcmwp_show_dynamic_message_on_product_page() {
	global $product;

	$div_bgcolor          = get_option( 'dcmp-background-colors' );
	$messages_color       = get_option( 'dcmp-text-colors' );
	$border_radius        = get_option( 'dcmp-border-radius' );
	$button_bgcolor       = get_option( 'dcmp-button-background-colors' );
	$button_color         = get_option( 'dcmp-button-text-colors' );
	$button_border_radius = get_option( 'dcmp-button-border-radius' );

	// All published product id.
	$args              = array(
		'numberposts' => -1,
		'post_type'   => 'dcmp_msg',
		'fields'      => 'ids',
		'limit'       => -1,
		'status'      => 'publish',
	);
	$all_dcmp_post_ids = get_posts( $args );

	$messages = '';

	// Loop Through each rule and get the values from Custom Meta Box.
	foreach ( $all_dcmp_post_ids as $dcmp_post_id ) {
		$product_ids   = array();
		$taxonomy_type = get_post_meta( $dcmp_post_id, 'dcmp_taxonomy_type', true );

		if ( 'product_category' === $taxonomy_type ) {
			$choose_product_or_category = get_post_meta( $dcmp_post_id, 'dcmp_selected_category', true );
			$product_ids                = dcmwp_get_category_products( $choose_product_or_category );

		} else {
			$choose_product_or_category = intval( get_post_meta( $dcmp_post_id, 'dcmp_selected_product', true ) );
		}

		$dcmp_message_type     = get_post_meta( $dcmp_post_id, 'dcmp_message_type', true );
		$threshold             = get_post_meta( $dcmp_post_id, 'dcmp_threshold_value', true );
		$text_message          = get_post_meta( $dcmp_post_id, 'dcmp_after_initial_message', true );
		$threshold_message     = get_post_meta( $dcmp_post_id, 'dcmp_threshold_message', true );
		$show_in_product_page  = get_post_meta( $dcmp_post_id, 'dcmp_show_in_product_page', true );
		$expiry_date           = get_post_meta( $dcmp_post_id, 'dcmp_expiry_date', true );
		$expiry_date_timestamp = strtotime( $expiry_date );
		$message_icon          = get_post_meta( $dcmp_post_id, 'dcmp_message_icon', true );

		$currency_symbol_enable = get_option( 'dcmp-enable-currency-symbol' );
		$current_date_timestamp = strtotime( gmdate( 'Y-m-d' ) );

		$product = wc_get_product();

		if ( empty( $product ) ) {
			continue;
		}
		if ( ! ( $expiry_date_timestamp >= $current_date_timestamp || empty( $expiry_date ) ) ) {
			continue;
		}

		if ( $product->is_type( 'variable' ) ) {
			$product_id = $product->get_id();
			$page_id    = $product_id;

			foreach ( $product->get_children() as $variation_id ) {

				if ( $variation_id === $choose_product_or_category ) {
					$product_id = $variation_id;
				}
			}
		} elseif ( $product->is_type( 'grouped' ) ) {
			$page_id = $product->get_id();
			foreach ( $product->get_children() as $variation_id ) {

				if ( ( $variation_id === $choose_product_or_category || in_array( $variation_id, $product_ids, true ) ) ) {
					$product_id = $variation_id;
				}
			}
		} else {
			$product_id = $product->get_id();
			$page_id    = $product_id;
		}

		if ( ( ( $product_id === $choose_product_or_category ) || ( -2 === $choose_product_or_category ) ) && is_single( $page_id ) ) {

			if ( 'on' === $show_in_product_page ) {

				// To check current product is present in Cart or not.
				if ( false === dcmwp_check_product_is_in_cart( $product_id ) ) {
					// Show initial message only on product page.
					$qualifying_message = array(
						'text_msg'          => get_post_meta( $dcmp_post_id, 'dcmp_product_page_message', true ),
						'threshold_reached' => 'no',
					);

				} else {
					$qualifying_message = dcmwp_get_dynamic_cart_message( $taxonomy_type, $choose_product_or_category, $dcmp_message_type, $threshold, $text_message, $currency_symbol_enable, $threshold_message );
				}
			} else {
				continue;
			}
		} elseif ( in_array( $product_id, $product_ids, true ) && is_single( $page_id ) ) {

			if ( 'on' === $show_in_product_page ) {
				$product_present_in_cart = false;

				// To check current category product is present in Cart or not.
				foreach ( $product_ids as $pord_id ) {
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

						if ( $pord_id === $cart_item['product_id'] ) {
							$product_present_in_cart = true;
						}
					}
				}
				if ( false === $product_present_in_cart ) {
					$qualifying_message = array(
						'text_msg'          => get_post_meta( $dcmp_post_id, 'dcmp_product_page_message', true ),
						'threshold_reached' => 'no',
					);
				} else {
					$qualifying_message = dcmwp_get_dynamic_cart_message( $taxonomy_type, $choose_product_or_category, $dcmp_message_type, $threshold, $text_message, $currency_symbol_enable, $threshold_message );
				}
			} else {
				continue;

			}
		} else {

			continue;
		}

		$stock_status = $product->get_stock_status();
		if ( $product->is_type( 'variable' ) ) {
			// To check is current variation is in stock status. If it is in stock then only show message.
				$variation_obj = wc_get_product( $product_id );
				$stock_status  = $variation_obj->get_stock_status();

		}

		if ( isset( $qualifying_message['text_msg'] ) && ( ! empty( $qualifying_message['text_msg'] ) ) && ( 'instock' === $stock_status ) ) {
			$threshold_class = '';
			$button_message  = '';

			if ( 'yes' === $qualifying_message['threshold_reached'] ) {

				$message_icon    = 'fa-check-square-o';
				$threshold_class = 'dcmp-threshold-reached';

			} else {
				$button_message = dcmwp_get_button_message( $dcmp_post_id );
			}

			$message_html = '<div class="dcmp-message-box ' . $threshold_class . '"><i class="fa ' . $message_icon . ' fa-2x" aria-hidden="true"></i>
			<div class="dcmp-message-box-content">' . esc_attr( $qualifying_message['text_msg'] ) . '</div><div class="dcmwp-button-div">' . $button_message . '
			</div></div>';

			$messages .= $message_html;
		}
	}

	if ( ! empty( $messages ) ) {
		// Change the Background Color & Text Color in the Frontend( Cart Page).
		?>

		<style>
			.dcmp-cart-notices-wrapper .dcmp-message-box {
				background-color: <?php echo ! empty( $div_bgcolor ) ? esc_attr( $div_bgcolor ) : '#fff4b8'; ?>;
				color: <?php echo ! empty( $messages_color ) ? esc_attr( $messages_color ) : '#e6ae15'; ?>;
				border-radius: <?php echo ! empty( $border_radius ) ? esc_attr( $border_radius ) : '0'; ?>;
			}

			button.dcmwp-button {
				background-color: <?php echo ( ! empty( $button_bgcolor ) ? esc_attr( $button_bgcolor ) : '#e6ae15' ); ?> !important; 
				border-radius: <?php echo ( ! empty( $button_border_radius ) ? esc_attr( $button_border_radius ) : '0' ); ?>;
			}

			button.dcmwp-button a{
				color: <?php echo ! empty( $button_color ) ? esc_attr( $button_color ) : '#fff4b8'; ?>;
			}
		</style>
		<?php

		// Display message on product page for all instock product.
		if ( 'instock' === $product->get_stock_status() ) {

			$message  = '<div class="dcmp-cart-notices-wrapper">';
			$message .= apply_filters( 'dcmwp_customize_product_page_messages', $messages );
			$message .= '</div>';
			echo wp_kses_post( $message );
		}
	}
}
add_action( 'woocommerce_before_main_content', 'dcmwp_show_dynamic_message_on_product_page' );


/**
 * Call to action button and its message.
 *
 * @param int $dcmp_post_id .
 * @return string $button .
 */
function dcmwp_get_button_message( $dcmp_post_id ) {

	$button              = '';
	$show_message_button = get_post_meta( $dcmp_post_id, 'dcmp_show_message_button', true );

	// add message on call to action button.
	if ( ! empty( $show_message_button ) ) {

		$button_label = get_post_meta( $dcmp_post_id, 'dcmp_message_button_label', true );
		$button_url   = get_post_meta( $dcmp_post_id, 'dcmp_message_button_url', true );
		$open_new_tab = 'on' === get_post_meta( $dcmp_post_id, 'dcmp_message_open_new_tab', true ) ? 'target=_blank' : '';

		if ( ! empty( $button_url ) && ! empty( $button_label ) ) {

			$button = '<button class="dcmwp-button"><a href="' . esc_url( $button_url ) . '" ' . esc_attr( $open_new_tab ) . '>' . esc_attr( $button_label ) . '</a></button>';
		}
	}
	return $button;
}

/**
 * To check current product is in cart or not from single product to show initial message for product page.
 *
 * @param int $product_id .
 * @return boolean .
 */
function dcmwp_check_product_is_in_cart( $product_id ) {

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$item_id = ( $product_id === $cart_item['product_id'] ) ? intval( $cart_item['product_id'] ) : intval( $cart_item['variation_id'] );
		if ( $product_id === $item_id ) {
			return true;
		}
	}
	return false;
}

/**
 * To get all product ids of selected category from dynamic cart message CPT.
 *
 * @param string $category_slug .
 * @return array $product_ids .
 */
function dcmwp_get_category_products( $category_slug ) {
	$product_ids = wc_get_products(
		array(
			'category' => array( $category_slug ),
			'limit'    => -1, // All products .
			'status'   => 'publish', // Only published products .
			'return'   => 'ids',
		)
	);
	return map_deep( $product_ids, 'intval' );
}


/**
 * To show dynamic messages in the cart page.
 */
function dcmwp_show_dynamic_message_cart_page() {

	$messages = dcmwp_get_dynamic_message( 'cart' );
	$messages = ! empty( $messages ) ? $messages : null;
	echo wp_kses_post( $messages );

}
add_action( 'woocommerce_before_cart_contents', 'dcmwp_show_dynamic_message_cart_page' );


/**
 * To show dynamic messages in the checkout page.
 */
function dcmwp_show_dynamic_message_checkout_page() {

	$messages = dcmwp_get_dynamic_message( 'checkout' );
	$messages = ! empty( $messages ) ? $messages : null;
	echo wp_kses_post( $messages );
}
add_action( 'woocommerce_before_checkout_form', 'dcmwp_show_dynamic_message_checkout_page' );



/**
 * Gets all messages on cart and chekout page.
 *
 * @param string $qualifying_message .
 * @param int    $dcmp_post_id .
 * @param string $message_icon .
 *
 * @return string
 */
function dcmwp_get_all_cart_message( $qualifying_message, $dcmp_post_id, $message_icon ) {

	$messages = ''; // cart mesages.
	if ( isset( $qualifying_message['text_msg'] ) && ( ! empty( $qualifying_message['text_msg'] ) ) ) {
		$threshold_class = '';
		$button_message  = '';

		if ( 'yes' === $qualifying_message['threshold_reached'] ) {
			$message_icon    = 'fa-check-square-o';
			$threshold_class = 'dcmp-threshold-reached';
		} else {
			$button_message = dcmwp_get_button_message( $dcmp_post_id );
		}

		$message_html = '<div class="dcmp-message-box ' . $threshold_class . '"><i class="fa ' . $message_icon . ' fa-2x" aria-hidden="true"></i>
		<div class="dcmp-message-box-content">' . esc_attr( $qualifying_message['text_msg'] ) . '</div><div class="dcmwp-button-div">' . $button_message . '
		</div></div>';
		// Append current message with previous message.
		$messages .= $message_html;
	}
	return $messages;
}


/**
 * Loops through each Custom Post Type and get all the meta-box values for cart and checkout page.
 * Appends the messages and encloses it in a div.
 *
 * @param string $current_page To determine the page in order to print mesages.
 * @return string returns all the messages appended and enclosed in a div.
 */
function dcmwp_get_dynamic_message( $current_page ) {

	$div_bgcolor          = get_option( 'dcmp-background-colors' );
	$messages_color       = get_option( 'dcmp-text-colors' );
	$border_radius        = get_option( 'dcmp-border-radius' );
	$button_bgcolor       = get_option( 'dcmp-button-background-colors' );
	$button_color         = get_option( 'dcmp-button-text-colors' );
	$button_border_radius = get_option( 'dcmp-button-border-radius' );

	$currency_symbol_enable = get_option( 'dcmp-enable-currency-symbol' );
	$current_date_timestamp = strtotime( gmdate( 'Y-m-d' ) );

	// to get values from Custom Post Type.
	$args              = array(
		'post_type'      => 'dcmp_msg',
		'fields'         => 'ids',
		'posts_per_page' => -1, // All products.
		'status'         => 'publish', // Only published products.
	);
	$all_dcmp_post_ids = get_posts( $args );

	$messages = '';

	// Loop Through each rule and get the values from Custom Meta Box.
	foreach ( $all_dcmp_post_ids as $dcmp_post_id ) {

		$taxonomy_type = get_post_meta( $dcmp_post_id, 'dcmp_taxonomy_type', true );

		if ( 'product_category' === $taxonomy_type ) {
			$choose_product_or_category = get_post_meta( $dcmp_post_id, 'dcmp_selected_category', true );
		} else {
			$choose_product_or_category = intval( get_post_meta( $dcmp_post_id, 'dcmp_selected_product', true ) );
		}

		$dcmp_message_type     = get_post_meta( $dcmp_post_id, 'dcmp_message_type', true );
		$threshold_value       = intval( get_post_meta( $dcmp_post_id, 'dcmp_threshold_value', true ) );
		$text_message          = get_post_meta( $dcmp_post_id, 'dcmp_after_initial_message', true );
		$show_in_checkout      = get_post_meta( $dcmp_post_id, 'dcmp_show_in_checkout', true );
		$threshold_message     = get_post_meta( $dcmp_post_id, 'dcmp_threshold_message', true );
		$expiry_date           = get_post_meta( $dcmp_post_id, 'dcmp_expiry_date', true );
		$expiry_date_timestamp = strtotime( $expiry_date );
		$message_icon          = get_post_meta( $dcmp_post_id, 'dcmp_message_icon', true );

		if ( $expiry_date_timestamp >= $current_date_timestamp || empty( $expiry_date ) ) {
			if ( 'checkout' === $current_page ) {
				if ( 'on' === $show_in_checkout ) {
					$qualifying_message = dcmwp_get_dynamic_cart_message( $taxonomy_type, $choose_product_or_category, $dcmp_message_type, $threshold_value, $text_message, $currency_symbol_enable, $threshold_message );

					$messages .= dcmwp_get_all_cart_message( $qualifying_message, $dcmp_post_id, $message_icon );
				}
			} else {
				$qualifying_message = dcmwp_get_dynamic_cart_message( $taxonomy_type, $choose_product_or_category, $dcmp_message_type, $threshold_value, $text_message, $currency_symbol_enable, $threshold_message );

				$messages .= dcmwp_get_all_cart_message( $qualifying_message, $dcmp_post_id, $message_icon );
			}
		} else {
			continue;
		}
	}

	if ( empty( $messages ) ) {
		return null;
	} else {
			// Change the Background Color & Text Color in the Frontend( Cart Page).
		?>
			<style>
				.dcmp-cart-notices-wrapper .dcmp-message-box {
					background-color: <?php echo ! empty( $div_bgcolor ) ? esc_attr( $div_bgcolor ) : '#fff4b8'; ?>;
					color: <?php echo ! empty( $messages_color ) ? esc_attr( $messages_color ) : '#e6ae15'; ?>;
					border-radius: <?php echo ! empty( $border_radius ) ? esc_attr( $border_radius ) : '0'; ?>;
				}

				button.dcmwp-button {
					background-color: <?php echo ( ! empty( $button_bgcolor ) ? esc_attr( $button_bgcolor ) : '#e6ae15' ); ?> !important; 
					border-radius: <?php echo ( ! empty( $button_border_radius ) ? esc_attr( $button_border_radius ) : '0' ); ?>;
				}

				button.dcmwp-button a{
					color: <?php echo ! empty( $button_color ) ? esc_attr( $button_color ) : '#fff4b8'; ?>;
				}
			</style>
		<?php
	}

	// Create a div to place the message.

	$message  = '<div class="dcmp-cart-notices-wrapper">';
	$message .= apply_filters( 'dcmwp_customize_cart_checkout_messages', $messages );
	$message .= '</div>';
	return $message;
}

/**
 * Checks for the presence of a particular product/product category.
 * Count/Calcualte its amount and quantities to show remaining values.
 * Replaces appropriate shortcode value in text message.
 *
 * @param string  $taxonomy_type Product Name / Product Category.
 * @param mixed   $choose_product_or_category Choose Product or Category value.
 * @param string  $dcmp_message_type Simple/Price/Quantity.
 * @param int     $threshold_value Price/Quantity Value.
 * @param string  $text_message Message to be displayed if Price/Quantity is less then the threshold specified.
 * @param boolean $currency_symbol_enable Enable/Disable the Currency symbol in messages.
 * @param string  $threshold_message Message to be displayed if Price/Quantity is more then the threshold specified.
 * @return array returns formatted text message.
 */
function dcmwp_get_dynamic_cart_message( $taxonomy_type, $choose_product_or_category, $dcmp_message_type, $threshold_value, $text_message, $currency_symbol_enable, $threshold_message ) {

	$category_total   = 0; // total  cart amount.
	$cart_count       = 0; // total cart quantity.
	$category_in_cart = false;

	// loops through each item in cart page and checks for the presence of a particular category.
	// if found count/calculate its amount and quantities.
	if ( 'product_category' === $taxonomy_type ) {

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_ids = dcmwp_get_category_products( $choose_product_or_category );
			if ( in_array( $cart_item['product_id'], $product_ids, true ) ) {
				$category_in_cart = true;
				$category_total   = $category_total + $cart_item['line_total'];
				$cart_count      += $cart_item['quantity'];
			}
		}
		$category_total = apply_filters( 'dcmwp_change_cart_category_total', $category_total ); // modify the total calculated price of a particular category.
		$cart_count     = apply_filters( 'dcmwp_change_cart_category_count', $cart_count ); // modify the total calculated quantity of a particular category.
	} else {
		// loops through each item in cart page and checks for the presence of a particular product
		// if found count/calculate its amount and quantities.
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$item_id = ( $choose_product_or_category === $cart_item['product_id'] ) ? intval( $cart_item['product_id'] ) : intval( $cart_item['variation_id'] );

			if ( $choose_product_or_category === $item_id || ( -2 === $choose_product_or_category ) ) {
				$category_in_cart = true;
				$category_total  += $cart_item['line_total']; // total line cart total.
				$cart_count      += $cart_item['quantity']; // total cart quantity.
			}
		}
		$category_total = apply_filters( 'dcmwp_change_cart_product_total', $category_total ); // modify the total calculated price of a particular product.
		$cart_count     = apply_filters( 'dcmwp_change_cart_product_count', $cart_count ); // modify the total calculated quantity of a particular product.
	}

	// Performs the required calculation based on Select Condition Meta Box value.
	// Replaces the appropriate shortcode in text message with the respective calculated values.
	// Returns the formatted text message.
	if ( $category_in_cart ) {
		if ( 'dcmp_price' === $dcmp_message_type ) {  // For Price type message .
			if ( $threshold_value > $category_total ) {
				$final_cost   = $threshold_value - $category_total;
				$text_message = str_replace( '{price}', $final_cost, $text_message );
				if ( '1' === $currency_symbol_enable ) {
					// To get Currency symbol.
					$currency_symbol = get_woocommerce_currency_symbol();
					$text_message    = str_replace( '{cs}', $currency_symbol, $text_message );
				} else {
					$text_message = str_replace( '{cs}', '', $text_message );
				}
				return array(
					'text_msg'          => $text_message,
					'threshold_reached' => 'no',
				);
			} else {
				return array(
					'text_msg'          => $threshold_message,
					'threshold_reached' => 'yes',
				);
			}
		} elseif ( 'quantity' === $dcmp_message_type ) {  // For quantity type message .
			if ( $threshold_value > $cart_count ) {
				$rem_quantity = $threshold_value - $cart_count;
				$text_message = str_replace( '{qty}', $rem_quantity, $text_message );
				if ( '1' === $currency_symbol_enable ) {
					// To get Currency symbol.
					$currency_symbol = get_woocommerce_currency_symbol();
					$text_message    = str_replace( '{cs}', $currency_symbol, $text_message );
				} else {
					$text_message = str_replace( '{cs}', '', $text_message );
				}
				return array(
					'text_msg'          => $text_message,
					'threshold_reached' => 'no',
				);
			} else {
				$text_message = $threshold_message;
				return array(
					'text_msg'          => $threshold_message,
					'threshold_reached' => 'yes',
				);
			}
		} else {  // For simple text message.
			return array(
				'text_msg'          => $text_message,
				'threshold_reached' => 'no',
			);
		}
	}
}

add_action( 'wp_ajax_dcmp_update', 'dcmp_ajax_update_notice' );
add_action( 'wp_ajax_nopriv_dcmp_update', 'dcmp_ajax_update_notice' );

/**
 * Update rating Notice.
 */
function dcmp_ajax_update_notice() {
	global $current_user;
	if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dynamic-cart-messages-woocommerce' ) ) {
			wp_die( esc_html__( 'Permission Denied.', 'dynamic-cart-messages-woocommerce' ) );
		}

		update_user_meta( $current_user->ID, 'dcmp_rate_notices', 'rated' );
		echo esc_url( network_admin_url() );
	}
	wp_die();
}


add_action( 'admin_notices', 'dcmp_plugin_notice' );

/**
 * Rating notice widget.
 * Save the date to display notice after 10 days.
 */
function dcmp_plugin_notice() {
	global $current_user;
	$user_id = $current_user->ID;

	// if plugin is activated and date is not set then set the next 10 days.
	$today_date = strtotime( 'now' );

	if ( ! get_user_meta( $user_id, 'dcmp_notices_time' ) ) {
		$after_10_day = strtotime( '+10 day', $today_date );
		update_user_meta( $user_id, 'dcmp_notices_time', $after_10_day );
	}

	// gets the option of user rating status and week status.
	$rate_status = get_user_meta( $user_id, 'dcmp_rate_notices', true );
	$next_w_date = get_user_meta( $user_id, 'dcmp_notices_time', true );

	// show if user has not rated the plugin and it has been 1 week.
	if ( 'rated' !== $rate_status && $today_date > $next_w_date ) {
		?>
		<div class="notice notice-warning is-dismissible">
			<p><span><?php esc_html_e( "Awesome, you've been using", 'dynamic-cart-messages-woocommerce' ); ?></span><span><?php echo '<strong> Dynamic Cart Messages for WooCommerce </strong>'; ?><span><?php esc_html_e( 'for more than 1 week', 'dynamic-cart-messages-woocommerce' ); ?></span></p>
			<p><?php esc_html_e( 'If you like our plugin would you like to rate our plugin at WordPress.org ?', 'dynamic-cart-messages-woocommerce' ); ?></p>
			<span><a href="https://wordpress.org/plugins/dynamic-cart-messages-woocommerce/#reviews" target="_blank"><?php esc_html_e( "Yes, I'd like to rate it!", 'dynamic-cart-messages-woocommerce' ); ?></a></span>  -  <span><a class="dcmp_hide_rate" href="#"><?php esc_html_e( 'I already did!', 'dynamic-cart-messages-woocommerce' ); ?></a></span>
			<br/><br/>
		</div>
		<?php
	}
	?>

	<!-- Load jquery class -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script>
		let dcmpAjaxURL = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
		let dcmpNonce = "<?php echo esc_attr( wp_create_nonce( 'dynamic-cart-messages-woocommerce' ) ); ?>";

		// redirect to same page after rated.
		jQuery(".dcmp_hide_rate").click(function (event) {

			event.preventDefault();
			jQuery.ajax({
				method: 'POST',
				url: dcmpAjaxURL,
				data: {
					action: 'dcmp_update',
					nonce: dcmpNonce,
				},
				success: (res) => {
					window.location.href = window.location.href
				}
			});

		});
	</script>
	<?php
}
