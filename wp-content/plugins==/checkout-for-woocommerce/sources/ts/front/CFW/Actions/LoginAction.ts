import { Alert, AlertInfo }    from '../Components/Alert';
import Main                    from '../Main';
import { LogInData, AjaxInfo } from '../Types/Types';
import Action                  from './Action';

/**
 *
 */
class LoginAction extends Action {
    /**
     *
     * @param id
     * @param ajaxInfo
     * @param email
     * @param password
     * @param otherFields
     */
    constructor( id: string, ajaxInfo: AjaxInfo, email: string, password: string, otherFields: object ) {
        let data = {
            'wc-ajax': id,
            email,
            password,
        };

        data = {
            ...data,
            ...otherFields,
        };

        super( id, data );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        if ( resp.logged_in ) {
            ( <any>window ).location.reload();
        } else {
            const alertInfo: AlertInfo = {
                type: 'error',
                message: resp.message,
                cssClass: 'cfw-alert-error',
            };

            const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
            alert.addAlert();
        }
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        const alertInfo: AlertInfo = {
            type: 'error',
            message: `An error occurred during login. Error: ${errorThrown} (${textStatus})`,
            cssClass: 'cfw-alert-error',
        };

        const alert: Alert = new Alert( Main.instance.alertContainer, alertInfo );
        alert.addAlert();
    }
}

export default LoginAction;
