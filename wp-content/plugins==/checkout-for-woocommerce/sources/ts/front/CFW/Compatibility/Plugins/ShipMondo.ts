import { Alert, AlertInfo } from '../../Components/Alert';
import Main                 from '../../Main';
import TabService           from '../../Services/TabService';
import Compatibility        from '../Compatibility';

class ShipMondo extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     * @param {boolean} load Should load be fired on instantiation
     */
    constructor( main: Main, params ) {
        super( main, params, 'ShipMondo' );
    }

    load( main: Main, params ): void {
        main.tabService.tabContainer.bind( 'easytabs:before', ( event, clicked, target ) => {
            if ( jQuery( target ).attr( 'id' ) === TabService.paymentMethodTabId ) {
                const selectedShippingMethod = jQuery( "input[name='shipping_method[0]']:checked" );

                if ( selectedShippingMethod.length && selectedShippingMethod.val().toString().indexOf( 'shipmondo_shipping_gls' ) !== -1 ) {
                    const shopIdField = jQuery( 'input[name^="shop_ID"]' );

                    if ( shopIdField.length > 0 &&  shopIdField.val().toString() === '' ) {
                        const alertInfo: AlertInfo = {
                            type: 'error',
                            message: params.notice,
                            cssClass: 'cfw-alert-error',
                        };

                        const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
                        alert.addAlert( true );

                        event.stopImmediatePropagation();

                        return false;
                    }
                }
            }
        } );
    }
}

export default ShipMondo;
