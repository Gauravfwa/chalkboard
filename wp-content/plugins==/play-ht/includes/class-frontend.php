<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend logic
 *
 * @package Play_HT
 */
class Frontend extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 12 );

		/**
		 * Append podcast to post.
		 * Elementor uses 999999 and if we go fewer than them, player will not be shown in the elementor edit page.
		 */
		add_filter( 'the_content', [ $this, 'append_podcast_to_post' ], 2000000 );

		add_action( 'wp_footer', [ $this, 'show_play_btn' ] );
	}

	/**
	 * Load needed assets.
	 */
	public function enqueue_assets() {

		// Disbale in homepage.
		$disable_on_home = apply_filters( 'playht_disable_on_home', false );
		if ( is_home() && $disable_on_home ) {
			return false;
		}

		// Disable on frontpage.
		$disable_on_front_page = apply_filters( 'playht_disable_on_front_page', false );
		if ( is_front_page() && $disable_on_front_page ) {
			false;
		}

		$is_post_type_supported = playht_is_post_type_supported( get_post_type() );
		// Post type is not enabled in options.
		if ( ! $is_post_type_supported ) {
			return;
		}

		$podcast_data = maybe_unserialize( get_post_meta( get_the_ID(), 'play_podcast_data', true ) );
		// Audio status is not available.
		if ( ! isset( $podcast_data['audio_status'] ) ) {
			return;
		}

		// Audio is not converted.
		if ( $podcast_data['audio_status'] !== 2 ) {
			return;
		}

		$current_post       = get_post( get_the_ID() );
		$has_shortcode      = has_shortcode( $current_post->post_content, 'playht_player' );
		$has_lb_shortcode   = has_shortcode( $current_post->post_content, 'playht_listen_button' );
		$has_element        = playht_has_elementor_player( get_the_ID() );
		$has_article_player = get_option( 'playHt_articleplayer_switch', false );
		$has_title_player   = get_option( 'playht_Listenbutton_switch', '1' );
		$has_page_player    = get_option( 'playht_button_switch', false );

		// I is necessary to have the shortcode, or global page player, or title player to load needed assets.
		if ( ! ( $has_shortcode || $has_lb_shortcode || $has_title_player || $has_page_player || $has_article_player || $has_element ) ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		if ( $has_lb_shortcode || $has_title_player || $has_page_player || $has_element ) {
			wp_enqueue_script( 'playht-pageplayer-plugin' );
			wp_enqueue_style( 'playht-pageplayer-plugin' );
		}
		wp_enqueue_script( 'playht-pageplayer' );
		wp_enqueue_style( 'playht-pageplayer' );
	}

	/**
	 * Add the podcast to the post
	 *
	 * Show article player if enabled and there is no shortcode.
	 * Show title player (listen button) if enabled.
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function append_podcast_to_post( $content ) {

		global $post;
		$is_post_type_supported = playht_is_post_type_supported( get_post_type() );

		// Post type is not enabled in options.
		if ( ! $is_post_type_supported ) {
			return $content;
		}

		$podcast_data = maybe_unserialize( get_post_meta( get_the_ID(), 'play_podcast_data', true ) );
		// Audio status is not available.
		if ( ! isset( $podcast_data['audio_status'] ) ) {
			return $content;
		}

		// Audio is not converted.
		if ( $podcast_data['audio_status'] !== 2 ) {
			return $content;
		}

		$has_shortcode      = has_shortcode( $post->post_content, 'playht_player' );
		$has_lb_shortcode   = has_shortcode( $post->post_content, 'playht_listen_button' );
		$has_element        = playht_has_elementor_player( $post->ID );
		$has_article_player = get_option( 'playHt_articleplayer_switch', false );
		$has_title_player   = get_option( 'playht_Listenbutton_switch', '1' );
		$has_page_player    = get_option( 'playht_button_switch', false );

		// return if no type of player is enabled.
		if ( ! ( $has_shortcode || $has_title_player || $has_page_player || $has_article_player || $has_element ) ) {
			return $content;
		}

		$podcast     = '';
		$play_button = '';

		// Show Article Player.
		if ( $has_article_player && ! $has_shortcode && ! $has_element ) {
			$podcast = playht_load_view(
				'front-end/podcast_iframe',
				[
					'article_url'   => $podcast_data['url'],
					'article_voice' => $podcast_data['voice'],
					'trans_id'      => $podcast_data['trans_id'],
					'blog_app_id'   => get_option( 'wppp_blog_appId' ),
				],
				true
			);
		}

		// Has page player or has listen_button but not manually added.
		if ( ( $has_title_player || $has_page_player ) && ! $has_lb_shortcode ) {
			$play_button = playht_listen_button( [ 'post_id' => $post->ID ] );
		}

		return $play_button . $podcast . $content;
	}

	public function show_play_btn() {
		echo "<script>document.querySelectorAll('.playHtListenArea').forEach(function(el) {el.style.display = 'block'});</script>";
	}

}
