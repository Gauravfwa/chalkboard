jQuery(document).ready(function($){
	var element = jQuery('input[name="afacr_enable_guest"]:checked');

	$('.select2-multiple').select2();

	if ( !element ) {
		return;
	}

	var value = element.val();

	if ( 'ask' == value ) {

		$('.guest-user-ask-or-always').closest('tr').show();
		$('.guest-user-ask').closest('tr').show();

	} else if ( 'always' == value ) {

		$('.guest-user-ask-or-always').closest('tr').show();
		$('.guest-user-ask').closest('tr').hide();

 
	} else if ( 'never' == value || 'checkout' == value) {

		$('.guest-user-ask-or-always').closest('tr').hide();
		$('.guest-user-ask').closest('tr').hide();
	}

	// Hide coupons meta box for orders email templates.
	if ( 'cart' == $('select#afacr_email_type option:selected').val() ) {

		$('div#coupons-settings').show();

	} else {
		
		$('div#coupons-settings').hide();
	}
	
	jQuery(document).click( 'input[name="afacr_enable_guest"]', function(){
		var element = jQuery('input[name="afacr_enable_guest"]:checked');

		if ( !element ) {
			return;
		}

		var value = element.val();

		if ( 'ask' == value ) {

			$('.guest-user-ask-or-always').closest('tr').show();
			$('.guest-user-ask').closest('tr').show();

		} else if ( 'always' == value ) {

			$('.guest-user-ask-or-always').closest('tr').show();
			$('.guest-user-ask').closest('tr').hide();


		} else if ( 'never' == value || 'checkout' == value ) {

			$('.guest-user-ask-or-always').closest('tr').hide();
			$('.guest-user-ask').closest('tr').hide();
		}
	});

	jQuery('button.afacr_send_email').on('click', function(){

		$template_id = $(this).closest('td').find('select[name="afacr_email_send"] option:selected').val();

		var url = window.location.href + '&send_email=' + $template_id + '&cart_id=' + $(this).data('cart');

		window.location.href = url;

	});

	// Hide coupons meta box for orders email templates.
	jQuery('select#afacr_email_type').on('change', function(){

		if ( 'cart' == $(this).find('option:selected').val() ) {

			$('div#coupons-settings').show();

		} else {

			$('div#coupons-settings').hide();
		}
		
	});

	
});
