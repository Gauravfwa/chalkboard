import Main          from '../../Main';
import Compatibility from '../Compatibility';

class NIFPortugal extends Compatibility {
    private _currentNIF = '';

    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'NIF (Num. de Contribuinte Português) for WooCommerce' );
    }

    load(): void {
        const checkoutForm = jQuery( 'form.checkout' );

        if ( checkoutForm.length === 0 ) {
            return;
        }

        this.enforceFieldVisibility();

        checkoutForm.on( 'change', '#shipping_country', () => {
            this.enforceFieldVisibility();
        } );
    }

    enforceFieldVisibility(): void {
        const country = jQuery( '#shipping_country' ).val();
        const shippingNIFField = jQuery( '#shipping_nif_field' );
        const shippingNIF = jQuery( '#shipping_nif' );

        if ( country === 'PT' ) {
            if ( shippingNIFField.is( ':hidden' ) ) {
                shippingNIFField.show();

                if ( this._currentNIF !== '' ) {
                    shippingNIF.val( this._currentNIF );
                }

                this._currentNIF = '';
            }
        } else if ( shippingNIFField.is( ':visible' ) ) {
            this._currentNIF = shippingNIF.val().toString();
            shippingNIF.val( '' );
            shippingNIFField.hide();
        }
    }
}

export default NIFPortugal;
