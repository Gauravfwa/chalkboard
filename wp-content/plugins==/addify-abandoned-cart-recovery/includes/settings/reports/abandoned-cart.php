<?php
/**
 *
 * Abandoned cart tab.
 *
 * @package  addify-abandoned-cart-recovery/includes/settings/reports
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once AFACR_PLUGIN_DIR . '/includes/list-tables/class-af-cart-list-table.php';

$cart_list_table = new AF_Cart_List_Table();

$cart_list_table->screen = get_current_screen();

$cart_list_table->prepare_items();

?>

<div class="afrac-cart-table">
	<section class="cart-report">
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php
					echo esc_html__( 'WooCommerce Abandoned Carts', 'addify_acr' );
				?>
			</h1>
			<form method="POST">
			<?php

				$cart_list_table->display();
			?>
			</form>
		</div>
	</section>
</div>
