import 'core-js/features/object/assign';
import 'ts-polyfill';
import cfwDomReady            from './_functions';
import Accordion              from './front/CFW/Components/Accordion';
import Cart                   from './front/CFW/Components/Cart';
import TermsAndConditions     from './front/CFW/Components/TermsAndConditions';
import Main                   from './front/CFW/Main';
import AlertService           from './front/CFW/Services/AlertService';
import DataService            from './front/CFW/Services/DataService';
import LoggingService         from './front/CFW/Services/LoggingService';
import PaymentGatewaysService from './front/CFW/Services/PaymentGatewaysService';
import UpdateCheckoutService  from './front/CFW/Services/UpdateCheckoutService';

// eslint-disable-next-line import/prefer-default-export
class OrderPay {
    constructor() {
        cfwDomReady( () => {
            /**
             * Services
             */
            // Init runtime params
            DataService.initRunTimeParams();

            new PaymentGatewaysService();

            // Alert Service
            new AlertService( Main.getAlertContainer() );

            /**
             * Components
             */
            // Accordion Component
            new Accordion();

            // Cart Component
            new Cart();

            // Load Terms and Conditions Component
            new TermsAndConditions();

            // Payment Gateway Service
            new PaymentGatewaysService();

            // Trigger updated checkout
            UpdateCheckoutService.triggerUpdatedCheckout();

            // Init checkout ( WooCommerce native event )
            jQuery( document.body ).trigger( 'init_checkout' );
            LoggingService.logEvent( 'Fired init_checkout event.' );
        } );
    }
}

new OrderPay();
