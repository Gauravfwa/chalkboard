<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPPP_MAIN_FILE', __FILE__ );
define( 'WPPP_DIR', plugin_dir_path( WPPP_MAIN_FILE ) );
define( 'WPPP_URI', plugin_dir_url( WPPP_MAIN_FILE ) );
define( 'WPPP_IMAGES_PATH', WPPP_URI . 'assets/images/' );
define( 'WPPP_JS_PATH', WPPP_URI . 'assets/dist/js/' );
define( 'WPPP_CSS_PATH', WPPP_URI . 'assets/dist/css/' );

define( 'PLAY_URL', 'https://play.ht/' );

// URLs to be later used in JS audio conversion
define( 'CONVERSION_URL', PLAY_URL . 'api/transcripe' );
define( 'ADD_ORIGIN_URL', PLAY_URL . 'api/addOrigin' );
define( 'REFERRAL_URL', PLAY_URL . 'api/plugin/saveReferralSource' );

define( 'PLAYHT_VERSION', '3.6.4' );
define( 'PLAYHT_DB_VERSION', '1.1.0' );
