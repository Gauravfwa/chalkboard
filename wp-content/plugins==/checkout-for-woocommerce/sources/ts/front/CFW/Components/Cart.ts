import UpdateCartAction      from '../Actions/UpdateCartAction';
import Main                  from '../Main';
import DataService           from '../Services/DataService';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';

class Cart {
    constructor() {
        this.setUpMobileCartDetailsReveal();
        this.setQuantityStepperTriggers();
        this.setQuantityPromptTriggers();
        this.setManualFireTriggers();
    }

    setQuantityStepperTriggers(): void {
        jQuery( document.body ).on( 'click', '.cfw-quantity-stepper-btn-minus', function () {
            const quantityValue = jQuery( this ).siblings( '.cfw-edit-item-quantity-value' ).first();
            const quantityLabel = jQuery( this ).parents( '.cart-item-row' ).find( '.cfw-cart-item-quantity-bubble' ).first();
            let newQuantity = Number( quantityValue.val() ) - Number( jQuery( quantityValue ).data( 'step' ) );
            const minQuantity = Number( jQuery( quantityValue ).data( 'min-value' ) );

            if ( newQuantity > 0 && newQuantity < minQuantity ) {
                newQuantity = minQuantity;
            }

            if ( newQuantity > 0 || ( <any>window ).confirm( DataService.getSetting( 'delete_confirm_message' ) ) ) {
                quantityValue.val( newQuantity );
                quantityLabel.text( newQuantity );

                new UpdateCartAction( 'update_cart', DataService.getAjaxInfo(), UpdateCheckoutService.getData() ).load();
            }
        } );

        jQuery( document.body ).on( 'click', '.cfw-quantity-stepper-btn-plus', function () {
            const quantityValue = jQuery( this ).siblings( '.cfw-edit-item-quantity-value' ).first();
            const quantityLabel = jQuery( this ).parents( '.cart-item-row' ).find( '.cfw-cart-item-quantity-bubble' ).first();
            const maxQuantity = Number( jQuery( quantityValue ).data( 'max-quantity' ) );
            let newQuantity = Number( quantityValue.val() ) + Number( jQuery( quantityValue ).data( 'step' ) );

            if ( newQuantity > maxQuantity ) {
                newQuantity = maxQuantity;
            }

            if ( newQuantity <= maxQuantity ) {
                quantityValue.val( newQuantity );
                quantityLabel.text( newQuantity );

                new UpdateCartAction( 'update_cart', DataService.getAjaxInfo(), UpdateCheckoutService.getData() ).load();
            }
        } );
    }

    /**
     *
     */
    setQuantityPromptTriggers(): void {
        jQuery( document.body ).on( 'click', '.cfw-quantity-bulk-edit', ( event ) => {
            const response = ( <any>window ).prompt( DataService.getSetting( 'quantity_prompt_message' ), jQuery( event.target ).data( 'quantity' ) );

            // If we have input
            if ( response !== null ) {
                const newQuantity = Number( response );

                if ( newQuantity > 0 || ( <any>window ).confirm( DataService.getSetting( 'delete_confirm_message' ) ) ) {
                    jQuery( event.target ).siblings( '.cfw-quantity-stepper' ).find( '.cfw-edit-item-quantity-value' ).val( newQuantity );

                    new UpdateCartAction( 'update_cart', DataService.getAjaxInfo(), UpdateCheckoutService.getData() ).load();
                }
            }
        } );
    }

    /**
     *
     */
    setUpMobileCartDetailsReveal(): void {
        const showCartDetails = jQuery( '#cfw-mobile-cart-header' );

        showCartDetails.on( 'click', ( e ) => {
            e.preventDefault();
            jQuery( '#cfw-cart-summary-content' ).slideToggle( 300 );
            jQuery( '#cfw-expand-cart' ).toggleClass( 'active' );
        } );
    }

    setManualFireTriggers(): void {
        jQuery( document.body ).on( 'cfw-fire-udpate-cart-action', () => {
            new UpdateCartAction( 'update_cart', DataService.getAjaxInfo(), UpdateCheckoutService.getData() ).load();
        } );
    }
}

export default Cart;
