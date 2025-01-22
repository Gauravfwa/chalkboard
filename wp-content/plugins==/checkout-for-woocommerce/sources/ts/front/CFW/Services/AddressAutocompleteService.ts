import DataService from './DataService';

declare let google: any;

class AddressAutocompleteService {
    private _address_formats = {
        DE: 'street_name house_number',
        AL: 'street_name house_number',
        AO: 'street_name house_number',
        AR: 'street_name house_number',
        AT: 'street_name house_number',
        BY: 'street_name house_number',
        BE: 'street_name house_number',
        BO: 'street_name house_number',
        BA: 'street_name house_number',
        BW: 'street_name house_number',
        BR: 'street_name, house_number',
        BN: 'house_number, street_name',
        BG: 'street_name house_number',
        BI: 'street_name house_number',
        CM: 'street_name house_number',
        BQ: 'street_name house_number',
        CF: 'street_name house_number',
        TD: 'street_name house_number',
        CL: 'street_name house_number',
        CO: 'street_name house_number',
        KM: 'street_name house_number',
        HR: 'street_name house_number',
        CW: 'street_name house_number',
        CZ: 'street_name house_number',
        DK: 'street_name house_number',
        DO: 'street_name house_number',
        EC: 'street_name house_number',
        SV: 'street_name house_number',
        GQ: 'street_name house_number',
        ER: 'street_name house_number',
        EE: 'street_name house_number',
        ET: 'street_name house_number',
        FO: 'street_name house_number',
        FI: 'street_name house_number',
        GR: 'street_name house_number',
        GL: 'street_name house_number',
        GD: 'street_name house_number',
        GT: 'street_name house_number',
        GN: 'street_name house_number',
        GW: 'street_name house_number',
        HT: 'street_name house_number',
        VA: 'street_name house_number',
        HN: 'street_name house_number',
        HU: 'street_name house_number',
        IS: 'street_name house_number',
        IR: 'street_name house_number',
        IT: 'street_name house_number',
        JO: 'street_name house_number',
        KZ: 'street_name house_number',
        KI: 'street_name house_number',
        KW: 'street_name house_number',
        KG: 'street_name house_number',
        LV: 'street_name house_number',
        LR: 'street_name house_number',
        LY: 'street_name house_number',
        LI: 'street_name house_number',
        LT: 'street_name house_number',
        MO: 'street_name house_number',
        MK: 'street_name house_number',
        MY: 'street_name house_number',
        ML: 'street_name house_number',
        MX: 'street_name house_number',
        MD: 'street_name house_number',
        ME: 'street_name house_number',
        MZ: 'street_name, house_number',
        NL: 'street_name house_number',
        NO: 'street_name house_number',
        PK: 'house_number - street_name',
        PA: 'street_name house_number',
        PY: 'street_name house_number',
        PE: 'street_name house_number',
        PL: 'street_name house_number',
        PT: 'street_name house_number',
        QA: 'street_name house_number',
        RO: 'street_name house_number',
        RU: 'street_name house_number',
        LC: 'street_name house_number',
        WS: 'street_name house_number',
        SM: 'street_name house_number',
        ST: 'street_name house_number',
        RS: 'street_name house_number',
        SX: 'street_name house_number',
        SK: 'street_name house_number',
        SI: 'street_name house_number',
        SB: 'street_name house_number',
        SO: 'street_name house_number',
        SS: 'street_name house_number',
        ES: 'street_name, house_number',
        SD: 'street_name house_number',
        SR: 'street_name house_number',
        SJ: 'street_name house_number',
        SE: 'street_name house_number',
        CH: 'street_name house_number',
        SY: 'street_name house_number',
        TJ: 'street_name house_number',
        TZ: 'street_name house_number',
        TR: 'street_name house_number',
        UA: 'street_name house_number',
        UY: 'street_name house_number',
        VU: 'street_name house_number',
        EH: 'street_name house_number',
    };

