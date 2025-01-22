<?php
/**
 * Settings for cart restrictions
 *
 * Displays the settings for fee for  cart amount, Products and Product Categories
 *
 * @package  addify-abandoned-cart-recovery/includes/settings
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_settings_section(
	'afacr-coupon-sec',         // ID used to identify this section and with which to register options.
	esc_html__( 'Coupons Settings', 'addify_acr' ),   // Title to be displayed on the administration page.
	'', // Callback used to render the description of the section.
	'afacr_coupon_section'                           // Page on which to add this section of options.
);

add_settings_field(
	'afacr_coupons_prefix',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Coupons Prefix', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_coupons_prefix_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_coupon_section',                          // The page on which this option will be displayed.
	'afacr-coupon-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Three Letters prefix for coupons.', 'addify_acr' ) )
);

register_setting(
	'afacr_coupon_fields',
	'afacr_coupons_prefix'
);

add_settings_field(
	'afacr_delete_once_used',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Delete the coupons once used', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_delete_once_used_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_coupon_section',                          // The page on which this option will be displayed.
	'afacr-coupon-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Delete the coupons once used.', 'addify_acr' ) )
);

register_setting(
	'afacr_coupon_fields',
	'afacr_delete_once_used'
);

add_settings_field(
	'afacr_delete_expired',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Delete Coupons when Expired', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_delete_expired_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_coupon_section',                          // The page on which this option will be displayed.
	'afacr-coupon-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Delete the coupons when expired.', 'addify_acr' ) )
);

register_setting(
	'afacr_coupon_fields',
	'afacr_delete_expired'
);


/**
 * Coupons prefix.
 *
 * @param array $args arguments.
 */
function afacr_coupons_prefix_callback( $args = array() ) {
	?>
	<input type="text" pattern="[A-Za-z]{3}$" name="afacr_coupons_prefix" id="" value="<?php echo esc_attr( get_option( 'afacr_coupons_prefix' ) ); ?>" />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Delete once used.
 *
 * @param array $args arguments.
 */
function afacr_delete_once_used_callback( $args = array() ) {
	?>
	<input type="checkbox" name="afacr_delete_once_used" id="" value="yes" <?php echo checked( 'yes', esc_attr( get_option( 'afacr_delete_once_used' ) ) ); ?> />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Delete expired.
 *
 * @param array $args arguments.
 */
function afacr_delete_expired_callback( $args = array() ) {
	?>
	<input type="checkbox" name="afacr_delete_expired" id="" value="yes" <?php echo checked( 'yes', esc_attr( get_option( 'afacr_delete_expired' ) ) ); ?> />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}
