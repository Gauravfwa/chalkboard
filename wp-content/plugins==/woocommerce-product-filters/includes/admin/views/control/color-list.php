<div class="control color-list-control control-inline-style"
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

		<?php if ( count( $colors ) ) : ?>

		<div class="color-list-main-container">
			<div class="color-list">
				<?php
				foreach ( $colors as $color_options ) :

					$color_term = get_term( $color_options['term'] );

					$term_color_css = '';

					$color_type = isset( $color_options['type'] ) ? $color_options['type'] : 'color';

					if ( 'color' === $color_type && isset( $color_options['color'] ) && $color_options['color'] ) {
						$term_color_css = 'background-color: ' . esc_attr( $color_options['color'] ) . ';';
					} elseif ( 'image' === $color_type && isset( $color_options['image'] ) && $color_options['image'] ) {
						$term_color_css = 'background-image: url(' . esc_attr( $color_options['image'] ) . ');';
					}

					if ( isset( $color_options['borderColor'] ) && $color_options['borderColor'] ) {
						$term_color_css .= ' border-color: ' . $color_options['borderColor'] . ';';
					}

					$image_url = null;

					if ( isset( $color_options['image'] ) && $color_options['image'] ) {
						$image_url = wp_get_attachment_url( $color_options['image'] );
					}
					?>

					<div class="color-item"
						data-term="<?php echo esc_attr( $color_options['term'] ); ?>">

						<div class="color-item-inner">

							<div class="color-item-header">

								<div class="preview-color-container">

									<div class="preview-color" 
									<?php
									if ( $term_color_css ) :
										?>
										style="<?php echo esc_attr( $term_color_css ); ?>"<?php endif; ?>></div>

								</div>

								<div class="term-name-container">

									<div class="term-name"><?php echo esc_html( $color_term->name ); ?></div>

								</div>

							</div>

							<div class="color-item-main">

								<div class="color-box">
									<div class="fill-container">

										<div class="container-inner">

											<div class="color-picker-container" 
											<?php
											if ( 'color' !== $color_options['type'] ) :
												?>
												style="display: none;"<?php endif; ?>>

												<div class="color-item-option-header">
													<span class="text"><?php echo esc_html__( 'Color', 'wcpf' ); ?></span>
												</div>

												<div class="color-item-option-content">
													<input type="text" class="color-picker-element" name="<?php echo esc_attr( 'colors.' . $color_term->term_id . '.color' ); ?>">
												</div>

											</div>

											<div class="image-container" 
											<?php
											if ( 'image' !== $color_options['type'] ) :
												?>
												style="display: none;"<?php endif; ?>>

												<div class="color-item-option-header">

													<span class="text"><?php echo esc_html__( 'Image', 'wcpf' ); ?></span>

												</div>

												<div class="color-item-option-content">

													<div class="preview-image-container 
													<?php
													if ( ! $image_url ) :
														?>
														woocommerce-product-filter-hidden<?php endif; ?>">

														<img src="
														<?php
														if ( $image_url ) {
															echo esc_attr( $image_url ); }
														?>
															" class="image-element"/>

														<div class="delete-image"></div>

													</div>

													<div class="upload-container 
													<?php
													if ( $image_url ) :
														?>
														woocommerce-product-filter-hidden<?php endif; ?>">

														<button class="button upload-image">

															<span class="text"><?php echo esc_html__( 'Upload image', 'wcpf' ); ?></span>

														</button>

													</div>

												</div>

											</div>

										</div>

									</div>
									<div class="type-container">

										<div class="container-inner">

											<div class="color-item-option-header">
												<span class="text"><?php echo esc_html__( 'Type', 'wcpf' ); ?></span>
											</div>

											<div class="color-item-option-content">

												<div class="switch-element" data-name="type">
													<div class="option first-option-text 
													<?php
													if ( 'color' === $color_options['type'] ) :
														?>
														active<?php endif; ?>" data-value="color">
														<i class="switch-icon wcpf-icon-paint-bucket-streamline"></i>
														<span class="text">
														<?php echo esc_html__( 'Color', 'wcpf' ); ?>
													</span>
													</div>
													<div class="option second-option-text 
													<?php
													if ( 'image' === $color_options['type'] ) :
														?>
														active<?php endif; ?>" data-value="image">
														<i class="switch-icon wcpf-icon-picture-streamline"></i>
														<span class="text">
														<?php echo esc_html__( 'Image', 'wcpf' ); ?>
													</span>
													</div>
												</div>

											</div>

										</div>

									</div>
								</div>

								<div class="extra-box">

									<div class="border-container">

										<div class="container-inner">

											<div class="color-item-option-header">
												<span class="text"><?php echo esc_html__( 'Border', 'wcpf' ); ?></span>
											</div>

											<div class="color-item-option-content">
												<input type="text" class="border-picker-element" name="<?php echo esc_attr( 'colors.' . $color_term->term_id . '.border' ); ?>">
											</div>

										</div>

									</div>

									<div class="marker-container">

										<div class="container-inner">

											<div class="color-item-option-header">
												<span class="text"><?php echo esc_html__( 'Marker', 'wcpf' ); ?></span>
											</div>

											<div class="color-item-option-content">

												<div class="switch-element" data-name="marker">
													<div class="option first-option-text 
													<?php
													if ( 'light' === $color_options['markerStyle'] ) :
														?>
														active<?php endif; ?>" data-value="light">
														<i class="switch-icon wcpf-icon-android-sunny"></i>
														<span class="text">
														<?php echo esc_html__( 'Light', 'wcpf' ); ?>
													</span>
													</div>
													<div class="option second-option-text 
													<?php
													if ( 'dark' === $color_options['markerStyle'] ) :
														?>
														active<?php endif; ?>" data-value="dark">
														<i class="switch-icon wcpf-icon-moon-o"></i>
														<span class="text">
														<?php echo esc_html__( 'Dark', 'wcpf' ); ?>
													</span>
													</div>
												</div>

											</div>

										</div>

									</div>

								</div>

							</div>

						</div>

					</div>

				<?php endforeach; ?>
			</div>
		</div>

		<?php else : ?>

			<div class="empty-list-text"><?php echo esc_html__( 'Empty options list', 'wcpf' ); ?></div>

		<?php endif; ?>

	</div>
</div>
