import { Alert, AlertInfo } from '../../Components/Alert';
import Main                 from '../../Main';
import TabService           from '../../Services/TabService';
import Compatibility        from '../Compatibility';

class MyShipper extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'MyShipper' );
    }

    load( main: Main, params ): void {
        const easyTabsWrap: any = main.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:after', () => {
            if ( Main.instance.tabService.getCurrentTab().attr( 'id' ) === TabService.shippingMethodTabId ) {
                Main.instance.updateCheckoutService.triggerUpdateCheckout();
            }
        } );

        easyTabsWrap.bind( 'easytabs:before', ( event, clicked, target ) => {
            if ( jQuery( target ).attr( 'id' ) === TabService.paymentMethodTabId ) {
                const selectedShippingMethod = jQuery( "input[name='shipping_method[0]']:checked" );
                const shippingNumber = jQuery( 'input.shipper_number' ).first();

                if ( selectedShippingMethod.length && selectedShippingMethod.val().toString().indexOf( 'use_my_shipper' ) !== -1 ) {
                    if ( shippingNumber.length === 0 || shippingNumber.val() === '' ) {
                        // Prevent removing alert on next update checkout
                        Main.instance.preserveAlerts = true;

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

            return true;
        } );
    }
}

export default MyShipper;
