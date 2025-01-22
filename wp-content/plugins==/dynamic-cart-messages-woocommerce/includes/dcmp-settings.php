<?php
/**
 * Add a Custom Post Type called "Cart Messages"
 */
function dcmwp_custom_post_type() {
	// UI labels for Custom Post Type.
	$labels = array(
		'name'                  => _x( 'Cart Messages', 'Post type general name', 'dynamic-cart-messages-woocommerce' ),
		'singular_name'         => _x( 'Cart Messages', 'Post type singular name', 'dynamic-cart-messages-woocommerce' ),
		'menu_name'             => _x( 'Cart Messages', 'Admin Menu text', 'dynamic-cart-messages-woocommerce' ),
		'name_admin_bar'        => _x( 'Cart Messages', 'Add New on Toolbar', 'dynamic-cart-messages-woocommerce' ),
		'add_new'               => esc_attr__( 'Add New', 'dynamic-cart-messages-woocommerce' ),
		'add_new_item'          => __( 'Add New Cart Messages', 'dynamic-cart-messages-woocommerce' ),
		'new_item'              => __( 'New Cart Messages', 'dynamic-cart-messages-woocommerce' ),
		'edit_item'             => __( 'Edit Cart Messages', 'dynamic-cart-messages-woocommerce' ),
		'view_item'             => __( 'View Cart Messages', 'dynamic-cart-messages-woocommerce' ),
		'all_items'             => __( 'All Cart Messages', 'dynamic-cart-messages-woocommerce' ),
		'search_items'          => __( 'Search Cart Messages', 'dynamic-cart-messages-woocommerce' ),
		'parent_item_colon'     => __( 'Parent Cart Messages:', 'dynamic-cart-messages-woocommerce' ),
		'not_found'             => __( 'No Cart Messages found.', 'dynamic-cart-messages-woocommerce' ),
		'not_found_in_trash'    => __( 'No Cart Messages found in Trash.', 'dynamic-cart-messages-woocommerce' ),
		'archives'              => _x( 'Cart Messages archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'dynamic-cart-messages-woocommerce' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'dynamic-cart-messages-woocommerce' ),
		'filter_items_list'     => _x( 'Filter Cart Messages list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'dynamic-cart-messages-woocommerce' ),
		'items_list_navigation' => _x( 'Cart Messages list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'dynamic-cart-messages-woocommerce' ),
		'items_list'            => _x( 'Cart Messages list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'dynamic-cart-messages-woocommerce' ),

	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array(
			'slug'       => 'dcmp_msg',
			'with_front' => false,
		),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => null,
		'supports'           => array( 'title' ),

	);
	// Registering your Custom Post Type.
	register_post_type( 'dcmp_msg', $args );
}


/**
 * All Settings fields args for CPT.
 *
 * @return array .
 */
function dcmwp_get_cpt_setting_fields() {

	return array(
		array(
			'name'    => __( 'Enable Cart Message', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_control_cart_msg',
			'tooltip' => __( 'You can Enable or Disable the Cart Message by this switch', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'toggle',
		),
		array(
			'name'           => esc_attr__( 'Show Cart Message based on', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_taxonomy_type',
			'required_label' => true,
			'type'           => 'radio',
			'options'        => array(
				array(
					'name'  => __( 'Product Category', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'product_category',
				),
				array(
					'name'  => __( 'Product', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'product_name',
				),
			),
		),
		array(
			'name'           => esc_attr__( 'Choose Category', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_selected_category',
			'required_label' => true,
			'type'           => 'category_select',
		),
		array(
			'name'           => __( 'Choose Product', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_selected_product',
			'required_label' => true,
			'type'           => 'select',
			'options'        => wc_get_products(
				array(
					'limit'        => -1,                      // All products.
					'status'       => 'publish',              // Only published products.
					'stock_status' => 'instock',
					'return'       => 'ids',
				)
			),
		),
		array(
			'name'           => __( 'Message Criteria', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_message_type',
			'type'           => 'radio',
			'tooltip'        => __( "The type of criteria based on which you want to show the message to customers. Choose 'Simple' to show a plain message; choose 'Amount' - if your offer requires customers to add a certain amount to cart and choose 'Quantity' if your offer requires adding a certain quantity.", 'dynamic-cart-messages-woocommerce' ),
			'required_label' => true,
			'options'        => array(
				array(
					'name'  => __( 'Simple', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'simple_text',
				),
				array(
					'name'  => __( 'Amount', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'dcmp_price',
				),
				array(
					'name'  => esc_attr__( 'Quantity', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'quantity',
				),
			),
		),
		array(
			'name'           => __( 'Threshold', 'dynamic-cart-messages-woocommerce' ),
			'desc'           => __( 'Enter the threshold value for price or quantity', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_threshold_value',
			'required_label' => true,
			'tooltip'        => __( 'The quantity / price amount you want the customer to add in their cart to avail the discount (or free shipping) you are offering them.Initial Message before Message Criteria is satisfied .', 'dynamic-cart-messages-woocommerce' ),
			'type'           => 'threshold_text',
		),
		array(
			'name'    => __( 'Start Date', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_start_date',
			'tooltip' => __( 'Select a date to determine when you want the messages to start appearing on the cart page and other pages after a specific date.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'date',
		),
		array(
			'name'    => __( 'Expiration Date', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_expiry_date',
			'tooltip' => __( 'Choose a date if you want the messages to stop showing up on cart page (and other pages) after a particular date.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'date',
		),
		array(
			'name'    => __( 'Enable Countdown Timer', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_custom_countdown',
			'tooltip' => __( 'Enabling this option allows you to include a custom countdown.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'checkbox',
		),
		array(
			'name'    => __( 'Countdown Timer Format', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_countdown_timer_style',
			'type'    => 'dropdown',
			'tooltip' => __( 'Choose a Countdown Timer Design.', 'dynamic-cart-messages-woocommerce' ),
			// 'tooltip2' => __( 'This is a preview of the Countdown Timer format. You can change the colors of the preview by picking colors from "Message Background Color" and "Message Text Color". This option becomes available when you enable "Custom Style for Cart Message".', 'dynamic-cart-messages-woocommerce' ),
			'options' => array(
				array(
					'name'  => __( 'Default', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'default',
				),
				array(
					'name'  => __( 'Small Box Ghost', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'default_ghost',
				),
				array(
					'name'  => __( 'Large Box Fill', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'large_box_fill',
				),
				array(
					'name'  => __( 'Large Box Ghost', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'large_box_ghost',
				),
			),
		),
		array(
			'name'           => esc_attr__( 'Countdown Type', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_countdown_type',
			'required_label' => true,
			'type'           => 'radio',
			'tooltip'        => __( "Choose a Countdown Timer type. For a scheduled countdown timer, you'll need to specify an expiration date.", 'dynamic-cart-messages-woocommerce' ),
			'options'        => array(
				array(
					'name'  => __( 'Schedule Timer', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'static_counter',
				),
				array(
					'name'  => __( 'Evergreen Countdown', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fake_counter',
				),
			),
		),
		array(
			'name'    => __( 'Countdown Time', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_countdown_time',
			'tooltip' => __( 'Enter the desired time for the fake countdown timer in days, hours, minutes, and seconds.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'countdown_time',
		),
		array(
			'name'    => __( 'Countdown Expired Text', 'dynamic-cart-messages-woocommerce' ),
			'tooltip' => __( 'Enter the message to be displayed when the countdown reaches zero.', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_countdown_expired',
			'type'    => 'text',
		),
		array(
			'name'           => __( 'Initial Message on Criteria Match', 'dynamic-cart-messages-woocommerce' ),
			'desc'           => __(
				'Use placeholder {cs} to show default currency symbol and {price} to show price.<br>For E.g Buy for {cs}{price} more to avail Free delivery in your cart! will show up as "Buy for $10 more to avail Free delivery in your cart!"',
				'dynamic-cart-messages-woocommerce'
			),
			'required_label' => true,
			'id'             => 'dcmp_after_initial_message',
			'type'           => 'text',
			'tooltip'        => __( 'The initial messsage that  your potential buyers will see initially when they add the product / product category you selected  above.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Message icon', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_icon',
			'type'    => 'radio',
			'options' => array(
				array(
					'name'  => __( 'Font Awesome Icons', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'custom_icon_color',
				),
				array(
					'name'  => __( 'Font Awesome Icons+', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'custom_icon_color_pro',
				),
				array(
					'name'  => __( 'Custom Icon', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'custom_icon',
				),
				array(
					'name'  => __( 'No Icon', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'no_icon',
				),
			),
		),
		array(
			'name'    => __( 'Font Awesome Icons', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_message_icon',
			'type'    => 'select_message_icon',
			'tooltip' => __( 'You can opt to show a icon before your message to make it more appealing like ', 'dynamic-cart-messages-woocommerce' ) . ' <a href="' . esc_url( plugins_url( 'assets/images/screenshot-4.png', dirname( __FILE__ ) ) ) . '" target="_blank">' . __( 'this', 'dynamic-cart-messages-woocommerce' ) . '</a>',
			'options' => array(
				array(
					'name'  => __( 'Select icon', 'dynamic-cart-messages-woocommerce' ),
					'value' => '',
				),
				array(
					'name'  => __( 'Shopping Cart', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-shopping-cart',
				),
				array(
					'name'  => __( 'Gift', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-gift',
				),
				array(
					'name'  => __( 'Percentage', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-percent',
				),
				array(
					'name'  => __( 'Cash', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-money',
				),
				array(
					'name'  => __( 'Truck', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-truck',
				),
				array(
					'name'  => __( 'Coupon', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-ticket',
				),
				array(
					'name'  => __( 'Discount', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-tag',
				),
				array(
					'name'  => __( 'Check Square', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-check-square-o',
				),
				array(
					'name'  => __( 'Calender', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'fa-calendar',
				),
			),
		),
		array(
			'name'    => __( 'Threshold Message', 'dynamic-cart-messages-woocommerce' ),
			'desc'    => __( 'Enter the message to display when the threshold condition are satisfied', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_threshold_message',
			'tooltip' => __( 'The message to be displayed when the Message Criteria is satisfied. For example - "Congratulations. 20% discount applied to your cart!"', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'text',
		),
		array(
			'name'    => __( 'Show Call to Action', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_show_message_button',
			'tooltip' => __( 'Check this if you want to show a button in the message where customer can click and see other offers, terms and confitions or Shop page to buy more products.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'checkbox',
		),
		array(
			'name'           => __( 'Button Label', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_message_button_label',
			'type'           => 'text',
			'tooltip'        => __( 'The text  that the customer will see on the button. For Example "Buy More", "Terms and Conditions" or "Shop Now"', 'dynamic-cart-messages-woocommerce' ),
			'desc'           => __( 'Check this box to show custom buttom after message', 'dynamic-cart-messages-woocommerce' ),
			'required_label' => true,
		),
		array(
			'name'           => __( 'Button URL', 'dynamic-cart-messages-woocommerce' ),
			'tooltip'        => __( 'The URL the customer will be redirected to.', 'dynamic-cart-messages-woocommerce' ),
			'id'             => 'dcmp_message_button_url',
			'desc'           => __( 'URL for buttom. e.g: https://example.com', 'dynamic-cart-messages-woocommerce' ),
			'type'           => 'text',
			'required_label' => true,
		),
		array(
			'name'    => __( 'Open in new tab', 'dynamic-cart-messages-woocommerce' ),
			'tooltip' => __( 'Opens the Button URL in a new window or tab', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_message_open_new_tab',
			'type'    => 'checkbox',
		),
		array(
			'name'    => __( 'Show in Checkout Page', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_show_in_checkout',
			'tooltip' => __( 'Check this if you want your customers to see this message in checkout page as well.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'checkbox',
		),
		array(
			'name'    => __( 'Show in Product Page ', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_show_in_product_page',
			'tooltip' => __( 'Check this if you want your customers to see this message on product page as well.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'checkbox',
		),
		array(
			'name'           => __( 'Initial Message for Product page', 'dynamic-cart-messages-woocommerce' ),
			'desc'           => __( 'The message that your customers will see on the product page even before they have added that product to cart', 'dynamic-cart-messages-woocommerce' ),
			'required_label' => true,
			'id'             => 'dcmp_product_page_message',
			'type'           => 'text',
		),
		array(
			'name'    => __( 'Custom Style for Cart Message', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_custom_color',
			'desc'    => __( 'Set Custom Colors to the Cart Message ', 'dynamic-cart-messages-woocommerce' ),
			'tooltip' => __( 'Select a color if you prefer a different color for your Cart Message Box. If any field in the Custom Style is left empty, it will default to the Global Settings.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'checkbox',
		),
		array(
			'name'    => __( 'Gradient Background Color for Cart Message', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_grad_msg_bg_color',
			'tooltip' => __( 'Select this option to add a gardient color option for Cart Message Background.', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'checkbox',
		),
		array(
			'name'    => __( 'Gradient Effect', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_grad_effect',
			'type'    => 'dropdown',
			'tooltip' => __( 'Choose a Gradient Effect for the Cart Message Background Color.', 'dynamic-cart-messages-woocommerce' ),
			'options' => array(
				array(
					'name'  => __( 'Top to Bottom', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'default',
				),
				array(
					'name'  => __( 'Left to Right', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'left_to_right',
				),
				array(
					'name'  => __( 'Diagonal', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'top_left_to_bottom_right',
				),
			),
		),
		array(
			'name'    => __( 'Message Background Color', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-message-background-colors',
			'type'    => 'text',
			'tooltip' => __( 'Choose the background color for the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Message Text Color', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-message-text-colors',
			'type'    => 'text',
			'tooltip' => __( 'Choose the text color for the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Message Radius', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_custom_message_radius',
			'desc'    => __( 'Measurement unit used is "px"', 'dynamic-cart-messages-woocommerce' ),
			'type'    => 'radius',
			'tooltip' => __( 'Set the radius for rounded edges on the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Custom Icon Color', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-icon-colors',
			'type'    => 'text',
			'tooltip' => __( 'Choose the color for the icon on the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Message Box Border Style', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-message-box-border-style',
			'type'    => 'dropdown',
			'tooltip' => __( 'Choose a border style for the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
			'options' => array(
				array(
					'name'  => __( 'None', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'none',
				),
				array(
					'name'  => __( 'Solid', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'solid',
				),
				array(
					'name'  => __( 'Dotted', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'dotted',
				),
				array(
					'name'  => __( 'Dashed', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'dashed',
				),
				array(
					'name'  => __( 'Double', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'double',
				),
				array(
					'name'  => __( 'Groove', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'groove',
				),
				array(
					'name'  => __( 'Ridge', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'ridge',
				),
				array(
					'name'  => __( 'Inset', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'inset',
				),
				array(
					'name'  => __( 'Outset', 'dynamic-cart-messages-woocommerce' ),
					'value' => 'outset',
				),
			),
		),
		array(
			'name'    => __( 'Message Box Border Color', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-message-box-border-colors',
			'type'    => 'text',
			'tooltip' => __( 'Choose the border color for the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Button Background Color', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-button-background-colors',
			'type'    => 'text',
			'tooltip' => __( 'Choose the background color for the button on the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Button Text Color', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-button-text-colors',
			'type'    => 'text',
			'tooltip' => __( 'Choose the text color for the button on the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Button Radius', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp_custom_button_radius',
			'type'    => 'radius',
			'desc'    => __( 'Measurement unit used is "px"', 'dynamic-cart-messages-woocommerce' ),
			'tooltip' => __( 'Set the radius for the rounded edges of the button on the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'    => __( 'Button Background Color on Hover', 'dynamic-cart-messages-woocommerce' ),
			'id'      => 'dcmp-custom-button-text-colors-on-hover',
			'type'    => 'text',
			'tooltip' => __( 'Choose the hover effect color for the button on the Cart Message.', 'dynamic-cart-messages-woocommerce' ),
		),
		array(
			'name'  => __( 'Cart Message Preview', 'dynamic-cart-messages-woocommerce' ),
			'id'    => 'dcmp-cart-message-preview',
			'type'  => 'button',
			'value' => 'Preview',
		),
	);
}



/**
 * Add meta box to the Custom Post Type created and save the value.
 */
function dcmwp_add_metabox() {

	add_meta_box( 'dcmw-meta-box', __( 'Cart Message', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_meta_box_callback', 'dcmp_msg', 'normal', 'high' );
}
add_action( 'admin_menu', 'dcmwp_add_metabox' );



/**
 * Callback function to show fields in meta box
 */
function dcmwp_meta_box_callback() {
	global $post;

	echo '<input type="hidden" name="dynamic_cart_message_woocommerce" value="', esc_attr( wp_create_nonce( basename( __FILE__ ) ) ), '" />';

	echo '<table class="form-table dcmw-table">';
	$fields = dcmwp_get_cpt_setting_fields();

	foreach ( $fields as $field ) {
		// get current post meta data.
		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr id="main_',esc_attr( $field['id'] ),'">',
		'<th><label for="', esc_attr( $field['id'] ), '">', esc_attr( $field['name'] ), '<span class="dcmp-required"> ' . ( isset( $field['required_label'] ) ? ' *' : '' ) . '</span></label></th>',
		'<td>';
		switch ( $field['type'] ) {
			case 'text':
				if ( 'dcmp-custom-message-background-colors' === $field['id'] ) {
					$bg_color_grad = get_post_meta( $post->ID, 'dcmp-custom-message-background-colors-gradient', true );
					echo '<input type="text" name="' . esc_attr( $field['id'] ) . '" class="dcmp-input-field" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" />';
					echo '<input type="text" name="' . esc_attr( $field['id'] ) . '-gradient" class="dcmp-input-field gradient_field" id="' . esc_attr( $field['id'] ) . '-gradient" value="' . esc_attr( $bg_color_grad ) . '" />';
				} else {
					echo '<input type="text" name="' . esc_attr( $field['id'] ) . '" class="dcmp-input-field" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" />';
				}
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip">
					<div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				$text = array( 'dcmp_countdown_expired', 'dcmp-custom-message-background-colors', 'dcmp-custom-message-text-colors', 'dcmp-custom-icon-colors', 'dcmp-custom-message-box-border-colors', 'dcmp-custom-button-background-colors', 'dcmp-custom-button-text-colors', 'dcmp-custom-button-text-colors-on-hover' );
				if ( in_array( $field['id'], $text, true ) ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				if ( isset( $field['desc'] ) ) {
					echo '<br><p id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</p>';
				}

				break;
			case 'threshold_text':
				echo '<input type="number" name="' . esc_attr( $field['id'] ) . '" class="dcmp-input-field" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" pattern="[0-9]{1,5}"/>';
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip">
					<div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				echo '<br><p id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</p>';
				break;
			case 'date':
				echo '<input type="date" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $meta ) . '" />';
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip">
					<div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				if ( isset( $field['desc'] ) ) {
					echo '<br><div id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</div>';
				}
				if ( 'dcmp_start_date' === $field['id'] ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				break;

			case 'category_select':
				wc_product_dropdown_categories(
					array(
						'selected' => null !== get_post_meta( $post->ID, $field['id'], true ) ? get_post_meta( $post->ID, $field['id'], true ) : '',
						'name'     => 'dcmp_selected_category',
						'class'    => 'dcmp_selected_category',
						'id'       => 'dcmp_selected_category',
					)
				);
				break;

			case 'select':
				echo '<select name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '">';
				echo '<option ' . ( intval( $meta ) === -2 ? 'selected="selected"' : '' ) . ' value="-2" >All Products</option>';
				foreach ( $field['options'] as $my_products ) {
					$product   = wc_get_product( $my_products );
					$option_id = $product->get_id();

					echo '<option ' . ( ( intval( $meta ) === $option_id ) ? 'selected="selected"' : '' ) . ' value="' . esc_attr( $option_id ) . '" >' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
					if ( $product->is_type( 'variable' ) ) {

						foreach ( $product->get_children() as $variation_id ) {
							$variation = wc_get_product( $variation_id );
							$option_id = $variation_id;
							echo '<option ' . ( intval( $meta ) === $option_id ? 'selected="selected"' : '' ) . ' value="' . esc_attr( $option_id ) . '" >' . esc_html( wp_strip_all_tags( $variation->get_formatted_name() ) ) . '</option>';
						}
					}
				}
				echo '</select>';

				break;
			case 'select_message_icon':
				echo '<select name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $option ) {
					echo '<option ' . ( $meta === $option['value'] ? 'selected="selected"' : '' ) . ' value="' . esc_attr( $option['value'] ) . '" >' . esc_attr( $option['name'] ) . '</option>';
				}
				echo '</select>';
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip">
					<div class="tooltipdata">' . wp_kses_post( $field['tooltip'] ) . '</div></span>';
				}

				if ( isset( $meta ) ) {
					echo '<span class="message-icon"><i class="fa ' . esc_attr( $meta ) . ' fa-2x " aria-hidden="true"></i></span>';
				}

				if ( 'dcmp_fa_icon' === $field['id'] ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				break;

			case 'radio':
				foreach ( $field['options'] as $option ) {
					echo '<input type="radio" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $option['value'] ) . '" id="' . esc_attr( $option['value'] ) . '" ' . ( ( $meta == $option['value'] ) ? ' checked="checked"' : '' ) . ' />' . esc_attr( ( $option['name'] ) );
					$radio = array( 'static_counter', 'fake_counter', 'custom_icon_color_pro', 'custom_icon', 'no_icon' );
					if ( in_array( $option['value'], $radio, true ) ) {
						echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
					}
				}
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip">
					<div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				// if ( 'dcmp_countdown_type' === $field['id'] ) {
				// echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				// }
				break;

			case 'checkbox':
				if ( isset( $field['options'] ) ) {
					foreach ( $field['options'] as $option ) {
						echo '<input type="checkbox" name="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $option['value'] ) . '" id="' . esc_attr( $option['value'] ) . '" ' . ( ( $meta === $option['value'] ) ? ' checked="checked"' : '' ) . ' />' . esc_attr( ( $option['name'] ) ) . '<br><br>';
					}
				} else {
					echo '<input type="checkbox" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '"' . ( esc_attr( $meta ) ? ' checked="checked"' : '' ) . ' />';
				}
				if ( isset( $field['desc'] ) ) {
					echo '<span class="setting-desc"><div id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</div></span>';
				}
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip">
					<div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				$checkbox = array( 'dcmp_custom_countdown', 'dcmp_custom_color', 'dcmp_grad_msg_bg_color' );
				if ( in_array( $field['id'], $checkbox, true ) ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				break;

			case 'button':
				echo '<input type="button" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" />';
				if ( 'dcmp-cart-message-preview' === $field['id'] ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				break;

			case 'dropdown':
				echo '<select name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $option ) {
					echo '<option ' . ( $meta === $option['value'] ? 'selected="selected"' : '' ) . ' value="' . esc_attr( $option['value'] ) . '" >' . esc_attr( $option['name'] ) . '</option>';
				}
				echo '</select>';
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip"><div class="tooltipdata">' . wp_kses_post( $field['tooltip'] ) . '</div></span>';
				}
				$dropdown = array( 'dcmp_countdown_timer_style', 'dcmp_grad_effect', 'dcmp-custom-message-box-border-style' );
				if ( in_array( $field['id'], $dropdown, true ) ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				break;

			case 'countdown_time':
				$duration = intval( get_post_meta( $post->ID, 'duration', true ) );
				$days     = floor( $duration / ( 24 * 60 * 60 ) );
				$hours    = floor( ( $duration % ( 24 * 60 * 60 ) ) / ( 60 * 60 ) );
				$minutes  = floor( ( $duration % ( 60 * 60 ) ) / 60 );
				$seconds  = $duration % 60;
				?>
						<label for="days">Days : </label>
						<input type="number" id="days" class="time_input" name="days" min="0" value="<?php echo esc_attr( $days ); ?>">
	
						<label for="hours"> Hours : </label>
						<input type="number" id="hours" class="time_input" name="hours" min="0" max="23" value="<?php echo esc_attr( $hours ); ?>">
	
						<label for="minutes"> Minutes : </label>
						<input type="number" id="minutes" class="time_input" name="minutes" min="0" max="59" value="<?php echo esc_attr( $minutes ); ?>">
	
						<label for="seconds"> Seconds : </label>
						<input type="number" id="seconds" class="time_input" name="seconds" min="0" max="59" value="<?php echo esc_attr( $seconds ); ?>">
					<?php

					if ( isset( $field['desc'] ) ) {
						echo '<span class="setting-desc"><div id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</div></span>';
					}
					if ( isset( $field['tooltip'] ) ) {
						echo '<span class="setting-help-tip"><div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
					}
					if ( 'dcmp_countdown_time' === $field['id'] ) {
						echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
					}
				break;

			case 'radius':
				echo '<input type="number" id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" placeholder="10" value="' . esc_attr( $meta ) . '">';
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip"><div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				$radius = array( 'dcmp_custom_button_radius', 'dcmp_custom_message_radius' );
				if ( in_array( $field['id'], $radius, true ) ) {
					echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				}
				if ( isset( $field['desc'] ) ) {
					echo '<br><p id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</p>';
				}

				break;

			case 'toggle':
				echo '<label class="switch"><input type="checkbox" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '"' . ( esc_attr( $meta ) ? ' checked="checked"' : '' ) . '><span class="slider round"></span></label>';
				if ( isset( $field['tooltip'] ) ) {
					echo '<span class="setting-help-tip"><div class="tooltipdata">' . esc_attr( $field['tooltip'] ) . '</div></span>';
				}
				if ( isset( $field['desc'] ) ) {
					echo '<br><p id="' . esc_attr( $field['id'] ) . '_field_desc">' . esc_attr( $field['desc'] ) . '</p>';
				}
				echo '<span class="dcm-pro-alert pointer"><b> [Pro] </b></span>';
				break;
		}
		echo '</td></tr>';
	}

	echo '</table>';
}


/**
 * Save data from meta box
 *
 * @param int $post_id Custom Post type id.
 */
function dcmwp_save_metabox_data( $post_id ) {

	// verify nonce.
	if ( ! wp_verify_nonce( isset( $_POST['dynamic_cart_message_woocommerce'] ) ? sanitize_text_field( wp_unslash( $_POST['dynamic_cart_message_woocommerce'] ) ) : '', basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions.
	if ( 'page' === ( isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '' ) ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	$fields = dcmwp_get_cpt_setting_fields();
	foreach ( $fields as $field ) {
		$old = get_post_meta( $post_id, $field['id'], true );

		// $new = sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) );
		$new = ( isset( $_POST[ $field['id'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) ) : '' );

		if ( isset( $new ) && ( $new !== $old ) ) {
			update_post_meta( $post_id, $field['id'], $new );
		} elseif ( ( '' === $new ) && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
add_action( 'save_post', 'dcmwp_save_metabox_data' );



/**
 * Add a Settings API section Cart Message Custom Post Type
 *
 * @package WordPress
 */

/**
 * Create a Settings option in the Cart Message Custom Post Type
 */
function dcmwp_add_submenu_page() {
	add_submenu_page(
		'edit.php?post_type=dcmp_msg',
		__( 'Free Shipping Settings', 'dynamic-cart-messages-woocommerce' ),
		__( 'Settings', 'dynamic-cart-messages-woocommerce' ),
		'manage_options',
		'dynamic-cart-message-settings',
		'dcmwp_settings_page'
	);
}
add_action( 'admin_menu', 'dcmwp_add_submenu_page' );

/**
 * Add a Settings API section Cart Message Custom Post Type
 */
function dcmwp_settings_page() {
	?>
<div class="dcmwp-wrap">
	<?php settings_errors(); ?>
	<h2><?php echo esc_attr__( 'Dynamic Cart Messages Settings', 'dynamic-cart-messages-woocommerce' ); ?></h2>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'settings-sections' );
			do_settings_sections( 'dcmw-section-settings' );
			submit_button();
		?>
	</form>
</div>
	<?php
}

/**
 * Select background color of the message section
 */
function dcmwp_display_background_color() {
	$bg_color = get_option( 'dcmp-background-colors' );
	?>

<input type="text"  id="dcmp-background-colors" name="dcmp-background-colors" value="<?php echo ( esc_attr( $bg_color ) ? esc_attr( $bg_color ) : '#fff4b8' ); ?>" class="dcmp-color-field" data-default-color="#fff4b8" />
	<?php
}
/**
 * Select Button background color of the message section
 */
function dcmwp_button_background_color() {
	$button_bg_color = get_option( 'dcmp-button-background-colors' );
	?>
	<input type="text"  id="dcmp-button-background-colors" name="dcmp-button-background-colors" value="<?php echo ( esc_attr( $button_bg_color ) ? esc_attr( $button_bg_color ) : '#e6ae15' ); ?>" class="dcmp-color-field" data-default-color="#e6ae15" />
	<?php
}

/**
 * Select Text color of the message
 */
function dcmwp_text_color() {
	$msg_color = get_option( 'dcmp-text-colors' );
	?>
	<input type="text"  id="dcmp-text-colors" name="dcmp-text-colors" value="<?php echo ( esc_attr( $msg_color ) ? esc_attr( $msg_color ) : '#e6ae15' ); ?>" class="dcmp-color-field" data-default-color="#e6ae15" />
	<?php
}

/**
 * Select Button Text color of the message
 */
function dcmwp_button_text_color() {
	$msg_color = get_option( 'dcmp-button-text-colors' );
	?>
	<input type="text"  id="dcmp-button-text-colors" name="dcmp-button-text-colors" value="<?php echo ( esc_attr( $msg_color ) ? esc_attr( $msg_color ) : '#e6ae15' ); ?>" class="dcmp-color-field" data-default-color="#fff4b8" />
	<?php
}

/**
 * To input field for Border radius.
 */
function dcmwp_border_radius() {
	?>
<input type="text" id="dcmp-border-radius" name="dcmp-border-radius" placeholder="10px" value="<?php echo esc_attr( get_option( 'dcmp-border-radius' ) ); ?>">
<p> 
	<?php
	/* translators: 1:Raduis Unit in px 2:Raduis Unit in em  */
	echo sprintf( esc_html__( 'Values must be in "px" or "em" e.g %1$s or %2$s', 'dynamic-cart-messages-woocommerce' ), '<strong>10px</strong>', '<strong>1em</strong>' );
	?>
	</p>
	<?php
}
/**
 * To input field for Button Border radius.
 */
function dcmwp_button_border_radius() {

	?>
<input type="text" id="dcmp-button-border-radius" name="dcmp-button-border-radius" placeholder="10px" value="<?php echo esc_attr( get_option( 'dcmp-button-border-radius' ) ); ?>">
<p> 
	<?php
	/* translators: 1:Radius Unit in px 2:Radius Unit in em  */
	echo sprintf( esc_html__( 'Values must be in "px" or "em" e.g %1$s or %2$s', 'dynamic-cart-messages-woocommerce' ), '<strong>10px</strong>', '<strong>1em</strong>' );
	?>
	</p>
	<?php
}

/**
 * Enable/Disable the currency symbol
 */
function dcmwp_display_currency_symbol() {
	?>
<input type="checkbox" id="dcmp-enable-currency-symbol" name="dcmp-enable-currency-symbol" value="1"
	<?php checked( 1, get_option( 'dcmp-enable-currency-symbol' ), true ); ?>>
	<?php
	echo '<span class="setting-help-tip"><div class="tooltipdata">' . esc_html__( 'Check this if you want to show the currency symbol provided by WooCommerce.', 'dynamic-cart-messages-woocommerce' ) . '</div></span>';
}

/**
 * Add the fields to the Settings API section and save the values
 */
function dcmwp_display_setting_fields() {
	add_settings_section( 'message-settings-sections', 'Message Settings', null, 'dcmw-section-settings' );
	add_settings_section( 'button-settings-sections', 'Button Settings', null, 'dcmw-section-settings' );

	add_settings_field( 'dcmp-background-colors', esc_attr__( 'Background Color', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_display_background_color', 'dcmw-section-settings', 'message-settings-sections' );
	add_settings_field( 'dcmp-text-colors', esc_attr__( 'Text Color', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_text_color', 'dcmw-section-settings', 'message-settings-sections' );
	add_settings_field( 'dcmp-border-radius', esc_attr__( 'Radius', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_border_radius', 'dcmw-section-settings', 'message-settings-sections' );
	add_settings_field( 'dcmp-button-background-colors', esc_attr__( 'Background Color', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_button_background_color', 'dcmw-section-settings', 'button-settings-sections' );
	add_settings_field( 'dcmp-button-text-colors', esc_attr__( 'Text Color', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_button_text_color', 'dcmw-section-settings', 'button-settings-sections' );
	add_settings_field( 'dcmp-button-border-radius', esc_attr__( 'Border Radius', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_button_border_radius', 'dcmw-section-settings', 'button-settings-sections' );
	add_settings_field( 'dcmp-enable-currency-symbol', esc_attr__( 'Enable Currency Symbol', 'dynamic-cart-messages-woocommerce' ), 'dcmwp_display_currency_symbol', 'dcmw-section-settings', 'button-settings-sections' );

	register_setting( 'settings-sections', 'dcmp-background-colors' );
	register_setting( 'settings-sections', 'dcmp-text-colors' );
	register_setting( 'settings-sections', 'dcmp-border-radius' );
	register_setting( 'settings-sections', 'dcmp-button-background-colors' );
	register_setting( 'settings-sections', 'dcmp-button-text-colors' );
	register_setting( 'settings-sections', 'dcmp-button-border-radius' );
	register_setting( 'settings-sections', 'dcmp-enable-currency-symbol' );
}

add_action( 'admin_init', 'dcmwp_display_setting_fields' );

/**
 * Show 'Settings' action links on the plugin screen.
 *
 * @param mixed $links Plugin Action links.
 *
 * @return array
 */
function dcmwp_dynccart_plugin_setting_link( $links ) {
	$action_links = array(
		'settings' => '<a href="' . admin_url( 'edit.php?post_type=dcmp_msg' ) . '" aria-label="' . esc_attr__( 'Settings', 'discontinued-products-stock-status' ) . '">' . esc_html__( 'Settings', 'dynamic-cart-messages-woocommerce' ) . '</a>',
	);
	return array_merge( $action_links, $links );
}
add_filter( 'plugin_action_links_' . DCMPW_DYNMAIC_CART_PLUGIN_BASENAME, 'dcmwp_dynccart_plugin_setting_link', 10, 1 );
