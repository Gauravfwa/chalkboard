<?php

use Play_HT\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function playht_load_view( $view_name, $args = null, $return = false ) {
	if ( $return ) {
		// start buffer
		ob_start();
	}

	Plugin::get_instance()->load_view( $view_name, $args );

	if ( $return ) {
		// get buffer flush
		return ob_get_clean();
	}
}

function playht_conversion_check() {
	// if user not registered return false
	if ( false === get_option( 'wppp_blog_appId' ) || false === get_option( 'wppp_blog_userId' ) ) {
		return new WP_Error( 'play', __( 'You must login first to be able to use play.ht to add audio to your articles.', 'play-ht' ) );
	}

	// check quota end date
	$user_data = maybe_unserialize( get_option( 'wppp_play_user_data' ) );
	if ( ! empty( $user_data['package'] ) ) {
		if ( time() < $user_data['package']['pro_package_end'] ) {
			return new WP_Error( 'play', __( 'Your subscription is over, you need to renew your subscription.', 'play-ht' ) );
		}
	}
	//@todo check quota
	return 1;
}

function playht_get_post_listens_count( $app_id, $article_id, $post_id ) {

	$url = 'https://a.play.ht/listens/article/?app_id=' . $app_id . '&created_at__gte=' . get_the_date( 'Y-m-d', $post_id ) . '&created_at__lte=' . date( 'Y-m-d' ) . '&article_id=' . $article_id;

	$response = wp_remote_get( $url, [ 'timeout' => 60 ] );

	if ( ! wp_remote_retrieve_response_code( $response ) ) {
		return 0;
	}

	if ( is_wp_error( $response ) ) {
		return 0;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ) );
	return $body->hits->total;
}

function playht_get_post_listens_time( $app_id, $article_id, $post_id ) {

	$url = 'https://a.play.ht/listeningtime/article/?app_id=' . $app_id . '&created_at__gte=' . get_the_date( 'Y-m-d', $post_id ) . '&created_at__lte=' . date( 'Y-m-d' ) . '&article_id=' . $article_id;

	$response = wp_remote_get( $url, [ 'timeout' => 60 ] );

	if ( ! wp_remote_retrieve_response_code( $response ) ) {
		return 0;
	}

	if ( is_wp_error( $response ) ) {
		return 0;
	}

	$body        = json_decode( wp_remote_retrieve_body( $response ) );
	$minutes_tot = ( $body->hits->total * 10 ) / 60;
	return round( $minutes_tot, 2 );
}

function playht_player( array $config = [] ) {

	$post_id = get_the_ID();
	$app_id  = get_option( 'wppp_blog_appId' );

	$player_width  = $config['width'];
	$player_height = $config['height'];

	if ( playht_has_audio( $post_id ) ) {
		// Podcast stored data
		$podcast_data = maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );

		$article_url   = ! empty( $podcast_data['url'] ) ? $podcast_data['url'] : '';
		$article_voice = ! empty( $podcast_data['voice'] ) ? $podcast_data['voice'] : '';

		// {“title”:“Audio”,%20"message”:%20"hello”,%20"download_button_text”:%20"D”} phpcs:ignore
		$json_config = wp_json_encode( $config, true );

		return '
		<div
			id="playht-iframe-wrapper"
			style="display:flex; max-height:' . $player_height . '; justify-content:' . $config['alignment'] . '">
			<iframe
				allowfullscreen=""
				frameborder="0"
				scrolling="no"
				class="playht-iframe-player"
				data-appId="' . $app_id . '"
				article-url="' . $article_url . '"
				data-voice="' . $article_voice . '"
				src="https://play.ht/embed/?article_url=' . $article_url . '&voice=' . $article_voice . '&config=' . esc_attr( $json_config ) . '"
				width="' . $player_width . '"
				height="' . $player_height . '"
				style="max-height:' . $player_height . '">
			</iframe>
		</div>';
	}

	return false;
}

function playht_has_audio( $post_id = 0 ) {
	if ( ! $post_id ) {
		return false;
	}

	$podcast_data = maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );
	// 2: converted audio.
	return ( isset( $podcast_data['audio_status'] ) && 2 === $podcast_data['audio_status'] );
}

function playht_has_elementor_player( $post_id = 0 ) {
	if ( ! $post_id ) {
		return false;
	}

	if ( ! class_exists( 'Elementor\Plugin' ) ) {
		return false;
	}

	if ( ! Elementor\Plugin::instance()->db->is_built_with_elementor( $post_id ) ) {
		return false;
	}

	$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

	if ( empty( $elementor_data ) ) {
		return false;
	}

	if ( false !== strpos( $elementor_data, '"widgetType":"playht"' ) ) {
		return true;
	}

	return false;
}

