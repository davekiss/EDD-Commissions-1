<?php


/**
 * Register the payouts batch exporter
 * @since  2.4.2
 */
function eddc_register_payouts_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_payouts_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_payouts_batch_export', 10 );

/**
 * Loads the commissions payouts batch process if needed
 *
 * @since  2.4.2
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function eddc_include_payouts_batch_processer( $class ) {

	if ( 'EDD_Batch_Commissions_Payout' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/class-batch-commissions-payout.php';
	}

}

/**
 * Register the payouts batch exporter
 * @since  2.4.2
 */
function eddc_register_mark_paid_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_paid_batch_processer', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_mark_paid_batch_export', 10 );

/**
 * Loads the commissions payouts batch process if needed
 *
 * @since  2.4.2
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function eddc_include_paid_batch_processer( $class ) {

	if ( 'EDD_Batch_Commissions_Mark_Paid' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/class-batch-commissions-mark-paid.php';
	}

}

/**
 * Register a filter against the user search when commissions is active
 *
 * @since  3.2
 * @return void
 */
function eddc_register_filter_found_users() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_filter( 'edd_ajax_found_users', 'eddc_filter_found_users', 10, 2 );
	}
}
add_action( 'admin_init', 'eddc_register_filter_found_users' );

/**
 * Filter the users found by the ajax search to include PayPal email
 *
 * @since  3.2
 * @param  array $users          The users found by the default search
 * @param  string $search_query  The query searched for
 * @return array                 The array of found users
 */
function eddc_filter_found_users( $users, $search_query ) {

	$exclude = array();
	if ( ! empty( $users ) ) {
		foreach ( $users as $user ) {
			$exclude[] = $user->ID;
		}
	}

	$get_users_args = array(
		'number'     => 9999,
		'exclude'    => $exclude,
		'meta_query' => array(
			array(
				'key'     => 'eddc_user_paypal',
				'value'   => $search_query,
				'compare' => 'LIKE',
			),
		),
	);

	$found_users = get_users( $get_users_args );
	if ( ! empty( $found_users ) ) {
		$users = array_merge( $users, $found_users );
	}

	return $users;
}
