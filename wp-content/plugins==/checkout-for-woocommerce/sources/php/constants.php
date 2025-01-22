<?php
if ( ! defined( 'CFW_DEV_MODE' ) ) {
	// Dev Mode
	define( 'CFW_DEV_MODE', getenv( 'CFW_DEV_MODE' ) == 'true' ? true : false );
}

define( 'CFW_BYPASS_LOAD', ( ! empty( $_GET['bypass-cfw'] ) || ( defined( 'CFW_BYPASS_TEMPLATE' ) && CFW_BYPASS_TEMPLATE ) ) );
