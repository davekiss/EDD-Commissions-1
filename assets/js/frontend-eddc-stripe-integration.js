jQuery(document).ready(function($) {
	$('body').on('click', '#eddc_stripe_apply', function(e) {
		var country = $('#eddc_stripe_county').val();
		var email   = $('#eddc_stripe_email').val();
		var tos     = $('#eddc_stripe_agreement').is(':checked');
		var user_id = $('#eddc_stripe_user_id').val();
		var type    = $('#eddc_stripe_account_type').val();

		var data = {
			edd_action: 'stripe_account_step_1',
			country: country,
			email: email,
			tos: tos,
			user_id: user_id,
			type: type
		};

		 $.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: edd_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {
				if ( true === response.success ) {
					$('.eddc-stripe-wrapper').html(response.new_form);
				} else {
					var error_messages = '';
					console.log( response);
					$.each( response.errors, function( index, value ) {
						error_messages = error_messages + '<p>' + value + '</p>'
					});
					$('.eddc-stripe-errors').addClass('edd-alert edd-alert-error');
					$('.eddc-stripe-errors').html(error_messages);
				}
			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		}).done(function (response) {

		});
	});

	$('body').on('click', '#eddc_stripe_update', function(e) {
		var fields = $('.eddc-stripe-wrapper input, .eddc-stripe-wrapper select');
		var field_data = {};
		$.each( fields, function() {
			var key = $(this).data('key');
			if ( typeof key != 'undefined' ) {
				field_data = $.extend(true, field_data, eddc_expand( key, $(this).val() ) );
			}
		});

		var token_args = {
			country: $('#eddc_stripe_update_country').val(),
			currency: $('#eddc_stripe_bank_currency').val(),
			routing_number: $('#eddc_stripe_bank_routing_num').val(),
			account_number: $('#eddc_stripe_bank_acct_num').val(),
			account_holder_name: $('#eddc_stripe_bank_acct_holder').val(),
			account_holder_type: $('#eddc_stripe_bank_acct_holder_type').val()
		};

		Stripe.bankAccount.createToken( token_args, function( status, response ) {
			if ( false != response ) {
				var token = response['id'];
				$('.eddc-stripe-account-update-details').append("<input type='hidden' id='edd-stripe-new-token' name='edd_stripe_token' value='" + token + "' />");

				var data = {
					edd_action: 'stripe_account_step_2',
					field_data: field_data,
					token: token,
					user_id: $('#eddc_stripe_update_user_id').val()
				};

				 $.ajax({
					type: "POST",
					data: data,
					dataType: "json",
					url: edd_scripts.ajaxurl,
					xhrFields: {
						withCredentials: true
					},
					success: function (response) {
						if ( true === response.success ) {
							$('.eddc-stripe-messages').addClass('edd-alert edd-alert-success');
							$('.eddc-stripe-messages').html('<p>' + response.message + '</p>');
							setTimeout( function() {
								location.reload();
							}, 1000);
						} else {
							var error_messages = '';
							console.log( response);
							$.each( response.errors, function( index, value ) {
								error_messages = error_messages + '<p>' + value + '</p>'
							});
							$('.eddc-stripe-messages').addClass('edd-alert edd-alert-error');
							$('.eddc-stripe-messages').html(error_messages);
						}
					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {

				});
			}
		});
		return false;

	});
});

function eddc_expand(str, value) {
    var items = str.split(".") // split on dot notation
    var output = {} // prepare an empty object, to fill later
    var ref = output // keep a reference of the new object

    //  loop through all nodes, except the last one
    for(var i = 0; i < items.length - 1; i ++)
    {
        ref[items[i]] = {} // create a new element inside the reference
        ref = ref[items[i]] // shift the reference to the newly created object
    }

    ref[items[items.length - 1]] = value // apply the final value

    return output // return the full object
}