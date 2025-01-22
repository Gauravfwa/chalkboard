<?php
/**
 * Edit abandoned cart.
 *
 * @package  addify-abandoned-cart-recovery/templates/admin
 * @version  1.0.0
 */

$cart = get_post( $cart_id );

if ( !is_a( $cart, 'WP_Post') ) {
	return;
}

$cart_content    = json_decode( $cart->post_content, true );
$user_id         = get_post_meta( $cart->ID, 'user_id', true );
$cart_user_email = get_post_meta( $cart->ID, 'user_email', true );
$user            = get_user_by( 'id', $user_id );

$cart_totals = get_post_meta( $cart_id, 'cart_totals', true );


if ( is_object( $user ) ) {

	$customer_name = isset( $user->display_name ) ? $user->display_name : $user->user_login;
	$first_name    = $user->user_firstname;
	$last_name     = $user->user_lastname;

} else {

	if ( !empty( get_post_meta( $cart_id, 'full_name', true ) ) ) {

		$customer_name = get_post_meta( $cart_id, 'full_name', true );
		$first_name    = get_post_meta( $cart_id, 'first_name', true );
		$last_name     = get_post_meta( $cart_id, 'last_name', true );

	} else {
		$first_name    = '';
		$last_name     = '';
		$customer_name = 'Guest';
	}
}

?>
<section class="cart">
	<div class="user-info">
		<table cellspacing="0">
			<?php if ( !empty( $first_name ) ) : ?>
				<tr>
					<th>
						<?php esc_html_e( 'First Name', 'addify_acr' ); ?>
					</th>

					<td>
						<?php echo esc_html( $first_name ); ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( !empty( $last_name ) ) : ?>
				<tr>
					<th>
						<?php esc_html_e( 'Last Name', 'addify_acr' ); ?>
					</th>

					<td>
						<?php echo esc_html( $last_name ); ?>
					</td>

				</tr>
			<?php endif; ?>
			<?php if ( empty( $first_name ) && empty( $last_name )  ) : ?>
				<tr>
					<th>
						<?php esc_html_e( 'Customer Name', 'addify_acr' ); ?>
					</th>

					<td>
						<?php echo esc_html( $customer_name ); ?>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th>
					<?php esc_html_e( 'Customer Email', 'addify_acr' ); ?>
				</th>

				<td>
					<?php echo esc_html( $cart_user_email ); ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="cart-details">
		<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
			<thead>
				<tr>
					<th class="product-thumbnail">&nbsp;</th>
					<th class="product-name"><?php esc_html_e( 'Product', 'addify_acr' ); ?></th>
					<th class="product-price"><?php esc_html_e( 'Price', 'addify_acr' ); ?></th>
					<th class="product-quantity"><?php esc_html_e( 'Quantity', 'addify_acr' ); ?></th>
					<th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'addify_acr' ); ?></th>
				</tr>
			</thead>
			<tbody>

				<?php
				foreach ( $cart_content as $cart_item ) {

					if ( isset( $cart_item['variation_id'] ) && 0 !== $cart_item['variation_id'] ) {

						$_product          = wc_get_product( intval( $cart_item['variation_id'] ) );
						$product_permalink = $_product->get_permalink();

					} elseif ( isset( $cart_item['product_id'] ) ) {

						$_product          = wc_get_product( intval( $cart_item['product_id'] ) );
						$product_permalink = $_product->get_permalink();
					}
					?>
					<tr>

						<td class="product-thumbnail">
						<?php
							$thumbnail = $_product->get_image();

						if ( ! $product_permalink ) {
							echo wp_kses_post( $thumbnail );
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $thumbnail ) );
						}
						?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'addify_acr' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( $_product->get_name() );
						} else {
							echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ) );
						}
						?>
						</td>

						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'addify_acr' ); ?>">
						<?php
							echo wp_kses_post( wc_price( $_product->get_price() ) );
						?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'addify_acr' ); ?>">
						<?php
							echo esc_attr( $cart_item['quantity'] );
						?>
						</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'addify_acr' ); ?>">
						<?php
							echo wp_kses_post( wc_price( $_product->get_price() * $cart_item['quantity'] ) );
						?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<div class="cart_totals">
			<h2><?php echo wp_kses_post( 'Cart totals', 'addify_acr' ); ?></h2>

			<table class="shop_table shop_table_responsive" cellspacing="0">

				<tbody>
					<tr class="cart-subtotal">
						<th><?php echo wp_kses_post( 'Subtotal', 'addify_acr' ); ?> </th>
						<td data-title="Subtotal">
							<?php echo wp_kses_post( wc_price( get_post_meta( $cart_id, 'cart_subtotal', true ) ) ); ?>
						</td>
					</tr>
					<?php if ( !empty( floatval( $cart_totals['subtotal_tax'] ) ) ) : ?>
						<tr class="cart-total">
							<th><?php echo wp_kses_post( 'Subtotal Tax', 'addify_acr' ); ?> </th>
							<td data-title="Subtotal">
								<?php echo wp_kses_post( wc_price( $cart_totals['subtotal_tax'] ) ); ?>
							</td>
						</tr>
					<?php endif; ?>
					<?php if ( !empty( floatval( $cart_totals['shipping_total'] ) ) ) : ?>
						<tr class="shipping-subtotal">
							<th><?php echo wp_kses_post( 'Shipping', 'addify_acr' ); ?> </th>
							<td data-title="Subtotal">
								<?php echo wp_kses_post( wc_price( $cart_totals['shipping_total'] ) ); ?>
							</td>
						</tr>
					<?php endif; ?>
					<?php if ( !empty( floatval( $cart_totals['total'] ) ) ) : ?>
						<tr class="tax-subtotal">
							<th><?php echo wp_kses_post( 'Total', 'addify_acr' ); ?> </th>
							<td data-title="Subtotal">
								<?php echo wp_kses_post( wc_price( $cart_totals['total'] ) ); ?>
							</td>
						</tr>
					<?php endif; ?>
					
				</tbody>
			</table>
		</div>
	</div>
</section>
