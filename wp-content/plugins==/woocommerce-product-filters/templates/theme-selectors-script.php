<script id="wcpf-theme-selecotrs-script">
	(function () {
		var themeSelectors = <?php echo wp_json_encode( $selectors ); ?>;

		if (!window.hasOwnProperty('wcpfFrontApp')) {
			return;
		}

		var app = window.wcpfFrontApp;

		app.objectContainer.set('ThemeSelectors', themeSelectors);
	})();
</script>
