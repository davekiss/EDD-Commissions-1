<?php
/**
 * Commissions Functions.
 *
 * @package EDD_Commissions
 * @subpackage Functions
 * @copyright   Copyright (c) 2017, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieves an instance of EDD_Commission for a specified ID.
 *
 * @since 2.7
 *
 * @param mixed int|EDD_Commission|WP_Post $commission Commission ID, EDD_Commission object or WP_Post object.
 * @return mixed false|object EDD_Commission if a valid commission ID, false otherwise.
 */
function eddc_get_commission( $commission = null ) {
	if ( is_a( $commission, 'WP_Post' ) || is_a( $commission, 'EDD_Commission' ) ) {
		$commission_id = $commission->ID;
	} else {
		$commission_id = $commission;
	}

	if ( empty( $commission_id ) ) {
		return false;
	}

	$cache_key  = md5( 'eddc_commission' . $commission_id );
	$commission = wp_cache_get( $cache_key, 'commissions' );

	if ( false === $commission ) {
		$commission = new EDD_Commission( $commission_id );
		if ( empty( $commission->ID ) || ( (int) $commission->ID !== (int) $commission_id ) ) {
			return false;
		} else {
			wp_cache_set( $cache_key, $commission, 'commissions' );
		}
	}

	return $commission;
}

/**
 * Register a view for the single commission view
 *
 * @since  3.3
 * @param  array $views An array of existing views
 * @return array        The altered list of views
 */
function eddc_register_default_commission_views( $views ) {
	$default_views = array(
		'overview' => 'eddc_commissions_view',
		'delete'   => 'eddc_commissions_delete_view'
	);

	return array_merge( $views, $default_views );
}
add_filter( 'eddc_commission_views', 'eddc_register_default_commission_views', 1, 1 );


/**
 * Register a tab for the single commission view
 *
 * @since  3.3
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs
 */
function eddc_register_default_commission_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-products', 'title' => __( 'Overview', 'eddc' ) ),
	);

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'eddc_commission_tabs', 'eddc_register_default_commission_tabs', 1, 1 );


/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since  3.3
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs, with 'delete' at the bottom
 */
function eddc_register_delete_commission_tab( $tabs ) {
	$tabs['delete'] = array( 'dashicon' => 'dashicons-trash', 'title' => __( 'Delete', 'eddc' ) );

	return $tabs;
}
add_filter( 'eddc_commission_tabs', 'eddc_register_delete_commission_tab', PHP_INT_MAX, 1 );
