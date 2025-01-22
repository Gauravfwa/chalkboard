import Main          from '../../Main';
import Compatibility from '../Compatibility';

class PayPalPlusCw extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'PayPalPlusCw' );
    }

    load( main: Main ): void {
        jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
            main.updateCheckoutService.queueUpdateCheckout();
        } );
    }
}

export default PayPalPlusCw;
