jQuery(document).ready(function () {

	var thp_current_prod_id = jQuery(".thp-target-product-id").text();
	
	jQuery(".thp-upsell-product-select").select2({
        placeholder: thp_upsellmetabox_vars.select2_searchproduct_placeholder,
		allowClear: true,
		minimumInputLength: 3,
        ajax: {
            url: "admin-ajax.php?action=thp_search_product_to_upsell",
            dataType: 'json',
            type: "GET",
            delay: 250,
            data: function (params) {
                return {
                    thp_search_term: params.term,
					thp_current_prod_id: thp_current_prod_id
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            }
        }
    });
	
	jQuery(".thp-upsellpopup-template, .thp-upsellpopup-template-multiple").select2({
        placeholder: thp_upsellmetabox_vars.select2_template_placeholder,
		allowClear: true,
		minimumResultsForSearch: Infinity
    });
	
	jQuery(".thp-upsellpopup-redirect, .thp-upsellpopup-redirect-no").select2({
        placeholder: thp_upsellmetabox_vars.select2_redirect_placeholder,
		allowClear: true,
		minimumResultsForSearch: Infinity
    });

});