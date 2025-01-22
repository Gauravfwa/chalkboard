import { Alert, AlertInfo } from '../../Components/Alert';
import Main                 from '../../Main';
import Compatibility        from '../Compatibility';

class WCPont extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'Pont shipping for Woocommerce' );
    }

    load( main: Main ): void {
        const easyTabsWrap: any = main.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:before', ( event, clicked, target ) => {
            const selected_shipping_method = jQuery( '[name="shipping_method[0]"]:checked' ).val().toString();

            if ( jQuery( '[name="wc_selected_pont"]' ).val() == '' && selected_shipping_method.indexOf( 'wc_pont_' ) >= 0 ) {
                // Prevent removing alert on next update checkout
                Main.instance.preserveAlerts = true;

                const alertInfo: AlertInfo = {
                    type: 'error',
                    message: 'Nem választottál átvevőhelyet',
                    cssClass: 'cfw-alert-error',
                };

                const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
                alert.addAlert( true );

                event.stopImmediatePropagation();

                return false;
            }
        } );
    }
}

export default WCPont;
