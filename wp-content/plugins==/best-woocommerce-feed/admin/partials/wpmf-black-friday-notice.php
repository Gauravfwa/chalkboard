<?php
/**
 * This file is responsible for displaying black friday notice
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

?>
<div
		id="wpfm-black-friday-notice"
		class="wpfm-black-friday-notice notice notice-success is-dismissible"
		style="background-image: url(<?php echo esc_url( WPFM_PLUGIN_DIR_URL . 'admin/icon/bf_bg.png' ); ?>); padding: 10px 0; background-repeat: no-repeat; background-size: cover;"
>
	<div class="wpfm-bf-wrapper">
		<div class="wpfm-logo">
			<img src="<?php echo esc_url( WPFM_PLUGIN_ASSETS_FOLDER . 'icon/wpfm_logo.png' ); ?>" alt="wpfm-black-friday">
		</div>
		<div class="wpfm-bf-text">
			<p><?php echo esc_html__( 'Upgrade to pro with a', 'rex-product-feed' ); ?></p>
			<h3><?php echo esc_html__( 'Huge Black Friday Discount', 'rex-product-feed' ); ?></h3>
			<p><?php echo esc_html__( 'Generate ultimate product feed in minutes.', 'rex-product-feed' ); ?></p>
		</div>
		<div class="wpfm-bf-button">
			<p>Get</p>
			<a href="https://rextheme.com/best-woocommerce-product-feed#upgrade-pro" target="_blank">
				20% off
			</a>
			<p class="wpfm-bf-coupon"><?php echo esc_html__( 'Coupon:', 'rex-product-feed' ); ?> REX19BF</p>
		</div>
	</div>
</div>