function playht_listen_button( $config = [] ) {
	if ( ! isset( $config['post_id'] ) ) {
		return '';
	}

	$tag = 'div';

	if ( 'yes' == $config['inline'] ) {
		$tag = 'span';
	}

	$podcast_data = maybe_unserialize( get_post_meta( $config['post_id'], 'play_podcast_data', true ) );

	if ( ! is_array( $podcast_data ) ) {
		return '';
	}

	$article_url   = $podcast_data['url'];
	$article_audio = $podcast_data['article_audio'];

	ob_start();
	?>
	<<?php echo $tag;?> class="playHtListenArea" style="display:none;margin: 0;">
		<<?php echo $tag; ?> id="playht-audioplayer-element" data-play-article="<?php echo $article_url; ?>" data-play-audio="<?php echo $article_audio; ?>">
		</<?php echo $tag; ?>>
	</<?php echo $tag; ?>>
	<?php

	return ob_get_clean();
}

function playht_has_listen_button() {
	$post_content = get_the_content( get_the_ID() );

	if ( has_shortcode( $post_content, 'playht_listen_button' ) ) {
		return 1;
	}

	return get_option( 'playht_Listenbutton_switch', '1' );
}

function playht_get_admin_post_type() {

	global $post, $typenow, $current_screen, $pagenow;

	if ( $post && $post->post_type ) {
		return $post->post_type;
	} elseif ( $typenow ) {
		return $typenow;
	} elseif ( $current_screen && $current_screen->post_type ) {
		return $current_screen->post_type;
	} elseif ( isset( $_REQUEST['post_type'] ) ) {
		return sanitize_key( $_REQUEST['post_type'] );
	} elseif ( 'post.php' === $pagenow && isset( $_GET['post'] ) ) {
		return get_post_type( $_GET['post'] );
	} elseif ( 'edit.php' === $pagenow && ! isset( $_GET['post'] ) ) {
		return 'post';
	}

	return null;
}

function playht_is_post_type_supported( $current_post_type = null ) {

	if ( ! $current_post_type ) {
		$current_post_type = playht_get_admin_post_type();
	}

	$supported_post_types = [];
	$post_type_option     = get_option( 'playht_type_switch', [] );

	if ( is_array( $post_type_option ) ) {
		$supported_post_types = array_keys( $post_type_option );
	}

	return in_array( $current_post_type, $supported_post_types, true );
}

function playht_has_draft( $post_id ) {
	return (bool) get_post_meta( $post_id, 'playht_draft', true );
}

function playht_get_credits() {
	$credits = maybe_unserialize( get_option( 'wppp_play_user_data' ) );

	if ( isset( $credits['usage'] ) ) {
		return $credits['usage'];
	}

	return 0;
}

/**
 * 0, not-converted
 * 1, converting
 * 2, converted successfully
 * 3, conversion failed
 * 4, draft
 */
function playht_action_rows( $post_id ) {
	$podcast_data = maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );
	$links = '';

	if ( playht_has_draft( $post_id ) ) {
		$links .= '<div class="playht-draft-actions" style="display:block;"><a style="cursor:pointer;text-decoration: none;" id="playht-edit-draft" data-postid="' . $post_id . '" >' . __( 'Edit Draft', 'play-ht' ) . '</a> <span style="color:#dcdcde;">|</span> <a style="cursor:pointer;text-decoration: none; color:#b32d2e;" id="playht-delete-draft" data-postId="' . $post_id . '" >' . __( 'Delete Draft', 'play-ht' ) . '</a></div>';
	} elseif ( ! isset( $podcast_data['audio_status'] ) || in_array( $podcast_data['audio_status'], [ 0, 1 ] ) ) {
		$links .= '<a style="cursor:pointer;" id="playht-add-audio" data-postId="' . $post_id . '" >' . __( 'Add Audio', 'play-ht' ) . '</a>';
	} elseif ( 2 === $podcast_data['audio_status'] ) {
		// Lang is empty.
		$links .= '<span style="vertical-align:-3px; cursor: pointer;" class="playht-admin-audio-player" data-audio="' . esc_attr( $podcast_data['article_audio'] ) . '"><span class="playht-icon-play-circled"></span></span>';
		$links .= '<span style="vertical-align:middle;" class="playht-converted-audio-details">' . __( 'Listen', 'play-ht' ) . '</span> ';
		$links .= '<div><a style="cursor:pointer;" id="playht-edit-audio" data-postid="' . $post_id . '" >' . __( 'Edit Audio', 'play-ht' ) . '</a> <span style="color:#dcdcde;">|</span> <a style="cursor:pointer;color:#b32d2e;" id="playht-delete-audio" data-postId="' . $post_id . '" >' . __( 'Delete Audio', 'play-ht' ) . '</a></div>';
	}

	return $links;
}
