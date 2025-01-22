<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-text-list',
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

	if ( $use_inline_style ) {
		$field_class[] = 'wcpf-text-list-inline-style';
	}

	if ( ! count( $option_items ) || ! $is_enabled_element ) {
		$field_class[] = 'wcpf-status-disabled';
	}
	?>
<div class="<?php echo esc_attr( implode( ' ', $field_class ) ); ?>">
	<div class="wcpf-inner">
		<?php if ( $is_display_title ) : ?>
			<div class="wcpf-text-list-title wcpf-field-title wcpf-heading-label">
				<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
				<?php if ( $is_toggle_active ) : ?>
					<span class="box-toggle"></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="wcpf-text-list field-input-container wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<?php
			foreach ( $option_items as $item ) {
				$template_loader->render_template(
					'field/text-item.php',
					array(
						'item'                  => $item,
						'filter_key'            => $filter_key,
						'display_product_count' => $display_product_count,
						'tree_view_style'       => $tree_view_style,
					)
				);
			}
			?>
		</div>
	</div>
</div>
