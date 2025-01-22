<?php

add_action( 'cfw_checkout_before_main_container', 'cfw_glass_override_breadcrumb_colors' );

function cfw_glass_override_breadcrumb_colors() {
	$template_slug    = basename( __DIR__ );
	$settings_manager = cfw_get_main()->get_settings_manager();
	?>
	<style type="text/css">
		#cfw #cfw-breadcrumb li.tab.active a {
			color: <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?>;
			border-bottom-color: <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?>;
		}

		input[type="checkbox"]:checked {
			box-shadow: 0 0 0 10px <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?> inset !important;
		}

		input[type="radio"]:checked:after {
			background-color: <?php echo $settings_manager->get_setting( 'button_color', array( $template_slug ) ); ?> !important;
		}
	</style>
	<?php
}

add_action( 'cfw_cart_html_table_start', 'cfw_glass_cart_heading', 21 );

function cfw_glass_cart_heading() {
	?>
	<tr>
		<td colspan="4">
			<h3>
				<?php _e( 'Your Cart', 'checkout-wc' ); ?>
			</h3>
		</td>
	</tr>
	<?php
}

// Move notices inside container
remove_action( 'cfw_order_pay_main_container_start', 'cfw_wc_print_notices_with_wrap', 10 );
add_action( 'cfw_order_pay_before_order_review', 'cfw_wc_print_notices', 0 );

remove_action( 'cfw_checkout_main_container_start', 'cfw_wc_print_notices_with_wrap', 10 );
add_action( 'cfw_checkout_before_order_review', 'cfw_wc_print_notices', 0 );
