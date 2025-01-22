<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'script_loader_tag', 'playht_add_data_minify_attr', 10, 3 );

function playht_add_data_minify_attr( $tag, $handle ) {

	$excluded_handles = [
		'playht-pageplayer-plugin',
		'playht-pageplayer',
	];

	if ( in_array( $handle, $excluded_handles, true ) ) {
		$tag = str_replace( ' src=', ' data-minify="0" src=', $tag );
	}
	return $tag;
}

add_filter( 'autoptimize_filter_js_exclude', 'playht_compatibility_autoptimize' );

function playht_compatibility_autoptimize( $excluded_js_files ) {
	$playht_files = 'playht-pageplayer-plugin.js, pageplayer.js';
	return $excluded_js_files . ', ' . $playht_files;
}

add_filter( 'litespeed_optimize_js_excludes', 'playht_compatibility_litespeed_cache' );

function playht_compatibility_litespeed_cache( $files ) {
	$files[] = 'playht-pageplayer-plugin.js';
	$files[] = 'pageplayer.js';
	return $files;
}

add_filter( 'rocket_minify_excluded_external_js', 'playht_compatibility_wp_rocket_external_js' );

function playht_compatibility_wp_rocket_external_js( array $excluded_files ) {
	$excluded_files[] = 'play.ht';
	$excluded_files[] = 'play-ht';
	return $excluded_files;
}

add_filter( 'rocket_exclude_defer_js', 'playht_compatibility_wp_rocket_defer' );

function playht_compatibility_wp_rocket_defer( array $excluded_files ) {
	$excluded_files[] = 'playht-pageplayer-plugin.js';
	$excluded_files[] = 'pageplayer.js';
	$excluded_files[] = 'play.ht';
	$excluded_files[] = 'play-ht';

	return $excluded_files;
}


add_filter( 'rocket_excluded_inline_js_content', 'playht_compatibility_wp_rocket_inline_js' );

function playht_compatibility_wp_rocket_inline_js( array $exclude_patterns ) {
	$exclude_patterns[] = 'playht';
	$exclude_patterns[] = 'play.ht';
	$exclude_patterns[] = 'play-ht';

	return $exclude_patterns;
}
