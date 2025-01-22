jQuery(document).ready(function($){

	$('.afacr-btn-ask-guest').click();
	if ( $('input[name="afacr_agree_terms"]').lenght > 0 ) {
		if ( 'yes' == $('input[name="afacr_agree_terms"]:checked').val() ) {
			$('input[name="afacr_email_address"]').closest('tr').show();
			$('button[name="afacr_cancel_modal"').hide();
			$('button[name="afacr_save_email"').show();
		} else {
			$('input[name="afacr_email_address"]').closest('tr').hide();
			$('button[name="afacr_cancel_modal"').show();
			$('button[name="afacr_save_email"').hide();
		}

		$('input[name="afacr_agree_terms"]').click(function(){
			if ( 'yes' == $('input[name="afacr_agree_terms"]:checked').val() ) {
				$('input[name="afacr_email_address"]').closest('tr').show();
				$('button[name="afacr_cancel_modal"').hide();
				$('button[name="afacr_save_email"').show();
			} else {
				$('input[name="afacr_email_address"]').closest('tr').hide();
				$('button[name="afacr_cancel_modal"').show();
				$('button[name="afacr_save_email"').hide();
			}
		});
	}

	$('#afacr_cancel_modal').click(function(){
		var url              = window.location.href;
		window.location.href = url + '?modal_cancel=true';
	});
	
	jQuery( document ).on( 'focusout', 'input#billing_email', function() {

		jQuery( document.body ).trigger( "update_checkout" );
		
	});	

});
