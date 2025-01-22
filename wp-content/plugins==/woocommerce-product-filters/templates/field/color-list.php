<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-color-list',
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
			<div class="wcpf-checkbox wcpf-field-title wcpf-heading-label">
				<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
				<?php if ( $is_toggle_active ) : ?>
					<span class="box-toggle"></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="wcpf-color-list field-input-container wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<?php foreach ( $option_items as $item ) : ?>
				<?php
					$item_style = '';

					$item_class = array(
						'wcpf-color-item',
						$item['marker_style'] . '-marker',
					);

					if ( $item['option_is_set'] ) {
						$item_class[] = 'selected';
					}

					if ( $item['disabled'] ) {
						$item_class[] = 'disabled';
					}

					if ( 'color' === $item['type'] ) {
						$item_style .= ' background-color: ' . $item['color'] . ';';
					}

					if ( 'image' === $item['type'] ) {
						$url = wp_get_attachment_url( $item['image'] );

						if ( $url ) {
							$item_style .= ' background-image: url(' . esc_attr( $url ) . ');';
						}
					}

					if ( $item['border_color'] ) {
						$item_style .= ' border-color: ' . $item['border_color'] . ';';

						$item_class[] = 'using-border';
					}
					?>
				<div class="<?php echo esc_attr( implode( ' ', $item_class ) ); ?>"
					data-value="<?php echo esc_attr( $item['key'] ); ?>"
					data-title="<?php echo esc_attr( $item['title'] ); ?>"
					title="<?php echo esc_attr( $item['title'] ); ?>"
					style="<?php echo esc_attr( $item_style ); ?>"></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
