import { AlertInfo, Alert }  from '../Components/Alert';
import Main                  from '../Main';
import DataService           from '../Services/DataService';
import LoggingService        from '../Services/LoggingService';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';
import { AjaxInfo }          from '../Types/Types';
import Action                from './Action';

/* eslint-disable no-debugger, no-console */

class CompleteOrderAction extends Action {
    /**
     *
     * @param id
     * @param ajaxInfo
     * @param checkoutData
     */
    constructor( id: string, ajaxInfo: AjaxInfo, checkoutData: any ) {
        super( id, checkoutData );

        Main.addOverlay();

        this.setup();
    }

    /**
     *
     */
    setup(): void {
        DataService.checkoutForm.off( 'form:validate' );

        this.load();
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        try {
            if ( resp.result === 'success' && DataService.checkoutForm.triggerHandler( 'checkout_place_order_success' ) !== false ) {
                // Fire events that need to run before redirect
                jQuery( document.body ).trigger( 'cfw-order-complete-before-redirect', [ DataService.checkoutForm, resp ] );
                LoggingService.logEvent( 'Fired cfw-order-complete-before-redirect event.' );

                Main.instance.parsleyService.destroy();

                if ( resp.redirect.indexOf( 'https://' ) === -1 || resp.redirect.indexOf( 'http://' ) === -1 ) {
                    ( <any>window ).location = resp.redirect;
                } else {
                    ( <any>window ).location = decodeURI( resp.redirect );
                }
            } else if ( resp.result === 'failure' ) {
                throw new Error( 'Result failure' );
            } else {
                throw new Error( 'Invalid response' );
            }
        } catch ( err ) {
            // Amazon Pay triggers update_checkout after a failed submission
            // This prevents the generated alerts from being immediately scrubbed.
            if ( Main.instance ) {
                Main.instance.preserveAlerts = true;
            }

            // Reload page
            if ( resp.reload === true ) {
                ( <any>window ).location.reload();
                return;
            }

            ( <any>window ).dispatchEvent( new CustomEvent( 'cfw-checkout-failed-before-error-message', { detail: { response: resp } } ) );

            Alert.removeAlerts( Main.getAlertContainer() );

            if ( resp.messages !== '' ) {
                // Wrapping the response in a <div /> is required for correct parsing
                const messages = jQuery( jQuery.parseHTML( `<div>${resp.messages}</div>` ) );

                // Errors
                const woocommerceErrorMessages = messages.find( '.woocommerce-error li' ).length ? messages.find( '.woocommerce-error li' ) : messages.find( '.woocommerce-error' );

                jQuery.each( woocommerceErrorMessages, ( i, el ) => {
                    const alert: Alert = new Alert( Main.getAlertContainer(), <AlertInfo> {
                        type: 'error',
                        message: jQuery.trim( jQuery( el ).text() ),
                        cssClass: 'cfw-alert-error',
                    } );
                    alert.addAlert();
                } );

                // Info
                const wooCommerceInfoMessages = messages.find( '.woocommerce-info li' ).length ? messages.find( '.woocommerce-info li' ) : messages.find( '.woocommerce-info' );

                jQuery.each( wooCommerceInfoMessages, ( i, el ) => {
                    const alert: Alert = new Alert( Main.getAlertContainer(), <AlertInfo> {
                        type: 'notice',
                        message: jQuery.trim( jQuery( el ).text() ),
                        cssClass: 'cfw-alert-info',
                    } );
                    alert.addAlert();
                } );

                // Messages
                const wooCommerceMessages = messages.find( '.woocommerce-message li' ).length ? messages.find( '.woocommerce-message li' ) : messages.find( '.woocommerce-message' );

                jQuery.each( wooCommerceMessages, ( i, el ) => {
                    const alert: Alert = new Alert( Main.getAlertContainer(), <AlertInfo> {
                        type: 'success',
                        message: jQuery.trim( jQuery( el ).text() ),
                        cssClass: 'cfw-alert-success',
                    } );
                    alert.addAlert();
                } );

                // EveryPay doesn't understand WooCommerce, so fix it for them
                if ( resp.messages.includes( '<script' ) ) {
                    jQuery( document.body ).prepend( `<div style="display:none">${resp.messages}</div>` );
                }
            } else {
                /**
                 * If the payment gateway comes back with no message, show a generic error.
                 */
                const alertInfo: AlertInfo = {
                    type: 'error',
                    message: 'An unknown error occurred. Response from payment gateway was empty.',
                    cssClass: 'cfw-alert-error',
                };

                const alert: Alert = new Alert( Main.getAlertContainer(), alertInfo );
                alert.addAlert();

                // Console log the error + raw response
                console.log( alertInfo.message );
                console.log( resp );
            }

            // Reload page
            if ( resp.reload === true ) {
                window.location.reload();
                return;
            }

            // Trigger update in case we need a fresh nonce
            if ( resp.refresh === true ) {
                UpdateCheckoutService.triggerUpdatedCheckout();
            }

            this.submitError();
        }
    }

