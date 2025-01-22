import LoggingService from './LoggingService';

class BillingAddressSyncService {
    private _fields = [
        'first_name',
        'last_name',
        'address_1',
        'address_2',
        'company',
        'country',
        'postcode',
        'state',
        'city',
        'phone',
    ];

    constructor() {
        jQuery( document.body ).on( 'init_checkout', () => {
            // initially set the values on page load
            // make sure it runs after Garlic by delaying a second
            setTimeout( () => {
                this.enforceBillingAddressValues();
                this.listenForShippingChanges();
                this.listenForBillingChanges();
                this.listenForSameAsShippingToggle();
            }, 1000 );
        } );
    }

    listenForShippingChanges() {
        jQuery( '[name^="shipping_"]' ).on( 'change', ( event ) => {
            const sameAsShipping = jQuery( 'input[name="bill_to_different_address"]:checked' ).val();
            const shippingField  = jQuery( event.target );
            const billingField   = jQuery( `[name="${shippingField.attr( 'name' ).replace( 'shipping_', 'billing_' )}"]` );

            if ( sameAsShipping === 'same_as_shipping' ) {
                this.syncField( shippingField, billingField );
            }
        } );
    }

    listenForBillingChanges() {
        jQuery( '[name^="billing_"]' ).on( 'change', ( event, param ) => {
            // Only process this if a human changed the value
            // OR if cfw_store was passed as the first parameter (zip / address autocomplete)
            if ( typeof event.originalEvent !== 'undefined' || param === 'cfw_store' ) {
                const billingField =  jQuery( event.target );
                billingField.data( 'saved-value', billingField.val() );
            }
        } );
    }

    listenForSameAsShippingToggle() {
        const sameAsShipping = jQuery( 'input[name="bill_to_different_address"]' );

        sameAsShipping.on( 'change', () => {
            this.enforceBillingAddressValues();
        } );
    }

    enforceBillingAddressValues() {
        if ( jQuery( 'input[name="bill_to_different_address"]:checked' ).val() === 'same_as_shipping' ) {
            jQuery( '[name^="shipping_"]' ).each( ( i, element ) => {
                const shippingField  = jQuery( element );
                const billingField   = jQuery( `[name="${shippingField.attr( 'name' ).replace( 'shipping_', 'billing_' )}"]` );

                this.syncField( shippingField, billingField );
            } );
        } else {
            jQuery( '[name^="billing_"]' ).each( ( i, element ) => {
                const billingField = jQuery( element );
                const savedValue = billingField.data( 'saved-value' );

                if ( typeof savedValue !== 'undefined' ) {
                    billingField.val( savedValue ).trigger( 'cfw_store' ).trigger( 'change' );
                    LoggingService.logEvent( 'Fired cfw_store event.' );
                }
            } );
        }
    }

    /**
     *
     * @param srcField
     * @param destField
     */
    syncField( srcField, destField ) {
        destField.val( srcField.val() ).trigger( 'cfw_store' ).trigger( 'change' );
        LoggingService.logEvent( 'Fired cfw_store event.' );
    }
}

export default BillingAddressSyncService;
