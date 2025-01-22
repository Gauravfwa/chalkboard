<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_playht_player( $atts ) {

	$default_atts = array(
		'width'                => '100%',
		'height'               => '90px', // Iframe size, Player size will not change.
		'alignment'            => 'center',
		'title'                => __( 'Audio', 'play-ht' ),
		'message'              => __( 'Hello', 'play-ht' ),
		'download_text_button' => __( 'Download', 'play-ht' ),
	);

	$atts = shortcode_atts( $default_atts, $atts, 'playht_player' );

	if ( isset( $_GET['debug'] ) && 'true' == $_GET['debug'] ) {
		print_r( $atts );
	}

	return playht_player( $atts );
}

add_shortcode( 'playht_player', 'get_playht_player' );


function playht_listen_button_shortcode( $atts ) {

	$open_tag = $close_tag = '';

	// Defaults are empty in order to avoid affecting existing users.
	$default_atts = array(
		'tag'    => '',
		'inline' => '',
	);

	$atts           = shortcode_atts( $default_atts, $atts, 'playht_listen_button' );
	$supported_tags = array( 'p', 'span', 'div' );
	$context        = 'shortcode_playht_listen_button';
	$supported_tags = apply_filters( 'playht_shortcode_wrapper_tags', $supported_tags, $context );

	if ( in_array( $atts['tag'], $supported_tags, true ) ) {
		$open_tag  = '<' . $atts['tag'] . '>';
		$close_tag = '</' . $atts['tag'] . '>';
	}

	$config['post_id'] = get_the_ID();
	$config['inline']  = $atts['inline'];

	return $open_tag . playht_listen_button( $config ) . $close_tag;
}

add_shortcode( 'playht_listen_button', 'playht_listen_button_shortcode' );
