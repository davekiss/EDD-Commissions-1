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
});