    /**
     * Try to fix invalid JSON
     *
     * @param rawResponse
     * @param dataType
     */
    public dataFilter( rawResponse: string, dataType: string ): any {
        // We only want to work with JSON
        if ( dataType !== 'json' ) {
            return rawResponse;
        }

        if ( this.isValidJSON( rawResponse ) ) {
            return rawResponse;
        }
        // Attempt to fix the malformed JSON
        const maybeValidJSON = rawResponse.match( /{"result.*}/ );

        if ( maybeValidJSON === null ) {
            console.log( 'Unable to fix malformed JSON' );
        } else if ( this.isValidJSON( maybeValidJSON[ 0 ] ) ) {
            console.log( 'Fixed malformed JSON. Original:' );
            console.log( rawResponse );
            // eslint-disable-next-line no-param-reassign,prefer-destructuring
            rawResponse = maybeValidJSON[ 0 ];
        } else {
            console.log( 'Unable to fix malformed JSON' );
        }

        return rawResponse;
    }

    public isValidJSON( rawJSON ) {
        try {
            const json = jQuery.parseJSON( rawJSON );

            return ( json && typeof json === 'object' );
        } catch ( e ) {
            return false;
        }
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        let message: string;

        if ( xhr.status === 0 ) {
            message = 'Could not connect to server. Please refresh and try again or contact site administrator.';
        } else if ( xhr.status === 404 ) {
            message = 'Requested resource could not be found. Please contact site administrator. (404)';
        } else if ( xhr.status === 500 ) {
            message = 'An internal server error occurred. Please contact site administrator. (500)';
        } else if ( textStatus === 'parsererror' ) {
            message = 'Server response could not be parsed. Please contact site administrator.';
        } else if ( textStatus === 'timeout' || xhr.status === 504 ) {
            message = 'The server timed out while processing your request. Please refresh and try again or contact site administrator.';
        } else if ( textStatus === 'abort' ) {
            message = 'Request was aborted. Please contact site administrator.';
        } else {
            message = `Uncaught Error: ${xhr.responseText}`;
        }

        console.log( `CheckoutWC XHR response: ${xhr.response}` );
        console.log( `CheckoutWC XHR responseText: ${xhr.responseText}` );
        console.log( `CheckoutWC XHR status: ${xhr.status}` );
        console.log( `CheckoutWC XHR errorThrown: ${errorThrown}` );

        const alertInfo: AlertInfo = {
            type: 'error',
            message,
            cssClass: 'cfw-alert-error',
        };

        const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
        alert.addAlert();

        this.submitError();
    }

    submitError() {
        // Remove processing / unblock it
        DataService.checkoutForm.removeClass( 'processing' ).unblock();

        // Fire checkout_error
        jQuery( document.body ).trigger( 'checkout_error' );
        LoggingService.logEvent( 'Fired checkout_error event.' );
    }
}

export default CompleteOrderAction;
