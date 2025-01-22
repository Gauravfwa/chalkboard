<?php
/**
 * Included in main plugin file to add compatibility for specific themes.
 */

// Get the current theme
$active_theme = wp_get_theme();

/**
 * eLumine by WisdmLabs
 * Added in version 1.3 on April 19, 2020
 */
if ( 'eLumine' == $active_theme->name || 'eLumine' == $active_theme->parent_theme ) {

	function lqc_elumine_css() {

		wp_enqueue_style( 'lqc-elumine', plugin_dir_url( __DIR__ ) . 'assets/css/elumine.css', array( 'lqc-learndash-quiz-customizer' ), '1.3' );

	}

	// Priority of 11 should load this after Design Upgrade styles
	add_action( 'wp_enqueue_scripts', 'lqc_elumine_css', 11 );

} // end if eLumine