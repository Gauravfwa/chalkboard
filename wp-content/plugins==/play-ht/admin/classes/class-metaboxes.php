<?php

namespace Wordpress\Play;

/**
 * Backend logic
 *
 * @package WordPress\Play
 */
class Metaboxes extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// avoid injecting our content on learnPress plugin pages
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'lp_course' ) {
			return false;
		}

		$current_post_type    = Helpers::get_admin_post_type();
		$supported_post_types = get_option( 'playht_type_switch' );
		if ( is_array( $supported_post_types ) ) {
			$supported_post_types = array_keys( $supported_post_types );
		}

		if ( ! in_array( $current_post_type, $supported_post_types, true ) ) {
			return;
		}

		// add podacst metabox to posts page
		add_action( 'add_meta_boxes', [ &$this, 'podcast_meta_box' ] );
	}

	/**
	 * Add metabox to edit post page
	 */
	public function podcast_meta_box() {
		// add it directly after the publish box.
		$post_type_a = get_option( 'playht_type_switch' );
		if ( is_array( $post_type_a ) ) {
			$flip_array = array_keys( $post_type_a );
		} else {
			$flip_array = [ 'post', 'page' ];
		}

		add_meta_box(
			'podcast-meta-box-id',
			__( 'Play.ht - Audio Accessibility', 'play-ht' ),
			[
				&$this,
				'podcast_metabox_content',
			],
			$flip_array,
			'side',
			'high'
		);
	}

	/**
	 * metabox content
	 */
	public function podcast_metabox_content() {
		WPPP_view( 'back-end/metabox_content', [ 'post_id', get_the_ID() ] );
	}
}
