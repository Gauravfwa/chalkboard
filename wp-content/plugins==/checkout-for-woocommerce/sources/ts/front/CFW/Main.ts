import Accordion                     from './Components/Accordion';
import Cart                          from './Components/Cart';
import Coupons                       from './Components/Coupons';
import FormField                     from './Components/FormField';
import Form                          from './Components/Form';
import LoginForm                     from './Components/LoginForm';
import PaymentRequestButtons         from './Components/PaymentRequestButtons';
import TermsAndConditions            from './Components/TermsAndConditions';
import AddressAutocompleteService    from './Services/AddressAutocompleteService';
import AlertService                  from './Services/AlertService';
import BillingAddressSyncService     from './Services/BillingAddressSyncService';
import CompleteOrderService          from './Services/CompleteOrderService';
import CountryRowResizeService       from './Services/CountryRowResizeService';
import DataService                   from './Services/DataService';
import FieldPersistenceService       from './Services/FieldPersistenceService';
import LoggingService                from './Services/LoggingService';
import ParsleyService                from './Services/ParsleyService';
import PaymentGatewaysService        from './Services/PaymentGatewaysService';
import TabService                    from './Services/TabService';
import TooltipService                from './Services/TooltipService';
import UpdateCheckoutService         from './Services/UpdateCheckoutService';
import ValidationService             from './Services/ValidationService';
import ZipAutocompleteService        from './Services/ZipAutocompleteService';
import { AjaxInfo }                  from './Types/Types';

/**
 * The main class of the front end checkout system
 */
class Main {
    /**
     * @type {any}
     * @private
     */
    private _tabContainer: any;

    /**
     * @type {any}
     * @private
     */
    private _alertContainer: any;

    /**
     * @type {any}
     * @private
     */
    private _settings: any;

    /**
     * @type {TabService}
     * @private
     */
    private _tabService: TabService;

    /**
     * @type {UpdateCheckoutService}
     * @private
     */
    private _updateCheckoutService: UpdateCheckoutService;

    /**
     * @type {PaymentGatewaysService}
     * @private
     */
    private _paymentGatewaysService: PaymentGatewaysService;

    /**
     * @type {boolean}
     * @private
     */
    private _preserveAlerts: boolean;

    /**
     * @type boolean
     * @private
     */
    private _loadTabs: any;

    /**
     * @type {Main}
     * @private
     * @static
     */
    private static _instance: Main;

    /**
     * @type {ParsleyService}
     * @private
     */
    private _parsleyService: ParsleyService;

    /**
     * @param {any} checkoutFormElement
     * @param {any} alertContainer
     * @param {any} tabContainerElement
     * @param {any} breadCrumbElement
     * @param {AjaxInfo} ajaxInfo
     * @param {any} settings
     */
    constructor( checkoutFormElement: any, alertContainer: any, tabContainerElement, breadCrumbElement, settings: any ) {
        Main.instance = this;

        DataService.checkoutForm = checkoutFormElement;
        this.tabContainer = tabContainerElement;
        this.alertContainer = alertContainer;
        this.settings = settings;
        this.loadTabs = this.settings.load_tabs;

        /**
         * Services
         */
        // Maybe Load Tab Service
        if ( this.loadTabs ) {
            this.tabService = new TabService( this.tabContainer, breadCrumbElement );
        }

        // Setup the validation service - has to happen after tabs are setup
        new ValidationService();

        // Field Persistence Service
        new FieldPersistenceService( checkoutFormElement );

        // Parsley Service
        this.parsleyService = new ParsleyService();

        // Zip Autocomplete Service
        new ZipAutocompleteService();

        // Address Autocomplete Service
        new AddressAutocompleteService();

        // Complete Order Service
        new CompleteOrderService();

        // Payment Gateway Service
        this.paymentGatewaysService = new PaymentGatewaysService();

        // Update Checkout Service
        this.updateCheckoutService = new UpdateCheckoutService();

        // Billing Address Sync Service
        new BillingAddressSyncService();

        // Alert Service
        new AlertService( this.alertContainer );

        // Country Row Resize Service
        new CountryRowResizeService();

        // Tooltips
        new TooltipService();

        /**
         * Components
         */
        // Load Form component
        new Form();

        // Load Accordion component
        new Accordion();

        // Load Login Form component
        new LoginForm();

        // Load FormField Component
        new FormField();

        // Load Coupons component
        new Coupons();

        // Load Terms and Conditions Component
        new TermsAndConditions();

        // Load Payment Request Buttons Component
        new PaymentRequestButtons();

        // Cart Component
        new Cart();

        /**
         * Compatibility Classes
         */
        this.loadCompatibilityClasses();

        jQuery( document.body ).on( 'cfw-remove-overlay', () => {
            Main.removeOverlay();
        } );

        // Page load actions
        jQuery( window ).on( 'load', () => {
            const wpadmin_bar = jQuery( '#wpadminbar' );

            if ( wpadmin_bar.length ) {
                wpadmin_bar.appendTo( 'html' );
            }

            // Give plugins a chance to react to our hidden, invisible shim checkbox
            jQuery( '#ship-to-different-address-checkbox' ).trigger( 'change' );

            // Don't blow away pre-existing alerts on the first update checkout call
            this.preserveAlerts = true;

            // Trigger initial update checkout
            this.updateCheckoutService.triggerUpdateCheckout();

            // Init checkout ( WooCommerce native event )
            jQuery( document.body ).trigger( 'init_checkout' );
            LoggingService.logEvent( 'Fired init_checkout event.' );
        } );
    }

