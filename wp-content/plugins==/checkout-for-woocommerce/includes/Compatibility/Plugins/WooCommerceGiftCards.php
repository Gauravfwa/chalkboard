<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommerceGiftCards extends Base {
	public function is_available() {
		return function_exists( 'WC_GC' );
	}

	public function run() {
		$WooCommerce_GiftCards = WC_GC();

		remove_action( 'woocommerce_review_order_before_submit', array( $WooCommerce_GiftCards->cart, 'display_form' ), 9 );
		add_action( 'cfw_coupon_module_end', array( $this, 'display_form' ), 9, 1 );
	}

	public function run_on_wp_loaded() {
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'add_fragment' ), 10, 1 );
	}

	/**
	 * Display form to add gift card.
	 *
	 * @param bool $mobile
	 *
	 * @return void
	 */
	public function display_form( $mobile = false ) {
		if ( $mobile ) {
			return;
		}

		$WooCommerce_GiftCards = WC_GC();

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( $WooCommerce_GiftCards->cart->cart_contains_gift_card() ) {
			return;
		}

		$get_private_notices_callback = function() {
			return $this->notices;
		};

		$get_private_notices = $get_private_notices_callback->bindTo( $WooCommerce_GiftCards->cart, '\\WC_GC_Cart' );

		$notices = $get_private_notices();
		?>
		<div class="add_gift_card_form">
			<?php
			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					if ( empty( $notice['type'] ) ) {
						$notice['type'] = 'message';
					}
					echo '<div class="woocommerce-' . esc_attr( $notice['type'] ) . '">' . esc_html( $notice['text'] ) . '</div>';
				}
			}
			?>
			<h4><?php cfw_esc_html_e( 'Have a Gift Card?', 'woocommerce-gift-cards' ); ?></h4>
			<div id="wc_gc_cart_redeem_form" class="row cfw-input-wrap-row">
				<div class="col-lg-8 no-gutters">
					<div class="col-lg-12" id=wc_gc_cart_code_field" data-priority="10">
						<div class="cfw-input-wrap cfw-text-input">
							<label for="cfw-promo-code" class="cfw-input-label"><?php cfw_esc_attr_e( 'Enter your code&hellip;', 'woocommerce-gift-cards' ); ?></label>
							<input data-storage="false" placeholder="<?php cfw_esc_attr_e( 'Enter your code&hellip;', 'woocommerce-gift-cards' ); ?>" type="text" name="wc_gc_cart_code" id="wc_gc_cart_code" class="input-text" autocomplete="off" data-parsley-required="false" style="width: 100%" />
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="cfw-input-wrap cfw-button-input">
						<button type="button" class="cfw-secondary-btn" name="wc_gc_cart_redeem_send" id="wc_gc_cart_redeem_send"><?php cfw_esc_html_e( 'Apply', 'woocommerce-gift-cards' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = array(
			'class'  => 'WooCommerceGiftCards',
			'params' => array(),
		);

		return $compatibility;
	}

	function add_fragment( $fragments ) {
		ob_start();
		$this->display_form();

		$fragments['.add_gift_card_form'] = ob_get_clean();

		return $fragments;
	}
}
