<?php
/**
 * Settings for cart restrictions.
 *
 * Displays the settings for fee for  cart amount, Products and Product Categories.
 *
 * @package  addify-abandoned-cart-recovery/includes/Settings
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_settings_section(
	'afacr-general-sec',         // ID used to identify this section and with which to register options.
	esc_html__( 'General Settings', 'addify_acr' ),   // Title to be displayed on the administration page.
	'', // Callback used to render the description of the section.
	'afacr_general_setting_section'                           // Page on which to add this section of options.
);

add_settings_field(
	'afacr_enable',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Enable Abandoned Cart', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_enable_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_general_setting_section',                          // The page on which this option will be displayed.
	'afacr-general-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Enable/disable Abandoned Cart Recovery. (This will enable/disable entire abandoned cart plugin functionality)', 'addify_acr' ) )
);

register_setting(
	'afacr_general_setting_fields',
	'afacr_enable'
);

add_settings_field(
	'afacr_consider_abandoned_time',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Consider as Abandoned Cart', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_consider_abandoned_time_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_general_setting_section',                          // The page on which this option will be displayed.
	'afacr-general-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Time in Minutes/Hours/Days to consider a cart as Abandoned. Default value is Zero and system will mark the cart as Abandoned after 1 hour.', 'addify_acr' ) )
);

register_setting(
	'afacr_general_setting_fields',
	'afacr_consider_abandoned_time'
);

add_settings_field(
	'afacr_delete_abandoned_cart_time',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Delete Abandoned Cart', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_delete_abandoned_cart_time_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_general_setting_section',                          // The page on which this option will be displayed.
	'afacr-general-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Time in Minutes/Days to Delete the Abandoned Cart. Zero for infinite.', 'addify_acr' ) )
);

register_setting(
	'afacr_general_setting_fields',
	'afacr_delete_abandoned_cart_time'
);

add_settings_field(
	'afacr_recover_cart_button_text',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Cart Recovery Button Text', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_recover_cart_button_text_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_general_setting_section',                          // The page on which this option will be displayed.
	'afacr-general-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Recover cart button text (Cart Recovery Email).', 'addify_acr' ) )
);

register_setting(
	'afacr_general_setting_fields',
	'afacr_recover_cart_button_text'
);

add_settings_field(
	'afacr_pay_order_button_text',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Pay Order Button Text', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_pay_order_button_text_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_general_setting_section',                          // The page on which this option will be displayed.
	'afacr-general-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Pay for Order button text (Pending Order Email).', 'addify_acr' ) )
);

register_setting(
	'afacr_general_setting_fields',
	'afacr_pay_order_button_text'
);

/**
 * Coupons prefix.
 *
 * @param array $args arguments.
 */
function afacr_enable_callback( $args = array() ) {
	?>
	<input type="checkbox" name="afacr_enable" id="afacr_enable" value="yes" <?php echo checked( 'yes', esc_attr( get_option( 'afacr_enable' ) ) ); ?> />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}


/**
 * Abandoned_time.
 *
 * @param array $args arguments.
 */
function afacr_consider_abandoned_time_callback( $args = array() ) {
	$values = (array) get_option( 'afacr_consider_abandoned_time' );
	$time   = isset( $values[0] ) && ! empty( $values[0] ) ? $values[0] : 1;
	$period = isset( $values[1] ) ? $values[1] : 'minutes';
	?>
	<input type="number" class="input-text" min="1" id="" placeholder="Time" name="afacr_consider_abandoned_time[0]" value="<?php echo intval( $time ); ?>">
	<select name="afacr_consider_abandoned_time[1]" >
		<option value="minutes" <?php echo 'minutes' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Minutes', 'addify_acr' ); ?> </option>
		<option value="hours" <?php echo 'hours' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Hours', 'addify_acr' ); ?> </option>
		<option value="days" <?php echo 'days' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Days', 'addify_acr' ); ?>  </option>
	</select>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Abandoned_time.
 *
 * @param array $args arguments.
 */
function afacr_delete_abandoned_cart_time_callback( $args = array() ) {
	$values = (array) get_option( 'afacr_delete_abandoned_cart_time' );
	$time   = isset( $values[0] ) ? $values[0] : 0;
	$period = isset( $values[1] ) ? $values[1] : 'minutes';
	?>
	<input type="number" class="input-text" min="0" id="" placeholder="Time" name="afacr_delete_abandoned_cart_time[0]" value="<?php echo intval( $time ); ?>">
	<select name="afacr_delete_abandoned_cart_time[1]" >
		<option value="minutes" <?php echo 'minutes' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Minutes', 'addify_acr' ); ?> </option>4
		<option value="hours" <?php echo 'hours' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Hours', 'addify_acr' ); ?> </option>
		<option value="days" <?php echo 'days' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Days', 'addify_acr' ); ?>  </option>
	</select>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}


/**
 * Recover Cart button Text.
 *
 * @param array $args arguments.
 */
function afacr_recover_cart_button_text_callback( $args = array() ) {
	$value = get_option( 'afacr_recover_cart_button_text' );
	$value = empty( $value ) ? 'Recover Cart' : $value;
	?>
	<input type="text" min="1" name="afacr_recover_cart_button_text" id="afacr_recover_cart_button_text" value="<?php echo esc_attr( $value ); ?>" />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Pay Order button text.
 *
 * @param array $args arguments.
 */
function afacr_pay_order_button_text_callback( $args = array() ) {
	$value = get_option( 'afacr_pay_order_button_text' );
	$value = empty( $value ) ? 'Pay for Order' : $value;
	?>
	<input type="text" min="1" name="afacr_pay_order_button_text" id="afacr_pay_order_button_text" value="<?php echo esc_attr( $value ); ?>" />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}
