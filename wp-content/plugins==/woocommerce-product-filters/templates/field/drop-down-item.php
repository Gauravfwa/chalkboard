<option value="<?php echo esc_attr( $item['key'] ); ?>"
		data-title="<?php echo esc_attr( $item['title'] ); ?>"
		<?php
		if ( $item['option_is_set'] ) :
			?>
			selected<?php endif; ?>
		<?php
		if ( $item['disabled'] ) :
			?>
			disabled<?php endif; ?>>
	<?php
	if ( $tree_view_style && $depth > 1 ) {
		echo str_repeat( '&nbsp;', ( $depth - 1 ) * 3 );
	}
	?>
	<span class="wcpf-title"><?php echo esc_html( $item['title'] ); ?></span>
	<?php
	if ( $display_product_count && isset( $item['product_count_html'] ) && $item['product_count'] ) {
		echo wp_kses_post( $item['product_count_html'] );
	}
	?>
</option>
<?php
if ( isset( $item['children'] ) && is_array( $item['children'] ) ) {
	foreach ( $item['children'] as $child_item ) {
		$template_loader->render_template(
			'field/drop-down-item.php',
			array(
				'item'                  => $child_item,
				'filter_key'            => $filter_key,
				'display_product_count' => $display_product_count,
				'depth'                 => $depth + 1,
				'tree_view_style'       => $tree_view_style,
			)
		);
	}
}
?>
