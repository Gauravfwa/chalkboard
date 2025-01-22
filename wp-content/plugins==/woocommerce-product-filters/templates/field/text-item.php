<?php
	$item_classes = array();

if ( $item['option_is_set'] ) {
	$item_classes[] = 'selected';
}

if ( $item['disabled'] ) {
	$item_classes[] = 'disabled';
}
?>
<div class="wcpf-text-item <?php echo esc_attr( implode( ' ', $item_classes ) ); ?>" data-value="<?php echo esc_attr( $item['key'] ); ?>">
	<div class="wcpf-item-inner wcpf-text-item-inner">
		<div class="wcpf-item-label wcpf-text-label">
			<div class="wcpf-title-container">
				<span class="wcpf-title"><?php echo esc_html( $item['title'] ); ?></span>
				<?php
				if ( $display_product_count && isset( $item['product_count_html'] ) && $item['product_count'] ) {
					echo wp_kses_post( $item['product_count_html'] );
				}
				?>
			</div>
		</div>
		<?php if ( $tree_view_style && isset( $item['children'] ) && count( $item['children'] ) ) : ?>
			<div class="wcpf-item-children-container wcpf-text-children-container">
				<?php
				foreach ( $item['children'] as $child_item ) {
					$template_loader->render_template(
						'field/text-item.php',
						array(
							'item'                  => $child_item,
							'filter_key'            => $filter_key,
							'tree_view_style'       => $tree_view_style,
							'display_product_count' => $display_product_count,
						)
					);
				}
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
if ( ! $tree_view_style && isset( $item['children'] ) && is_array( $item['children'] ) ) {
	foreach ( $item['children'] as $child_item ) {
		$template_loader->render_template(
			'field/text-item.php',
			array(
				'item'                  => $child_item,
				'filter_key'            => $filter_key,
				'tree_view_style'       => $tree_view_style,
				'display_product_count' => $display_product_count,
			)
		);
	}
}
?>
