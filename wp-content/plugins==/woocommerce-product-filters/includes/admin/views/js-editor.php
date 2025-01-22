<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
	</th>
	<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<textarea
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"><?php echo esc_textarea( $option_value ); // WPCS: XSS ok. ?></textarea>
	</td>
</tr>
<script>
	(function () {
		window.addEventListener('load', function () {
			var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};

			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				{
					indentUnit: 4,
					tabSize: 4,
					mode: 'javascript',
				}
			);

			wp.codeEditor.initialize( jQuery('<?php echo '#' . esc_attr( $value['id'] ); ?>'), editorSettings );
		});
	})();
</script>
