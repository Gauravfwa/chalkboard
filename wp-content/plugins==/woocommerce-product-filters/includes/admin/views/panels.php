<?php foreach ( $panels as $panel_key => $panel ) : ?>
	<script
		type="text/html"
		id="tmpl-wcpf-panel-<?php echo esc_attr( $panel_key ); ?>"
		class="wcpf-panel-template"
		data-panel-key="<?php echo esc_attr( $panel_key ); ?>"
		data-template-key="wcpf-panel-<?php echo esc_attr( $panel_key ); ?>">
		<div class="postbox panel-item">
			<?php $panel->render_panel(); ?>
		</div>
	</script>
<?php endforeach; ?>
