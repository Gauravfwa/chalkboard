<div class="control radio-list-control control-inline-style"
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
		<div class="radio-list
		<?php
		if ( $is_inline_style ) :
			?>
			radio-list-inline-style<?php endif; ?>">
			<?php foreach ( $options as $option_index => $option_title ) : ?>

				<div class="radio-item">

					<label class="label">

						<input class="radio control-element"
							type="radio"
							name="<?php echo esc_attr( $option_key ); ?>"
							value="<?php echo esc_attr( $option_index ); ?>">

						<span class="label-text"><?php echo esc_html( $option_title ); ?></span>

					</label>

				</div>

			<?php endforeach; ?>
		</div>
		<div class="validation-errors-container"></div>
	</div>
</div>
