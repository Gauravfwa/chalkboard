import Main                 from '../Main';
import { AjaxInfo }         from '../Types/Types';
import Action               from './Action';
import UpdateCheckoutAction from './UpdateCheckoutAction';

/**
 *
 */
class UpdateCartAction extends Action {
    /**
     *
     * @param id
     * @param ajaxInfo
     * @param formData
     */
    constructor( id: string, ajaxInfo: AjaxInfo, formData: any ) {
        super( id, formData );

        this.blockUI();
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            resp = JSON.parse( resp );
        }

        if ( resp.redirect !== false ) {
            window.location = resp.redirect;
        } else {
            // Fire updated_checkout event.
            Main.instance.updateCheckoutService.queueUpdateCheckout( {}, { update_shipping_method: false } );
        }
    }

    public blockUI(): void {
        jQuery( UpdateCheckoutAction.blockUISelector ).block( {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6,
            },
        } ).addClass( 'blocked' );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        // eslint-disable-next-line no-console
        console.log( `Update Cart Error: ${errorThrown} (${textStatus})` );
    }
}

export default UpdateCartAction;
