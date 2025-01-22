import { Alert, AlertInfo } from '../Components/Alert';
import Main                 from '../Main';
import DataService          from './DataService';
import LoggingService       from './LoggingService';
import TabService           from './TabService';

class AlertService {
    /**
     * @type {MutationObserver}
     * @private
     */
    private _errorObserver: MutationObserver;

    private _alertContainer: any;

    constructor( alertContainer: any ) {
        this.alertContainer = alertContainer;
        this.setMutationObserverWatcher();
    }

    setMutationObserverWatcher() {
        jQuery( document.body ).trigger( 'cfw-before-alert-service-mutation-observer-init' );
        LoggingService.logEvent( 'Fired cfw-before-alert-service-mutation-observer-init event.' );

        // Select the node that will be observed for mutations
        const targetNode = jQuery( 'form#checkout, form#order_review' ).get( 0 );

        // Options for the observer ( which mutations to observe )
        const config = { childList: true, characterData: true, subtree: true };

        if ( !this.errorObserver ) {
            // Create an observer instance linked to the callback function
            const observer = new MutationObserver( ( mutationsList ) => this.errorMutationListener( mutationsList ) );

            // Start observing the target node for configured mutations
            observer.observe( targetNode, config );

            this.errorObserver = observer;
        }
    }

    /**
     * @param mutationsList
     */
    errorMutationListener( mutationsList ) {
        // eslint-disable-next-line max-len
        if ( jQuery( `#${TabService.paymentMethodTabId}:visible` ).length || jQuery( `#${TabService.orderReviewTabId}:visible` ).length || jQuery( '.context-order-pay' ).length ) {
            for ( const mutation of mutationsList ) {
                if ( mutation.type === 'childList' ) {
                    const { addedNodes } = mutation;
                    let $errorNode: any = null;

                    Array.from( addedNodes ).forEach( ( node ) => {
                        const $node: any = jQuery( node );
                        const hasClass: boolean = $node.hasClass( 'woocommerce-error' );
                        const hasGroupCheckoutClass: boolean = $node.hasClass( 'woocommerce-NoticeGroup-checkout' );

                        if ( hasClass || hasGroupCheckoutClass ) {
                            jQuery( document.body ).trigger( 'cfw-remove-overlay' );
                            LoggingService.logEvent( 'Fired cfw-remove-overlay event.' );
                            $errorNode = $node;
                            $errorNode.attr( 'class', '' );
                        }
                    } );

                    if ( $errorNode ) {
                        if ( $errorNode.find( 'li' ).length > 0 ) {
                            jQuery.each( $errorNode.find( 'li' ), ( i, el ) => {
                                const alert: Alert = new Alert( this.alertContainer, <AlertInfo> {
                                    type: 'error',
                                    message: jQuery.trim( jQuery( el ).text() ),
                                    cssClass: 'cfw-alert-error',
                                } );
                                alert.addAlert();
                            } );
                        } else {
                            const alert: Alert = new Alert( this.alertContainer, <AlertInfo> {
                                type: 'error',
                                message: jQuery.trim( $errorNode.text() ),
                                cssClass: 'cfw-alert-error',
                            } );
                            alert.addAlert();
                        }

                        $errorNode.remove();
                    }
                }
            }
        }
    }

    /**
     * @returns {MutationObserver}
     */
    get errorObserver(): MutationObserver {
        return this._errorObserver;
    }

    /**
     * @param {MutationObserver} value
     */
    set errorObserver( value: MutationObserver ) {
        this._errorObserver = value;
    }

    get alertContainer(): any {
        return this._alertContainer;
    }

    set alertContainer( value: any ) {
        this._alertContainer = value;
    }
}

export default AlertService;
