<?php
	$layout_class = array(
		'wcpf-layout-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-layout-simple-box',
	);

	$is_hide = false;

	if ( $is_toggle_active ) {
		$layout_class[] = 'wcpf-box-style';

		if ( 'hide' === $default_toggle_state ) {
			$layout_class[] = 'wcpf-box-hide';

			$is_hide = true;
		}
	}

	if ( $css_class ) {
		$layout_class[] = $css_class;
	}
	?>
<div class="<?php echo esc_attr( implode( ' ', $layout_class ) ); ?>">
	<div class="wcpf-inner">
		<div class="wcpf-simple-box-heading wcpf-heading-label">
			<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
			<?php if ( $is_toggle_active ) : ?>
				<span class="box-toggle"></span>
			<?php endif; ?>
		</div>
		<div class="wcpf-simple-box-content wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<?php
			foreach ( $child_components as $child_component ) {
				$child_component->template_render();
			}
			?>
		</div>
	</div>
</div>
