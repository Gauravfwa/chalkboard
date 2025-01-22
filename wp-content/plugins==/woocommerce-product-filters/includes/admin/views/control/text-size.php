<div class="control text-size-control control-inline-style"
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
		<div class="control-inputs-container">
			<input type="text"
				class="text-size control-element"
				name="<?php echo esc_attr( $option_key ); ?>"
				<?php
				if ( $placeholder ) :
					?>
					placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php endif; ?>>
			<select class="select-unit" name="<?php echo esc_attr( $option_key ) . 'Unit'; ?>" 
														<?php
														if ( count( $units ) === 1 ) :
															?>
				disabled<?php endif; ?>>
				<?php foreach ( $units as $key_unit => $title_unit ) : ?>
					<option value="<?php echo esc_attr( $key_unit ); ?>"><?php echo esc_html( $title_unit ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="validation-errors-container"></div>
	</div>
</div>
