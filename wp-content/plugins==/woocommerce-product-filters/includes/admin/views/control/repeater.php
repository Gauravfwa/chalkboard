<div class="control repeater-control control-inline-style"
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
		<div class="repeater-main-container">
			<div class="repeater-list"></div>
			<div class="repeater-bottom-navigation">
				<button class="button add-item"><?php echo esc_html( $add_item_text ); ?></button>
			</div>
		</div>
		<div class="validation-errors-container"></div>
		<div class="repeater-item-template" style="display: none;">
			<?php
			foreach ( $controls as $control ) {
				$control->render_control();
			}
			?>
		</div>
	</div>
</div>
