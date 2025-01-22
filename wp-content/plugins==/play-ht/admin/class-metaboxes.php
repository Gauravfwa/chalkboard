<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backend logic
 *
 * @package Play_HT
 */
class Metaboxes extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// add podacst metabox to posts page.
		add_action( 'add_meta_boxes', [ &$this, 'podcast_meta_box' ] );
	}

	/**
	 * Add metabox to edit post page
	 */
	public function podcast_meta_box() {
		$supported_post_types = get_option( 'playht_type_switch', [] );
		if ( is_array( $supported_post_types ) ) {
			$post_types = array_keys( $supported_post_types );
		} else {
			$post_types = [ 'post', 'page' ];
		}

		add_meta_box(
			'podcast-meta-box-id',
			__( 'Play.ht - Audio Accessibility', 'play-ht' ),
			[
				&$this,
				'podcast_metabox_content',
			],
			$post_types,
			'side',
			'high'
		);
	}

	/**
	 * metabox content
	 */
	public function podcast_metabox_content() {
		playht_load_view( 'back-end/metabox_content', [ 'post_id', get_the_ID() ] );
	}
}
