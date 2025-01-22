(function () {
	function init() {

		var isGtinChecked = document.getElementById("woocommerce_gmc-settings_gmc_gtin_enable").checked;
		if (isGtinChecked) {
			document.getElementById("woocommerce_gmc-settings_gmc_gtin_options").closest("tr").style.display = "";
		} else {
			document.getElementById("woocommerce_gmc-settings_gmc_gtin_options").closest("tr").style.display = "none";
		}

		document.getElementById("woocommerce_gmc-settings_gmc_gtin_enable").addEventListener("click", gmcGtinOptionsClick);

		function gmcGtinOptionsClick() {
			var isGtinChecked = document.getElementById("woocommerce_gmc-settings_gmc_gtin_enable").checked;
			if (isGtinChecked) {
				document.getElementById("woocommerce_gmc-settings_gmc_gtin_options").closest("tr").style.display = "";
			} else {
				document.getElementById("woocommerce_gmc-settings_gmc_gtin_options").closest("tr").style.display = "none";
			}
		}

		var isBadgeChecked = document.getElementById("woocommerce_gmc-settings_gmc_badge_enable").checked;
		if (isBadgeChecked) {
			document.getElementById("woocommerce_gmc-settings_gmc_badge_position").closest("tr").style.display = "";
		} else {
			document.getElementById("woocommerce_gmc-settings_gmc_badge_position").closest("tr").style.display = "none";
		}

		document.getElementById("woocommerce_gmc-settings_gmc_badge_enable").addEventListener("click", gmcBadgeEnableClick);

		function gmcBadgeEnableClick() {
			var isBadgeChecked = document.getElementById("woocommerce_gmc-settings_gmc_badge_enable").checked;
			if (isBadgeChecked) {
				document.getElementById("woocommerce_gmc-settings_gmc_badge_position").closest("tr").style.display = "";
			} else {
				document.getElementById("woocommerce_gmc-settings_gmc_badge_position").closest("tr").style.display = "none";
			}
		}

	}

	document.addEventListener('DOMContentLoaded', init);

}());