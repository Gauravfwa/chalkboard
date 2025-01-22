<?php
	$option_title = '';

if ( is_array( $option_data ) && isset( $option_data['title'] ) ) {
	$option_title = $option_data['title'];
} elseif ( is_string( $option_data ) ) {
	$option_title = $option_data;
}
?>
<li>
	<label class="selectit">
		<input type="checkbox"
			class="control-element"
			value="<?php echo esc_attr( $option_index ); ?>"
			name="<?php echo esc_attr( $option_key ); ?>">

		<span class="text"><?php echo esc_html( $option_title ); ?></span>
	</label>
	<?php
	if ( is_array( $option_data ) && isset( $option_data['children'] ) ) {
		echo '<ul class=\'children\'>';

		foreach ( $option_data['children'] as $child_index => $child_data ) {
			$template_loader->render_template(
				'check-list-item.php',
				array(
					'option_key'   => $option_key,
					'option_index' => $child_index,
					'option_data'  => $child_data,
				),
				__DIR__
			);
		}

		echo '</ul>';
	}
	?>
</li>
