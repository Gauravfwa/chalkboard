<?php
/**
 * Add heading to cart
 *
 * Priority 21 puts it in the cart summary content div
 */
add_action( 'cfw_checkout_cart_summary', 'cfw_futurist_cart_heading', 21 );
add_action( 'cfw_thank_you_cart_summary', 'cfw_futurist_cart_heading', 21 );
add_action( 'cfw_order_pay_cart_summary', 'cfw_futurist_cart_heading', 21 );

function cfw_futurist_cart_heading() {
	?>
	<h3>
		<?php _e( 'Your Cart', 'checkout-wc' ); ?>
	</h3>
	<?php
}

remove_action( 'cfw_checkout_before_order_review', 'cfw_breadcrumb_navigation', 10 );
add_action( 'cfw_checkout_main_container_start', 'futurist_breadcrumb_navigation', 10 );

function futurist_breadcrumb_navigation() {
	cfw_auto_wrap( 'cfw_breadcrumb_navigation' );
}

add_action( 'cfw_before_header', 'cfw_futurist_special_styles' );

function cfw_futurist_special_styles() {
	?>
	<style type="text/css">
		<?php
		$cfw                     = cfw_get_main();
		$active_template         = $cfw->get_templates_manager()->get_active_template()->get_slug();
		$header_background_color = $cfw->get_settings_manager()->get_setting( 'header_background_color', array( $active_template ) );

		if ( '#ffffff' === $header_background_color ) {
			$header_background_color = '#333';
		}
		?>
		/**
		Special Futurist breadcrumb styles
		 */
		body.futurist #cfw-breadcrumb:after {
			background: <?php echo $header_background_color; ?>;
		}

		body.futurist #cfw-breadcrumb li > a {
			color: <?php echo $header_background_color; ?>;
		}

		body.futurist #cfw-breadcrumb li:before {
			background: <?php echo $header_background_color; ?>;
			border: 2px solid <?php echo $header_background_color; ?>;
		}
	</style>
	<?php
}
