<?php

namespace Play_HT;

if ( ! isset( $post_id ) ) {
	$post_id = get_the_ID();
}

$podcast_data = (array) maybe_unserialize( get_post_meta( $post_id, 'play_podcast_data', true ) );
$app_id       = get_option( 'wppp_blog_appId' );

$article_id = '';
if ( is_array( $podcast_data ) && isset( $podcast_data['play_article_id'] ) ) {
	$article_id = $podcast_data['play_article_id'];
}

$is_draft = playht_has_draft( $post_id );

$is_converted = false;
if ( isset( $podcast_data['audio_status'] ) && 2 === $podcast_data['audio_status'] ) {
	$is_converted = true;
}

?>
<div class="converting-msg-wrapper"></div>

<span class="spinner metabox-play-spinner" style="float: left;margin-right: -35px;"></span>

<?php if ( ! $is_converted && ! $is_draft ) : ?>

	<?php $post_content = get_the_content($post_id); ?>

	<?php if ( empty( $post_content ) ) : ?>
		<p class="playht-admin-help-text">
			<?php esc_html_e( 'Please add content to the post and save it as a draft or publish and refresh this pagebefore adding an audio.', 'play-ht' ); ?>
		</p>
	<?php else: ?>
	<p>
		<span class="t-bottom t-lg full-width">
			<input type="button" id="convert_podcast"
			value="<?php esc_attr_e( 'Add Audio', 'play-ht' ); ?>" class="playht-button button-large button button-primary" data-postid="<?php echo esc_attr( $post_id ); ?>">
		</span>
	</p>
	<p class="playht-admin-help-text">
		<?php esc_html_e( 'Use the Add Audio button to create and embed an audio version of the post.', 'play-ht' ); ?>
	</p>
	<?php endif; ?>
<?php endif; ?>

<?php if ( $is_converted ) : ?>

	<p>
		<?php $voice_name = isset( $podcast_data['voice'] ) ? $podcast_data['voice'] : ''; ?>
		<span><?php esc_html_e( 'Voice: ', 'play-ht' ); ?></span><strong><?php echo esc_html( $voice_name ); ?></strong>
	</p>
	<p>
		<span><?php esc_html_e( 'Total listens:', 'play-ht' ); ?> </span><strong><?php echo playht_get_post_listens_count( $app_id, $article_id, $post_id ); ?></strong>
	</p>
	<p>
		<span><?php esc_html_e( 'Total listening time: ', 'play-ht' ); ?></span><strong><?php echo playht_get_post_listens_time( $app_id, $article_id, $post_id ); ?></strong><span><?php esc_html_e( ' minutes', 'play-ht' ); ?></span>
	</p>
	<hr>
	<p>
		<?php esc_html_e( 'Shortcodes', 'play-ht' ); ?>
	</p>
	<p>
		<code style="display:block; border:1px solid #afafaf;border-radius: 3px;padding: 10px;color: #23282d;">[playht_player width="100%" height="90px" voice="<?php echo $podcast_data['voice']; ?>"]</code>
	</p>
	<p>
		<code style="display:block; border:1px solid #afafaf;border-radius: 3px;padding: 10px;color: #23282d;">[playht_listen_button inline="yes" tag="p"]</code>
	</p>
	<p class="playht-admin-help-text ">
		<?php esc_html_e( 'Paste the Shortcode in the editor where you want to display the audio player. It will overrides the default article player.', 'play-ht' ); ?>
	</p>
	<hr>
	<p style="text-align: center;">
		<span id="download_podcast_wrapper" class="t-bottom t-lg full-width">
			<a
			id="download_podcast"
			class="playht-button-link button-large button-link"
			href="<?php echo esc_attr( $podcast_data['article_audio'] ); ?>" target="_blank" >
				<?php esc_html_e( 'Listen / Download as MP3', 'play-ht' ); ?>
			</a>
		</span>
	</p>
	<?php if ( ! $is_draft ) : ?>
		<p>
			<span class="t-bottom t-lg full-width">
				<input type="button" id="playht-edit-audio" data-postid="<?php echo esc_attr( $post_id ); ?>" value="<?php esc_attr_e( 'Edit Audio', 'play-ht' ); ?>" class="playht-button button button-large button-secondary">
			</span>
		</p>
	<?php endif; ?>
	<p style="text-align: center;">
		<span class="delete_podcast">
			<a class="playht-button-link button-link button-link-delete" id="playht-delete-audio" data-postid="<?php echo esc_attr( $post_id ); ?>">
				<?php esc_html_e( 'Delete audio from this post', 'play-ht' ); ?>
			</a>
	</span>
	</p>
	<hr>
<?php endif; ?>

<?php if ( $is_draft ) : ?>
	<p>
		<span class="t-bottom t-lg full-width">
			<input type="button" id="convert_podcast" data-postid="<?php echo esc_attr( $post_id ); ?>" value="<?php esc_attr_e( 'Edit Draft', 'play-ht' ); ?>" class="playht-button button button-large button-secondary">
		</span>
	</p>

	<p style="text-align: center;">
		<span class="delete_podcast">
			<a class="playht-button-link button-link button-link-delete" id="playht-delete-draft" data-postid="<?php echo esc_attr( $post_id ); ?>">
				<?php esc_html_e( 'Delete Draft', 'play-ht' ); ?>
			</a>
	</span>
	</p>
<?php endif; ?>
<hr>
<p>
	<strong><?php esc_html_e( 'Facing issues? ', 'play-ht' ); ?></strong><?php esc_html_e( 'Please send us an email at ', 'play-ht' ); ?><a href="mailto:support@play.ht">support@play.ht</a> <?php esc_html_e( 'explaining the problem with screenshots if possible.', 'play-ht' ); ?>
</p>
