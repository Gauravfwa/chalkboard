import Main          from '../../Main';
import Compatibility from '../Compatibility';

class OrderDeliveryDate extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'OrderDeliveryDate' );
    }

    load(): void {
        jQuery( document.body ).one( 'updated_checkout', () => {
            jQuery( 'input[name="shipping_method[0]"]:checked' ).trigger( 'change' );
        } );
    }
}

export default OrderDeliveryDate;
