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

// Abandoned carts and orders.

// Quantity.

$total_abandoned_carts = get_option( 'afacr_total_abandoned_carts' );

$total_abandoned_orders = get_option( 'afacr_total_pending_orders' );

$total_abandoned_carts_orders = $total_abandoned_carts + $total_abandoned_orders;

// Amount.

$total_abandoned_carts_amt = get_option( 'afacr_total_abandoned_cart_amount' );

$total_abandoned_orders_amt = get_option( 'afacr_total_pending_orders_amount' );

$total_abandoned_carts_orders_amt = $total_abandoned_carts_amt + $total_abandoned_orders_amt;

// Recovered carts and orders.

// Quantity.

$total_recovered_carts = get_option( 'afacr_total_recovered_carts' );

$total_recovered_orders = get_option( 'afacr_total_recovered_orders' );

$total_recovered_carts_orders = $total_recovered_carts + $total_recovered_orders;

// Amount.

$total_recovered_carts_amt = get_option( 'afacr_total_recovered_cart_amount' );

$total_recovered_orders_amt = get_option( 'afacr_total_recovered_orders_amount' );

$total_recovered_carts_orders_amt = $total_recovered_carts_amt + $total_recovered_orders_amt;

// Emails Report.

$total_cart_emails = get_option( 'afacr_total_abandoned_carts_emails' );

$total_order_emails = get_option( 'afacr_total_pending_orders_emails' );

$total_emails = $total_order_emails + $total_cart_emails;

?>

<div class="afrac-dashboard">
	<section class="curve-charts">
		<div id="curve_chart"></div>
	</section>
	<section class="dashboard-charts">
		<div class="col-6">
			<div id="piechart-amount"></div>
		</div>
		<div class="col-6">
			<div id="piechart-quantity"></div>
		</div>
	</section>
	<section class="cart-report">
		<div class="row">

			<div class="col">
				<h3> <?php echo esc_html__( 'Total Abandoned Carts and Orders Amount Report', 'addify_acr' ); ?> </h3>
				<table>
					<tr>
						<th> <?php echo esc_html__( 'Total Amount', 'addify_acr' ); ?>  </th>
						<td> <?php echo wp_kses_post( wc_price( $total_abandoned_carts_orders_amt ) ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Abandoned Cart', 'addify_acr' ); ?> </th>
						<td> <?php echo wp_kses_post( wc_price( $total_abandoned_carts_amt ) ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Pending Orders', 'addify_acr' ); ?> </th>
						<td> <?php echo wp_kses_post( wc_price( $total_abandoned_orders_amt ) ); ?> </td>
					</tr>
				</table>
			</div>

			<div class="col">
				<h3> <?php echo esc_html__( 'Total Recovered Carts and Orders Amount Report', 'addify_acr' ); ?> </h3>
				<table>
					<tr>
						<th> <?php echo esc_html__( 'Total Amount', 'addify_acr' ); ?>  </th>
						<td> <?php echo wp_kses_post( wc_price( $total_recovered_carts_orders_amt ) ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Amount of Recovered Carts', 'addify_acr' ); ?> </th>
						<td> <?php echo wp_kses_post( wc_price( $total_recovered_carts_amt ) ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Amount of Recovered Orders', 'addify_acr' ); ?> </th>
						<td> <?php echo wp_kses_post( wc_price( $total_recovered_orders_amt ) ); ?> </td>
					</tr>
				</table>
			</div>

			<div class="col">
				<h3> <?php echo esc_html__( 'Total Abandoned Cart and Orders Quantity', 'addify_acr' ); ?> </h3>
				<table>
					<tr>
						<th> <?php echo esc_html__( 'Total Abandoned Carts and Orders Quantity', 'addify_acr' ); ?> </th>
						<td><?php echo esc_attr( $total_abandoned_carts_orders ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Abandoned carts Quantity', 'addify_acr' ); ?> </th>
						<td> <?php echo esc_attr( $total_abandoned_carts ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Pending Orders Quantity', 'addify_acr' ); ?> </th>
						<td> <?php echo esc_attr( $total_abandoned_orders ); ?> </td>
					</tr>
				</table>
			</div>

			<div class="col">
				<h3> <?php echo esc_html__( 'Recovered Cart and Orders', 'addify_acr' ); ?> </h3>
				<table>
					<tr>
						<th> <?php echo esc_html__( 'Total Recovered Carts and Orders', 'addify_acr' ); ?> </th>
						<td>  <?php echo esc_attr( $total_recovered_carts_orders ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Recovered Carts', 'addify_acr' ); ?> </th>
						<td>  <?php echo esc_attr( $total_recovered_carts ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Recovered Orders', 'addify_acr' ); ?> </th>
						<td> <?php echo esc_attr( $total_recovered_orders ); ?> </td>
					</tr>
				</table>
			</div>

			<div class="col">
				<h3> <?php echo esc_html__( 'Emails Report', 'addify_acr' ); ?> </h3>
				<table>
					<tr>
						<th> <?php echo esc_html__( 'Total Emails sent', 'addify_acr' ); ?> </th>
						<td> <?php echo esc_attr( $total_emails ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Emails for Abandoned Cart', 'addify_acr' ); ?> </th>
						<td> <?php echo esc_attr( $total_cart_emails ); ?> </td>
					</tr>
					<tr>
						<th> <?php echo esc_html__( 'Emails for Pending Orders', 'addify_acr' ); ?> </th>
						<td> <?php echo esc_attr( $total_order_emails ); ?> </td>
					</tr>
				</table>
			</div>

		</div>
	</section>
</div>
