<div class="wcpf-layout-item wcpf-layout-columns wcpf-front-element-<?php echo esc_attr( $entity_id ); ?>">
	<?php
	foreach ( $columns as $column_index => $column ) :

		$width = isset( $column['options']['width'] ) ? $column['options']['width'] : false;
		?>
	<div class="wcpf-layout-column wcpf-layout-column-<?php echo esc_attr( $entity_id ); ?> wcpf-layout-column-<?php echo esc_attr( $column_index ); ?> wcpf-layout-<?php echo esc_attr( $entity_id ); ?>-column-<?php echo esc_attr( $column_index ); ?>"
		<?php
		if ( $width ) :
			?>
			style="width: <?php echo esc_attr( $width ); ?>;" <?php endif; ?>>
		<?php
		foreach ( $column['components'] as $child_component ) {
			$child_component->template_render();
		}
		?>
	</div>
	<?php endforeach; ?>
</div>
