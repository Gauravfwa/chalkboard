<?php
/**
 * Email Logs Table.
 *
 * @package  addify-abandoned-cart-recovery/includes/settings/reports
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once AFACR_PLUGIN_DIR . '/includes/list-tables/class-af-email-log-table.php';

$emails_log_table         = new AF_Email_log_Table();
$emails_log_table->screen = get_current_screen();

?>
<div class="afrac-email-log-table">
	<section class="cart-report">
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php
				echo esc_html__( 'WooCommerce Abandoned Cart Email Logs', 'addify_acr' );
				?>
			</h1>
			<form method="POST">
			<?php

				$emails_log_table->prepare_items();

				$emails_log_table->display();
			?>
			</form>
		</div>
	</section>
</div>