    /**
     * Attach change events to postcode fields
     */
    constructor() {
        if ( !DataService.getSetting( 'enable_address_autocomplete' ) ) {
            return;
        }

        if ( typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined' || typeof google.maps.places.Autocomplete === 'undefined' ) {
            // eslint-disable-next-line no-console
            console.log( 'CheckoutWC: Could not load Google Maps object.' );
            return;
        }

        if ( DataService.getSetting( 'needs_shipping_address' ) === true ) {
            const shipping_address_1 = jQuery( '#shipping_address_1' );

            shipping_address_1.prop( 'autocomplete', 'new-password' );

            const shipping_autocomplete = new google.maps.places.Autocomplete( shipping_address_1.get( 0 ), { types: [ 'geocode' ] } );

            shipping_autocomplete.setFields( [ 'address_component' ] );

            if ( DataService.getSetting( 'address_autocomplete_shipping_countries' ) !== false ) {
                shipping_autocomplete.setComponentRestrictions( { country: DataService.getSetting( 'address_autocomplete_shipping_countries' ) } );
            }

            shipping_autocomplete.addListener( 'place_changed', () => {
                this.fillAddress( 'shipping_', shipping_autocomplete );
            } );
        }

        const billing_address_1 = jQuery( '#billing_address_1' );

        billing_address_1.prop( 'autocomplete', 'new-password' );

        const billing_autocomplete = new google.maps.places.Autocomplete( billing_address_1.get( 0 ), { types: [ 'geocode' ] } );

        billing_autocomplete.setFields( [ 'address_component' ] );

        if ( DataService.getSetting( 'address_autocomplete_billing_countries' ) !== false ) {
            billing_autocomplete.setComponentRestrictions( { country: DataService.getSetting( 'address_autocomplete_billing_countries' ) } );
        }

        billing_autocomplete.addListener( 'place_changed', () => {
            this.fillAddress( 'billing_', billing_autocomplete );
        } );
    }

    fillAddress( prefix: string, autocomplete_object: any ) {
        if ( !autocomplete_object.getPlace().hasOwnProperty( 'address_components' ) ) {
            return;
        }

        const parts = <any>autocomplete_object.getPlace().address_components.reduce( ( parts, component ) => {
            parts[ component.types[ 0 ] ] = component.short_name || '';

            return parts;
        }, {} );

        // Address 1 field holder
        const address1_field = jQuery( `#${prefix}address_1` );

        // Unprocessed value
        const raw_value = address1_field.val().toString();

        // Standard format
        let address_1 = 'house_number street_name';

        if ( this._address_formats.hasOwnProperty( parts.country ) ) {
            address_1 = this._address_formats[ parts.country ];
        }

        // Process <subpremise>/<street number> <route> formats
        const regex = RegExp( '^(.*?)\/(.*?) ' ); // get all the user entered values before a match with the street name; group #1 = unit number, group #2 = street number
        const results = regex.exec( raw_value );

        // If this is an array, format was unit/house number format
        if ( Array.isArray( results ) ) {
            address_1 = `${results[ 1 ]}/${results[ 2 ]} ${parts.route}`;
        } else {
            if ( parts.hasOwnProperty( 'route' ) ) {
                address_1 = address_1.replace( 'street_name', parts.route );
            }

            if ( parts.hasOwnProperty( 'street_number' ) ) {
                address_1 = address_1.replace( 'house_number', parts.street_number );
            }

            if ( parts.hasOwnProperty( 'premise' ) ) {
                address_1 = address_1.replace( 'house_number', `${parts.premise}, ` );
            }

            if ( parts.hasOwnProperty( 'subpremise' ) ) {
                address_1 = address_1.replace( 'house_number', `${parts.subpremise}, ` );
            }
        }

        /**
         * Clean up any vestigial replacement miscellanea
         */
        address_1 = address_1.replace( 'house_number', '' );
        address_1 = address_1.replace( 'street_name', '' );
        address_1 = address_1.replace( 'undefined', '' );

        let city = parts.locality || parts.postal_town || parts.sublocality_level_1 || parts.administrative_area_level_2 || parts.administrative_area_level_3;
        city     = city.replace( 'undefined', '' );

        jQuery( document.body ).one( 'country_to_state_changed', () => {
            setTimeout( () => {
                const state = jQuery( `#${prefix}state` );

                // Special State handling
                if ( !state.is( 'select' ) || state.find( `option[value="${parts.administrative_area_level_1}"]` ).length ) {
                    state.val( parts.administrative_area_level_1 );
                } else {
                    state.find( `option:contains(${parts.administrative_area_level_1})` ).attr( 'selected', 'selected' );
                }

                state.trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );
            } );
        } );

        address1_field.val( address_1 ).trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );

        jQuery( `#${prefix}country` ).val( parts.country ).trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );
        jQuery( `#${prefix}postcode` ).val( parts.postal_code ).trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );
        jQuery( `#${prefix}city` ).val( city ).trigger( 'change', [ 'cfw_store' ] ).trigger( 'keyup' );
    }
}

export default AddressAutocompleteService;
