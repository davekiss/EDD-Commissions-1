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

	$('.eddc-edit-commission-user').on('click', function(e) {
		e.preventDefault();

		var link = $(this);
		var user_input = $('input.eddc-commission-user');

		eddc_edit_commission_item(link, user_input);

		$('.eddc-commission-user').toggle();
	});

	$('.eddc-commission-user').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	$('.eddc-edit-commission-download').on('click', function(e) {
		e.preventDefault();

		var link = $(this);
		var user_input = $('input.eddc-commission-download');

		eddc_edit_commission_item(link, user_input);

		$('.eddc-commission-download').toggle();
	});

	$('.eddc-commission-download').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	$('.eddc-edit-commission-rate').on('click', function(e) {
		e.preventDefault();

		var link = $(this);
		var user_input = $('input.eddc-commission-rate');

		eddc_edit_commission_item(link, user_input);

		$('.eddc-commission-rate').toggle();
	});

	$('.eddc-commission-rate').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	$('.eddc-edit-commission-amount').on('click', function(e) {
		e.preventDefault();

		var link = $(this);
		var user_input = $('input.eddc-commission-amount');

		eddc_edit_commission_item(link, user_input);

		$('.eddc-commission-amount').toggle();
	});

	$('.eddc-commission-amount').on('change', function() {
		$('#eddc_update_commission').fadeIn('fast').css('display', 'inline-block');
	});

	function eddc_edit_commission_item (link, input) {
		if (link.text() === eddc_vars.action_edit) {
			link.data('current-value', input.val());
			link.text(eddc_vars.action_cancel);
		} else {
			input.val(link.data('current-value'));
			$('#eddc_update_commission').fadeOut('fast', function () {
				$(this).css('display', 'none');
			});
			link.text(eddc_vars.action_edit);
		}
	}
});
