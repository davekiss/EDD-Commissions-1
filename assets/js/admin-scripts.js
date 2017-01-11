jQuery(document).ready(function($) {

	$('.eddc-commissions-export-toggle').click( function() {
		$('.eddc-commissions-export-toggle').toggle();
		$('#eddc-export-commissions').toggle();
	});

	$('body').on('click', '.eddc-download-payout-file', function(e) {
		$(this).attr('disabled', 'disabled');
		$('#eddc-export-commissions').hide();
		$('#eddc-export-commissions-mark-as-paid').show();
		window.scrollTo(0, 0);
	});

	$('#eddc-commission-delete-comfirm').change( function() {
		var submit_button = $('#eddc-delete-commission');

		if ( $(this).prop('checked') ) {
			submit_button.attr('disabled', false);
		} else {
			submit_button.attr('disabled', true);
		}
	});

	$('.eddc-edit-commission').on('click', function(e) {
		e.preventDefault();

		var link, user_input, download_input, rate_input, amount_input;

		link = $(this);
		user_input = $('input.eddc-commission-user');
		download_input = $('input.eddc-commission-download');
		rate_input = $('input.eddc-commission-rate');
		amount_input = $('input.eddc-commission-amount');

		if (link.text() === eddc_vars.action_edit) {
			link.text(eddc_vars.action_cancel);
		} else {
			$('#eddc_update_commission').fadeOut('fast', function () {
				$(this).css('display', 'none');
			});
			link.text(eddc_vars.action_edit);
		}

		$('#eddc_user_chosen').toggle();
		$('#eddc_download_chosen').toggle();
		$('.eddc-commission-rate').toggle();
		$('.eddc-commission-amount').toggle();
	});

	$('.eddc-commission-user').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	$('.eddc-commission-download').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	$('.eddc-commission-rate').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	$('.eddc-commission-amount').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});
});
