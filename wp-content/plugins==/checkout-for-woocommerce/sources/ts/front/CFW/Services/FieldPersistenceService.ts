/**
 * Field Persistence Service
 */
import DataService    from './DataService';
import LoggingService from './LoggingService';

class FieldPersistenceService {
    constructor( form: any ) {
        const excludes = DataService.getSetting( 'field_persistence_excludes' );

        form.garlic( {
            events: [ 'textInput', 'input', 'change', 'click', 'keypress', 'paste', 'focus', 'cfw_store' ],
            destroy: false,
            excluded: excludes.join( ', ' ),
            onRetrieve: this.onRetrieve,
        } );

        this.setListeners();
    }

    setListeners() {
        jQuery( document.body ).on( 'cfw-order-complete-before-redirect', ( event, form ) => {
            this.destroyForm( form );
        } );

        // After Parsley Service resets field
        jQuery( document.body ).on( 'cfw-after-field-country-to-state-changed', ( e ) => {
            jQuery( e.target ).garlic();
        } );
    }

    destroyForm( form: any ) {
        jQuery( form ).find( ':input' ).each( ( index, element ) => {
            jQuery( element ).garlic( 'destroy' );
        } );
    }

    onRetrieve( element, retrievedValue ) {
        jQuery( document.body ).trigger( 'cfw-field-persistence-after-retrieve-value', [ element, retrievedValue ] );
        LoggingService.logEvent( 'Fired cfw-field-persistence-after-retrieve-value event.' );
    }
}

export default FieldPersistenceService;
