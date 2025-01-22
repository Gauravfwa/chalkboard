<div class="panel list-panel">
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
	</h2>
	<div class="inside controls-wrapper">
		<form class="panel-form">
			<?php
			foreach ( $controls as $control ) {
				$control->render_control();
			}
			?>
		</form>
		<?php
		if ( ! $panel_auto_save ) {
			$template_loader->render_template( 'parts/bottom-navigation.php', array(), __DIR__ );
		}
		?>
	</div>
</div>
