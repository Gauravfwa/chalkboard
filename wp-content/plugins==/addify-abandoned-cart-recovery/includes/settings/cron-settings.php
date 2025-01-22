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
	'afacr-cron-sec',         // ID used to identify this section and with which to register options.
	esc_html__( 'Cron Job Settings', 'addify_acr' ),   // Title to be displayed on the administration page.
	'', // Callback used to render the description of the section.
	'afacr_cron_section'                           // Page on which to add this section of options.
);

add_settings_field(
	'afacr_time_type',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Cron Time Type', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_time_type_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_cron_section',                          // The page on which this option will be displayed.
	'afacr-cron-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Time type to schedule the cron Job. Default type is Minutes.', 'addify_acr' ) )
);

register_setting(
	'afacr_cron_fields',
	'afacr_time_type'
);

add_settings_field(
	'afacr_cron_time',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Cron Job Time', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_cron_time_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_cron_section',                          // The page on which this option will be displayed.
	'afacr-cron-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Time to schedule the cron Job. Default time is 5 Minutes.', 'addify_acr' ) )
);

register_setting(
	'afacr_cron_fields',
	'afacr_cron_time'
);

/**
 * Select time.
 *
 * @param array $args arguments.
 */
function afacr_time_type_callback( $args = array() ) {
	$value = get_option( 'afacr_time_type' );
	$value = empty( $value ) ? 'minutes' : $value;
	?>
	<select name="afacr_time_type" >
		<option value="seconds" <?php echo 'seconds' === $value ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Seconds', 'addify_acr' ); ?> </option>
		<option value="minutes" <?php echo 'minutes' === $value ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Minutes', 'addify_acr' ); ?> </option>
		<option value="hours" <?php echo 'hours' === $value ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Hours', 'addify_acr' ); ?> </option>
		<option value="days" <?php echo 'days' === $value ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Days', 'addify_acr' ); ?>  </option>
	</select>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Select time.
 *
 * @param array $args arguments.
 */
function afacr_cron_time_callback( $args = array() ) {
	$value = get_option( 'afacr_cron_time' );
	$value = empty( $value ) ? 5 : $value;
	?>
	<input type="number" min="1" name="afacr_cron_time" id="afacr_cron_time" value="<?php echo intval( $value ); ?>" />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}
