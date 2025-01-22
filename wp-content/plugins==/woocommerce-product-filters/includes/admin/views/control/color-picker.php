<div class="control color-picker-control control-inline-style"
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
		<div class="control-element-container">
			<input type="text"
				class="color-picker-element control-element"
				name="<?php echo esc_attr( $option_key ); ?>">
		</div>
		<div class="validation-errors-container"></div>
	</div>
</div>
