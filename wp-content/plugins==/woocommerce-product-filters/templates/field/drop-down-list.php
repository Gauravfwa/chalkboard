<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-drop-down-list',
	);

	$is_hide = false;

	if ( $is_toggle_active ) {
		$field_class[] = 'wcpf-box-style';

		if ( 'hide' === $default_toggle_state ) {
			$field_class[] = 'wcpf-box-hide';

			$is_hide = true;
		}
	}

	if ( $css_class ) {
		$field_class[] = $css_class;
	}

	if ( ! count( $option_items ) || ! $is_enabled_element ) {
		$field_class[] = 'wcpf-status-disabled';
	}
	?>
<div class="<?php echo esc_attr( implode( ' ', $field_class ) ); ?>">
	<div class="wcpf-inner">
		<?php if ( $is_display_title ) : ?>
		<div class="wcpf-drop-down wcpf-field-title wcpf-heading-label">
			<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
			<?php if ( $is_toggle_active ) : ?>
				<span class="box-toggle"></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="wcpf-drop-down-list field-input-container wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<select class="wcpf-input wcpf-input-drop-down wcpf-drop-down-style-<?php echo esc_attr( $drop_down_style ); ?>"
					name="<?php echo esc_attr( $filter_key ); ?>">
				<?php
				foreach ( $option_items as $item ) {
					$template_loader->render_template(
						'field/drop-down-item.php',
						array(
							'item'                  => $item,
							'filter_key'            => $filter_key,
							'display_product_count' => $display_product_count,
							'depth'                 => 1,
							'tree_view_style'       => $tree_view_style,
						)
					);
				}
				?>
			</select>
		</div>
	</div>
</div>
