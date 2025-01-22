import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WooCommerceGermanized extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     * @param {boolean} load Should load be fired on instantiation
     */
    constructor( main: Main, params ) {
        super( main, params, 'WooCommerceGermanized' );
    }

    load( main: Main ): void {
        jQuery( window ).on( 'load', () => {
            jQuery( document ).off( 'change', '.payment_methods input[name="payment_method"]' );
        } );
    }
}

export default WooCommerceGermanized;
