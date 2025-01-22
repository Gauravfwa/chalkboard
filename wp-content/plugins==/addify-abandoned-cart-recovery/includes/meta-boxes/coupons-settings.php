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

$value       = get_post_meta( $post->ID, 'afacr_coupon_value', true );
$coupon_type = get_post_meta( $post->ID, 'afacr_coupon_type', true );
$validity    = get_post_meta( $post->ID, 'afacr_coupon_validity', true );
$active      = get_post_meta( $post->ID, 'afacr_enable_coupon', true );
?>
<div class="afacr-metabox-fields">
	<table class="addify-table-optoin">

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Enable Coupon', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="checkbox" name="afacr_enable_coupon" value="yes" <?php checked( 'yes', $active ); ?> >
				<p><?php echo esc_html__( 'Enable/Disable Coupon', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Coupon Value', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="number" min="0" name="afacr_coupon_value" value="<?php echo esc_attr( $value ); ?>" >
				<p><?php echo esc_html__( 'Fixed or percentage value for discount.', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Coupon Type', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<select name="afacr_coupon_type" id="afacr_coupon_type" data-placeholder="Select Coupen Type">
					<option value="amount" <?php echo 'amount' === $coupon_type ? 'selected' : ''; ?> > <?php echo esc_html__( 'Amount', 'addify_adcod' ); ?> </option>
					<option value="percentage" <?php echo 'percentage' === $coupon_type ? 'selected' : ''; ?> > <?php echo esc_html__( 'Percentage', 'addify_adcod' ); ?></option>			
				</select>
				<p><?php echo esc_html__( 'Select Coupons Type i.e. Discount in fixed amount or percentage amount.', 'addify_adcod' ); ?></p>
			</td>
		</tr>

		<tr class="addify-option-field">
			<th>
				<div class="option-head">
					<h3>
						<?php echo esc_html__( 'Coupon Validity', 'addify_adcod' ); ?>
					</h3>
				</div>
			</th>
			<td>
				<input type="number" name="afacr_coupon_validity" value="<?php echo esc_attr( $validity ); ?>" > <?php echo esc_html__( 'Days', 'addify_adcod' ); ?>
				<p><?php echo esc_html__( 'Enter coupons validity time period in days.', 'addify_adcod' ); ?></p>
			</td>
		</tr>
	</table>
</div>
