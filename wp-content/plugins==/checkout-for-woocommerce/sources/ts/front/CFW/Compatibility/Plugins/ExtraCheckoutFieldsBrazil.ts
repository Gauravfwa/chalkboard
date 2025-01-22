import Main           from '../../Main';
import LoggingService from '../../Services/LoggingService';
import Compatibility  from '../Compatibility';

class ExtraCheckoutFieldsBrazil extends Compatibility {
    private _targetNodes = [
        'billing_persontype_field',
        'billing_cnpj_field',
        'billing_cpf_field',
    ];

    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor( main: Main, params ) {
        super( main, params, 'ExtraCheckoutFieldsBrazil' );
    }

    load() {
        // Options for the observer (which mutations to observe)
        const config = { attributes: true, childList: false, subtree: false };

        // Callback function to execute when mutations are observed
        const callback = ( mutationsList ) => {
            mutationsList.forEach( ( { type, target } ) => {
                if ( type !== 'attributes' ) {
                    return;
                }

                const group = target.id.split( '_' )[ 0 ];

                if ( target.classList.contains( 'validate-required' ) && target.style.display !== 'none' ) {
                    LoggingService.logNotice( `${target.id} needs to be validated!` );

                    jQuery( target ).find( ':input' ).attr( 'data-parsley-trigger', 'keyup change focusout' )
                        .attr( 'data-parsley-group', group )
                        .attr( 'data-parsley-required', 'true' );
                } else if ( !target.classList.contains( 'validate-required' ) || target.style.display === 'none' ) {
                    LoggingService.logNotice( `${target.id} needs to be UNVALIDATED!` );

                    jQuery( target ).find( ':input' ).removeAttr( 'data-parsley-trigger' )
                        .removeAttr( 'data-parsley-group' )
                        .attr( 'data-parsley-required', 'false' );
                }
            } );

            this.refreshParsley();
        };

        // Create an observer instance linked to the callback function
        const observer = new MutationObserver( callback );

        // Start observing the target node for configured mutations
        this._targetNodes.forEach( ( value ) => {
            observer.observe( document.getElementById( value ), config );
        } );

        jQuery( '#billing_persontype' ).one( 'select2:select', () => {
            jQuery( '#billing_cnpj_field, #billing_cpf_field' ).addClass( 'cfw-extra-checkout-fields-force-observe' );
        } );
    }

    /**
     * Debounce refreshing parsley
     */
    refreshParsley( ) {
        // eslint-disable-next-line global-require
        const debounce = require( 'debounce' );
        debounce( Main.instance.parsleyService.refreshParsley, 200 );
    }
}

export default ExtraCheckoutFieldsBrazil;
