<?php

namespace Wordpress\Play;

class Play_Requests extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// conversion scripts
		add_action( 'admin_enqueue_scripts', [ &$this, 'load_admin_play_requests' ], 10 );
	}

	/**
	 * Load scripts for requests
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_admin_play_requests() {
		$post_author = [];
		$post_id     = '';

		// handle back url upon register and subscription, load script to save appId and uid
		if ( isset( $_GET['appId'] ) && isset( $_GET['uid'] ) && isset( $_GET['page'] ) && 'play-welcome-page' == $_GET['page'] ) {

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
				[
					'ajax_url' => ADD_ORIGIN_URL,
					'user_id'  => $userId,
					'app_id'   => $appId,
					'bolg'     => $blog,
				]
			);

			// initialize firebase
			wp_enqueue_script( 'initialize_firebase' );

			wp_enqueue_script( 'retrieve_user_data' );

			// localizing script.
			wp_localize_script(
				'retrieve_user_data',
				'wppp_retrieve',
				[
					'user_id'  => get_option( 'wppp_blog_userId' ),
					'app_id'   => get_option( 'wppp_blog_appId' ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				]
			);

			// send referral theme
			wp_enqueue_script( 'referral_theme' );

			// localizing script.
			wp_localize_script(
				'referral_theme',
				'wppp_theme',
				[
					'ajax_url' => REFERRAL_URL,
					'user_id'  => $userId,
					'theme'    => get_template(),
				]
			);
		}

		// load conversion script only in edit
		if ( 'post-new.php' === $GLOBALS['pagenow'] || isset( $_GET['action'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {

			$post_id = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
			// google analytics script
			wp_enqueue_script( 'analytics' );

			// regeneratorRuntime polyfill
			wp_enqueue_script( 'regenerator_runtime' );

			wp_enqueue_script( 'sweetalert' );

			wp_enqueue_script( 'save_conversion_data' );

			$credits = maybe_unserialize( get_option( 'wppp_play_user_data' ) );
			$credits = $credits['usage'];

			$post      = get_post( $post_id );
			$post_meta = (array) maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );

			$post_meta['title']          = $post->post_title;
			$post_meta['published_date'] = $post->post_date_gmt;
			$post_meta['image']          = wp_get_attachment_url( get_post_thumbnail_id( $post_id ), 'thumbnail' );

			$author_id                       = $post->post_author;
			$post_author['author_image']     = get_the_author_meta( 'avatar', $author_id );
			$post_author['author_name']      = get_the_author_meta( 'nickname', $author_id );
			$post_author['author_firstname'] = get_the_author_meta( 'user_firstname', $author_id );
			$post_author['author_lastname']  = get_the_author_meta( 'user_lastname', $author_id );

			$article_url = home_url( '?p=' . $post_id );

			wp_localize_script(
				'save_conversion_data',
				'wppp_conv_data',
				[
					'admin_url'         => admin_url( 'admin-ajax.php' ),
					'post_id'           => $post_id,
					'article_url'       => $article_url,
					'home_url'          => home_url( '?p=' ),
					'request_processed' => __( 'Your article is sent in order to be converted.', 'play-ht' ),
					'can_convert'       => wppp_conversion_check(),
					'appId'             => get_option( 'wppp_blog_appId' ),
					'userId'            => get_option( 'wppp_blog_userId' ),
					'post'              => $post_meta,
					'current_credits'   => $credits,
					'post_author'       => $post_author,
					'post_title'        => $post->post_title,
				]
			);
		}

		$current_screen = get_current_screen();

		if ( 'edit' === $current_screen->base ) {

			if ( ! Helpers::is_post_type_supported() ) {
				return;
			}

			// regeneratorRuntime polyfill
			wp_enqueue_script( 'regenerator_runtime' );
			// google analytics script
			wp_enqueue_script( 'analytics' );
			wp_enqueue_script( 'sweetalert' );
			wp_enqueue_script( 'save_conversion_data' );

			// get the current user selected screen items_per_page option
			$screen = get_current_screen();

			$option            = $screen->get_option( 'per_page', 'option' );
			$user              = get_current_user_id();
			$current_post_type = Helpers::get_admin_post_type();

			$per_page = get_user_meta( $user, $option, true );
			if ( empty( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}

			$paged      = ( get_query_var( 'paged' ) ) ? ( get_query_var( 'paged' ) - 1 ) : 0;
			$postOffset = $paged * $per_page;

			$args = [
				'post_type'      => $current_post_type, // sfwd-lessons // lp_lesson
				'post_status'    => 'any',
				'offset'         => $postOffset,
				'posts_per_page' => $per_page,
				'orderby'        => get_query_var( 'orderby' ),
				'order'          => get_query_var( 'order' ),
				's'              => get_search_query(),
			];

			$post_list     = get_posts( $args );
			$posts_meta    = [];
			$posts_authors = [];
			foreach ( $post_list as $post ) {
				$id        = $post->ID;
				$author_id = $post->post_author;

				$posts_meta[ $id ]          = (array) maybe_unserialize( get_post_meta( $id, 'play_podcast_data', true ) );
				$posts_meta[ $id ]['url']   = home_url( '?p=' . $id );
				$posts_meta[ $id ]['image'] = maybe_unserialize( get_the_post_thumbnail_url( $id ) );

				$posts_meta[ $id ]['title']          = $post->post_title;
				$posts_meta[ $id ]['published_date'] = $post->post_date_gmt;
				if ( $posts_meta[ $id ]['published_date'] === '0000-00-00 00:00:00' ) {
					$posts_meta[ $id ]['published_date'] = $post->post_modified_gmt;
				}
				$posts_meta[ $id ]['image'] = wp_get_attachment_url( get_post_thumbnail_id( $id ), 'thumbnail' );

				$posts_meta[ $id ] = array_filter( $posts_meta[ $id ] );

				$posts_authors[ $id ]['author_image']     = get_avatar_url( $author_id, 146 );
				$posts_authors[ $id ]['author_name']      = get_the_author_meta( 'nickname', $author_id );
				$posts_authors[ $id ]['author_firstname'] = get_the_author_meta( 'user_firstname', $author_id );
				$posts_authors[ $id ]['author_lastname']  = get_the_author_meta( 'user_lastname', $author_id );
			}

			$credits = maybe_unserialize( get_option( 'wppp_play_user_data' ) );
			$credits = $credits['usage'];

			wp_localize_script(
				'save_conversion_data',
				'wppp_conv_data',
				[
					'admin_url'         => admin_url( 'admin-ajax.php' ),
					'rest_endpoint'     => esc_url_raw( rest_url() ),
					'nonce'             => wp_create_nonce( 'wp_rest' ),
					'request_processed' => __( 'Your request is being processed.', 'play-ht' ),
					'can_convert'       => wppp_conversion_check(),
					'home_url'          => home_url( '?p=' ),
					'appId'             => get_option( 'wppp_blog_appId' ),
					'userId'            => get_option( 'wppp_blog_userId' ),
					'posts'             => $posts_meta,
					'current_credits'   => $credits,
					'posts_authors'     => $posts_authors,
				]
			);
		}

		if ( isset( $_GET['play'] ) ) {
			if ( 'edit' === $current_screen->base && $_GET['play'] == 'del_podcast' ) {
				$this->handle_delete_process( ( $_GET['post'] ) );
			}
		}
	}

	/**
	 * Handle podcast deletion
	 *
	 * @param $post_id
	 */
	public function handle_delete_process( $post_id ) {
		update_post_meta( $post_id, 'deleted_podcast', get_post_meta( $_GET['post'], 'play_podcast_data', true ) );
		delete_post_meta( $post_id, 'play_podcast_data' );
	}
}
