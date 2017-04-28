<?php
/*
Plugin Name: Easy Digital Downloads - Commissions
Plugin URI: http://easydigitaldownloads.com/extension/commissions
Description: Record commisions automatically for users in your site when downloads are sold
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Version: 3.3.2
Text Domain: eddc
Domain Path: languages
*/


/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/

// plugin folder url
if ( ! defined( 'EDDC_PLUGIN_URL' ) ) {
	define( 'EDDC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// plugin folder path
if ( ! defined( 'EDDC_PLUGIN_DIR' ) ) {
	define( 'EDDC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
// plugin root file
if ( ! defined( 'EDDC_PLUGIN_FILE' ) ) {
	define( 'EDDC_PLUGIN_FILE', __FILE__ );
}

define( 'EDD_COMMISSIONS_VERSION', '3.3.2' );


/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function eddc_textdomain() {
	load_plugin_textdomain( 'eddc', false, dirname( plugin_basename( EDDC_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'init', 'eddc_textdomain' );


function edd_commissions__install() {

	add_option( 'eddc_version', EDD_COMMISSIONS_VERSION, '', false );

}
register_activation_hook( __FILE__, 'edd_commissions__install' );


function edd_commissions_load() {
	// Integration - Simple Shipping
	if ( class_exists( 'EDD_Simple_Shipping' ) ){
		include_once EDDC_PLUGIN_DIR . 'includes/integrations/simple-shipping.php';
	}

	// Integration - Paypal Adaptive
	if ( function_exists( 'epap_load_class' ) ) {
		include_once EDDC_PLUGIN_DIR . 'includes/integrations/paypal-adaptive-payments.php';
	}

	// Integration - Recurring Payments
	if ( class_exists( 'EDD_Recurring' ) ) {
		include_once EDDC_PLUGIN_DIR . 'includes/integrations/recurring-payments.php';
	}

	include_once EDDC_PLUGIN_DIR . 'includes/commission-actions.php';
	include_once EDDC_PLUGIN_DIR . 'includes/commission-functions.php';
	include_once EDDC_PLUGIN_DIR . 'includes/email-functions.php';
	include_once EDDC_PLUGIN_DIR . 'includes/post-type.php';
	include_once EDDC_PLUGIN_DIR . 'includes/scripts.php';
	include_once EDDC_PLUGIN_DIR . 'includes/short-codes.php';
	include_once EDDC_PLUGIN_DIR . 'includes/user-meta.php';

	include_once EDDC_PLUGIN_DIR . 'includes/classes/class-edd-commission.php';
	include_once EDDC_PLUGIN_DIR . 'includes/classes/class-rest-api.php';

	if ( is_admin() ) {
		// Handle licensing
		if ( class_exists( 'EDD_License' ) ) {
			$eddc_license = new EDD_License( __FILE__, 'Commissions', EDD_COMMISSIONS_VERSION, 'Pippin Williamson' );
		}
		//include_once(EDDC_PLUGIN_DIR . 'includes/scheduled-payouts.php');
		//include_once(EDDC_PLUGIN_DIR . 'includes/masspay/class-paypal-masspay.php');

		include_once EDDC_PLUGIN_DIR . 'includes/admin/commissions.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/commissions-actions.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/commissions-filters.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/customers.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/export-actions.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/export-functions.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/metabox.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/misc-functions.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/reports.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/settings.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/widgets.php';

		include_once EDDC_PLUGIN_DIR . 'includes/admin/classes/EDD_C_List_Table.php';
		include_once EDDC_PLUGIN_DIR . 'includes/admin/classes/class-admin-notices.php';

		include_once EDDC_PLUGIN_DIR . 'includes/admin/upgrades.php';
	}

	add_action( 'fes_load_fields_require', 'eddc_add_fes_functionality' );
}
add_action( 'plugins_loaded', 'edd_commissions_load', 1 );


function eddc_add_fes_functionality(){
	if ( class_exists( 'EDD_Front_End_Submissions' ) ) {
		if ( version_compare( fes_plugin_version, '2.3', '>=' ) ) {
			include_once( EDDC_PLUGIN_DIR . 'includes/integrations/fes-commissions-email-field.php' );
			add_filter(  'fes_load_fields_array', 'eddc_add_commissions_email', 10, 1 );
			function eddc_add_commissions_email( $fields ){
				$fields['eddc_user_paypal'] = 'FES_Commissions_Email_Field';
				return $fields;
			}
		}
	}
}
