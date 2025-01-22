<div class="control element-list-control"
	data-control-type="<?php echo esc_attr( $control_key ); ?>"
	data-option-key="<?php echo esc_attr( $option_key ); ?>">
	<div class="control-label">
		<span class="text"><?php echo esc_html( $label ); ?></span>
	</div>
	<div class="control-content">
		<?php foreach ( $elements as $element ) : ?>
			<div class="element-item"
				data-element-code="<?php echo esc_attr( $element['id'] ); ?>"
				<?php
				if ( isset( $element['default_state'] ) && is_array( $element['default_state'] ) ) :
					?>
						data-default-state="<?php echo esc_attr( wp_json_encode( $element['default_state'] ) ); ?>"<?php endif; ?>>
				<div class="element-item-inner">
					<div class="element-picture-container">
						<img class="element-picture" src="<?php echo esc_attr( $element['picture_url'] ); ?>"/>
					</div>
					<div class="element-title-container">
						<div class="element-title">
							<span class="text"><?php echo esc_html( $element['title'] ); ?></span>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
