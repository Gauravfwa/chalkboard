<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Requests extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// conversion scripts
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_play_requests' ), 10 );
	}

	private function enqueue_common_conversion_scripts() {
		wp_enqueue_script( 'regenerator_runtime' );
		wp_enqueue_script( 'analytics' );
		wp_enqueue_script( 'sweetalert' );
		wp_enqueue_script( 'save_conversion_data' );
	}

	private function add_audio_edit_screen() {
		if ( ! playht_is_post_type_supported() ) {
			return;
		}

		$this->enqueue_common_conversion_scripts();

		// get the current user selected screen items_per_page option
		$screen = get_current_screen();

		$option            = $screen->get_option( 'per_page', 'option' );
		$user              = get_current_user_id();
		$current_post_type = playht_get_admin_post_type();

		$per_page = get_user_meta( $user, $option, true );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		$paged      = ( get_query_var( 'paged' ) ) ? ( get_query_var( 'paged' ) - 1 ) : 0;
		$postOffset = $paged * $per_page;

		$args = array(
			'post_type'      => $current_post_type, // sfwd-lessons // lp_lesson
			'post_status'    => 'any',
			'offset'         => $postOffset,
			'posts_per_page' => $per_page,
			'orderby'        => get_query_var( 'orderby' ),
			'order'          => get_query_var( 'order' ),
			's'              => get_search_query(),
		);

		$post_list     = get_posts( $args );
		$posts_meta    = array();
		$posts_authors = array();

		foreach ( $post_list as $post ) {
			$id                   = $post->ID;
			$posts_meta[ $id ]    = array_filter( $this->prepare_post_meta( $post ) );
			$posts_authors[ $id ] = $this->prepare_author_data( $post );
		}

		wp_localize_script(
			'save_conversion_data',
			'wppp_conv_data',
			array(
				'admin_url'         => admin_url( 'admin-ajax.php' ),
				'rest_endpoint'     => esc_url_raw( rest_url() ),
				'nonce'             => wp_create_nonce( 'wp_rest' ),
				'request_processed' => __( 'Your request is being processed.', 'play-ht' ),
				'can_convert'       => playht_conversion_check(),
				'home_url'          => home_url( '?p=' ),
				'appId'             => get_option( 'wppp_blog_appId' ),
				'userId'            => get_option( 'wppp_blog_userId' ),
				'posts'             => $posts_meta,
				'current_credits'   => playht_get_credits(),
				'posts_authors'     => $posts_authors,
			)
		);
	}

	private function prepare_author_data( $post ) {
		$author_id                       = $post->post_author;
		$post_author['author_image']     = get_the_author_meta( 'avatar', $author_id );
		$post_author['author_name']      = get_the_author_meta( 'nickname', $author_id );
		$post_author['author_firstname'] = get_the_author_meta( 'user_firstname', $author_id );
		$post_author['author_lastname']  = get_the_author_meta( 'user_lastname', $author_id );
		return $post_author;
	}

	private function prepare_post_meta( $post ) {
		$post_meta                   = (array) maybe_unserialize( get_post_meta( $post->ID, 'play_podcast_data', true ) );
		$post_meta['url']            = home_url( '?p=' . $post->ID );
		$post_meta['title']          = $post->post_title;
		$post_meta['published_date'] = $post->post_date_gmt;

		if ( $post_meta['published_date'] === '0000-00-00 00:00:00' ) {
			$post_meta['published_date'] = $post->post_modified_gmt;
		}

		$post_meta['image'] = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
		return $post_meta;
	}

	private function handle_first_load() {
		$appId  = filter_input( INPUT_GET, 'appId', FILTER_SANITIZE_STRING );
		$userId = filter_input( INPUT_GET, 'uid', FILTER_SANITIZE_STRING );
		$blog   = site_url();

		if ( preg_match( '/https?:/', site_url() ) ) {
			$blog = preg_replace( '/https?:\/\//', '', site_url() );
		}

		// save play query params
		update_option( 'wppp_blog_appId', $appId );
		update_option( 'wppp_blog_userId', $userId );

		// flag 1 (user connected to play)
		update_option( 'wppp_status_flag', 1 );

		wp_enqueue_script( 'add_blog_domain' );

		// localizing script.
		wp_localize_script(
			'add_blog_domain',
			'wppp_blog',
			array(
				'ajax_url' => ADD_ORIGIN_URL,
				'user_id'  => $userId,
				'app_id'   => $appId,
				'bolg'     => $blog,
			)
		);

		// initialize firebase
		wp_enqueue_script( 'initialize_firebase' );

		wp_enqueue_script( 'retrieve_user_data' );

		// localizing script.
		wp_localize_script(
			'retrieve_user_data',
			'wppp_retrieve',
			array(
				'user_id'  => get_option( 'wppp_blog_userId' ),
				'app_id'   => get_option( 'wppp_blog_appId' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);

		// send referral theme
		wp_enqueue_script( 'referral_theme' );

		// localizing script.
		wp_localize_script(
			'referral_theme',
			'wppp_theme',
			array(
				'ajax_url' => REFERRAL_URL,
				'user_id'  => $userId,
				'theme'    => get_template(),
			)
		);
	}

	public function load_admin_play_requests() {

		// handle back url upon register and subscription, load script to save appId and uid
		if ( isset( $_GET['appId'] ) && isset( $_GET['uid'] ) && isset( $_GET['page'] ) && 'play-welcome-page' == $_GET['page'] ) {

			$this->handle_first_load();
		}

		// load conversion script only in edit
		if ( 'post-new.php' === $GLOBALS['pagenow'] || isset( $_GET['action'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {

			$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

			$this->enqueue_common_conversion_scripts();

			$post = get_post( $post_id );

			wp_localize_script(
				'save_conversion_data',
				'wppp_conv_data',
				array(
					'admin_url'         => admin_url( 'admin-ajax.php' ),
					'post_id'           => $post_id,
					'article_url'       => home_url( '?p=' . $post_id ),
					'home_url'          => home_url( '?p=' ),
					'request_processed' => __( 'Your article is sent in order to be converted.', 'play-ht' ),
					'can_convert'       => playht_conversion_check(),
					'appId'             => get_option( 'wppp_blog_appId' ),
					'userId'            => get_option( 'wppp_blog_userId' ),
					'post'              => $this->prepare_post_meta( $post ),
					'current_credits'   => playht_get_credits(),
					'post_author'       => $this->prepare_author_data( $post ),
					'post_title'        => $post->post_title,
				)
			);
		}

		$current_screen = get_current_screen();

		if ( 'edit' === $current_screen->base ) {
			$this->add_audio_edit_screen();
		}
	}

}
