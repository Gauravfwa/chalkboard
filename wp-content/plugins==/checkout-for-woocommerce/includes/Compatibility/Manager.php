<?php

namespace Objectiv\Plugins\Checkout\Compatibility;

/**
 * Class Compatibility
 *
 * @link checkoutwc.com
 * @since 1.0.1
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Manager {
	private $_active_modules = array();
	public function __construct() {

		/**
		 * Plugins
		 */
		$this->add_plugin_module( 'WooCommerceCore' );
		$this->add_plugin_module( 'MixPanel' );
		$this->add_plugin_module( 'SkyVergeCheckoutAddons' );
		$this->add_plugin_module( 'Tickera' );
		$this->add_plugin_module( 'PixelCaffeine' );
		$this->add_plugin_module( 'OneClickUpsells' );
		$this->add_plugin_module( 'GoogleAnalyticsPro' );
		$this->add_plugin_module( 'OnePageCheckout' );
		$this->add_plugin_module( 'WooCommerceSubscriptions' );
		$this->add_plugin_module( 'WooCommerceGermanized' );
		$this->add_plugin_module( 'CraftyClicks' );
		$this->add_plugin_module( 'CheckoutManager' );
		$this->add_plugin_module( 'CheckoutFieldEditor' );
		$this->add_plugin_module( 'CheckoutAddressAutoComplete' );
		$this->add_plugin_module( 'NLPostcodeChecker' );
		$this->add_plugin_module( 'PostNL' );
		$this->add_plugin_module( 'ActiveCampaign' );
		$this->add_plugin_module( 'UltimateRewardsPoints' );
		$this->add_plugin_module( 'WooCommerceSmartCoupons' );
		$this->add_plugin_module( 'EUVATNumber' );
		$this->add_plugin_module( 'SkyVergeSocialLogin' );
		$this->add_plugin_module( 'WooCommercePriceBasedOnCountry' );
		$this->add_plugin_module( 'FacebookForWooCommerce' );
		$this->add_plugin_module( 'Webshipper' );
		$this->add_plugin_module( 'OrderDeliveryDate' );
		$this->add_plugin_module( 'OrderDeliveryDateLite' );
		$this->add_plugin_module( 'WooFunnelsOrderBumps' );
		$this->add_plugin_module( 'MartfuryAddons' );
		$this->add_plugin_module( 'WCFieldFactory' );
		$this->add_plugin_module( 'MondialRelay' );
		$this->add_plugin_module( 'SUMOPaymentPlans' );
		$this->add_plugin_module( 'WooCommerceAddressValidation' );
		$this->add_plugin_module( 'ElementorPro' );
		$this->add_plugin_module( 'SendCloud' );
		$this->add_plugin_module( 'CO2OK' );
		$this->add_plugin_module( 'DiviUltimateHeader' );
		$this->add_plugin_module( 'DiviUltimateFooter' );
		$this->add_plugin_module( 'ExtraCheckoutFieldsBrazil' );
		$this->add_plugin_module( 'MyCredPartialPayments' );
		$this->add_plugin_module( 'CountryBasedPayments' );
		$this->add_plugin_module( 'GermanMarket' );
		$this->add_plugin_module( 'StrollikCore' );
		$this->add_plugin_module( 'WooCommerceCheckoutFieldEditor' );
		$this->add_plugin_module( 'IndeedAffiliatePro' );
		$this->add_plugin_module( 'ShipMondo' );
		$this->add_plugin_module( 'Chronopost' );
		$this->add_plugin_module( 'JupiterXCore' );
		$this->add_plugin_module( 'OxygenBuilder' );
		$this->add_plugin_module( 'Fattureincloud' );
		$this->add_plugin_module( 'CSSHero' );
		$this->add_plugin_module( 'NIFPortugal' );
		$this->add_plugin_module( 'WooCommerceOrderDelivery' );
		$this->add_plugin_module( 'PortugalVaspKios' );
		$this->add_plugin_module( 'WPCProductBundles' );
		$this->add_plugin_module( 'YITHDeliveryDate' );
		$this->add_plugin_module( 'CartFlows' );
		$this->add_plugin_module( 'PWGiftCardsPro' );
		$this->add_plugin_module( 'UpsellOrderBumpOffer' );
		$this->add_plugin_module( 'NextGenGallery' );
		$this->add_plugin_module( 'Weglot' );
		$this->add_plugin_module( 'WooCommerceGiftCards' ); // WooCommerce Gift Cards (official)
		$this->add_plugin_module( 'BeaverThemer' );
		$this->add_plugin_module( 'WooCommerceCarrierAgents' );
		$this->add_plugin_module( 'WooCommerceAdvancedMessages' );
		$this->add_plugin_module( 'WooCommerceServices' );
		$this->add_plugin_module( 'SalientWPBakery' );
		$this->add_plugin_module( 'WPWebWooCommerceSocialLogin' );
		$this->add_plugin_module( 'WCPont' );
		$this->add_plugin_module( 'MailerLite' );
		$this->add_plugin_module( 'ApplyOnline' );
		$this->add_plugin_module( 'YITHCompositeProducts' );
		$this->add_plugin_module( 'WooCommerceExtendedCouponFeaturesPro' );
		$this->add_plugin_module( 'WooCommerceSubscriptionGifting' );
		$this->add_plugin_module( 'WooCommerceGermanMarket' );
		$this->add_plugin_module( 'IconicWooCommerceDeliverySlots' );
		$this->add_plugin_module( 'MyShipper' );
		$this->add_plugin_module( 'EnhancedEcommerceGoogleAnalytics' );
		$this->add_plugin_module( 'WooCommercePointsandRewards' );
		$this->add_plugin_module( 'SavedAddressesForWooCommerce' );
		$this->add_plugin_module( 'TranslatePress' );
		$this->add_plugin_module( 'SUMOSubscriptions' );

		/**
		 * Gateways
		 */
		$this->add_gateway_module( 'PayPalCheckout' );
		$this->add_gateway_module( 'Stripe' );
		$this->add_gateway_module( 'PayPalForWooCommerce' );
		$this->add_gateway_module( 'Braintree' );
		$this->add_gateway_module( 'BraintreeForWooCommerce' );
		$this->add_gateway_module( 'AmazonPay' );
		$this->add_gateway_module( 'KlarnaCheckout' );
		$this->add_gateway_module( 'KlarnaPayment' );
		$this->add_gateway_module( 'AfterPayKrokedil' );
		$this->add_gateway_module( 'ToCheckout' );
		$this->add_gateway_module( 'In3' );
		$this->add_gateway_module( 'InpsydePayPalPlus' );
		$this->add_gateway_module( 'WooSquarePro' );
		$this->add_gateway_module( 'PayPalPlusCw' );
		$this->add_gateway_module( 'PostFinance' );
		$this->add_gateway_module( 'Square' );
		$this->add_gateway_module( 'StripeWooCommerce' );
		$this->add_gateway_module( 'WooCommercePensoPay' );
		$this->add_gateway_module( 'Vipps' );

		/**
		 * Themes
		 */
		$this->add_theme_module( 'Avada' );
		$this->add_theme_module( 'Porto' );
		$this->add_theme_module( 'GeneratePress' );
		$this->add_theme_module( 'TMOrganik' );
		$this->add_theme_module( 'BeaverBuilder' );
		$this->add_theme_module( 'Astra' );
		$this->add_theme_module( 'Savoy' );
		$this->add_theme_module( 'OceanWP' );
		$this->add_theme_module( 'Atelier' );
		$this->add_theme_module( 'Jupiter' );
		$this->add_theme_module( 'The7' );
		$this->add_theme_module( 'Zidane' );
		$this->add_theme_module( 'Atik' );
		$this->add_theme_module( 'Optimizer' );
		$this->add_theme_module( 'Verso' );
		$this->add_theme_module( 'Listable' );
		$this->add_theme_module( 'Flevr' );
		$this->add_theme_module( 'Divi' );
		$this->add_theme_module( 'Electro' );
		$this->add_theme_module( 'JupiterX' );
		$this->add_theme_module( 'Blaszok' );
		$this->add_theme_module( 'Konte' );
		$this->add_theme_module( 'Genesis' );
		$this->add_theme_module( 'TheBox' );
		$this->add_theme_module( 'Barberry' );
		$this->add_theme_module( 'Stockie' );
		$this->add_theme_module( 'Tokoo' );
		$this->add_theme_module( 'FuelThemes' );
		$this->add_theme_module( 'SpaSalonPro' );
		$this->add_theme_module( 'Shoptimizer' );
		$this->add_theme_module( 'Flatsome' );
		$this->add_theme_module( 'Pro' );
	}

	/**
	 * @param string $class
	 * @param string $type
	 * @return mixed
	 */
	public function add_module( string $class, string $type = 'Plugins' ) {
		$key    = $class;
		$prefix = 'Objectiv\\Plugins\\Checkout\\Compatibility';

		$class = "$prefix\\$type\\$class";

		$this->_active_modules[ $key ] = new $class();

		return $this->_active_modules[ $key ];
	}

	/**
	 * @param string $class
	 */
	public function add_plugin_module( string $class ) {
		$this->add_module( $class );
	}

	/**
	 * @param string $class
	 */
	public function add_gateway_module( string $class ) {
		$this->add_module( $class, 'Gateways' );
	}

	/**
	 * @param string $class
	 */
	public function add_theme_module( string $class ) {
		$this->add_module( $class, 'Themes' );
	}

	/**
	 * @return array
	 */
	public function get_active_modules(): array {
		return $this->_active_modules;
	}
}