    /**
     * Load contextually relevant compatibility classes
     */
    loadCompatibilityClasses(): void {
        // Compatibility Class Creation
        Object.keys( DataService.getCompatibilityClasses() ).forEach( ( key ) => {
            // eslint-disable-next-line max-len
            new ( <any>window ).cfwCompatibilityClasses[ DataService.getCompatibilityClass( key ).class ]( Main.instance, DataService.getCompatibilityClass( key ).params ).load( Main.instance, DataService.getCompatibilityClass( key ).params );
        } );
    }

    /**
     * Adds a visual indicator that the checkout is doing something
     */
    static addOverlay(): void {
        if ( jQuery( `#${TabService.paymentMethodTabId}:visible` ).length || jQuery( `#${TabService.orderReviewTabId}:visible` ).length ) {
            const { checkoutForm } = DataService;
            const formData = checkoutForm.data();

            if ( formData[ 'blockUI.isBlocked' ] !== 1 ) {
                checkoutForm.block( {
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6,
                    },
                } );
            }
        }
    }

    /**
     * Remove the visual indicator
     */
    static removeOverlay(): void {
        const form = DataService.checkoutForm;

        form.unblock();
    }

    static getAlertContainer() {
        return DataService.getElement( 'alertContainerId' );
    }

    /**
     * @returns {TabContainer}
     */
    get tabContainer() {
        return this._tabContainer;
    }

    /**
     * @return {any}
     */
    get alertContainer(): any {
        return this._alertContainer;
    }

    /**
     * @param {any} value
     */
    set alertContainer( value: any ) {
        this._alertContainer = value;
    }

    /**
     * @param value
     */
    set tabContainer( value: any ) {
        this._tabContainer = value;
    }

    /**
     * @returns {any}
     */
    get settings(): any {
        return this._settings;
    }

    /**
     * @param value
     */
    set settings( value: any ) {
        this._settings = value;
    }

    /**
     * @returns {TabService}
     */
    get tabService(): TabService {
        return this._tabService;
    }

    /**
     * @param {TabService} value
     */
    set tabService( value: TabService ) {
        this._tabService = value;
    }

    /**
     * @returns {UpdateCheckoutService}
     */
    get updateCheckoutService(): UpdateCheckoutService {
        return this._updateCheckoutService;
    }

    /**
     * @param {UpdateCheckoutService} value
     */
    set updateCheckoutService( value: UpdateCheckoutService ) {
        this._updateCheckoutService = value;
    }

    /**
     * @returns {PaymentGatewaysService}
     */
    get paymentGatewaysService(): PaymentGatewaysService {
        return this._paymentGatewaysService;
    }

    /**
     * @param {PaymentGatewaysService} value
     */
    set paymentGatewaysService( value: PaymentGatewaysService ) {
        this._paymentGatewaysService = value;
    }

    /**
     * @returns {ParsleyService}
     */
    get parsleyService(): ParsleyService {
        return this._parsleyService;
    }

    /**
     * @param {ParsleyService} value
     */
    set parsleyService( value: ParsleyService ) {
        this._parsleyService = value;
    }

    /**
     * @returns {boolean}
     */
    get preserveAlerts(): boolean {
        return this._preserveAlerts;
    }

    /**
     * @param {boolean} value
     */
    set preserveAlerts( value: boolean ) {
        this._preserveAlerts = value;
    }

    get loadTabs(): any {
        return this._loadTabs;
    }

    set loadTabs( value: any ) {
        this._loadTabs = value;
    }

    /**
     * @returns {Main}
     */
    static get instance(): Main {
        return Main._instance;
    }

    /**
     * @param {Main} value
     */
    static set instance( value: Main ) {
        if ( !Main._instance ) {
            Main._instance = value;
        }
    }
}

export default Main;
