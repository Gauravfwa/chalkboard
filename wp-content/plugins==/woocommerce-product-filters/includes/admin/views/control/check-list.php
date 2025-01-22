<div class="control check-list-control control-inline-style check-list-style-<?php echo esc_attr( $style_name ); ?>"
	data-control-type="<?php echo esc_attr( $control_key ); ?>"
	data-option-key="<?php echo esc_attr( $option_key ); ?>">
	<div class="control-label">
		<span class="label-text">
			<?php echo esc_html( $label ); ?>

			<?php if ( $required ) : ?>
				<abbr class="required" title="<?php echo esc_attr( __( 'required', 'wcpf' ) ); ?>">*</abbr>
			<?php endif; ?>
		</span>

		<?php if ( $control_description ) : ?>
			<div class="control-description">
				<span class="text"><?php echo esc_html( $control_description ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<div class="control-content">
		<div class="check-box-list">
			<?php if ( is_array( $options ) && count( $options ) ) : ?>
				<ul class="list">
					<?php
					foreach ( $options as $option_index => $option_data ) {
						$template_loader->render_template(
							'check-list-item.php',
							array(
								'option_key'   => $option_key,
								'option_index' => $option_index,
								'option_data'  => $option_data,
							),
							__DIR__
						);
					}
					?>
				</ul>
			<?php else : ?>
				<div class="empty-list-text"><?php echo esc_html__( 'Empty options list', 'wcpf' ); ?></div>
			<?php endif; ?>
		</div>
		<div class="validation-errors-container"></div>
	</div>
</div>
