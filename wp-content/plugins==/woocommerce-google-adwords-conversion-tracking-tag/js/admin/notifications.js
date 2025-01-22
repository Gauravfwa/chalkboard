jQuery(document).on("click", ".pmw-notification-dismiss-button", function () {

	const htmlElement = jQuery(this)

	fetch(pmwNotificationsApi.root + "pmw/v1/notifications/", {
		method : "POST",
		cache  : "no-cache",
		headers: {
			"Content-Type": "application/json",
			"X-WP-Nonce"  : pmwNotificationsApi.nonce,
		},
		body   : JSON.stringify({
			notification: jQuery(this).attr("id"),
		}),
	})
		.then(function (response) {
			if (response.ok) {
				return response.json()
			}
		})
		.then(function (data) {
			if (data.success) {
				htmlElement.closest(".notice").fadeOut(300, function () {
					htmlElement.remove()
				})
			}
		})
})

jQuery(document).on("click", ".pmw.opportunity-dismiss", function () {

	const opportunityId = jQuery(this).attr("data-opportunity-id")
	const htmlElement   = jQuery(this)

	fetch(pmwNotificationsApi.root + "pmw/v1/notifications/", {
		method : "POST",
		cache  : "no-cache",
		headers: {
			"Content-Type": "application/json",
			"X-WP-Nonce"  : pmwNotificationsApi.nonce,
		},
		body   : JSON.stringify({
			notification : "dismiss_opportunity",
			opportunityId: opportunityId,
		}),
	})
		.then(function (response) {
			if (response.ok) {
				return response.json()
			}
		})
		.then(function (data) {
			if (data.success) {
				htmlElement.appendTo(".pmw-opportunity-dismissed")
			}
		})
})
