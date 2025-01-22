import { Alert, AlertInfo } from '../../Components/Alert';
import Main                 from '../../Main';
import TabService           from '../../Services/TabService';
import Compatibility        from '../Compatibility';

declare let mrwpPluginSettings: any;
declare function mrwp_prepare_shipping() : boolean;
declare function mrwpParcelPickerInit() : void;
declare function mrwpShippingCode( shippingIds: string, selectedShipping: string ) : string;
declare function mrwpNeedsParcelPicker( option: boolean ) : boolean;

class MondialRelay extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'MondialRelay' );
    }

    load( main: Main ): void {
        jQuery( '#cfw-shipping-action' ).hide();

        jQuery( document.body ).on( 'updated_checkout', () => {
            jQuery( '#cfw-shipping-action' ).show();
        } );

        const easyTabsWrap: any = main.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:before', ( event, clicked, target ) => {
            if ( jQuery( target ).attr( 'id' ) === TabService.paymentMethodTabId ) {
                if ( jQuery( '#mrwp_parcel_shop_mandatory' ).val() === 'Yes' ) {
                    if ( jQuery( '#mrwp_parcel_shop_id' ).val() === '' ) {
                        // Prevent removing alert on next update checkout
                        Main.instance.preserveAlerts = true;

                        const alertInfo: AlertInfo = {
                            type: 'error',
                            message: 'Vous n\'avez pas encore choisi de Point Relais.',
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

export default MondialRelay;
