import DataService from './DataService';

class ZipAutocompleteService {
    constructor() {
        this.setZipAutocompleteHandlers();
    }

    /**
     * Attach change events to postcode fields
     */
    setZipAutocompleteHandlers() {
        if ( DataService.getSetting( 'enable_zip_autocomplete' ) === true ) {
            jQuery( document.body ).on( 'textInput input change keypress paste', '#shipping_postcode, #billing_postcode', this.autoCompleteCityState );
        }
    }

    autoCompleteCityState( e ) {
        if ( typeof e.originalEvent === 'undefined' ) {
            return;
        }

        const type = e.currentTarget.id.split( '_' )[ 0 ]; // either shipping or billing
        const zip = e.currentTarget.value.trim();
        const val = jQuery( `#${type}_country` ).val();
        const country = typeof val === 'undefined' || val == null ? '' : val.toString();

        /**
         * Unfortunately, some countries copyright their zip codes
         * Meaning that you can only look up by the first 3 characters which
         * does not provide enough specificity so we skip them
         *
         * This is an incomplete list. Just hitting some big ones here.
         */
        const incompatibleCountries = [ 'GB', 'CA' ];

        if ( incompatibleCountries.indexOf( country ) === -1 ) {
            ZipAutocompleteService.getZipData( country, zip, type );
        }
    }

    static getZipData( country, zip, type ) {
        jQuery.ajax( {
            url: `https://api.zippopotam.us/${country}/${zip}`,
            dataType: 'json',
            success: ( result ) => {
                const { 'place name': city, 'state abbreviation': state } = result.places[ 0 ];

                const state_field = jQuery( `[name="${type}_state"]:visible` );

                // Cleanup Parsley messages
                state_field.val( state ).trigger( 'change', [ 'cfw_store' ] );
                state_field.removeClass( 'parsley-error' ).parent().find( '.parsley-errors-list' ).remove();

                // If there's more than one result, don't autocomplete city
                // This prevents crappy autocompletes
                if ( result.places.length === 1 ) {
                    const city_field = jQuery( `#${type}_city` );

                    // Cleanup Parsley messages
                    city_field.val( city ).trigger( 'change', [ 'cfw_store' ] );
                    city_field.removeClass( 'parsley-error' ).parent().find( '.parsley-errors-list' ).remove();
                }
            },
        } );
    }
}

export default ZipAutocompleteService;
