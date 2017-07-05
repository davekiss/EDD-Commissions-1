<?php
/**
 * Commissions Filters.
 *
 * @package     EDD_Commissions
 * @subpackage  Core
 * @copyright   Copyright (c) 2017, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Listen for calls to get_post_meta and see if we need to filter them.
 *
 * @since  3.4.8
 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
 * @param  int    $object_id   The object ID post meta was requested for.
 * @param  string $meta_key    The meta key requested.
 * @param  bool   $single      If the person wants the single value or an array of the value
 * @return mixed               The value to return
 */
function eddc_get_meta_backcompat( $value, $object_id, $meta_key, $single ) {
	global $wpdb;

	$meta_keys = apply_filters( 'eddc_post_meta_backwards_compat_keys', array( '_edd_commission_info', '_edd_commission_payment_id', '_download_id', '_edd_all_access_info' ) );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $value;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$show_notice     = ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout );
	$commission      = new EDD_Commission( $object_id );

	if ( empty( $commission->id ) ) {
		// We didn't find a commission record with this ID...so let's check and see if it was a migrated one
		$object_id = $wpdb->get_var( "SELECT commission_id FROM {$wpdb->prefix}edd_commissionmeta WHERE meta_key = '_edd_commission_legacy_id' AND meta_value = $object_id" );
		if ( ! empty( $object_id ) ) {
			$commission = new EDD_Commission( $object_id );
		} else {
			return $value;
		}
	}

	switch( $meta_key ) {

		case '_edd_commission_info':

			$value = array(
				'user_id'  => $commission->user_id,
				'rate'     => $commission->rate,
				'amount'   => $commission->amount,
				'currency' => $commission->currency,
				'type'     => $commission->type,
			);

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_info postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_commission_status' :

			$value = $commission->status;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _commission_status postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;


		case '_edd_commission_payment_id':

			$value = $commission->payment_id;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_payment_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_download_id':

			$value = $commission->download_id;

			if ( $show_notice ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _download_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'eddc' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_edd_all_access_info':
			$commission = new EDD_Commission( $object_id );
			$value      = $commission->get_meta( '_edd_all_access_info' );
			break;

		default:
			// Developers can hook in here with add_action( 'eddc_post_meta_backwards_compat-meta_key... in order to
			// Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Commission::get_meta
			$value = apply_filters( 'eddc_post_meta_backwards_compat-' . $meta_key, $value, $object_id );
			break;
	}

	return array( $value );

}
add_filter( 'get_post_metadata', 'eddc_get_meta_backcompat', 99, 4 );