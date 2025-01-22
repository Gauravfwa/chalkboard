<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-box-list',
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

	$box_style = '';

	if ( $box_size ) {
		$box_size = esc_attr( $box_size );

		$box_style .= 'height: ' . $box_size . '; ';

		$box_style .= 'width: ' . $box_size . '; ';

		$box_style .= 'line-height: ' . $box_size . ';';
	}
	?>
<div class="<?php echo esc_attr( implode( ' ', $field_class ) ); ?>">
	<div class="wcpf-inner">
		<?php if ( $is_display_title ) : ?>
			<div class="wcpf-box-title wcpf-field-title wcpf-heading-label">
				<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
				<?php if ( $is_toggle_active ) : ?>
					<span class="box-toggle"></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="wcpf-box-list field-input-container wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<?php
			foreach ( $option_items as $item ) {
				$template_loader->render_template(
					'field/box-item.php',
					array(
						'item'       => $item,
						'filter_key' => $filter_key,
						'box_style'  => $box_style,
					)
				);
			}
			?>
		</div>
	</div>
</div>
