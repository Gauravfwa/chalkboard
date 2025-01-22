import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WooFunnelsOrderBumps extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'WooFunnelsOrderBumps' );
    }

    load( main: Main ): void {
        jQuery( document.body ).on( 'wfob_bump_trigger', () => {
            main.updateCheckoutService.queueUpdateCheckout();
        } );
    }
}

export default WooFunnelsOrderBumps;
