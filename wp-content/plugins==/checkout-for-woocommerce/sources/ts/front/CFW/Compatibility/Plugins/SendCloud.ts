import { Alert, AlertInfo } from '../../Components/Alert';
import Main                 from '../../Main';
import TabService           from '../../Services/TabService';
import Compatibility        from '../Compatibility';

class SendCloud extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'SendCloud' );
    }

    load( main: Main, params ): void {
        const easyTabsWrap: any = main.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:before', ( event, clicked, target ) => {
            if ( jQuery( target ).attr( 'id' ) === TabService.paymentMethodTabId ) {
                const selectedShippingMethod = jQuery( "input[name='shipping_method[0]']:checked" );
                const selectedServicePoint = jQuery( '#sendcloudshipping_service_point_selected' );

                if ( selectedShippingMethod.length && selectedShippingMethod.val().toString().indexOf( 'service_point_shipping_method' ) !== -1 ) {
                    if ( selectedServicePoint.length === 0 || selectedServicePoint.val() === '' ) {
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
        } );
    }
}

export default SendCloud;
