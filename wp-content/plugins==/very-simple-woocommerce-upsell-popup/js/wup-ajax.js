var prod_id;

jQuery(document).ready(function () {
	
	jQuery(document).on('adding_to_cart', function(event, buttonobj, prodobj) {
		prod_id = prodobj.product_id;
	});
	
	jQuery(document).on('added_to_cart', function() {
		
		if (prod_id == null) {
			prod_id = jQuery('.single_add_to_cart_button').val();
		}
		
		if (prod_id == '') {
			prod_id = jQuery("input[type='hidden'][name='product_id']").val();
		}
		
		jQuery.get(
			thp_popup_vars.ajaxurl,
			{'action':
				'thp_ajax_popup_trigger',
				prod_id : prod_id
			},
			//cache: false,
			function (data) {
				if (jQuery.trim(data)){ //if returned data is not empty
					jQuery('body').append(data);
					var loading = jQuery('#thp-spinner-container');
					jQuery(document)
					.ajaxStart(function () {
						loading.show();
					})
					.ajaxStop(function () {
						loading.hide();
					});
				}
			});
	});
});

jQuery(document).on('click','.thp-button-yes, .thp-button-no',function(e){
	e.preventDefault();
	
	if ( jQuery( this ).is( '.thp-button-yes' ) ) {
		
		var select_val = jQuery('.thp-wuppro-select-dropdown').val();
		
		if (select_val == '') {
			alert(thp_popup_vars.choose_one);
			return;
		}
		
		var up_pid = jQuery('#thp-hidden-prod-id').val();
		var clicked = 'yes';
		
		jQuery.get(
			thp_popup_vars.ajaxurl,
			{'action':
				'thp_popup_form_action_ajax',
				clicked : clicked,
				prod_id : prod_id,
				up_pid : up_pid
			},
			function (data) {
				if (jQuery.trim(data)){ //if not empty
					var redurl = data;
					var pattern = /^((http|https):\/\/)/;
					
					if(pattern.test(redurl)) {
						window.location.replace(redurl);
					}
					else {
						jQuery('.thp-dark-overlay, .thp-content-body').remove(); //odd urls will not be redirected
					}
				}
				else { //if stay on page
					var notifn = '<div class="thp-added2cart-notif" style="padding:50px; text-align:center; font-size:26px;">' + thp_popup_vars.added_to_cart + '</div>';
					jQuery('.thp-content-body').html(notifn);
					setTimeout(function(){
						jQuery('.thp-dark-overlay, .thp-content-body').remove();
					}, 900); //in milisecond
					jQuery(document.body).trigger('wc_fragment_refresh');
				}
			});
	}
	else if ( jQuery( this ).is( '.thp-button-no' ) ) {
		var clicked = 'no';
		jQuery.get(
			thp_popup_vars.ajaxurl,
			{'action':
				'thp_popup_form_action_ajax',
				clicked : clicked,
				prod_id : prod_id
			},
			function (data) {
				if (jQuery.trim(data)){ //if not empty
					var redurl = data;
					var pattern = /^((http|https):\/\/)/;
					
					if(pattern.test(redurl)) {
						window.location.replace(redurl);
					}
					else {
						jQuery('.thp-dark-overlay, .thp-content-body').remove(); //odd urls will not be redirected
					}
				}
				else {
					jQuery('.thp-dark-overlay, .thp-content-body').remove();
				}
			});
	}
});