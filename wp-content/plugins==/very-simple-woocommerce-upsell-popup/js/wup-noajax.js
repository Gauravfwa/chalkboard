jQuery(document).on('change','select[name^="available-variations-"]',function(){
	
	var variant_price_html;
	var variant_id;
	var upsell_id;
	
	variant_price_html = jQuery(this).find(':selected').data('wup-price-html');
	variant_id = jQuery(this).find(':selected').val();
	upsell_id = jQuery(this).data('wup-upsell-pid');
	
	jQuery('.price-display-'+upsell_id).html(variant_price_html);
	
	if ( jQuery('#thp-hidden-prod-id').length ) {
		jQuery('#thp-hidden-prod-id').val(variant_id);
	}
	
});

jQuery(document).on('click','.thp-button-yes',function(e){
	
	var select_val = jQuery('.thp-wuppro-select-dropdown').val();
	
	if (select_val == '') {
		alert(wup_noajax_js_vars.choose_one);
		e.preventDefault();
		return;
	}
	
});