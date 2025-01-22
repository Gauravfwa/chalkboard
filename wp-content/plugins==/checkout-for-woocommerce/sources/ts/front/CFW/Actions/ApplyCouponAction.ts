import { Alert, AlertInfo } from '../Components/Alert';
import Main                 from '../Main';
import LoggingService       from '../Services/LoggingService';
import Action               from './Action';

/**
 *
 */
class ApplyCouponAction extends Action {
    /**
     * @param {string} id
     * @param {string} code
     */
    constructor( id: string, code: string ) {
        const data = {
            coupon_code: code,
        };

        super( id, data );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            resp = JSON.parse( resp );
        }

        if ( resp.result ) {
            jQuery( document.body ).trigger( 'cfw-apply-coupon-success' );
            LoggingService.logEvent( 'Fired cfw-apply-coupon-success event.' );
        } else {
            jQuery( document.body ).trigger( 'cfw-apply-coupon-failure' );
            LoggingService.logEvent( 'Fired cfw-apply-coupon-failure event.' );
        }

        jQuery( document.body ).trigger( 'cfw-apply-coupon-complete' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-complete event.' );

        jQuery( document.body ).trigger( 'applied_coupon_in_checkout', [ resp.code ] );

        Main.instance.updateCheckoutService.queueUpdateCheckout( {}, { update_shipping_method: false } );

        jQuery( 'form.woocommerce-checkout' ).before( resp.html );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        jQuery( document.body ).trigger( 'cfw-apply-coupon-error' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-error event.' );

        const alertInfo: AlertInfo = {
            type: 'error',
            message: `Failed to apply coupon. Error: ${errorThrown} (${textStatus})`,
            cssClass: 'cfw-alert-error',
        };

        const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
        alert.addAlert();
    }
}

export default ApplyCouponAction;
