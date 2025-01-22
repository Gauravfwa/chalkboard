<?php

add_action( 'cfw_checkout_before_order_review', 'copify_desktop_header', 10 );
add_action( 'cfw_thank_you_before_order_review', 'copify_desktop_header', 10 );
add_action( 'cfw_order_pay_before_order_review', 'copify_desktop_header', 10 );

function copify_desktop_header() {
	if ( ! has_action( 'cfw_custom_header' ) ) : ?>
	<div id="cfw-logo-container">
		<div class="cfw-logo">
			<a title="<?php echo get_bloginfo( 'name' ); ?>" href="<?php echo get_home_url(); ?>" class="logo"></a>
		</div>
	</div>
		<?php
	endif;
}

// 5 makes sure this is above notices
add_action( 'cfw_checkout_main_container_start', 'copify_mobile_logo', 5 );
add_action( 'cfw_thank_you_main_container_start', 'copify_mobile_logo', 5 );
add_action( 'cfw_order_pay_main_container_start', 'copify_mobile_logo', 5 );

function copify_mobile_logo() {
	if ( ! has_action( 'cfw_custom_header' ) ) :
		?>
		<div id="cfw-logo-container-mobile">
			<div class="cfw-logo">
				<?php
				/**
				 * Filters header logo / title link URL
				 *
				 * @since 3.0.0
				 *
				 * @param string $url The link URL
				 */
				$url = apply_filters( 'cfw_header_home_url', get_home_url() );
				?>
				<a title="<?php echo get_bloginfo( 'name' ); ?>" href="<?php echo $url; ?>" class="logo"></a>
			</div>
		</div>
		<?php
	endif;
}

add_action( 'cfw_checkout_after_order_review', 'copify_footer' );
add_action( 'cfw_thank_you_after_order_review', 'copify_footer' );
add_action( 'cfw_order_pay_after_order_review', 'copify_footer' );
function copify_footer() {
	if ( ! has_action( 'cfw_custom_footer' ) ) :
		?>
		<footer id="cfw-footer" class="row">
			<div class="col-lg-12">
				<div class="cfw-footer-inner entry-footer">
					<?php
					/**
					 * Fires at the top of footer
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_before_footer' );
					?>
					<?php if ( ! empty( $footer_text = cfw_get_main()->get_settings_manager()->get_setting( 'footer_text' ) ) ) : ?>
						<?php echo do_shortcode( $footer_text ); ?>
					<?php else : ?>
						Copyright &copy; <?php echo date( 'Y' ); ?>, <?php echo get_bloginfo( 'name' ); ?>. All rights reserved.
					<?php endif; ?>
					<?php
					/**
					 * Fires at the bottom of footer
					 *
					 * @since 3.0.0
					 */
					do_action( 'cfw_after_footer' );
					?>
				</div>
			</div>
		</footer>
		<?php
	endif;
}

// Move notices inside container
remove_action( 'cfw_order_pay_main_container_start', 'cfw_wc_print_notices_with_wrap', 10 );
add_action( 'cfw_order_pay_before_order_review', 'cfw_wc_print_notices', 0 );

remove_action( 'cfw_checkout_main_container_start', 'cfw_wc_print_notices_with_wrap', 10 );
add_action( 'cfw_checkout_before_order_review', 'cfw_wc_print_notices', 0 );
