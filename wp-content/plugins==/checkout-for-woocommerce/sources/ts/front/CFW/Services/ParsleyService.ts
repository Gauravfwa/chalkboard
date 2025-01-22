import DataService         from './DataService';
import LoggingService      from './LoggingService';

// eslint-disable-next-line camelcase
declare let wc_address_i18n_params: any;

class ParsleyService {
    /**
     * @type {any}
     * @private
     */
    private _parsley: any;

    private readonly _parsleyConfig: any;

    /**
     *
     */
    constructor() {
        // Attach errors to the outer parent so that select arrow styling isn't effected by dynamic height of cfw-input-wrap
        this._parsleyConfig = {
            errorsContainer( parsleyElement ) {
                return parsleyElement.$element.parent( '.cfw-input-wrap' ).parent( 'div' );
            },
        };

        this.setParsleyValidators();
    }

    /**
     *
     */
    setParsleyValidators(): void {
        // An instance of self that we can use without
        // worrying about scoping issues with functions
        const self = this;

        /**
         * Init Parsley
         */
        jQuery( window ).on( 'load', () => {
            this.parsley = ( <any>window ).Parsley;
            this.parsley.on( 'form:error', () => {
                jQuery( document.body ).trigger( 'cfw-remove-overlay' );
                LoggingService.logEvent( 'Fired cfw-remove-overlay event.' );
            } );

            try {
                // Parsley locale
                ( <any>window ).Parsley.setLocale( DataService.getSetting( 'parsley_locale' ) );
            } catch {
                const settings = DataService.getSettings();
                LoggingService.logError( `CheckoutWC: Could not load Parsley translation domain (${settings.parsley_locale})` );
            }

            DataService.checkoutForm.parsley( this._parsleyConfig );

            // If we don't call this here, changing the state
            // field to 'Select an option' doesn't fire validation
            ( <any>window ).setTimeout( () => self.refreshParsley() );
        } );

        /**
         * There's a lot going on here, but here's essentially what we're doing.
         *
         * If the state field changes, we check to see what field type it is and do the following:
         * - Add correct classes to field parent wrap
         * - Make any changes to the Parsley validation
         * - Refresh Parsley
         *
         * The fields we are handling in this routine are state, city, and postcode for both address types.
         *
         * Lastly, we do this in a timer because we can't guarantee that WooCommerce will be done making their changes
         * before this runs. So by using setTimeout, we ensure that they are completely done before we do our stuff.
         *
         * We use country_to_state_changing instead of country_to_state_changed because it always fires
         */
        jQuery( document.body ).on( 'country_to_state_changing', ( event, country, wrapper ) => {
            if ( typeof wrapper === 'undefined' ) {
                return;
            }

            ( <any>window ).setTimeout( () => {
                const localeJson = wc_address_i18n_params.locale.replace( /&quot;/g, '"' );
                const locale = JSON.parse( localeJson );

                const { state: { required: stateFieldRequired } } = jQuery.extend( true, {}, locale.default, locale[ country ] );

                wrapper.find( '#billing_state, #shipping_state' ).each( ( index, stateElement ) => {
                    const stateField = jQuery( stateElement );
                    const stateWrapper = stateField.parent( '.cfw-input-wrap' );

                    if ( stateField.is( 'select' ) ) {
                        stateField.attr( 'field_key', 'state' )
                            .addClass( 'garlic-auto-save' )
                            .trigger( 'cfw-after-field-country-to-state-changed' );
                        LoggingService.logEvent( 'Fired cfw-after-field-country-to-state-changed event.' );

                        stateWrapper.addClass( 'cfw-select-input cfw-floating-label' ).removeClass( 'cfw-hidden-input cfw-text-input' );
                    } else if ( stateField.attr( 'type' ) === 'text' ) {
                        const stateFieldLabel = stateField.siblings( 'label' ).text().replace( '*', '' );

                        stateField.attr( {
                            field_key: 'state',
                            placeholder: stateFieldLabel,
                        } );

                        stateElement.dataset.placeholder = stateFieldLabel;

                        stateField.addClass( 'garlic-auto-save input-text' )
                            .trigger( 'cfw-after-field-country-to-state-changed' );
                        LoggingService.logEvent( 'Fired cfw-after-field-country-to-state-changed event.' );

                        stateWrapper.addClass( 'cfw-text-input cfw-floating-label' ).removeClass( 'cfw-hidden-input cfw-select-input' );
                    } else {
                        stateField.addClass( 'hidden' );

                        stateWrapper.addClass( 'cfw-hidden-input' ).removeClass( 'cfw-text-input cfw-select-input cfw-floating-label' );
                    }

                    // Handle required toggle
                    // We have to add the parsley attributes here because the field is
                    // recreated and thus loses anything that was previously there.
                    const group = stateField.attr( 'id' ).split( '_' )[ 0 ];

                    stateField.attr( {
                        'data-parsley-trigger': stateFieldRequired ? 'keyup change focusout' : null,
                        'data-parsley-group': stateFieldRequired ? group : null,
                        'data-parsley-required': stateFieldRequired.toString(),
                    } );
                } );

                // eslint-disable-next-line func-names
                wrapper.find( '#billing_city, #shipping_city' ).attr( 'data-parsley-required', function () {
                    return jQuery( this ).is( ':visible' ).toString();
                } );

                // eslint-disable-next-line func-names
                wrapper.find( '#billing_postcode, #shipping_postcode' ).attr( 'data-parsley-required', function () {
                    return ( jQuery( this ).is( ':visible' ) && !jQuery( this ).siblings( 'label.optional' ).length ).toString();
                } );

                self.refreshParsley();
            } );
        } );
    }

    refreshParsley(): void {
        // Remove existing parsley errors.
        DataService.checkoutForm.find( '.parsley-errors-list' ).remove();

        // Re-register all the elements
        DataService.checkoutForm.parsley().refresh();
    }

    destroy(): void {
        // Destroy all the parsley!
        DataService.checkoutForm.parsley().destroy();
    }

    /**
     * @returns {any}
     */
    get parsley(): any {
        return this._parsley;
    }

    /**
     * @param value
     */
    set parsley( value: any ) {
        this._parsley = value;
    }
}

export default ParsleyService;
