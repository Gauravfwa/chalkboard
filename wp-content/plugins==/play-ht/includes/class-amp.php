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
class AMP extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// For Accelerated Mobile Pages (AMP)
		add_action( 'pre_amp_render_post', [ $this, 'amp_content_filters' ] );
	}

	/**
	 * For Accelerated Mobile Pages (AMP)
	 */
	public function amp_content_filters() {
		// Remove Filter
		remove_filter( 'the_content', [ $this, 'append_podcast_to_post' ] );
		remove_action( 'wp_enqueue_scripts', [ $this, 'load_play_scripts' ] );

		add_filter( 'the_content', [ $this, 'palyiframe_shortcodes' ] );
		add_action( 'amp_post_template_css', [ $this, 'ampforwp_add_custom_css' ] );
	}

	/**
	 * Function For Accelerated Mobile Pages (AMP)
	 */
	public function palyiframe_shortcodes( $content ) {

		global $post;
		$podcast_meta_data = get_post_meta( get_the_ID(), 'play_podcast_data', true );
		$podcast_data      = maybe_unserialize( $podcast_meta_data );

		if ( is_array( $podcast_data ) && $podcast_data['audio_status'] && $podcast_data['audio_status'] == 2 ) {
			$podcast = playht_load_view(
				'front-end/podcast_iframe',
				[
					'article_url'   => $podcast_data['url'],
					'article_voice' => $podcast_data['voice'],
					'blog_app_id'   => get_option( 'wppp_blog_appId' ),
				],
				true
			);
			return $podcast . $content;
		}
		return $content;
	}

	/**
	 * Add Custom Css For Accelerated Mobile Pages (AMP)
	 */
	public function ampforwp_add_custom_css() {     ?>
		amp-iframe.playht-iframe-player{background: inherit;}
		amp-iframe.playht-iframe-player{width: 100%;}
		<?php
	}
}
