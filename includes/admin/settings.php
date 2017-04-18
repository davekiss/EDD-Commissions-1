<?php
/**
 * Extension settings
 *
 * @package     EDDC
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.1
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers the subsection for EDD Settings
 *
 * @since       3.2.5
 * @param       array $sections The sections
 * @return      array Sections with commissions added
 */
function eddc_settings_section_extensions( $sections ) {
	$sections['commissions'] = __( 'Commissions', 'eddc' );
	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'eddc_settings_section_extensions' );


/**
 * Registers the new Commissions options in Extensions
 *
 * @since       1.2.1
 * @param       $settings array the existing plugin settings
 * @return      array The new EDD settings array with commissions added
 */
function eddc_settings_extensions( $settings ) {
	$calc_options = array(
		'subtotal'      => __( 'Subtotal (default)', 'eddc' ),
		'total'         => __( 'Total with Taxes', 'eddc' ),
		'total_pre_tax' => __( 'Total without Taxes', 'eddc' ),
	);

	$commission_settings = array(
		array(
			'id'      => 'eddc_header',
			'name'    => '<strong>' . __( 'Commissions Settings', 'eddc' ) . '</strong>',
			'desc'    => '',
			'type'    => 'header',
			'size'    => 'regular',
		),
		array(
			'id'      => 'edd_commissions_default_rate',
			'name'    => __( 'Default rate', 'eddc' ),
			'desc'    => __( 'Enter the default rate recipients should receive. This can be overwritten on a per-product basis. 10 = 10%', 'eddc' ),
			'type'    => 'text',
			'size'    => 'small',
		),
		array(
			'id'      => 'edd_commissions_calc_base',
			'name'    => __( 'Calculation Base', 'eddc' ),
			'desc'    => __( 'Should commissions be calculated from the subtotal (before taxes and discounts) or from the total purchase amount (after taxes and discounts)? ', 'eddc' ),
			'type'    => 'select',
			'options' => $calc_options,
		),
		array(
			'id'      => 'edd_commissions_autopay_pa',
			'name'    => __('Instant Pay Commmissions', 'eddc'),
			'desc'    => sprintf( __('If checked and <a href="%s">PayPal Adaptive Payments</a> gateway is installed, EDD will automatically pay commissions at the time of purchase', 'eddc'), 'https://easydigitaldownloads.com/downloads/paypal-adaptive-payments/' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_commissions_revoke_on_refund',
			'name'    => __('Revoke on Refund', 'eddc'),
			'desc'    => __('If checked EDD will automatically revoke any <em>unpaid</em> commissions when a payment is refunded.', 'eddc'),
			'type'    => 'checkbox',
		),
	);

	$payout_methods = array( 'paypal' => __( 'PayPal', 'eddc' ) );
	if ( defined( 'EDD_STRIPE_VERSION' ) ) {
		$payout_methods['stripe'] = __( 'Stripe', 'eddc' );
	}

	$commission_settings[] = array(
		'id'      => 'commissions_payout_method',
		'name'    => __( 'Payout Method', 'eddc' ),
		'desc'    => __( 'Choose how you will payout commissions', 'eddc' ),
		'type'    => 'select',
		'options' => $payout_methods,
		'std'     => 'paypal',
	);

	$commission_settings = apply_filters( 'eddc_settings', $commission_settings );

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$commission_settings = array( 'commissions' => $commission_settings );
	}

	return array_merge( $settings, $commission_settings );
}
add_filter( 'edd_settings_extensions', 'eddc_settings_extensions' );


/**
 * Add the Commissions Notifications emails subsection to the settings
 *
 * @since       3.2.12
 * @param       array $sections Sections for the emails settings tab
 * @return      array
 */
function eddc_settings_section_emails( $sections ) {
	$sections['commissions'] = __( 'Commission Notifications', 'eddc' );
	return $sections;
}
add_filter( 'edd_settings_sections_emails', 'eddc_settings_section_emails' );


/**
 * Registers the new Commissions options in Emails
 *
 * @since       3.0
 * @param       $settings array the existing plugin settings
 * @return      array
*/
function eddc_settings_emails( $settings ) {
	$commission_settings = array(
		array(
			'id'    => 'eddc_header',
			'name'  => '<strong>' . __( 'Commission Notifications', 'eddc' ) . '</strong>',
			'desc'  => '',
			'type'  => 'header',
			'size'  => 'regular'
		),
		array(
			'id'    => 'edd_commissions_disable_sale_alerts',
			'name'  => __( 'Disable New Sale Alerts', 'eddc' ),
			'desc'  => __( 'Check this box to disable the New Sale notification emails sent to commission recipients.', 'eddc' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'edd_commissions_email_subject',
			'name'  => __( 'Email Subject', 'eddc' ),
			'desc'  => __( 'Enter the subject for commission emails.', 'eddc' ),
			'type'  => 'text',
			'size'  => 'regular',
			'std'   => __( 'New Sale!', 'eddc' )
		),
		array(
			'id'    => 'edd_commissions_email_message',
			'name'  => __( 'Email Body', 'eddc' ),
			'desc'  => __( 'Enter the content for commission emails. HTML is accepted. Available template tags:', 'eddc' ) . '<br />' . eddc_display_email_template_tags(),
			'type'  => 'rich_editor',
			'std'   => eddc_get_email_default_body()
		)
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$commission_settings = array( 'commissions' => $commission_settings );
	}

	return array_merge( $settings, $commission_settings );

}
add_filter( 'edd_settings_emails', 'eddc_settings_emails' );
