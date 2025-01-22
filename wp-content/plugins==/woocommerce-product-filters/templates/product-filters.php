<div id="wcpf-filter-<?php echo esc_attr( $project_id ); ?>" class="wcpf-filter wcpf-front-element-<?php echo esc_attr( $project_id ); ?>">
	<div class="wcpf-filter-inner">
		<?php
		foreach ( $child_components as $child_component ) {
			$child_component->template_render();
		}
		?>
	</div>
</div>
<?php
	$template_loader->render_template(
		'project-script.php',
		array(
			'project_id'        => $project_id,
			'project_structure' => $project_structure,
		)
	);
	?>
