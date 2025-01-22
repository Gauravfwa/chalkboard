<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;

class Weglot extends Base {
	public function is_available() {
		return defined( 'WEGLOT_NAME' );
	}

	public function run_immediately() {
		// Weglot uses output buffering that runs afoul of our error prevention strategies
		wc_maybe_define_constant( 'OBJECTIV_BOOSTER_NO_BUFFER', true );

		add_filter( 'cfw_parsley_locale', array( $this, 'override_parsley_locale' ) );
	}

	function override_parsley_locale( $locale ) {
		if ( ! function_exists( 'weglot_get_service' ) ) {
			return $locale;
		}

		$weglot_locale = weglot_get_service( 'Request_Url_Service_Weglot' )->get_current_language();

		if ( $weglot_locale ) {
			$locale = $weglot_locale;
		}

		return $locale;
	}
}
