class PaymentRequestButtons {
    constructor() {
        jQuery( window ).on( 'load', () => {
            if ( jQuery( '#payment-info-separator-wrap .pay-button-separator' ).css( 'display' ) === 'none' ) {
                jQuery( '#cfw-payment-request-buttons' ).hide();
            }

            // Ladder of timings to try to handle even the slowest sites
            // TODO: We should be able to find a better way to do this
            const timings = [ 750, 1500, 3000, 6000 ];
            timings.forEach( ( time ) => {
                ( <any>window ).setTimeout( this.maybeShowExpressButtons, time );
            } );
        } );
    }

    maybeShowExpressButtons(): void {
        if ( jQuery( '#payment-info-separator-wrap .pay-button-separator' ).is( ':visible' ) ) {
            jQuery( '#cfw-payment-request-buttons' ).show();
        }
    }
}

export default PaymentRequestButtons;
