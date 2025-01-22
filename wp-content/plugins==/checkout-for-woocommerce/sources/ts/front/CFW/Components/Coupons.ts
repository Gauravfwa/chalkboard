import ApplyCouponAction     from '../Actions/ApplyCouponAction';
import RemoveCouponAction    from '../Actions/RemoveCouponAction';
import Main                  from '../Main';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';

class Coupons {
    constructor() {
        this.setShowCouponsModuleListener();
        this.setApplyCouponListener();
        this.setRemoveCouponListener();
        this.setApplyCouponMobileListener();
    }

    setShowCouponsModuleListener() {
        jQuery( document.body ).on( 'click', '.cfw-show-coupons-module', () => {
            jQuery( '.cfw-promo-wrap' ).slideDown( 300 );
            jQuery( '.cfw-show-coupons-module' ).hide();
        } );
    }

    /**
     *
     */
    setApplyCouponListener() {
        const promo_apply_button = jQuery( '#cfw-promo-code-btn' );

        jQuery( '#cfw-promo-code' ).on( 'keydown', ( e ) => {
            if ( e.which == 13 ) {
                e.preventDefault();

                promo_apply_button.trigger( 'click' );
            }
        } );

        promo_apply_button.on( 'click', () => {
            const couponField: any = jQuery( '#cfw-promo-code' );

            if ( couponField.val() !== '' ) {
                new ApplyCouponAction( 'cfw_apply_coupon', couponField.val() ).load();
                couponField.val( '' ).blur();
            }
        } );
    }

    setRemoveCouponListener() {
        jQuery( document.body ).on( 'click', '.woocommerce-remove-coupon', function ( e ) {
            e.preventDefault();
            new RemoveCouponAction( 'cfw_remove_coupon', jQuery( this ).data( 'coupon' ) ).load();
        } );
    }

    setApplyCouponMobileListener() {
        const promo_apply_button = jQuery( '#cfw-promo-code-btn-mobile' );

        jQuery( '#cfw-promo-code-mobile' ).on( 'keydown', ( e ) => {
            if ( e.which == 13 ) {
                e.preventDefault();

                promo_apply_button.trigger( 'click' );
            }
        } );

        promo_apply_button.on( 'click', () => {
            const couponField: any = jQuery( '#cfw-promo-code-mobile' );

            if ( couponField.val() !== '' ) {
                new ApplyCouponAction( 'cfw_apply_coupon', couponField.val() ).load();
                couponField.val( '' ).blur();
            }
        } );
    }
}

export default Coupons;
