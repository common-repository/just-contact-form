jQuery(document).ready(function() {

	jQuery('#just-contact-form-submit').click(function() {

		jQuery('#just-contact-form-result').hide();

		jQuery("#just-contact-form-ajax-load").show();

		jQuery("#just-contact-form").ajaxForm({
			target: '#just-contact-form-result',
			success: function() 
			{
				jQuery("#just-contact-form-ajax-load").hide();
				jQuery('#just-contact-form-result').hide().slideDown('fast');
			},
		});
		
	});
	
});