<div class="control select-control control-inline-style"
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
		<select name="<?php echo esc_attr( $option_key ); ?>" class="select control-element">
			<?php foreach ( $options as $option_index => $option_data ) : ?>

				<?php if ( is_array( $option_data ) ) : ?>

					<optgroup label="<?php echo esc_attr( $option_data['label'] ); ?>">

						<?php foreach ( $option_data['options'] as $child_key => $child_title ) : ?>

							<option value="<?php echo esc_attr( $child_key ); ?>">
								<?php echo esc_html( $child_title ); ?>
							</option>

						<?php endforeach; ?>

					</optgroup>

				<?php else : ?>

					<option value="<?php echo esc_attr( $option_index ); ?>">
						<?php echo esc_html( $option_data ); ?>
					</option>

				<?php endif; ?>

			<?php endforeach; ?>
		</select>
	</div>
</div>
