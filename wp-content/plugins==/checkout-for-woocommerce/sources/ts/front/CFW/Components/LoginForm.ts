import { debug }             from 'webpack';
import AccountExistsAction   from '../Actions/AccountExistsAction';
import LoginAction           from '../Actions/LoginAction';
import Main                  from '../Main';
import Utilities             from '../Modules/Utilities';
import DataService           from '../Services/DataService';

class LoginForm {
    constructor() {
        this.setListeners();
    }

    setListeners() {
        this.setAccountCheckListener();
        this.setLogInListener();
        this.setDefaultLoginFormListener();
        this.setAnimationListeners();
        this.setCreateAccountCheckboxListener();
    }

    /**
     *
     */
    setAccountCheckListener() {
        const emailInput: any = jQuery( '#billing_email' );

        if ( emailInput ) {
            // Add check to keyup event
            emailInput.on( 'keyup change', Utilities.debounce( this.triggerAccountExistsCheck, 250 ) );

            // Handles page onload use case
            this.triggerAccountExistsCheck();
        }
    }

    /**
     *
     */
    setLogInListener() {
        const emailInput: any = jQuery( '#billing_email' );

        if ( emailInput ) {
            const passwordInput: any = jQuery( '#cfw-password' );
            const loginBtn: any = jQuery( '#cfw-login-btn' );
            const otherFields = {};

            // Fire the login action on click
            loginBtn.on( 'click', () => {
                jQuery( '#cfw-login-details .cfw-input-container :input' ).not( '#billing_email, #cfw-password, #cfw-login-btn, #createaccount' ).each( function ( index ) {
                    otherFields[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
                } );

                new LoginAction( 'login', DataService.getAjaxInfo(), emailInput.val(), passwordInput.val(), otherFields ).load();
            } );
        }
    }

    setDefaultLoginFormListener() {
        jQuery( document.body ).on( 'click', 'a.showlogin', () => {
            jQuery( 'form.login, form.woocommerce-form--login' ).slideToggle();

            return false;
        } );
    }

    /**
     * Sets up animation listeners
     */
    setAnimationListeners(): void {
        const createAccountCheckbox = jQuery( '#createaccount' );

        jQuery( '#cfw-ci-login' ).on( 'click', () => {
            jQuery( '#cfw-login-slide' ).slideToggle( 300 ).toggleClass( 'stay-open' );

            if ( createAccountCheckbox.is( ':checked:enabled' ) ) {
                createAccountCheckbox.prop( 'checked', false ).trigger( 'change' );
            }
        } );
    }

    setCreateAccountCheckboxListener(): void {
        if ( !DataService.getSetting( 'registration_generate_password' ) ) {
            const createAccountCheckbox = jQuery( '#createaccount' );
            const accountPasswordSlide = jQuery( '#cfw-account-password-slide' );
            const accountPassword = jQuery( '#account_password' );

            createAccountCheckbox.on( 'change', function () {
                if ( jQuery( this ).is( ':checked' ) ) {
                    accountPasswordSlide.slideDown( 300 );
                    accountPassword.attr( 'data-parsley-required', 'true' );
                    accountPassword.prop( 'disabled', false );
                    jQuery( '#cfw-login-slide' ).slideUp( 300 );
                } else {
                    accountPasswordSlide.slideUp( 300 );
                    accountPassword.attr( 'data-parsley-required', 'false' );
                    accountPassword.prop( 'disabled', true );
                }
            } ).trigger( 'change' );
        }
    }

    triggerAccountExistsCheck() {
        const emailInput: any = jQuery( '#billing_email' );

        if ( emailInput ) {
            new AccountExistsAction( 'account_exists', DataService.getAjaxInfo(), emailInput.val(), !DataService.getSetting( 'enable_checkout_login_reminder' ) ).load();
        }
    }
}

export default LoginForm;
