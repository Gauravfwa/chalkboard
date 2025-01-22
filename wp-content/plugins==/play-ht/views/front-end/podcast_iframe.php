<?php
do_action( 'befor_play_podcast' );

?>
<div id="playht-iframe-wrapper" style="max-height: 210px !important;">
	<iframe
	scrolling="no"
	class="playht-iframe-player"
	id="playht-iframe-player"
	height="90px"
	width="100%"
	frameborder="0"
	style="max-height: 90px; height: 90px !important;"
	src="https://play.ht/embed/?article_url=<?php echo $article_url; ?>&voice=<?php echo $article_voice; ?>&appId=<?php echo $blog_app_id; ?>&trans_id=<?php echo $trans_id; ?>"
	data-voice="<?php echo $article_voice; ?>"
	article-url="<?php echo $article_url; ?>"
	data-appId="<?php echo $blog_app_id; ?>"
	allowfullscreen="">
	</iframe>
</div>
<?php
do_action( 'after_play_podcast' );
?>
