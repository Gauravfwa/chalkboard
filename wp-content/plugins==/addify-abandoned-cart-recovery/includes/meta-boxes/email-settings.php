<?php
/**
 * Admin file of Module
 *
 * Manage all settings and actions of admin
 *
 * @package  addify-abandoned-cart-recovery/includes/meta-boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$active        = get_post_meta( $post->ID, 'afacr_enable', true );
$subject       = get_post_meta( $post->ID, 'afacr_email_subject', true );
$email_type    = get_post_meta( $post->ID, 'afacr_email_type', true );
$time_and_type = json_decode( get_post_meta( $post->ID, 'afacr_time', true ) );
$automatic     = get_post_meta( $post->ID, 'afacr_automatic', true );
$sel_roles     = (array) json_decode( get_post_meta( get_the_ID(), 'afacr_customer_roles', true ) );

$time      = isset( $time_and_type[0] ) ? $time_and_type[0] : 0;
$time_type = isset( $time_and_type[1] ) ? $time_and_type[1] : 'minutes';
?>
<div class="afacr-metabox-fields">
	<table class="addify-table-optoin">

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Active', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="checkbox" name="afacr_enable" value="yes" <?php checked( 'yes', $active ); ?> >
				<p><?php echo esc_html__( 'Activate/Deactivate Email Template', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Email Type', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<select name="afacr_email_type" id="afacr_email_type" data-placeholder="Select Shipping Zones">
					<option value="cart" <?php echo 'cart' === $email_type ? 'selected' : ''; ?> > <?php echo esc_html__( 'Abandoned Cart', 'addify_adcod' ); ?> </option>
					<option value="order" <?php echo 'order' === $email_type ? 'selected' : ''; ?> > <?php echo esc_html__( 'Pending Order', 'addify_adcod' ); ?></option>			
				</select>
				<p><?php echo esc_html__( 'Select email type.', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Email Subject', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="text" name="afacr_email_subject" value="<?php echo esc_attr( $subject ); ?>" >
				<p><?php echo esc_html__( 'Subject of email.', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Automatically Send', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="checkbox" name="afacr_automatic" value="yes" <?php checked( 'yes', $automatic ); ?> >
				<p><?php echo esc_html__( 'Activate/Deactivate automatic delivery of email.', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Send After', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="number" name="afacr_time[0]" min="0" value="<?php echo esc_attr( $time ); ?>" >
				<select name="afacr_time[1]" class="half-wdith" >
					<option value="seconds" <?php echo 'seconds' === $time_type ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Seconds', 'addify_acr' ); ?> </option>
					<option value="minutes" <?php echo 'minutes' === $time_type ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Minutes', 'addify_acr' ); ?> </option>
					<option value="hours" <?php echo 'hours' === $time_type ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Hours', 'addify_acr' ); ?> </option>
					<option value="days" <?php echo 'days' === $time_type ? esc_attr( 'selected' ) : ''; ?> > <?php echo esc_html__( 'Days', 'addify_acr' ); ?>  </option>
				</select>
				<p><?php echo esc_html__( 'Insert time to send automatic emails. This time will be calculated after the cart is considered abandoned (not the time when the cart is created). The cart abandonment time can be changed from plugin settings.', 'addify_adcod' ); ?></p>
				<p><?php echo esc_html__( 'Email Sending Time = Time After Which the Cart is Considered Abandoned + The Above Time', 'addify_adcod' ); ?></p>
			</td>
		</tr>
		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Enable Email Template for User Roles', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<div class="all_cats">
					<ul>
					<?php
					global $wp_roles;
					$roles = $wp_roles->get_names();
					foreach ( $roles as $key => $value ) {
						?>
						<li class="par_cat">
							<input type="checkbox" class="parent" name="afacr_customer_roles[]" id="" value="<?php echo esc_attr( $key ); ?>" 
						<?php
						if ( ! empty( $sel_roles ) && in_array( (string) $key, $sel_roles, true ) ) {
							echo 'checked';
						}
						?>
							/>
						<?php
						echo esc_attr( $value );
						?>
						</li>

					<?php } ?>
						<li class="par_cat">
							<input type="checkbox" class="parent" name="afacr_customer_roles[]" id="" value="guest" 
							<?php
							if ( ! empty( $sel_roles ) && in_array( 'guest', $sel_roles, true ) ) {
								echo 'checked';
							}
							?>
								/>
							<?php echo esc_html__( 'Guest', 'addify_adcod' ); ?>
						</li>
					</ul>
				</div>
				<p><?php echo esc_html__( 'Select user roles for email templates.', 'addify_adcod' ); ?></p>
			</td>
		</tr>
	</table>
</div>

