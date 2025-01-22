<?php
if ( get_transient( 'gmc-notices' ) ) {
	?>
	<div class="updated woocommerce-message wc-connect">
		<p>
			<strong>
				<span>
				 <?php
				 _e( 'Google Merchant Center - Your\'re ready to integrate with your Merchant Center account and start collecting customer reviews!', 'wc-google-merchant-center-customer-reviews' );
				 ?>
				</span>
			</strong>
		</p>
		<p class="submit">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=integration' ) ); ?>"
			   class="wc-update-now button-primary"><?php _e( 'Settings', 'wc-google-merchant-center-customer-reviews' ); ?>
			</a>
		</p>
	</div>
	<?php
	delete_transient( 'gmc-notices' );
}