<?php
/**
 * Settings for cart restrictions
 *
 * Displays the settings for fee for  cart amount, Products and Product Categories
 *
 * @package  addify-abandoned-cart-recovery/includes/Settings
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


add_settings_section(
	'afacr-user-setting-sec',         // ID used to identify this section and with which to register options.
	esc_html__( 'Pending Orders Settings', 'addify_acr' ),   // Title to be displayed on the administration page.
	'', // Callback used to render the description of the section.
	'afacr_user_setting_section'                           // Page on which to add this section of options.
);

add_settings_field(
	'afacr_user_roles',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Enable Recover Pending orders for user roles', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_user_roles_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_user_setting_section',                          // The page on which this option will be displayed.
	'afacr-user-setting-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'Select user roles to enable recovery of pending orders. Leave empty or select all for all user roles.', 'addify_acr' ) )
);

register_setting(
	'afacr_user_setting_fields',
	'afacr_user_roles'
);

add_settings_field(
	'afacr_enable_guest',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Enable Abandoned Cart for Guest Users', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_enable_guest_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_user_setting_section',                          // The page on which this option will be displayed.
	'afacr-user-setting-sec',         // The name of the section to which this field belongs.
	array(
		esc_html__( 'Never (The guest abandoned cart wont be recorded and no recovery emails will be sent out).', 'addify_acr' ),
		esc_html__( 'Capture from checkout Page(This will capture cart from checkout after a user insert the billing email.).', 'addify_acr' ),
		esc_html__( 'Pre Capture without privacy text (This will enable a popup to pre-capture guest emails even before they go to checkout page. If user declined to share – the email will be captured from checkout page).', 'addify_acr' ),
		esc_html__( 'Pre Capture with privacy text (Use pre-capture email popup with privacy disclaimer. If user declined to share – the email will be captured from checkout page).', 'addify_acr' ),
	)
);

register_setting(
	'afacr_user_setting_fields',
	'afacr_enable_guest'
);

add_settings_field(
	'afacr_popup_title',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Popup Title', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_popup_title_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_user_setting_section',                          // The page on which this option will be displayed.
	'afacr-user-setting-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'This title will be displayed on pop-up modal for guest users.', 'addify_acr' ) )
);

register_setting(
	'afacr_user_setting_fields',
	'afacr_popup_title'
);


add_settings_field(
	'afacr_text_for_terms',                      // ID used to identify the field throughout the theme.
	esc_html__( 'Privacy Policy for Guest Users', 'addify_acr' ),    // The label to the left of the option interface element.
	'afacr_text_for_terms_callback',   // The name of the function responsible for rendering the option interface.
	'afacr_user_setting_section',                          // The page on which this option will be displayed.
	'afacr-user-setting-sec',         // The name of the section to which this field belongs.
	array( esc_html__( 'This text will be shown on pop-up modal along with a checkbox to ask the guest users to save the data for Abandoned cart recovery email.', 'addify_acr' ) )
);

register_setting(
	'afacr_user_setting_fields',
	'afacr_text_for_terms'
);

/**
 * User roles.
 *
 * @param array $args arguments.
 */
function afacr_user_roles_callback( $args = array() ) {

	$values = (array) get_option( 'afacr_user_roles' );
	?>
	<div class="all_cats">
		<ul>
		<?php
		global $wp_roles;
		$roles = $wp_roles->get_names();
		foreach ( $roles as $key => $value ) {
			?>
			<li class="par_cat">
				<input type="checkbox" class="parent" name="afacr_user_roles[]" id="" value="<?php echo esc_attr( $key ); ?>" 
				<?php echo in_array( (string) $key, $values, true ) ? 'checked' : ''; ?>
					/>
				<?php echo esc_attr( $value ); ?>
			</li>
		<?php } ?>
			<li class="par_cat">
				<input type="checkbox" class="parent" name="afacr_user_roles[]" id="" value="guest" 
				<?php echo in_array( 'guest', $values, true ) ? 'checked' : ''; ?>
				/>
				<?php echo esc_html__( 'Guest', 'addify_adcod' ); ?>
			</li>
		</ul>
	</div>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Enable Guest.
 *
 * @param array $args arguments.
 */
function afacr_enable_guest_callback( $args = array() ) {

	$value = get_option( 'afacr_enable_guest' );
	$value = empty( $value ) ? 'never' : $value;
	?>
	<input type="radio" name="afacr_enable_guest" id="" value="never" <?php echo checked( 'never', esc_attr( $value ) ); ?> /> 
	<?php echo esc_html__( 'Never', 'addify_acr' ); ?>
	<br/>
	<input type="radio" name="afacr_enable_guest" id="" value="checkout" <?php echo checked( 'checkout', esc_attr( $value ) ); ?> /> 
	<?php echo esc_html__( 'Capture from checkout page', 'addify_acr' ); ?>
	<br/>
	<input type="radio" name="afacr_enable_guest" id="" value="always" <?php echo checked( 'always', esc_attr( $value ) ); ?> />
	<?php echo esc_html__( 'Pre Capture without privacy text', 'addify_acr' ); ?>
	<br/>
	<input type="radio" name="afacr_enable_guest" id="" value="ask" <?php echo checked( 'ask', esc_attr( $value ) ); ?> />
	<?php echo esc_html__( 'Pre Capture with privacy text', 'addify_acr' ); ?>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[1] ); ?> </p>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[2] ); ?> </p>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[3] ); ?> </p>
	<?php
}

/**
 * Text for terms.
 *
 * @param array $args arguments.
 */
function afacr_text_for_terms_callback( $args = array() ) {

	$value = (array) get_option( 'afacr_text_for_terms' );
	?>
	<input type="text" name="afacr_text_for_terms[0]" class="guest-user-ask w-50" value="<?php echo esc_attr( isset( $value[0] ) ? $value[0] : '' ); ?>" placeholder="Heading for privacy policy">
	<br>
	<textarea name="afacr_text_for_terms[1]" id="" placeholder="Text for privacy policy" cols="100" rows="5"><?php echo esc_attr( isset( $value[1] ) ? $value[1] : '' ); ?></textarea>
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

/**
 * Pop up title.
 *
 * @param array $args arguments.
 */
function afacr_popup_title_callback( $args = array() ) {

	$value = (string) get_option( 'afacr_popup_title' );
	?>
	<input type="text" name="afacr_popup_title" class="guest-user-ask-or-always  w-50" placeholder="Title for Popup" value="<?php echo esc_html( $value ); ?>">
	<p class="description afreg_additional_fields_section_title"> <?php echo wp_kses_post( $args[0] ); ?> </p>
	<?php
}

