import 'core-js/features/object/assign';
import 'ts-polyfill';
import cfwDomReady     from './_functions';
import Cart            from './front/CFW/Components/Cart';
import DataService     from './front/CFW/Services/DataService';
import MapEmbedService from './front/CFW/Services/MapEmbedService';

class ThankYou {
    constructor() {
        const map_embed_service = new MapEmbedService();

        cfwDomReady( () => {
            map_embed_service.setMapEmbedHandlers();

            jQuery( '.status-step-selected' ).prevAll().addClass( 'status-step-selected' );

            // Init runtime params
            DataService.initRunTimeParams();

            // Cart Service
            new Cart();
        } );
    }
}

new ThankYou();
