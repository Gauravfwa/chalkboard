import { Md5 } from 'ts-md5/dist/md5';

export type AlertInfo = {
    type: 'error' | 'notice' | 'success',
    message: any,
    cssClass: string
};

/**
 *
 */
export class Alert {
    /**
     * @type {AlertInfo}
     * @private
     */
    private _alertInfo: AlertInfo;

    private _alertContainer: any;

    /**
     *
     * @param alertContainer
     * @param alertInfo
     */
    constructor( alertContainer: any, alertInfo: AlertInfo ) {
        this.alertInfo = alertInfo;
        this._alertContainer = jQuery( alertContainer );
    }

    /**
     * @param {boolean} temporary
     */
    addAlert( temporary: boolean = false ): void {
        const hash = Md5.hashStr( this.alertInfo.message + this.alertInfo.cssClass + this.alertInfo.type );
        let alertElement: JQuery<HTMLElement> = jQuery( `.cfw-alert-${hash}` );

        if ( alertElement.length == 0 ) {
            alertElement = <JQuery<HTMLElement>>jQuery( '#cfw-alert-placeholder' ).contents().clone();

            alertElement.find( '.message' ).html( this.alertInfo.message );
            alertElement.addClass( this.alertInfo.cssClass );
            alertElement.addClass( `cfw-alert-${hash}` );
            alertElement.appendTo( this.alertContainer );

            this.alertContainer.slideDown( 300 );

            alertElement = jQuery( `.cfw-alert-${hash}` );

            window.dispatchEvent( new CustomEvent( 'cfw-add-alert-event', { detail: { alertInfo: this.alertInfo } } ) );
        }

        // Temporary alerts are removed on tab switch
        if ( temporary ) {
            alertElement.addClass( 'cfw-alert-temporary' );
        }

        // Scroll to the top of the alert container
        jQuery( 'html, body' ).stop().animate( {
            scrollTop: jQuery( '#cfw-alert-container' ).offset().top,
        }, 300 );
    }

    /**
     * @param {any} alertContainer
     */
    static removeAlerts( alertContainer: any ): void {
        alertContainer.find( '.cfw-alert' ).remove();
    }

    /**
     * @returns {AlertInfo}
     */
    get alertInfo(): AlertInfo {
        return this._alertInfo;
    }

    /**
     * @param value
     */
    set alertInfo( value: AlertInfo ) {
        this._alertInfo = value;
    }

    get alertContainer(): any {
        return this._alertContainer;
    }

    set alertContainer( value: any ) {
        this._alertContainer = value;
    }
}
