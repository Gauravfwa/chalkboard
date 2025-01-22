=== Price Based on Country for WooCommerce ===
Contributors: oscargare
Tags: price based country, dynamic price based country, price by country, dynamic price, woocommerce, geoip, country-targeted pricing
Requires at least: 3.8
Tested up to: 6.3
Stable tag: 3.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add multicurrency support to WooCommerce, allowing you set product's prices in multiple currencies based on country of your site's visitor.

== Description ==

**Price Based on Country for WooCommerce** allows you to sell the same product in multiple currencies based on the country of the customer.

= How it works =

The plugin detects automatically the country of the website visitor throught the geolocation feature included in WooCommerce (2.3.0 or later) and display the currency and price you have defined previously for this country.

You have two ways to set product's price for each country:

* Calculate price by applying the exchange rate.
* Set price manually.

When country changes on checkout page, the cart, the order preview and all shop are updated to display the correct currency and pricing.

= Multicurrency =
Sell and receive payments in different currencies, reducing the costs of currency conversions.

= Country Switcher =
The extension include a country switcher widget to allow your customer change the country from the frontend of your website.

= Shipping currency conversion =
Apply currency conversion to Flat and International Flat Rate Shipping.

= Compatible with WPML =
WooCommerce Product Price Based on Countries is officially compatible with [WPML](https://wpml.org/extensions/woocommerce-product-price-based-countries/).

= Upgrade to Pro =

>This plugin offers a Pro addon which adds the following features:

>* Guaranteed support by private ticket system.
>* Automatic updates of exchange rates.
>* Add an exchange rate fee.
>* Round to nearest.
>* Display the currency code next to price.
>* Compatible with the WooCommerce built-in CSV importer and exporter.
>* Thousand separator, decimal separator and number of decimals by pricing zone.
>* Currency switcher widget.
>* Support to WooCommerce Subscriptions by Prospress .
>* Support to WooCommerce Product Bundles by SomewhereWarm .
>* Support to WooCommerce Product Add-ons by WooCommerce .
>* Support to WooCommerce Bookings by WooCommerce .
>* Support to WooCommerce Composite Product by SomewhereWarm.
>* Support to WooCommerce Name Your Price by Kathy Darling.
>* Bulk editing of variations princing.
>* Support for manual orders.
>* More features and integrations is coming.

>[Get Price Based on Country Pro now](https://www.pricebasedcountry.com?utm_source=wordpress.org&utm_medium=readme&utm_campaign=Extend)

= Requirements =

* WooCommerce 3.4 or later.
* If you want to receive payments in more of one currency, a payment gateway that supports them.

== Installation ==

1. Download, install and activate the plugin.
1. Go to WooCommerce -> Settings -> Product Price Based on Country and configure as required.
1. Go to the product page and sets the price for the countries you have configured avobe.

= Adding a country selector to the front-end =

Once you’ve added support for multiple country and their currencies, you could display a country selector in the theme. You can display the country selector with a shortcode or as a hook.

**Shortcode**

[wcpbc_country_selector other_countries_text="Other countries"]

**PHP Code**

do_action('wcpbc_manual_country_selector', 'Other countries');

= Customize country selector (only for developers) =

1. Add action "wcpbc_manual_country_selector" to your theme.
1. To customize the country selector:
	1. Create a directory named "woocommerce-product-price-based-on-countries" in your theme directory.
	1. Copy to the directory created avobe the file "country-selector.php" included in the plugin.
	1. Work with this file.

== Frequently Asked Questions ==

= How might I test if the prices are displayed correctly for a given country? =

If you are in a test environment, you can configure the test mode in the setting page.

In a production environment you can use a privacy VPN tools like [TunnelBear](https://www.tunnelbear.com/) or [ZenMate](https://zenmate.com/)

You should do the test in a private browsing window to prevent data stored in the session. Open a private window on [Firefox](https://support.mozilla.org/en-US/kb/private-browsing-use-firefox-without-history#w_how-do-i-open-a-new-private-window) or on [Chrome](https://support.google.com/chromebook/answer/95464?hl=en)

== Screenshots ==

1. Simple to get started with the Geolocation setup wizard.
2. Unlimited price zones.
3. Pricing zone properties.
4. Pricing zone properties (2).
5. Plugin settings.
6. Set the price manually or calculate by the exchange rate.
7. Includes a country selector widget.

== Changelog ==

= 3.3.1 (2023-10-24) =
* Fixed: Integrations are loaded when WooCommerce is not active.
* Fixed: The get_zone_from_order function fails when the parameter is a WC_Order instance.
* Fixed: Restore compatibility with the Porto theme skeleton screens in the "AJAX geolocation".

= 3.3.0 (2023-09-26) =
* Added: Improve compatibility with the infinite scroll feature included in themes or plugins.
* Added: License page style improvements.

[See changelog for all versions](https://plugins.svn.wordpress.org/woocommerce-product-price-based-on-countries/trunk/changelog.txt).

== Upgrade Notice ==

= 3.2 =
<strong>3.2 is a major update</strong>. If you use the Pro version, you must update it to the latest version.