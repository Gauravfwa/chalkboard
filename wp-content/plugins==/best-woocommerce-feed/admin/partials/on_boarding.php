<?php
/**
 * This file is responsible for displaying global setting options
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$hour_in_seconds             = defined( 'HOUR_IN_SECONDS' ) ? HOUR_IN_SECONDS : 3600;
$is_premium_activated        = apply_filters( 'wpfm_is_premium', false );
$custom_field                = get_option( 'rex-wpfm-product-custom-field', 'no' );
$pa_field                    = get_option( 'rex-wpfm-product-pa-field' );
$structured_data             = get_option( 'rex-wpfm-product-structured-data' );
$exclude_tax                 = get_option( 'rex-wpfm-product-structured-data-exclude-tax' );
$wpfm_cache_ttl              = get_option( 'wpfm_cache_ttl', 3 * $hour_in_seconds );
$wpfm_allow_private_products = get_option( 'wpfm_allow_private', 'no' );
$wpfm_hide_char              = get_option( 'rex_feed_hide_character_limit_field', 'on' );
$wpfm_fb_pixel_enabled       = get_option( 'wpfm_fb_pixel_enabled', 'no' );
$wpfm_fb_pixel_data          = get_option( 'wpfm_fb_pixel_value' );
$wpfm_enable_log             = get_option( 'wpfm_enable_log' );
$current_user_email          = get_option( 'wpfm_user_email', '' );
$pro_url                     = add_query_arg( 'pfm-dashboard', '1', 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=go_pro_button&utm_medium=plugin&utm_campaign=pfm_pro&utm_id=pfm_pro' );
$rollback_versions           = function_exists( 'rex_feed_get_roll_back_versions' ) ? rex_feed_get_roll_back_versions() : array();
$wpfm_remove_plugin_data     = get_option( 'wpfm_remove_plugin_data' );
$schedule_hours              = array(
	'1'   => '1 Hour',
	'3'   => '3 Hours',
	'6'   => '6 Hours',
	'12'  => '12 Hours',
	'24'  => '24 Hours',
	'168' => '1 Week',
);

if ( $is_premium_activated ) {
	$per_batch = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );
}
else {
	$per_batch = get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT ) > WPFM_FREE_MAX_PRODUCT_LIMIT ? WPFM_FREE_MAX_PRODUCT_LIMIT : get_option( 'rex-wpfm-product-per-batch', WPFM_FREE_MAX_PRODUCT_LIMIT );
}
?>

<div class="columns">
	<div class="column">
		<div class="rex-onboarding">
			<div class="rex-settings-tab-wrapper">
				<ul class="rex-settings-tabs">
					<li class="tab-link active" data-tab="tab4">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 23" width="31" height="23">
							<defs>
								<clipPath clipPathUnits="userSpaceOnUse" id="cp1">
									<path d="M-410 -36L910 -36L910 557L-410 557Z"/>
								</clipPath>
							</defs>
							<style>
								.control-icon {
									fill: none;
									stroke: #a8a7be;
									stroke-linecap: round;
									stroke-linejoin: round;
									stroke-width: 1.5
								}
							</style>
							<g id="Control" clip-path="url(#cp1)">
								<g id="Group 6">
									<g id="1 copy 5">
										<g id="Group 21">
											<path id="Stroke 1" class="control-icon"
												  d="M4.34 13C4.34 16.12 6.88 18.66 10 18.66C13.12 18.66 15.66 16.12 15.66 13C15.66 9.88 13.12 7.34 10 7.34C6.88 7.34 4.34 9.88 4.34 13Z"/>
											<path id="Stroke 3" class="control-icon"
												  d="M8.3 13C8.3 13.94 9.06 14.7 10 14.7C10.94 14.7 11.7 13.94 11.7 13C11.7 12.06 10.94 11.3 10 11.3C9.06 11.3 8.3 12.06 8.3 13Z"/>
											<path id="Stroke 5" class="control-icon" d="M10 5L10 6.79"/>
											<path id="Stroke 7" class="control-icon" d="M10 19.21L10 21"/>
											<path id="Stroke 9" class="control-icon" d="M2 13L3.79 13"/>
											<path id="Stroke 11" class="control-icon" d="M16.21 13L18 13"/>
											<path id="Stroke 13" class="control-icon" d="M15.66 7.34L14.39 8.61"/>
											<path id="Stroke 15" class="control-icon" d="M5.61 17.39L4.34 18.66"/>
											<path id="Stroke 17" class="control-icon" d="M4.34 7.34L5.61 8.61"/>
											<path id="Stroke 19" class="control-icon" d="M14.39 17.39L15.66 18.66"/>
										</g>
										<g id="Group 21 Copy">
											<path id="Stroke 1" class="control-icon"
												  d="M20.46 8C20.46 9.95 22.05 11.54 24 11.54C25.95 11.54 27.54 9.95 27.54 8C27.54 6.05 25.95 4.46 24 4.46C22.05 4.46 20.46 6.05 20.46 8Z"/>
											<path id="Stroke 3" class="control-icon"
												  d="M22.94 8C22.94 8.59 23.41 9.06 24 9.06C24.59 9.06 25.06 8.59 25.06 8C25.06 7.41 24.59 6.94 24 6.94C23.41 6.94 22.94 7.41 22.94 8Z"/>
											<path id="Stroke 5" class="control-icon" d="M24 3L24 4.12"/>
											<path id="Stroke 7" class="control-icon" d="M24 11.88L24 13"/>
											<path id="Stroke 9" class="control-icon" d="M19 8L20.12 8"/>
											<path id="Stroke 11" class="control-icon" d="M27.88 8L29 8"/>
											<path id="Stroke 13" class="control-icon" d="M27.54 4.46L26.75 5.25"/>
											<path id="Stroke 15" class="control-icon" d="M21.25 10.75L20.46 11.54"/>
											<path id="Stroke 17" class="control-icon" d="M20.46 4.46L21.25 5.25"/>
											<path id="Stroke 19" class="control-icon" d="M26.75 10.75L27.54 11.54"/>
										</g>
									</g>
								</g>
							</g>
						</svg>
						<?php echo esc_html__( 'Controls', 'rex-product-feed' ); ?>
					</li>
					<li class="tab-link" data-tab="tab2">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 22" width="20" height="22">
							<defs>
								<clipPath clipPathUnits="userSpaceOnUse" id="cp1">
									<path d="M-207 -37L1113 -37L1113 556L-207 556Z"/>
								</clipPath>
							</defs>
							<style>
								.marchants {
									fill: none;
									stroke: #a8a7be;
									stroke-linecap: round;
									stroke-linejoin: round;
									stroke-width: 1.5
								}
							</style>
							<g id="Control" clip-path="url(#cp1)">
								<g id="Group 6">
									<g id="1 copy 4">
										<g id="Group 7">
											<path id="Stroke 1" class="marchants"
												  d="M6.08 6.82C6.08 4.71 7.8 3 9.91 3C12.02 3 13.73 4.71 13.73 6.82L13.73 7.64"/>
											<path id="Stroke 3" class="marchants"
												  d="M13.73 7.64L16.35 7.64C17.2 7.64 17.83 8.43 17.65 9.26L15.8 17.8C15.65 18.5 15.03 19 14.31 19L5.38 19C4.66 19 4.04 18.5 3.89 17.8L2.03 9.26C1.85 8.43 2.48 7.64 3.33 7.64L10.93 7.64"/>
											<path id="Stroke 5" class="marchants" d="M5.71 14.26L16.53 14.26"/>
										</g>
									</g>
								</g>
							</g>
						</svg>
						<?php echo esc_html__( 'Merchants', 'rex-product-feed' ); ?>
					</li>
					<li class="tab-link status" data-tab="tab5">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 18" width="16" height="18">
							<defs>
								<clipPath clipPathUnits="userSpaceOnUse" id="cp1">
									<path d="M-851 -39L469 -39L469 554L-851 554Z"/>
								</clipPath>
							</defs>
							<style>
								.status-icon {
									fill: #a8a7be
								}
							</style>
							<g id="Control" clip-path="url(#cp1)">
								<g id="Group 6">
									<g id="1 copy">
										<path id="Shape" fill-rule="evenodd" class="status-icon"
											  d="M7.5 1.62L7.5 7.87C7.5 8.22 7.22 8.5 6.87 8.5L0.62 8.5C0.28 8.5 0 8.22 0 7.87L0 1.62C0 1.28 0.28 1 0.62 1L6.87 1C7.22 1 7.5 1.28 7.5 1.62ZM6.25 2.25L1.25 2.25L1.25 7.25L6.25 7.25L6.25 2.25ZM16 10.12L16 16.37C16 16.72 15.72 17 15.37 17L9.12 17C8.87 17 8.64 16.85 8.55 16.61C8.45 16.38 8.5 16.11 8.68 15.93L14.93 9.68C15.11 9.5 15.38 9.45 15.61 9.55C15.85 9.64 16 9.87 16 10.12ZM14.75 11.63L10.63 15.75L14.75 15.75L14.75 11.63ZM8.5 4.75C8.5 2.68 10.18 1 12.25 1C14.32 1 16 2.68 16 4.75C16 6.82 14.32 8.5 12.25 8.5C10.18 8.5 8.5 6.82 8.5 4.75ZM9.75 4.75C9.75 6.13 10.87 7.25 12.25 7.25C13.63 7.25 14.75 6.13 14.75 4.75C14.75 3.37 13.63 2.25 12.25 2.25C10.87 2.25 9.75 3.37 9.75 4.75ZM7.32 10.57L4.63 13.25L7.32 15.93C7.56 16.18 7.56 16.57 7.32 16.82C7.07 17.06 6.68 17.06 6.43 16.82L3.75 14.13L1.07 16.82C0.82 17.06 0.43 17.06 0.18 16.82C-0.06 16.57 -0.06 16.18 0.18 15.93L2.87 13.25L0.18 10.57C-0.06 10.32 -0.06 9.93 0.18 9.68C0.43 9.44 0.82 9.44 1.07 9.68L3.75 12.37L6.43 9.68C6.68 9.44 7.07 9.44 7.32 9.68C7.56 9.93 7.56 10.32 7.32 10.57Z"/>
									</g>
								</g>
							</g>
						</svg>
						<?php echo esc_html__( 'System Status', 'rex-product-feed' ); ?>
					</li>
					<li class="tab-link" data-tab="tab7">
						<svg width="18" height="22" viewBox="0 0 18 22" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10.8242 1.29437H3.32422C2.82694 1.29437 2.35002 1.49192 1.99839 1.84355C1.64676 2.19518 1.44922 2.67209 1.44922 3.16937V18.1694C1.44922 18.6667 1.64676 19.1436 1.99839 19.4952C2.35002 19.8468 2.82694 20.0444 3.32422 20.0444H14.5742C15.0715 20.0444 15.5484 19.8468 15.9 19.4952C16.2517 19.1436 16.4492 18.6667 16.4492 18.1694V6.91937L10.8242 1.29437Z" stroke="#A8A7BE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M10.8242 1.29437V6.91937H16.4492" stroke="#A8A7BE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8.94922 16.2944V10.6694" stroke="#A8A7BE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M6.13672 13.4819H11.7617" stroke="#A8A7BE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php echo esc_html__( 'Logs', 'rex-product-feed' ); ?>
					</li>
				</ul>

				<div class="rex-settings-tab-content">
					<div id="tab4" class="tab-content active block-wrapper">
						<h3 class="merchant-title"><?php echo esc_html__( 'Controls', 'rex-product-feed' ); ?> </h3>
						<div class="feed-settings">

							<div class="feed-left">

								<div class="single-merchant product-batch">
									<span class="title"><?php echo esc_html__( 'Product(s) per batch (Free users cannot generate more than ', 'rex-product-feed' ) . esc_html( WPFM_FREE_MAX_PRODUCT_LIMIT ) . esc_html__( ' products. For free users it will run only 1 batch)', 'rex-product-feed' ); ?></span>
									<div class="switch">
										<form id="wpfm-per-batch" class="wpfm-per-batch">
											<input id="wpfm_product_per_batch" type="number" name="wpfm_product_per_batch"
												value="<?php echo esc_attr( $per_batch ); ?>"
												min="1" max="<?php echo !$is_premium_activated ? esc_attr( WPFM_FREE_MAX_PRODUCT_LIMIT ) : esc_attr( 500 ); ?>">
											<button type="submit" class="save-batch">
												<span>save</span>
												<i class="fa fa-spinner fa-pulse fa-fw"></i>
											</button>
										</form>
									</div>
								</div>

								<div class="single-merchant wpfm-clear-btn">
									<span class="title"><?php echo esc_html__( 'Clear Batch (Remove all)', 'rex-product-feed' ); ?></span>
									<button class="wpfm-clear-batch" id="wpfm-clear-batch">
										<span>
											<?php echo esc_html__( 'Clear Batch', 'rex-product-feed' ); ?>
										</span>
										 <i class="fa fa-spinner fa-pulse fa-fw"></i>
									</button>
								</div>

								<div class="single-merchant detailed-product  purge-cache">
									<span class="title"><?php echo esc_html__( 'Purge Cache', 'rex-product-feed' ); ?></span>
									<button id="wpfm-purge-cache" class="wpfm-purge-cache">
										<span><?php echo esc_html__( 'Purge Cache', 'rex-product-feed' ); ?></span>
										<i class="fa fa-spinner fa-pulse fa-fw"></i>
									</button>
								</div>

								<div class="single-merchant">
									<span class="title"><?php echo esc_html__( 'Update WooCommerce variation child list that has no parent assigned (abandoned child)', 'rex-product-feed' ); ?></span>
									<button id="rex_feed_abandoned_child_list_update_button" class="rex-feed-abandoned-child-list-update-button">
										<span><?php echo esc_html__( 'Update List', 'rex-product-feed' ); ?></span>
										<i class="fa fa-spinner fa-pulse fa-fw"></i>
									</button>
								</div>

								<div class="single-merchant detailed-product detailed-merchants">
									<span class="title"><?php echo esc_html__( 'WPFM cache TTL', 'rex-product-feed' ); ?></span>
									<div class="wpfm-dropdown">
										<form id="wpfm-transient-settings" class="wpfm-transient-settings">
											<div class="wpfm-cache-ttl-area">
												<select id="wpfm_cache_ttl" name="wpfm_cache_ttl">
													<?php foreach ( $schedule_hours as $key => $label ) { ?>
														<option value="<?php echo esc_attr( (int) $key * $hour_in_seconds ); ?>" <?php selected( $wpfm_cache_ttl, (int) $key * $hour_in_seconds ); ?>><?php echo esc_attr( $label ); ?></option>
													<?php } ?>
												</select>
											
												<button type="submit" class="save-transient-button">
													<span><?php echo esc_html__( 'save', 'rex-product-feed' ); ?></span> 
													<i class="fa fa-spinner fa-pulse fa-fw"></i>
												</button>
											</div>
											<span class="helper-text"><?php echo esc_html__( 'When the cache will be expired.', 'rex-product-feed' ); ?></span>
										</form>
									</div>
								</div>


								<div class="single-merchant remove-plugin-data">
									<span class="title">
										<?php echo esc_html__( 'Remove All Plugin Data on Plugin Uninstallation', 'rex-product-feed' ); ?>
									</span>
									<div class="switch">
										<?php
										$checked = 'yes' === $wpfm_remove_plugin_data ? 'checked' : '';
										?>
										<div class="wpfm-switcher">
											<input class="switch-input" type="checkbox"
												id="remove_plugin_data" <?php echo esc_attr( $checked ); ?>>
											<label class="lever" for="remove_plugin_data"></label>
										</div>
									</div>
								</div>

								<div class="single-merchant enable-log">
									<span class="title">
										<?php echo esc_html__( 'Enable log', 'rex-product-feed' ); ?>
									</span>
									<div class="switch">
										<?php
										$checked = 'yes' === $wpfm_enable_log ? 'checked' : '';
										?>
										<div class="wpfm-switcher">
											<input class="switch-input" type="checkbox"
												id="wpfm_enable_log" <?php echo esc_attr( $checked ); ?>>
											<label class="lever" for="wpfm_enable_log"></label>
										</div>
									</div>
								</div>

								<div class="single-merchant hide-character">
									<span class="title">
										<?php echo esc_html__( 'Hide Character Limit Column', 'rex-product-feed' ); ?>
									</span>
									<div class="switch">
										<?php
										$checked = 'on' === $wpfm_hide_char ? 'checked' : '';
										?>
										<div class="wpfm-switcher">
											<input class="switch-input" type="checkbox"
												id="wpfm_hide_char" <?php echo esc_attr( $checked ); ?>>
											<label class="lever" for="wpfm_hide_char"></label>
										</div>
									</div>
								</div>

								<?php do_action( 'rex_feed_after_log_enable_button_field' ); ?>

								<div class="single-merchant detailed-product rex-feed-rollback">
									<span class="title"><?php echo esc_html__( 'Rollback to Older Version', 'rex-product-feed' ); ?></span>
									<div class="wpfm-dropdown">
										<div class="wpfm-rollback-option-area">
											<select id="wpfm_rollback_options" name="wpfm_rollback_options">
												<?php
												foreach ( $rollback_versions as $version ) {
													echo "<option value='" . esc_attr( $version ) . "'>" . esc_html( $version ) . "</option>";
												}
												?>
											</select>
											<?php
											echo sprintf(
												'<a data-placeholder-text="' . esc_html__( 'Reinstall', 'rex-product-feed' ) . ' v{VERSION}" href="#" data-placeholder-url="%s" class="rex-feed-button-spinner rex-feed-rollback-button btn-default">%s</a>',
												esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rex_feed_rollback&version=VERSION' ), 'rex_feed_rollback' ) ),
												esc_html__( 'Reinstall', 'rex-product-feed' )
											);
											?>
										</div>
										
                                        <span class="helper-text"><?php _e( '<b>Warning:</b> Please backup your database before making the rollback as you might loose you previous data.', 'rex-product-feed' ); // phpcs:ignore ?></span>
									</div>
								</div>


							</div>

							<div class="feed-right">
								<div class="single-merchant unique-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
									<?php if ( !$is_premium_activated ) { ?>
										<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
										class="wpfm-pro-cta">
											<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
										</a>
									<?php } ?>

									<span class="title"><?php echo esc_html__( 'Add Unique Product Identifiers ( Brand, GTIN, MPN, UPC, EAN, JAN, ISBN, ITF14, Offer price, Offer effective date, Additional info ) to product', 'rex-product-feed' ); ?></span>
									<div class="switch">
										<?php
										if ( !$is_premium_activated ) {
											$disabled = 'disabled';
											$checked  = '';
										} else {
											$disabled = '';
											$checked  = 'yes' === $custom_field ? 'checked' : '';
										}
										?>
										<div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
											<input class="switch-input" type="checkbox"
												id="rex-product-custom-field" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
											<label class="lever" for="rex-product-custom-field"></label>
										</div>
									</div>
								</div>

								<?php do_action( 'rex_feed_after_upi_enable_field' ); ?>

								<div class="single-merchant detailed-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
									<?php if ( !$is_premium_activated ) { ?>
										<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
										class="wpfm-pro-cta">
											<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
										</a>
									<?php } ?>

									<span class="title"><?php echo esc_html__( 'Add Detailed Product Attributes ( Size, Color, Pattern, Material, Age group, Gender ) to product', 'rex-product-feed' ); ?></span>
									<div class="switch">
										<?php
										if ( !$is_premium_activated ) {
											$disabled = 'disabled';
											$checked  = '';
										} else {
											$disabled = '';
											$checked  = 'yes' === $pa_field ? 'checked' : '';
										}
										?>
										<div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
											<input class="switch-input" type="checkbox"
												id="rex-product-pa-field" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
											<label class="lever" for="rex-product-pa-field"></label>
										</div>
									</div>
								</div>

								<div class="single-merchant exclude-tax <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
									<?php if ( !$is_premium_activated ) { ?>
										<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
										class="wpfm-pro-cta">
											<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
										</a>
									<?php } ?>

									<span class="title">
										<?php echo esc_html__( 'Exclude TAX from structured data prices', 'rex-product-feed' ); ?>
									</span>
									<div class="switch">
										<?php
										if ( !$is_premium_activated ) {
											$disabled = 'disabled';
											$checked  = '';
										} else {
											$disabled = '';
											$checked  = 'yes' === $exclude_tax ? 'checked' : '';
										}
										?>
										<div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
											<input class="switch-input" type="checkbox"
												id="rex-product-exclude-tax" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
											<label class="lever" for="rex-product-exclude-tax"></label>
										</div>
									</div>
								</div>

								<div class="single-merchant detailed-product">
									<span class="title"><?php echo esc_html__( 'Allow private products', 'rex-product-feed' ); ?></span>
									<div class="switch">
										<?php
										$disabled = '';
										$checked  = 'yes' === $wpfm_allow_private_products ? 'checked' : '';
										?>
										<div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
											<input class="switch-input" type="checkbox"
												id="rex-product-allow-private" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
											<label class="lever" for="rex-product-allow-private"></label>
										</div>
									</div>
								</div>


								<div class="single-merchant increase-product <?php echo !$is_premium_activated ? 'wpfm-pro' : ''; ?>">
									<?php if ( !$is_premium_activated ) { ?>
										<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
										class="wpfm-pro-cta">
											<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
										</a>
									<?php } ?>

									<span class="title">
										<?php
										echo esc_html__(
											'Increase the number of products that will be approved in Google\'s Merchant Center:
                                            This option will fix WooCommerce\'s (JSON-LD) structured data bug and add extra structured data elements to your pages',
											'rex-product-feed'
										);
										?>
									</span>
									<div class="switch">
										<?php
										if ( !$is_premium_activated ) {
											$disabled = 'disabled';
											$checked  = '';
										}
										else {
											$checked = 'yes' === $structured_data ? 'checked' : '';
										}
										?>
										<div class="wpfm-switcher <?php echo esc_attr( $disabled ); ?>">
											<input class="switch-input" type="checkbox"
												id="rex-product-structured-data" <?php echo esc_attr( $checked ); ?> <?php echo esc_attr( $disabled ); ?>>
											<label class="lever" for="rex-product-structured-data"></label>
										</div>
									</div>
								</div>

								<div class="single-merchant fb-pixel">
									<span class="title">
										<?php echo esc_html__( 'Enable Facebook Pixel', 'rex-product-feed' ); ?>
									</span>
									<div class="switch">
										<?php
										if ( 'yes' === $wpfm_fb_pixel_enabled ) {
											$checked      = 'checked';
											$hidden_class = '';
										}
										else {
											$checked      = '';
											$hidden_class = 'is-hidden';
										}
										?>
										<div class="wpfm-switcher">
											<input class="switch-input" type="checkbox" id="wpfm_fb_pixel" <?php echo esc_attr( $checked ); ?>>
											<label class="lever" for="wpfm_fb_pixel"></label>
										</div>
									</div>
								</div>

								<div class="single-merchant wpfm-fb-pixel-field <?php echo esc_attr( $hidden_class ); ?>">
									<span class="title"><?php echo esc_html__( 'Facebook Pixel id', 'rex-product-feed' ); ?></span>
									<div class="switch">
										<form id="wpfm-fb-pixel" class="wpfm-fb-pixel" style="width: 300px;">
											<input id="wpfm_fb_pixel" type="text" name="wpfm_fb_pixel"
												value="<?php echo esc_attr( $wpfm_fb_pixel_data ); ?>" style="width: 200px;">
											<button type="submit" class="save-fb-pixel"><span><?php echo esc_html__( 'save', 'rex-product-feed' ); ?></span>
												<i class="fa fa-spinner fa-pulse fa-fw"></i>
											</button>
										</form>
									</div>
								</div>

								<div class="single-merchant">
									<?php if ( !$is_premium_activated ) { ?>
										<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" title="Click to Upgrade Pro"
										   class="wpfm-pro-cta">
											<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
										</a>
									<?php } ?>

									<span class="title"><?php echo esc_html__( 'Get email notification if your feed is not generated properly', 'rex-product-feed' ); ?></span>
									<div class="switch">
										<form id="wpfm-user-email" class="wpfm-fb-pixel" style="width: 300px;" disabled>
											<input placeholder="user@email.com" id="wpfm_user_email" type="text" name="wpfm_user_email" value="<?php echo esc_attr( $current_user_email ); ?>" style="width: 200px;<?php echo !$is_premium_activated ? ' cursor: not-allowed; disabled' : ''; ?>">
											<button type="submit" class="save-user-email" <?php echo !$is_premium_activated ? ' style="background-color: #f2f2f8; color: #d9d9db; cursor: not-allowed;" disabled' : ''; ?>>
												<span><?php echo esc_html__( 'save', 'rex-product-feed' ); ?></span>
												<i class="fa fa-spinner fa-pulse fa-fw"></i>
											</button>
										</form>
									</div>
								</div>

							</div>

						</div>
					</div>
					<!--/settings tab-->

					<div id="tab2" class="tab-content block-wrapper">
						<div class="rex-merchant">
							<h3 class="merchant-title"><?php echo esc_html__( 'Available Merchants', 'rex-product-feed' ); ?></h3>
							<?php
							// Free vs pro merchants.
							$all_merchants = Rex_Feed_Merchants::get_merchants();
							$_merchants    = !empty( $all_merchants[ 'popular' ] ) ? $all_merchants[ 'popular' ] : array();

							if ( !$is_premium_activated ) {
								$_merchants = !empty( $all_merchants[ 'pro_merchants' ] ) ? array_merge( $_merchants, $all_merchants[ 'pro_merchants' ] ) : $_merchants;
							}

							$_merchants = !empty( $all_merchants[ 'free_merchants' ] ) ? array_merge( $_merchants, $all_merchants[ 'free_merchants' ] ) : $_merchants;

							// Result of bad planning.
							$_merchants[ 'google' ][ 'name' ]    = 'Google Shopping';
							$_merchants[ 'google_Ad' ][ 'name' ] = 'Google AdWords';
							$_merchants[ 'drm' ][ 'name' ]       = 'Google Remarketing (DRM)';

							foreach ( $_merchants as $key => $merchant ) {
								if ( $key ) {
									$show_pro = false;
									$style    = '';
									if ( $is_premium_activated ) {
										$pro_cls  = '';
										$disabled = '';
									}
									else {
										if ( isset( $merchant[ 'free' ] ) && $merchant[ 'free' ] ) {
											$pro_cls  = '';
											$disabled = '';
										}
										else {
											$pro_cls  = 'wpfm-pro';
											$disabled = 'disabled';
											$show_pro = true;
											$style    = 'style="pointer-events: none"';
										}
									}
									?>
									<div class="single-merchant <?php echo esc_attr( $pro_cls ); ?>">
										<?php if ( $show_pro ) { ?>
											<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank"
											   title="Click to Upgrade Pro" class="wpfm-pro-cta">
												<span class="wpfm-pro-tag"><?php echo esc_html__( 'pro', 'rex-product-feed' ); ?></span>
											</a>
										<?php } ?>

										<span class="title">
											<?php
											$merchant_name = !empty( $merchant['name'] ) ? $merchant['name'] : '';
											echo esc_html( $merchant_name );
											?>
										</span>

										<button class="single-merchant__button" type="button">
											<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product-feed&rex_feed_merchant=' . $key ) ); ?>" target="_self" <?php echo wp_kses( $style, wp_kses_allowed_html( 'post' ) ); ?>><?php echo esc_html__( 'Generate', 'rex-product-feed' ); ?></a>
										</button>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
					<!--/merchant tab-->

					<!--System Status-->
					<?php require_once plugin_dir_path( __FILE__ ) . 'rex-feed-system-status-markups.php'; ?>

					<div id="tab7" class="tab-content block-wrapper wpfm-log">
						<?php
						$logs      = WC_Admin_Status::scan_log_files();
						$wpfm_logs = array();

						$pattern = '/^wpfm|fatal/';
						foreach ( $logs as $key => $value ) {
							if ( preg_match( $pattern, $key ) ) {
								$wpfm_logs[ $key ] = $value;
							}
						}
						echo '<form id="wpfm-error-log-form" action="' . esc_url( admin_url( 'admin.php?page=wpfm_dashboard' ) ) . '" method="post">';
						echo '<select id="wpfm-error-log" name="wpfm-error-log">';
						echo '<option value="">Please Select</option>';
						foreach ( $wpfm_logs as $key => $value ) {
							echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $value ) . '</option>';
						}
						echo '<select>';
						echo '<button type="submit" class="btn-default">' . esc_html__( 'View log', 'rex-product-feed' ) . '</button>';
						echo '</form>';

						echo '<div id="log-viewer">';
						echo '<button id="wpfm-log-copy" class="btn-default" style="display: none"> <i class="fa fa-files-o"></i>' . esc_html__( 'Copy log', 'rex-product-feed' ) . '</button>';
						echo '<pre id="wpfm-log-content"></pre>';
						echo '</div>';
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
