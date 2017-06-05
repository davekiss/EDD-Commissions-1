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

	$meta_keys = array( '_edd_commission_info', '_edd_commission_payment_id', '_download_id' );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return $value;
	}

	$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
	$commission      = new EDD_Commission( $object_id );

	if ( $commission->id < 1 ) {
		return $value;
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

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_info postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'edd_sl' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_edd_commission_payment_id':

			$value = $commission->payment_id;

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _edd_commission_payment_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'edd_sl' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

		case '_download_id':

			$value = $commission->download_id;

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) {
				// Throw deprecated notice if WP_DEBUG is defined and on
				trigger_error( __( 'The _download_id postmeta is <strong>deprecated</strong> since EDD Commissions 3.4! Use the EDD_Commission object to get the relevant data, instead.', 'edd_sl' ) );

				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}

			break;

	}

	// If the 'single' param is false, we need to make this a single item array with the value within it
	if ( false === $single ) {
		$value = array( $value );
	}

	return $value;

}
/** TODO: Enable this and find a way to relate old commission IDs to the new ones **/
/** TODO: Re-enable this filter once deve is complete so I can find all the places we're referencing the meta in Commissions core */
//add_filter( 'get_post_metadata', 'eddc_get_meta_backcompat', 10, 4 );