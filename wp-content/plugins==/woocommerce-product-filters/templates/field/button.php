<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-button',
	);

	if ( $css_class ) {
		$field_class[] = $css_class;
	}
	?>
<div class="<?php echo esc_attr( implode( ' ', $field_class ) ); ?>">
	<button class="wcpf-button <?php echo 'wcpf-button-action-' . esc_attr( $action ); ?>">
		<span class="button-text"><?php echo wc_clean( $entity->get_title() ); ?></span>
	</button>
</div>
