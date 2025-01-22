import DataService from './DataService';

class LoggingService {
    static logError( message: string ) {
        // eslint-disable-next-line no-console
        console.log( `CheckoutWC: ${message} ⚠️` );
    }

    static logNotice( message: string ) {
        if ( DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: ${message} ℹ️` );
        }
    }

    static logEvent( message: string ) {
        if ( DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: ${message} 🔈` );
        }
    }

    static logAction( action: string ) {
        if ( DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: Running ${action} action. ☄️` );
        }
    }

    static logCompatibilityClassLoad( compatClass: string ) {
        if ( DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: Loaded ${compatClass} module. 🧩` );
        }
    }
}

export default LoggingService;
