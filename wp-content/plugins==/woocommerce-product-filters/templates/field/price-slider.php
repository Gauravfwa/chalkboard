<?php
	$field_class = array(
		'wcpf-field-item',
		'wcpf-front-element',
		'wcpf-front-element-' . $entity_id,
		'wcpf-field-price-slider',
	);

	$is_hide = false;

	if ( $is_toggle_active ) {
		$field_class[] = 'wcpf-box-style';

		if ( 'hide' === $default_toggle_state ) {
			$field_class[] = 'wcpf-box-hide';

			$is_hide = true;
		}
	}

	if ( $display_price_label ) {
		$field_class[] = 'wcpf-display-price-label';
	}

	if ( $display_min_max_input ) {
		$field_class[] = 'wcpf-display-min-max-inputs';
	}

	if ( $css_class ) {
		$field_class[] = $css_class;
	}

	$min_price = esc_attr( apply_filters( 'woocommerce_price_filter_widget_min_amount', $min_price ) );

	$min_price_value = isset( $filter_range['min_price'] ) && ! is_null( $filter_range['min_price'] ) ? esc_attr( $filter_range['min_price'] ) : $min_price;

	$max_price = esc_attr( apply_filters( 'woocommerce_price_filter_widget_max_amount', $max_price ) );

	$max_price_value = isset( $filter_range['max_price'] ) && ! is_null( $filter_range['max_price'] ) ? esc_attr( $filter_range['max_price'] ) : $max_price;

	$price_text = apply_filters( 'wcpf_translate', __( 'Price', 'wcpf' ) );
	?>
<div class="<?php echo esc_attr( implode( ' ', $field_class ) ); ?>">
	<div class="wcpf-inner">
		<?php if ( $is_display_title ) : ?>
		<div class="wcpf-price-slider wcpf-field-title wcpf-heading-label">
			<span class="text"><?php echo esc_html( $entity->get_title() ); ?></span>
			<?php if ( $is_toggle_active ) : ?>
				<span class="box-toggle"></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="wcpf-price-slider field-input-container wcpf-content"
		<?php
		if ( $is_hide ) :
			?>
			style="display: none;"<?php endif; ?>>
			<div class="priceSliderInput" style="display: none;"></div>
			<div class="priceSliderAmount">
				<div class="wcpf-price-slider-min-max-inputs">
					<input type="text"
						id="<?php echo esc_attr( $entity_id ); ?>-min-price"
						class="wcpf-input wcpf-min-input"
						name="minPrice"
						value="<?php echo esc_attr( $min_price_value ); ?>"
						data-min="<?php echo esc_attr( $min_price ); ?>"
						placeholder="<?php echo esc_attr( __( 'Min price', 'wcpf' ) ); ?>" />
					<input type="text"
						id="<?php echo esc_attr( $entity_id ); ?>-max-price"
						class="wcpf-input wcpf-max-input"
						name="maxPrice"
						value="<?php echo esc_attr( $max_price_value ); ?>"
						data-max="<?php echo esc_attr( $max_price ); ?>"
						placeholder="<?php echo esc_attr( __( 'Max price', 'wcpf' ) ); ?>" />
				</div>
				<div class="priceLabel" style="display: none;">
					<span class="price-text"><?php echo esc_html( $price_text ); ?>:</span>
					<span class="from"></span><span class="delimiter"> &mdash; </span><span class="to"></span>
				</div>
			</div>
		</div>
	</div>
</div>
