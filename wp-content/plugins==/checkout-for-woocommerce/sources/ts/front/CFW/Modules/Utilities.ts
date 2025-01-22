module Utilities {
    export function debounce( func: any, delay: number ) {
        let inDebounce;

        return function () {
            const context = this;
            const args = arguments;
            clearTimeout( inDebounce );
            inDebounce = ( <any>window ).setTimeout( () => func( context, args ), delay );
        };
    }
}

export default Utilities;
