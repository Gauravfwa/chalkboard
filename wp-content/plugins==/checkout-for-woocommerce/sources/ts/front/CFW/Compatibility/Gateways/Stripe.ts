import Main          from '../../Main';
import TabService    from '../../Services/TabService';
import Compatibility from '../Compatibility';

class Stripe extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'Stripe' );
    }

    load( main: Main ): void {
        jQuery( document ).on( 'stripeError', this.onError );
    }

    onError(): void {
        window.location.hash = TabService.paymentMethodTabId;
    }
}

export default Stripe;
