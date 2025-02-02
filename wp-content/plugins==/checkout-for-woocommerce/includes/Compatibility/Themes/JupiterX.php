<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class JupiterX extends Base {
	function is_available() {
		return function_exists( 'jupiterx_define_constants' );
	}

	function run_immediately() {
		add_action( 'woocommerce_review_order_after_submit', array( $this, 'remove_actions' ), 0 );
	}

	function remove_actions() {
		remove_action( 'woocommerce_review_order_after_submit', 'jupiterx_wc_continue_shopping_button' );
	}
}
