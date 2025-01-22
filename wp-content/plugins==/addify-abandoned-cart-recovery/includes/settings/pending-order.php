<?php
/**
 * Settings for cart restrictions.
 *
 * @package  addify-abandoned-cart-recovery/includes/Settings
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_settings_section(
	'afacr-pending-order-sec',         // ID used to identify this section and with which to register options.
	esc_html__( 'Pending Orders Settings', 'addify_acr' ),   // Title to be displayed on the administration page.
	'', // Callback used to render the description of the section.
	'afacr_pending_order_section'                           // Page on which to add this section of options.
);

add_settings_field(
	'afacr_enable_pending_order',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Enable Recover Pending orders', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_enable_pending_order_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_pending_order_section',                          // The page on which this option will be displayed.
	'afacr-pending-order-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Enable/Disable recovery of pending orders.', 'addify_acr' ) )
);

register_setting(
	'afacr_pending_order_fields',
	'afacr_enable_pending_order'
);

add_settings_field(
	'afacr_pending_order_status',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Pending Order Statuses', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_pending_order_status_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_pending_order_section',                          // The page on which this option will be displayed.
	'afacr-pending-order-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Select status of order for pending orders.', 'addify_acr' ) )
);

register_setting(
	'afacr_pending_order_fields',
	'afacr_pending_order_status'
);
/**
 * Enable_pending_order.
 *
 * @param array $args arguments.
 */
function afacr_enable_pending_order_callback( $args = array() ) {
	?>
	<input type="checkbox" name="afacr_enable_pending_order" id="" value="yes" <?php echo checked( 'yes', esc_attr( get_option( 'afacr_enable_pending_order' ) ) ); ?> />
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Delete_pending_order.
 *
 * @param array $args arguments.
 */
function afacr_delete_pending_order_callback( $args = array() ) {
	$values = (array) get_option( 'afacr_delete_pending_order' );
	$time   = isset( $values[0] ) ? $values[0] : 0;
	$period = isset( $values[1] ) ? $values[1] : 'minutes';
	?>
	<input type="number" class="input-text" min="0" id="" placeholder="Time" name="afacr_delete_pending_order[0]" value="<?php echo intval( $time ); ?>">
	<select name="afacr_delete_pending_order[1]" >
		<option value="minutes" <?php echo 'minutes' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Minutes', 'addify_acr' ); ?> </option>
		<option value="hours" <?php echo 'hours' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Hours', 'addify_acr' ); ?> </option>
		<option value="days" <?php echo 'days' === $period ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Days', 'addify_acr' ); ?>  </option>
	</select>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Pending Order Status.
 *
 * @param array $args arguments.
 */
function afacr_pending_order_status_callback( $args = array() ) {
	$values = (array) get_option( 'afacr_pending_order_status' );

	$values = empty( $values ) || empty( $values[0] ) ? array( 'wc-pending' ) : $values;

	?>
	<select id="afacr_pending_order_status" name="afacr_pending_order_status[]" class="select2-multiple width-30" multiple>
	<?php
	foreach ( wc_get_order_statuses() as $val => $name ) :
		?>
			<option value="<?php echo esc_attr( $val ); ?>" <?php echo in_array( $val, $values, true ) ? esc_attr( 'selected' ) : ''; ?> > 
			<?php echo esc_attr( $name ); ?> 
			</option>

		<?php
		endforeach;
	?>
	</select>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}
