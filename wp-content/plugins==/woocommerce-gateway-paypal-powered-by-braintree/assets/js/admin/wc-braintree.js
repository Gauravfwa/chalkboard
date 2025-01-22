/*!
 * WooCommerce Braintree Admin Javascript
 * Version 2.5.0
 *
 * Copyright (c) 2016-2021, Automattic, Inc.
 * Licensed under the GNU General Public License v3.0
 * http://www.gnu.org/licenses/gpl-3.0.html
 */
jQuery( document ).ready( ( $ ) => {

	'use strict';

	// get button sizes from localized params
	var buttonSizes = wc_braintree_admin_params.button_sizes;

	var defaultButton;
	var paylaterButton;
	var hasRenderedDefaultButton = false;
	var hasRenderedPaylaterButton = false;
	var renderingDefaultButton = false;
	var renderingPaylaterButton = false;

	var $payLaterMessageContainer = $( '#wc_braintree_paypal_pay_later_message_preview' );
	var payLaterMessage = null;

	var configurePayLaterMessaging = function() {

		$payLaterMessageContainer.attr( 'data-pp-style-logo-type', getPayLaterMessagingLogoType() );
		$payLaterMessageContainer.attr( 'data-pp-style-logo-position', $( '#woocommerce_braintree_paypal_pay_later_messaging_logo_position' ).val() );
		$payLaterMessageContainer.attr( 'data-pp-style-text-color', $( '#woocommerce_braintree_paypal_pay_later_messaging_text_color' ).val() );

		if ( ! payLaterMessage && $payLaterMessageContainer.length ) {

			payLaterMessage = paypal.Messages({
				/**
				 * Sets the Pay Later messaging amount after the first render to workaround an uncaught error that
				 * causes no message to show up the first time.
				 *
				 * The error starts with a warning: Invalid option value (currency). Expected USD but received "EUR",
				 * followed by Uncaught TypeError: Cannot read property 'payload' of undefined.
				 */
				onRender: function() {
					$payLaterMessageContainer.attr('data-pp-amount', 100);
				}
			});

			payLaterMessage
				.render( '#' + $payLaterMessageContainer.attr( 'id' ) )
				.catch( ( error ) => console.log( error ) );
		}
	};

	/**
	 * Gets the selected Pay Later Messaging logo type.
	 *
	 * @return {string}
	 */
	var getPayLaterMessagingLogoType = function() {

		return $( '#woocommerce_braintree_paypal_pay_later_messaging_logo_type' ).val();
	}

	$( '#woocommerce_braintree_paypal_enable_paypal_pay_later' ).on( 'change', function() {

		$( '.pay-later-field' ).closest( 'tr' ).toggle( $( this ).prop( 'checked' ) && $( this ).is( ':visible' ) );
	} ).change();

	$( '#woocommerce_braintree_paypal_pay_later_messaging_logo_type' ).on( 'change', function() {

		$( '#woocommerce_braintree_paypal_pay_later_messaging_logo_position' ).closest( 'tr' ).toggle( getPayLaterMessagingLogoType() === 'primary' );
	} ).change();

	$( '#woocommerce_braintree_paypal_button_color, #woocommerce_braintree_paypal_button_size, #woocommerce_braintree_paypal_button_shape, #woocommerce_braintree_paypal_enable_paypal_pay_later, #woocommerce_braintree_paypal_pay_later_messaging_text_color, #woocommerce_braintree_paypal_pay_later_messaging_logo_type, #woocommerce_braintree_paypal_pay_later_messaging_logo_position' ).on( 'change', function() {

		if ( defaultButton && defaultButton.close && hasRenderedDefaultButton ) {
			defaultButton.close().then( () => {
				$( '#wc_braintree_paypal_button_preview' ).empty();
			} );
		}

		if ( paylaterButton && paylaterButton.close && hasRenderedPaylaterButton ) {
			paylaterButton.close().then( () => {
				$( '#wc_braintree_paypal_button_preview_paylater' ).empty();
			} );
		}

		hasRenderedDefaultButton = false;
		hasRenderedPaylaterButton = false;

		var color         = $( '#woocommerce_braintree_paypal_button_color' ).val();
		var size          = $( '#woocommerce_braintree_paypal_button_size' ).val();
		var shape         = $( '#woocommerce_braintree_paypal_button_shape' ).val();
		var offerPayLater = $( '#woocommerce_braintree_paypal_enable_paypal_pay_later' ).is( ':checked' );

		var defaultParams = {
			env: 'sandbox',
			style: {
				label: 'pay',
				color: color,
				shape: shape,
				layout: 'vertical',
				tagline: false,
			},
			fundingSource: paypal.FUNDING.PAYPAL,
			client: {
				sandbox: 'sandbox',
			},
			payment: function( data, actions ) {
				return actions.payment.create( {
					payment: {
						transactions: [
							{
								amount: { total: '0.01', currency: 'USD' }
							}
						]
					}
				} );
			},
			onAuthorize: function( data, actions ) {}
		};

		if ( size !== 'responsive' ) {
			defaultParams.style.height = buttonSizes[size].height;
		}

		configurePayLaterMessaging();

		// show the Pay Later messaging only when we intend to show the Pay Later button
		$payLaterMessageContainer.toggle( offerPayLater );

		if ( ! hasRenderedDefaultButton && ! renderingDefaultButton ) {

			renderingDefaultButton = true;

			defaultButton = paypal.Buttons(defaultParams);

			if ( size !== 'responsive' ) {
				$( '#wc_braintree_paypal_button_preview' ).width( buttonSizes[size].width );
			} else {
				$( '#wc_braintree_paypal_button_preview' ).width( '100%' );
			}

			defaultButton.render( '#wc_braintree_paypal_button_preview' ).then( () => {
				hasRenderedDefaultButton = true;
				renderingDefaultButton = false;
			} );
		}

		if ( offerPayLater && ! hasRenderedPaylaterButton && ! renderingPaylaterButton ) {

			renderingPaylaterButton = true;

			var paylaterParams = defaultParams;
			paylaterParams.fundingSource = paypal.FUNDING.PAYLATER;

			paylaterButton = paypal.Buttons( paylaterParams );
			if ( paylaterButton.isEligible() ) {

				if ( size !== 'responsive' ) {
					$( '#wc_braintree_paypal_button_preview_paylater' ).width( buttonSizes[size].width );
				} else {
					$( '#wc_braintree_paypal_button_preview_paylater' ).width( '100%' );
				}

				paylaterButton.render( '#wc_braintree_paypal_button_preview_paylater' ).then( () => {
					hasRenderedPaylaterButton = true;
					renderingPaylaterButton = false;
				} );
			}
		}

	} ).first().change();
} );
