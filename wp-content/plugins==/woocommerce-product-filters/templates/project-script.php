<script id="wcpf-load-project-<?php echo esc_attr( $project_id ); ?>-script">
	(function () {
		var projectStructure = <?php echo wp_json_encode( $project_structure ); ?>,
			loadProject = function () {
				if (!window.hasOwnProperty('wcpfFrontApp')) {
					return;
				}

				var app = window.wcpfFrontApp,
					dispatcher = app.componentRegister.get('Filter/Dispatcher');

				dispatcher.loadProject(projectStructure);
			};

		if (document.readyState === 'complete') {
			loadProject();
		} else {
			window.addEventListener('load', loadProject);
		}
	})();
</script>
