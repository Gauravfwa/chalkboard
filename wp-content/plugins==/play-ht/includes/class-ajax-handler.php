<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler
 *
 * @package Play_HT
 */
class Ajax_Handler extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		add_action( 'wp_ajax_get_post_content_data', [ $this, 'get_post_content_data' ] );
		add_action( 'wp_ajax_save_post_conversion_data', [ $this, 'save_post_conversion_data' ] );
		add_action( 'wp_ajax_article_converted_success', [ $this, 'article_converted_success' ] );
		add_action( 'wp_ajax_save_draft', [ $this, 'save_draft' ] );
		add_action( 'wp_ajax_delete_draft', [ $this, 'delete_draft' ] );
		add_action( 'wp_ajax_delete_article_audio', [ $this, 'delete_article_audio' ] );
		add_action( 'wp_ajax_retry_article_audio', [ $this, 'retry_article_audio' ] );
		add_action( 'wp_ajax_refresh_audio_menu', [ $this, 'refresh_audio_menu' ] );
		add_action( 'wp_ajax_refresh_action_rows', [ $this, 'refresh_action_rows' ] );
		add_action( 'wp_ajax_article_converted_failed', [ $this, 'article_converted_failed' ] );
		add_action( 'wp_ajax_retrieve_user_data', [ $this, 'retrieve_user_data' ] );
		add_action( 'wp_ajax_retrieve_article_enable', [ $this, 'retrieve_article_enable' ] );
		add_action( 'wp_ajax_retrieve_default_voice', [ $this, 'retrieve_default_voice' ] );
		add_action( 'wp_ajax_retrieve_post_meta_data', [ $this, 'retrieve_post_meta_data' ] );
		add_action( 'wp_ajax_playht_set_user_data', [ $this, 'playht_set_user_data' ] );
	}

	public function get_post_content_data() {
		if ( ! isset( $_POST ) || empty( $_POST ) ) {
			wp_send_json_error( [ 'data' => __( 'Error, Please provide a valid data', 'play-ht' ) ] );
		}

		$post_id               = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$article_content       = '<p>' . get_post_field( 'post_title', $post_id ) . '</p>' . do_shortcode( html_entity_decode( get_post_field( 'post_content', $post_id ) ) );
		$article_content       = str_replace( '&nbsp;', ' ', $article_content );
		$article_content       = str_replace( '</p>', "\n", $article_content );
		$article_content_array = explode( "\n", wp_strip_all_tags( $article_content ) );

		wp_send_json_success( [ 'response' => $article_content_array ] );
	}

	/**
	 * Save conversion data // call to conversion url with data on playht server
	 */
	public function save_post_conversion_data() {
		if ( ! isset( $_POST ) || empty( $_POST ) ) {
			wp_send_json_error( [ 'data' => __( 'Error, Please provide a valid data', 'play-ht' ) ] );
		}

		$post_id               = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$ssml                  = json_decode( filter_input( INPUT_POST, 'ssml' ) );
		$language              = filter_input( INPUT_POST, 'lang', FILTER_SANITIZE_STRING );
		$voice                 = filter_input( INPUT_POST, 'voice', FILTER_SANITIZE_STRING );
		$wordsCount            = filter_input( INPUT_POST, 'wordsCount', FILTER_SANITIZE_STRING );
		$narrationStyle        = filter_input( INPUT_POST, 'narrationStyle', FILTER_SANITIZE_STRING );
		$audiospeed            = filter_input( INPUT_POST, 'globalSpeed', FILTER_SANITIZE_STRING );
		$play_article_id       = filter_input( INPUT_POST, 'play_article_id', FILTER_SANITIZE_STRING );
		$article_fallback_url  = filter_input( INPUT_POST, 'article_fallback_url', FILTER_SANITIZE_STRING );
		$post_url              = ( $article_fallback_url != 'NaN' || strpos( $article_fallback_url, 'undefined' ) == false || strpos( $article_fallback_url, get_site_url() ) == false ) ? $article_fallback_url : home_url( '?p=' . $post_id );
		$article_content       = '<p>' . get_the_title( $post_id ) . "</p>\n\n" . do_shortcode( html_entity_decode( get_post_field( 'post_content', $post_id ) ) );
		$article_content       = str_replace( '</p><p>', ' ', $article_content );
		$article_content       = str_replace( '&nbsp;', ' ', $article_content );
		$article_content_array = wp_strip_all_tags( $article_content );
		$article_content_array = explode( "\n\n", $article_content_array );
		$request_params        = [
			'url'            => $post_url,
			'voice'          => $voice,
			'narrationStyle' => $narrationStyle,
			'globalSpeed'    => $audiospeed,
			'appId'          => get_option( 'wppp_blog_appId' ),
			'content'        => $article_content_array,
			'ssml'           => $ssml,
			'wordsCount'     => $wordsCount,
			'platform'       => 'wordpress_plugin',
		];

		$transcription_response = wp_remote_post(
			CONVERSION_URL,
			[
				'headers' => [ 'Content-Type' => 'application/json; charset=utf-8' ],
				'body'    => wp_json_encode( $request_params ),
				'method'  => 'POST',
				'timeout' => 50, // the default is 5 seconds, and used to fail sometimes!
			]
		);

		if ( is_wp_error( $transcription_response ) ) {
			$error_message = $transcription_response->get_error_message();
			wp_send_json_error(
				[
					'sent'  => false,
					'error' => $error_message,
				]
			);
		} else {
			$parsed_body       = json_decode( wp_remote_retrieve_body( $transcription_response ), true );
			$trans_id          = $parsed_body['transcriptionId'];

			update_post_meta(
				$post_id,
				'play_podcast_data',
				maybe_serialize(
					[
						'url'             => $post_url,
						'lang'            => $language,
						'voice'           => $voice,
						'trans_id'        => $trans_id,
						'audio_status'    => 1, // 0 not-converted, 1 converting, 2 Done
						'listens'         => 0,
						'convertion_time' => date( 'Y-m-d H:i:s' ),
						'play_article_id' => $play_article_id,
					]
				)
			);

			wp_send_json_success( [ 'response' => $transcription_response ] );
		}

		exit( wp_json_encode( $this ) );
	}

	/**
	 * On Article Conversion Sucess
	 */
	public function article_converted_success() {
		// Get the Post ID
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );

		// Get All Stored Play data
		$podcast_data = maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );

		// Update the needed data
		$podcast_data['trans_id']      = filter_input( INPUT_POST, 'trans_id', FILTER_SANITIZE_STRING );
		$podcast_data['article_audio'] = filter_input( INPUT_POST, 'audio_url', FILTER_SANITIZE_URL );
		$podcast_data['audio_status']  = 2; // converted successfully

		// update post meta
		update_post_meta( $post_id, 'play_podcast_data', $podcast_data );
		delete_post_meta( $post_id, 'playht_draft' );
		playht_load_view( 'back-end/metabox_content', [ 'post_id' => $post_id ] );
		exit;
	}

	public function save_draft() {

		$post_id         = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$voice           = filter_input( INPUT_POST, 'voice', FILTER_SANITIZE_STRING );
		$play_article_id = filter_input( INPUT_POST, 'play_article_id', FILTER_SANITIZE_STRING );

		update_post_meta(
			$post_id,
			'playht_draft',
			[
				'audio_status'    => 4, // draft
				'listens'         => 0,
				'voice'           => $voice,
				'play_article_id' => $play_article_id,
			]
		);
	}

	public function delete_draft() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$deleted = delete_post_meta( $post_id, 'playht_draft' );
		if ( $deleted ) {
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	/**
	 * Delete audio on WordPress
	 */
	public function delete_article_audio() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );

		update_post_meta( $post_id, 'deleted_podcast', get_post_meta( $post_id, 'play_podcast_data', true ) );
		$deleted = delete_post_meta( $post_id, 'play_podcast_data' );

		if ( $deleted ) {
			wp_send_json_success();
		}
		wp_send_json_error( $post_id );
	}

	/**
	 * Retry audio conversion on WordPress if its showing error status
	 */
	public function retry_article_audio() {
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );

		$podcast_data = get_post_meta( $post_id, 'play_podcast_data', true );
		$podcast_data = maybe_unserialize( $podcast_data );

		if ( ! empty( $podcast_data['trans_id'] ) ) {
			wp_send_json_success( [ 'response' => $podcast_data['trans_id'] ] );
		} else {
			wp_send_json_success( [ 'response' => 'false' ] );
		}
	}

	/**
	 * Refresh metabox after deleting audio
	 */
	public function refresh_audio_menu() {
		if ( isset( $_POST['post_id'] ) ) {
			playht_load_view(
				'back-end/metabox_content',
				[
					'post_id' => sanitize_text_field( $_POST['post_id'] ),
				]
			);
		}
		exit;
	}

	public function refresh_action_rows() {
		if ( isset( $_POST['post_id'] ) ) {
			echo playht_action_rows( sanitize_text_field( $_POST['post_id'] ) );
		}
		exit;
	}

	/**
	 * On Article Conversion Failure
	 */
	public function article_converted_failed() {
		// Get the Post ID
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );

		// Get All Stored Play data
		$podcast_data = maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );

		// Update the needed data
		$podcast_data['article_error'] = 'audio conversion failed';
		$podcast_data['audio_status']  = 3; // conversion failed

		// update post meta
		update_post_meta( $post_id, 'play_podcast_data', $podcast_data );
	}

	/**
	 * Retrieve User data
	 */
	public function retrieve_user_data() {
		$userData                      = $_POST['userData'];
		$userData['plugin_user_saved'] = 1;

		$appId = $userData['appId'];
		// save Play user data
		update_option( 'wppp_play_user_data', maybe_serialize( $userData ) );
		update_option( 'wppp_blog_appid', maybe_serialize( $appId ) );

		do_action( 'Play/retrieve_user_data' );
	}

	public function retrieve_article_enable() {
		$enable_ap = $_POST['enable_ap'];
		// save Play user data
		update_option( 'playHt_articleplayer_switch', $enable_ap );
	}

	public function playht_set_user_data() {
		$voice = '';
		if ( isset( $_POST['value'] ) ) {
			$voice = sanitize_text_field( $_POST['value'] );
		}

		$user_data = maybe_unserialize( get_option( 'wppp_play_user_data' ) );

		if ( isset( $user_data['conf'] ) && isset( $user_data['conf']['default_voice'] ) && ! empty( $voice ) ) {
			$user_data['conf']['default_voice'] = $voice;
		}

		update_option( 'wppp_play_user_data', maybe_serialize( $user_data ) );
		wp_send_json_success();
	}

	public function retrieve_default_voice() {
		$user_data = maybe_unserialize( get_option( 'wppp_play_user_data' ) );

		if ( ! empty( $user_data ) && ! empty( $user_data['conf']['default_voice'] ) ) {
			$default_voice = $user_data['conf']['default_voice'];
		} else {
			$default_voice = 'Noah';
		}

		wp_send_json_success( [ 'response' => $default_voice ] );
	}

	/**
	 * retrieve_post_meta_data
	 *
	 * @return void
	 */
	public function retrieve_post_meta_data() {
		$post_id   = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
		$post_meta = maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );

		if ( ! is_array( $post_meta ) ) {
			$post_meta = (array) maybe_unserialize( $post_meta );
		}

		$post_draft_meta = (array) get_post_meta( $post_id, 'playht_draft', true );

		$response = array_filter( array_merge( $post_meta, $post_draft_meta ) );

		wp_send_json_success( [ 'response' => $response ] );
	}
}
