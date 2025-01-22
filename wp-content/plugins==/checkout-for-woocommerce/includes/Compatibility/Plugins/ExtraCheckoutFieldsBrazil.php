<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\Base;
use Objectiv\Plugins\Checkout\Main;

class ExtraCheckoutFieldsBrazil extends Base {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return class_exists( '\\Extra_Checkout_Fields_For_Brazil' );
	}

	public function run() {
		add_filter( 'wcbcf_billing_fields', array( $this, 'checkout_billing_fields' ) );
		add_filter( 'wcbcf_shipping_fields', array( $this, 'checkout_shipping_fields' ) );
	}

	function checkout_billing_fields( $fields ) {
		$unmodified_fields = WC()->countries->get_default_address_fields();

		$fields['billing_first_name'] = $unmodified_fields['first_name'];
		$fields['billing_last_name']  = $unmodified_fields['last_name'];
		$fields['billing_address_1']  = $unmodified_fields['address_1'];
		$fields['billing_address_2']  = $unmodified_fields['address_2'];

		if ( isset( $unmodified_fields['company'] ) ) {
			$fields['billing_company'] = $unmodified_fields['company'];
		} else {
			unset( $fields['billing_company'] );
		}

		$fields['billing_country']  = $unmodified_fields['country'];
		$fields['billing_postcode'] = $unmodified_fields['postcode'];
		$fields['billing_state']    = $unmodified_fields['state'];
		$fields['billing_city']     = $unmodified_fields['city'];

		$fields['billing_number']['columns']       = 12;
		$fields['billing_number']['class']         = array();
		$fields['billing_neighborhood']['columns'] = 12;
		$fields['billing_neighborhood']['class']   = array();
		$fields['billing_cellphone']['columns']    = 12;
		$fields['billing_cellphone']['class']      = array();

		if ( isset( $fields['billing_birthdate'] ) ) {
			$fields['billing_birthdate']['columns'] = 12;
			$fields['billing_birthdate']['class']   = array();
		}

		if ( isset( $fields['billing_sex'] ) ) {
			$fields['billing_sex']['columns'] = 12;
			$fields['billing_sex']['class']   = array();
		}

		return $fields;
	}

	function checkout_shipping_fields( $fields ) {
		$cfw               = Main::instance();
		$unmodified_fields = WC()->countries->get_default_address_fields();

		$fields['shipping_first_name'] = $unmodified_fields['first_name'];
		$fields['shipping_last_name']  = $unmodified_fields['last_name'];
		$fields['shipping_address_1']  = $unmodified_fields['address_1'];
		$fields['shipping_address_2']  = $unmodified_fields['address_2'];

		if ( isset( $unmodified_fields['company'] ) ) {
			$fields['shipping_company'] = $unmodified_fields['company'];
		} else {
			unset( $fields['shipping_company'] );
		}

		$fields['shipping_country']  = $unmodified_fields['country'];
		$fields['shipping_postcode'] = $unmodified_fields['postcode'];
		$fields['shipping_state']    = $unmodified_fields['state'];
		$fields['shipping_city']     = $unmodified_fields['city'];

		$fields['shipping_number']['columns']       = 12;
		$fields['shipping_number']['class']         = array();
		$fields['shipping_neighborhood']['columns'] = 12;
		$fields['shipping_neighborhood']['class']   = array();

		if ( $cfw->is_phone_fields_enabled() && ! empty( $unmodified_fields['phone'] ) ) {
			$fields['shipping_phone'] = $unmodified_fields['phone'];
		}

		return $fields;
	}

	function typescript_class_and_params( $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'ExtraCheckoutFieldsBrazil',
			'params' => array(),
		);

		return $compatibility;
	}
}
