import Main          from '../../Main';
import Compatibility from '../Compatibility';

declare let wc_braintree_credit_card_handler: any;
declare let wc_braintree_paypal_handler: any;

/**
 * Helper compatibility class for the Braintree plugin
 */
class Braintree extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'Braintree' );
    }

    /**
     * Loads the Braintree compatibility class
     *
     * @param {Main} main
     * @param {any} params
     */
    load( main: Main, params: any ): void {
        if ( params.cc_gateway_available ) {
            jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
                this.creditCardRefresh();
                this.savedPaymentMethods();
            } );
        }

        if ( params.paypal_gateway_available ) {
            jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
                this.paypalRefresh();
            } );
        }
    }

    /**
     * Calls the refresh_braintree method on the credit card handler. Resets the state back to default
     */
    creditCardRefresh(): void {
        if ( typeof wc_braintree_credit_card_handler !== 'undefined' ) {
            wc_braintree_credit_card_handler.refresh_braintree();
        }
    }

    paypalRefresh(): void {
        if ( typeof wc_braintree_paypal_handler !== 'undefined' ) {
            wc_braintree_paypal_handler.setup_braintree();
            wc_braintree_paypal_handler.handle_saved_payment_methods();
        }
    }

    savedPaymentMethods(): void {
        jQuery( '.wc-braintree-credit-card-new-payment-method-form .form-row' ).css( 'display', 'block' );
    }
}

export default Braintree;
