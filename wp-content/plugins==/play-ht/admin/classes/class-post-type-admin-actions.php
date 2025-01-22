<?php

namespace Wordpress\Play;

/**
 * Backend logic
 *
 * @package WordPress\Play
 */
class Post_Type_Admin_Actions extends Component {

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

		if ( ! Helpers::is_post_type_supported() ) {
			return;
		}

		// add custom columns to posts
		add_filter( 'manage_posts_columns', [ &$this, 'play_columns_head' ] );
		add_filter( 'manage_pages_columns', [ &$this, 'play_columns_head' ] );
		add_filter( 'manage_pages_custom_column', [ &$this, 'play_columns_head' ] );

		// custom column content
		add_action( 'manage_posts_custom_column', [ &$this, 'play_columns_content' ], 10, 2 );
		add_action( 'manage_pages_custom_column', [ &$this, 'play_columns_content' ], 10, 2 );

		// add action_row controllers dor play
		add_filter( 'post_row_actions', [ &$this, 'add_play_action_row' ], 90, 2 );
		add_filter( 'page_row_actions', [ &$this, 'add_play_action_row' ], 90, 2 );
	}

	/**
	 *  Add custom columns posts table
	 *
	 * @param $defaults
	 *
	 * @return array
	 */
	public function play_columns_head( $defaults ) {
		$new_order = [];

		if ( is_array( $defaults ) ) {
			foreach ( $defaults as $key => $title ) {
				$new_order[ $key ] = $title;
				if ( $key == 'title' ) {
					$new_order['has_audio'] = 'Has Audio';
					// TODO: enable this back when we replace keen with ES and read the number of listens from there.
					// $new_order[ 'no_of_plays' ] = 'No. of plays';
				}
			}
		}
			return $new_order;
	}

	/**
	 *  Play columns content
	 *
	 * @param $column_name
	 * @param $post_ID
	 */
	public function play_columns_content( $column_name, $post_ID ) {
		$podcast_data = get_post_meta( $post_ID, 'play_podcast_data', true );

		// No
		$has_audio = '<span class="playht-converting"><img src="https://firebasestorage.googleapis.com/v0/b/play-68705.appspot.com/o/images%2Fno-audio-grey.png?alt=media&token=d3cf7132-6a05-4e5f-a7b3-1d0e57771512" ><span>No</span></span>';
		$listens   = '0';

		$draft_actions = '';

		if ( Helpers::has_draft( $post_ID ) ) {
			$draft_actions = '<span class="playht-draft-actions" style="display:block;"><a style="cursor:pointer;text-decoration: underline;" id="playht-edit-draft" data-postid="' . $post_ID . '" >Edit Draft</a> - <a style="cursor:pointer;text-decoration: underline; color: red;" id="playht-delete-draft" data-postId="' . $post_ID . '" >Delete Draft</a></span>';
			$has_audio     = '';
		}

		if ( $podcast_data ) {
			$podcast_data = maybe_unserialize( $podcast_data );
			$listens      = isset( $podcast_data['listens'] ) ? $podcast_data['listens'] : 0;

			if ( $podcast_data['audio_status'] == 2 ) {
				// Yes
				$has_audio = '<span class="playht-converting"><img src="https://firebasestorage.googleapis.com/v0/b/play-68705.appspot.com/o/images%2Faudio-grey.png?alt=media&amp;token=d4ae8576-f576-4103-ab18-3eb5c1ca51d9" ><span>Yes</span></span>';
			}
			if ( $podcast_data['audio_status'] == 1 ) {
				// Converting
				$has_audio = '<span class="playht-converting"><img class="loading" src="https://firebasestorage.googleapis.com/v0/b/play-68705.appspot.com/o/images%2Fsettings.png?alt=media&token=58e7fc5a-aac9-4b5d-b721-6f7e2c986045" ><span>Converting</span></span>';
			}
		}

		// echo audio status
		if ( $column_name == 'has_audio' ) {
			echo $has_audio . PHP_EOL;
			echo $draft_actions;
		}

		// echo audio listens num
		if ( $column_name == 'no_of_plays' ) {
			echo $listens;
		}
	}


	/**
	 * add action_row controllers dor play
	 *
	 * 0, not-converted
	 * 1, converting
	 * 2, converted successfully
	 * 3, conversion failed
	 * 4, draft
	 *
	 * @param $actions
	 * @param $post
	 *
	 * @return array $actions
	 */
	public function add_play_action_row( $actions, $post ) {

		$podcast_data = get_post_meta( $post->ID, 'play_podcast_data', true );
		$podcast_data = maybe_unserialize( $podcast_data );

		$add_audio_action = [ 'add_podcast' => sprintf( '<a style="cursor:pointer;" id="playht-add-audio" data-postId="' . $post->ID . '" >Add Audio</a>' ) ];

		$edit_audio_action = [ 'edit_podcast' => sprintf( '<a style="cursor:pointer;" id="playht-edit-audio" data-postid="' . $post->ID . '" >Regenerate Audio</a>' ) ];

		$delete_audio_action = [ 'delete_podcast' => sprintf( '<a style="cursor:pointer;color:#b32d2e;" id="playht-delete-audio" data-postId="' . $post->ID . '" >Delete Audio</a>' ) ];

		if ( Helpers::has_draft( $post->ID ) ) {
			if ( isset( $podcast_data['audio_status'] ) && 2 === $podcast_data['audio_status'] ) {
				return array_merge( $actions, $delete_audio_action );
			}
			return $actions;
		}

		// No audio, no converted, only show Add audio.
		if ( $podcast_data === '' || in_array( $podcast_data['audio_status'], [ 0, 1 ] ) ) {
			return array_merge( $actions, $add_audio_action );
		}

		// Have audio, onlu show Edit and Delete.
		if ( 2 === $podcast_data['audio_status'] ) {
			return array_merge( $actions, $edit_audio_action, $delete_audio_action );
		}

		/**
		 * Return default actions, adding nothing.
		 * For draft, we show Edit draft and Delete draft in has audio column.
		 */
		if ( in_array( $podcast_data['audio_status'], [ 3, 4 ] ) ) {
			return $actions;
		}

		return array_merge( $actions, $add_audio_action, $edit_audio_action, $delete_audio_action );
	}
}
