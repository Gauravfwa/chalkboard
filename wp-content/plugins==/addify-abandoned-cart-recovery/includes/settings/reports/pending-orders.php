<?php
/**
 * Dashboard of Module.
 *
 * Generate report of dashboard.
 *
 * @package  addify-abandoned-cart-recovery/includes/settings/reports
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once AFACR_PLUGIN_DIR . '/includes/list-tables/class-af-pending-orders-list-table.php';

$orders_list_table = new AF_Pending_Orders_List_Table();

$orders_list_table->screen = get_current_screen();

?>

<div class="afrac-cart-table">
	<section class="cart-report">
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php

				echo esc_html__( 'WooCommerce Pending Orders', 'addify_acr' );
				?>
			</h1>
			<form method="POST">
			<?php

				$orders_list_table->prepare_items();

				$orders_list_table->display();
			?>
			</form>
		</div>
	</section>
</div>
