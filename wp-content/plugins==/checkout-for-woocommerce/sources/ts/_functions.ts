/**
 * An error tolerant DOM ready replacement for jQuery(document).ready()
 * @param fn
 */
function cfwDomReady( fn ) {
    // see if DOM is already available
    if ( document.readyState === 'complete' || document.readyState === 'interactive' ) {
    // call on next available tick
        setTimeout( fn, 1 );
    } else {
        document.addEventListener( 'DOMContentLoaded', fn );
    }
}

export default cfwDomReady;
