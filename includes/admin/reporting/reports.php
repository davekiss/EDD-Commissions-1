<?php
/**
 * Commission Reports
 *
 * @package     EDDC
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Report Box
 *
 * Renders the EDDC report box on the Reports page
 *
 * @since 3.3
 * @return void
*/
function eddc_add_reports_metabox() {
	?>
	<div class="postbox edd-export-commissions-history">
		<h3><span><?php _e('Export Commissions', 'eddc' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Download a CSV giving a detailed look into commissions over time.', 'eddc' ); ?></p>

			<form id="edd-export-commissions" class="edd-export-form edd-import-export-form" method="post">
				<?php echo EDD()->html->month_dropdown( 'start_month' ); ?>
				<?php echo EDD()->html->year_dropdown( 'start_year' ); ?>
				<?php echo _x( 'to', 'Date one to date two', 'eddc' ); ?>
				<?php echo EDD()->html->month_dropdown( 'end_month' ); ?>
				<?php echo EDD()->html->year_dropdown( 'end_year' ); ?>
				<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
				<input type="hidden" name="edd-export-class" value="EDD_Batch_Commissions_Report_Export"/>
				<span>
					<input type="submit" value="<?php _e( 'Generate CSV', 'eddc' ); ?>" class="button-secondary"/>
					<span class="spinner"></span>
				</span>
			</form>

		</div><!-- .inside -->
	</div><!-- .postbox -->
	<?php
}
add_action( 'edd_reports_tab_export_content_bottom', 'eddc_add_reports_metabox' );
