<?php foreach ( $projections as $projection_key => $projection ) : ?>
	<script
		type="text/html"
		id="tmpl-wcpf-projection-<?php echo esc_attr( $projection_key ); ?>"
		class="wcpf-projection-template"
		data-projection-key="<?php echo esc_attr( $projection_key ); ?>"
		data-template-key="wcpf-projection-<?php echo esc_attr( $projection_key ); ?>">
		<?php $projection->render_projection(); ?>
	</script>
<?php endforeach; ?>
