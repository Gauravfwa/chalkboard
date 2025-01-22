<?php
/**
 * Recovered cart table.
 *
 * @package  addify-abandoned-cart-recovery/includes/settings/reports
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once AFACR_PLUGIN_DIR . '/includes/list-tables/class-af-recovered-cart-list-table.php';


$recovered_cart_list_table = new AF_Recovered_Cart_List_Table();
$recovered_cart_list_table->prepare_items();
?>

<div class="afrac-recovered-cart-table">
	<section class="cart-report">
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php
				echo esc_html__( 'WooCommerce Recovered Carts', 'addify_acr' );
				?>
			</h1>
			<form method="POST">
			<?php

				$recovered_cart_list_table->display();
			?>
			</form>
		</div>
	</section>
</div>
