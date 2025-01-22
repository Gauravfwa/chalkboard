import Main                  from '../../Main';
import UpdateCheckoutService from '../../Services/UpdateCheckoutService';
import Compatibility         from '../Compatibility';

class PayPalForWooCommerce extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'PayPalForWooCommerce' );
    }

    load(): void {
        /**
         * Fix for 2.1.10+
         *
         * PayPal for WooCommerce sets window.location.href to itself, which by default doesn't
         * do anything because it treats this like a hash change. So, we use a variable to track
         * the current hash on page load and then determine if the hash has changed or if it has
         * been set to itself. If set to itself, we reload the page
         *
         * Otherwise, we update the currentHash variable. Yes it's hacky, but yes it works.
         */
        let currentHash = window.location.hash;

        window.addEventListener( 'popstate', () => {
            if ( window.location.hash === currentHash ) {
                window.location.reload();
            }

            currentHash = window.location.hash;
        } );
    }
}

export default PayPalForWooCommerce;
