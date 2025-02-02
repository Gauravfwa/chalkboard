<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class WooCommercePointsandRewards extends Base {
	public function is_available() {
		return function_exists( 'woocommerce_points_and_rewards_missing_wc_notice' );
	}

	public function run() {
		add_action( 'cfw_wp_head', array( $this, 'add_helper_script' ) );
	}

	function add_helper_script() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( document.body ).on( 'applied_coupon_in_checkout', function() {
					jQuery( ".wc_points_rewards_earn_points" ).remove();
					jQuery( ".wc_points_redeem_earn_points" ).remove();
				} );
			} );
		</script>
		<?php
	}
}
