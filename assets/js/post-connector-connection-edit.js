/**
 * Validate input on Connection add/edit screen
 */
jQuery(document).ready(function($) {

	$( 'form#pc-connection-form' ).submit(function() {
		return $(this).find('input.mandatory, select.mandatory').css('border-color', '#dfdfdf').filter(function() {
			var val = $(this).val();
			return val == '' || val == 0 || val == '0';
		}).css('border-color','#ff0000').length === 0;
	});

	$('#title').change('blur', function() {
		if( $('#slug').val() == '' ) {
			$('#slug').attr('placeholder', 'Generating slug...');

			var instance = this;

			var opts = {
				url: ajaxurl,
				type: 'POST',
				async: true,
				cache: false,
				dataType: 'json',
				data:{
					action: 'pc_connection_generate_slug',
					title: $('#title').val(),
					nonce: $('#pc-ajax-nonce').val()
				},
				success: function(response) {
					$('#slug').attr('placeholder', response.slug);
					return;
				},
				error: function(xhr,textStatus,e) {
					return;
				}
			};
			jQuery.ajax(opts);

		}
	});
});