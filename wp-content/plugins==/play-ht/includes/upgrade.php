<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'playht_upgrade_342' );

function playht_upgrade_342() {
	if ( get_option( 'playht_upgrade_342' ) ) {
		return;
	}

	global $wpdb;

	$posts = $wpdb->get_results( "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = 'play_podcast_data'", ARRAY_A );

	foreach ( $posts as $post ) {
		$draft_meta = maybe_unserialize( $post['meta_value'] );
		$post_id    = $post['post_id'];

		if ( ! is_array( $draft_meta ) ) {
			$draft_meta = maybe_unserialize( $draft_meta );
		}

		if ( isset( $draft_meta['audio_status'] ) && 4 === $draft_meta['audio_status'] ) {
			update_post_meta( $post_id, 'playht_draft', $draft_meta );
			delete_post_meta( $post_id, 'play_podcast_data' );
		}
	}

	update_option( 'playht_upgrade_342', true, true );
}
