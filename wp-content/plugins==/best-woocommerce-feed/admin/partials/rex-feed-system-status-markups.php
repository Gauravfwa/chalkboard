<?php
/**
 * This file is responsible for displaying system status
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/partials
 */

$system_status = Rex_Feed_System_Status::get_all_system_status();
?>

<div id="tab5" class="tab-content block-wrapper">

	<!-- `rex-system-status`  block -->
	<div class="system-status rex-system-status">

		<!-- `system-status__platform` element in the `rex-system-status` block  -->
		<div class="rex-system-status__platform">
			<h3 class="rex-system-status__heading">
				<?php echo esc_html__( 'System Status', 'rex-product-feed' ); ?>
			</h3>
			<button type="button" class="rex-system-status__button" id="rex-feed-system-status-copy-btn">
				<i class="fa fa-files-o"></i>
				<?php esc_html_e( 'Copy Status', 'rex-product-feed' ); ?>
			</button>
		</div>

		<!-- `rex-system-status__content` element in the `rex-system-status` block  -->
		<div class="rex-system-status__content">

			<?php
			foreach ( $system_status as $sys_status ) {
				if ( isset( $sys_status[ 'label' ] ) && '' !== $sys_status[ 'label' ] && isset( $sys_status[ 'message' ] ) && '' !== $sys_status[ 'message' ] ) {
					$skip_label = array( 'Version', 'WP Cron' );
					if ( in_array( $sys_status[ 'label' ], $skip_label ) && !isset( $sys_status[ 'status' ] ) ) {
						continue;
					}
					?>
				<!-- `rex-system-status__info` element in the `rex-system-status` block  -->
				<div class="rex-system-status__info">

					<!-- `rex-system-status__label` element in the `rex-system-status` block  -->
					<div class="rex-system-status__ground">
						<h6 class="rex-system-status__label">
						<?php echo esc_html( $sys_status[ 'label' ] ); ?>
						</h6>
					</div>

					<div class="rex-system-status__lists">

						<span class="rex-system-status__list">
						<?php
							$message = $sys_status[ 'message' ];  //phpcs:ignore
							$classes = 'dashicons dashicons-yes';
						if ( !empty( $sys_status[ 'label' ] ) && ( 'Product Types' === $sys_status[ 'label' ] || 'Total Products by Types' === $sys_status[ 'label' ] ) ) {
							$classes = '';
						}
						if ( isset( $sys_status[ 'status' ] ) && 'error' === $sys_status[ 'status' ] || isset( $sys_status[ 'is_writable' ] ) && 'False' === $sys_status[ 'is_writable' ] ) {
							echo wp_kses( "<mark class='error'><span class='dashicons dashicons-warning'></span>{$message}</mark>", rex_feed_get_allowed_kseser() );
						}
						else {
							echo wp_kses( "<mark class='yes'><span class='{$classes}'></span>{$message}</mark>", rex_feed_get_allowed_kseser() );
						}
						?>
						</span>
					</div>

				 </div>

					<?php
				}
			}
			?>
		
		</div>

	</div>

    <textarea name="" id="rex-feed-system-status-area" style="visibility: hidden; margin-top: 10px" cols="100" rows="30"><?php echo Rex_Feed_System_Status::get_system_status_text(); //phpcs:ignore?></textarea>
</div>
