import { Alert }             from '../Components/Alert';
import Main                  from '../Main';
import LoggingService        from '../Services/LoggingService';
import TabService            from '../Services/TabService';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';
import { AjaxInfo }          from '../Types/Types';
import Action                from './Action';

class UpdateCheckoutAction extends Action {
    /**
     *
     */
    private static _underlyingRequest: any = null;

    private static _fragments: any = [];

    /**
     *
     */
    // eslint-disable-next-line max-len
    private static _blockUISelector: string = '#cfw-billing-methods, #cfw-shipping-method-address-review, #cfw-shipping-methods, #cfw-cart-summary, #cfw-place-order, #cfw-payment-method-address-review';

    /**
     * @param {string} id
     * @param {AjaxInfo} ajaxInfo
     * @param fields
     * @param args
     */
    constructor( id: string, ajaxInfo: AjaxInfo, fields: any ) {
        super( id, fields );
    }

    public load(): void {
        this.blockUI();

        if ( UpdateCheckoutAction.underlyingRequest !== null ) {
            UpdateCheckoutAction.underlyingRequest.abort();
        }

        UpdateCheckoutAction.underlyingRequest = jQuery.post( this.url, this.data, this.response.bind( this ) );
    }

    public blockUI(): void {
        jQuery( UpdateCheckoutAction.blockUISelector ).not( '.blocked' ).block( {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6,
            },
        } );
    }

    public unblockUI(): void {
        jQuery( UpdateCheckoutAction.blockUISelector ).unblock().removeClass( 'blocked' );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
        // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        if ( resp.redirect !== false ) {
            window.location = resp.redirect;
        }

        // Payment methods
        const updatedPaymentMethodsContainer = jQuery( '#cfw-billing-methods' );

        /**
         * Save payment details to a temporary object
         */
        const paymentBoxInputsSelector = '.payment_box :input';
        const paymentBoxInputs =  jQuery( paymentBoxInputsSelector );
        const paymentDetails = {};
        paymentBoxInputs.each( function () {
            const ID = jQuery( this ).attr( 'id' );

            if ( ID ) {
                if ( jQuery.inArray( jQuery( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                    paymentDetails[ ID ] = jQuery( this ).prop( 'checked' );
                } else {
                    paymentDetails[ ID ] = jQuery( this ).val();
                }
            }
        } );

        /**
         * Update Fragments
         *
         * For our elements as well as those from other plugins
         */
        if ( resp.fragments ) {
            jQuery.each( resp.fragments, ( key: any, value ) => {
                // eslint-disable-next-line max-len
                if ( !Object.keys( UpdateCheckoutAction._fragments ).length || UpdateCheckoutAction.cleanseFragments( UpdateCheckoutAction._fragments[ key ] ) !== UpdateCheckoutAction.cleanseFragments( value ) ) {
                    /**
                     * Make sure value is truthy
                     *
                     * Because if it's false (say for Amazon Pay) we don't want to replace anything
                     */
                    if ( typeof value === 'string' ) {
                        jQuery( key ).replaceWith( value );
                    }
                }
            } );

            UpdateCheckoutAction._fragments = resp.fragments;
        }

        if ( !resp.show_shipping_tab ) {
            jQuery( 'body' ).addClass( 'cfw-hide-shipping' );

            // In case the current tab gets hidden
            if ( Main.instance.tabService.getCurrentTab().is( ':hidden' ) ) {
                TabService.go( Main.instance.tabService.getCurrentTab().prev().attr( 'id' ) );
            }
        } else {
            jQuery( 'body' ).removeClass( 'cfw-hide-shipping' );
        }

        /**
         * Fill in the payment details if possible without overwriting data if set.
         */
        if ( !jQuery.isEmptyObject( paymentDetails ) ) {
            jQuery( paymentBoxInputsSelector ).each( function () {
                const ID = jQuery( this ).attr( 'id' );
                const val = jQuery( this ).val();

                if ( ID ) {
                    if ( jQuery.inArray( jQuery( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                        jQuery( this ).prop( 'checked', paymentDetails[ ID ] ).trigger( 'change' );
                    } else if ( val !== null && val.toString().length === 0 ) {
                        jQuery( this ).val( paymentDetails[ ID ] ).trigger( 'change' );
                    }
                }
            } );
        }

        const alerts = [];

        if ( resp.notices.success ) {
            Object.keys( resp.notices.success ).forEach( ( key: any ) => {
                alerts.push( {
                    type: 'success',
                    message: resp.notices.success[ key ],
                    cssClass: 'cfw-alert-success',
                } );
            } );
        }

        if ( resp.notices.notice ) {
            Object.keys( resp.notices.notice ).forEach( ( key: any ) => {
                alerts.push( {
                    type: 'notice',
                    message: resp.notices.notice[ key ],
                    cssClass: 'cfw-alert-info',
                } );
            } );
        }

        if ( resp.notices.error ) {
            Object.keys( resp.notices.error ).forEach( ( key: any ) => {
                alerts.push( {
                    type: 'error',
                    message: resp.notices.error[ key ],
                    cssClass: 'cfw-alert-error',
                } );
            } );
        }

        if ( !Main.instance.preserveAlerts ) {
            Alert.removeAlerts( Main.getAlertContainer() );
        }

        Main.instance.preserveAlerts = false;

        if ( alerts.length > 0 ) {
            alerts.forEach( ( alertInfo: any ) => {
                const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
                alert.addAlert();
            } );
        }

        /**
         * Unblock UI
         */
        this.unblockUI();

        /**
         * Init selected gateway again
         *
         * Matches WooCommerce core checkout.js
         */
        Main.instance.paymentGatewaysService.initSelectedPaymentGateway();

        jQuery( document.body ).trigger( 'cfw_pre_updated_checkout', [ resp ] );
        LoggingService.logEvent( 'Fired cfw_pre_updated_checkout event.' );

        jQuery( document.body ).trigger( 'updated_cart_totals' );
        LoggingService.logEvent( 'Fired updated_cart_totals event.' );

        UpdateCheckoutService.triggerUpdatedCheckout( resp );

        updatedPaymentMethodsContainer.unblock();
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        /**
         * Unblock UI
         */
        this.unblockUI();

        // eslint-disable-next-line no-console
        console.log( `Update Checkout Error: ${errorThrown} (${textStatus})` );
    }

    /**
     * Cleanses our beautiful fragments of evil dirty bad stuff
     *
     * @param value
     * @returns {string}
     */
    static cleanseFragments( value: string ) {
        if ( typeof value !== 'string' ) {
            return value;
        }

        return value.replace( /checked='checked' data-order_button_text/g, 'data-order_button_text' )
            .replace( /reveal-content" style="display:none;">/g, 'reveal-content">' )
            .replace( /cfw-radio-reveal-li cfw-active">/g, 'cfw-radio-reveal-li">' )
            .replace( /cfw-radio-reveal-li ">/g, 'cfw-radio-reveal-li">' )
            .replace( /cfw-radio-reveal-content" >/g, 'cfw-radio-reveal-content">' );
    }

    /**
     * @returns {any}
     */
    static get underlyingRequest(): any {
        return this._underlyingRequest;
    }

    /**
     * @param value
     */
    static set underlyingRequest( value: any ) {
        this._underlyingRequest = value;
    }

    static get blockUISelector(): string {
        return this._blockUISelector;
    }

    static set blockUISelector( value: string ) {
        this._blockUISelector = value;
    }
}

export default UpdateCheckoutAction;
