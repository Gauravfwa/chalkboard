<div class="panel tabs-panel">
	<h2 class="hndle header">
		<div class="left-position">
			<?php
				$template_loader->render_template(
					'parts/header-navigation.php',
					array(
						'panel'    => $panel,
						'panel_id' => $panel_id,
					),
					__DIR__
				);
				?>
			<span class="title"><?php echo esc_html( $panel_title ); ?></span>
		</div>
		<div class="tabs-heading-wrapper">
			<?php
			foreach ( $tabs as $tab_id => $tab ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$heading_class = '';

				if ( reset( $tabs ) === $tab ) {
					$heading_class .= ' active-tab';
				}
				?>
				<div class="tab-heading<?php echo esc_attr( $heading_class ); ?>" data-tab-id="<?php echo esc_attr( $tab_id ); ?>">
					<div class="heading">
						<?php echo esc_html( $tab['label'] ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</h2>
	<div class="inside tabs-wrapper">
		<form class="panel-form">
			<?php
			$params = array(
				'nonce_save' => wp_create_nonce( 'wcpf_tabs_panel' ),
			);
			wp_localize_script( 'wcpf-admin-script', 'wcpf_tabs', $params );
			foreach ( $tabs as $tab_id => $tab ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$tab_class = '';

				if ( reset( $tabs ) === $tab ) {
					$tab_class .= ' active-tab';
				}
				?>
				<div class="tab-content<?php echo esc_attr( $tab_class ); ?>" data-tab-id="<?php echo esc_attr( $tab_id ); ?>">
					<?php
					foreach ( $tab['controls'] as $control ) {
						$control->render_control();
					}
					?>
				</div>
			<?php endforeach; ?>
		</form>
		<?php
		if ( ! $panel_auto_save ) {
			$template_loader->render_template( 'parts/bottom-navigation.php', array(), __DIR__ );
		}
		?>
	</div>
</div>
