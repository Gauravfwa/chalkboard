import { PaymentMethodData, AjaxInfo } from '../Types/Types';
import Action                          from './Action';

/**
 *
 */
class UpdatePaymentMethod extends Action {
    /**
     *
     * @param id
     * @param ajaxInfo
     * @param payment_method
     */
    constructor( id: string, ajaxInfo: AjaxInfo, payment_method: string ) {
        const data: PaymentMethodData = {
            'wc-ajax': id,
            payment_method,
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
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        // eslint-disable-next-line no-console
        console.log( `Update Payment Method Error: ${errorThrown} (${textStatus})` );
    }
}

export default UpdatePaymentMethod;
