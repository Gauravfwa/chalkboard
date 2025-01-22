<?php

/**
 * Adds <body> class based on option chosen in Customizer.
 *
 * @package   lqc-learndash-quiz-customizer
 * @copyright Copyright (c) 2019, Escape Creative, LLC
 * @license   GPL2+
 */

function lqc_body_classes( $classes ) {
	
	// Global plugin class
	$classes[] = 'lqc-plugin';

	return $classes;
}
add_filter( 'body_class', 'lqc_body_classes' );