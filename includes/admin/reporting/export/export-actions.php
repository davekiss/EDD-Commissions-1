<?php
/**
 * Export Actions
 *
 * These are actions related to exporting data from EDD Commissions.
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Register the commissions report batch exporter
 *
 * @since  3.3
 * @return void
 */
function eddc_register_commissions_report_batch_export() {
	add_action( 'edd_batch_export_class_include', 'eddc_include_commissions_report_batch_processer', 11, 1 );
}
add_action( 'edd_register_batch_exporter', 'eddc_register_commissions_report_batch_export', 11 );


/**
 * Loads the commissions report batch process if needed
 *
 * @since  3.3
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function eddc_include_commissions_report_batch_processer( $class ) {
	if ( 'EDD_Batch_Commissions_Report_Export' === $class ) {
		require_once EDDC_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-commissions-report.php';
	}
}
