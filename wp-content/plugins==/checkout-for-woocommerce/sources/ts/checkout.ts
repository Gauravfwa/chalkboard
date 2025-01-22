import 'core-js/features/object/assign';
import 'ts-polyfill';
import cfwDomReady                 from './_functions';
import { cfwCompatibilityClasses } from './cfw-compatibility-classes';
import Main                        from './front/CFW/Main';
import DataService                 from './front/CFW/Services/DataService';
import LoggingService              from './front/CFW/Services/LoggingService';

declare let cfwEventData: any;
( <any>window ).cfwCompatibilityClasses = cfwCompatibilityClasses;

// Fired from compatibility-classes.ts
cfwDomReady( () => {
    const data = cfwEventData;
    const formEl = jQuery( data.elements.checkoutFormSelector );
    const breadcrumbEl = jQuery( data.elements.breadCrumbElId );
    const alertContainerEl = jQuery( data.elements.alertContainerId );
    const tabContainerEl = jQuery( data.elements.tabContainerElId );

    // Allow users to add their own Typescript Compatibility classes
    jQuery( document.body ).trigger( 'cfw_checkout_before_load' );
    LoggingService.logEvent( 'Fired cfw_checkout_before_load event.' );

    // Init runtime params
    DataService.initRunTimeParams();

    // Kick it off!
    new Main( formEl, alertContainerEl, tabContainerEl, breadcrumbEl, data.settings );
} );
