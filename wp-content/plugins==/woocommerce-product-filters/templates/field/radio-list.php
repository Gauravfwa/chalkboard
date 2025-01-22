<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-radio-list',
	);

	$is_hide = false;

	if ( $is_toggle_active ) {
		$field_class[] = 'wcpf-box-style';

		if ( 'hide' === $default_toggle_state ) {
			$field_class[] = 'wcpf-box-hide';

			$is_hide = true;
		}
	}

	if ( $display_hierarchical_collapsed ) {
		$field_class[] = 'wcpf-hierarchical-collapsed';
	}

	if ( 'scrollbar' === $see_more_options ) {
		$field_class[] = 'wcpf-scrollbar';
	} elseif ( 'moreButton' === $see_more_options ) {
		$field_class[] = 'wcpf-contain-more-button';
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
		<div class="wcpf-radio wcpf-field-title wcpf-heading-label">
			<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
			<?php if ( $is_toggle_active ) : ?>
				<span class="box-toggle"></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="wcpf-radio-list field-input-container wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<?php
			foreach ( $option_items as $item ) {
				$template_loader->render_template(
					'field/radio-item.php',
					array(
						'item'                           => $item,
						'filter_key'                     => $filter_key,
						'tree_view_style'                => $tree_view_style,
						'display_hierarchical_collapsed' => $display_hierarchical_collapsed,
						'display_product_count'          => $display_product_count,
					)
				);
			}

			if ( 'moreButton' === $see_more_options ) {
				$template_loader->render_template(
					'more-button.php',
					array(
						'front_element' => $front_element,
					)
				);
			}
			?>
		</div>
	</div>
</div>
