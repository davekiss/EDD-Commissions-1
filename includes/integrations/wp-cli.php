<?php
/**
 * Easy Digital Downloads WP-CLI Tools for Commissions
 *
 * This class provides an integration point with the WP-CLI plugin allowing
 * access to EDD from the command line.
 *
 * @package     EDD
 * @subpackage  Classes/CLI
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/license/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

WP_CLI::add_command( 'edd-commissions', 'EDD_Commissions_CLI' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CL
 *
 * @since   1.0
 */
class EDD_Commissions_CLI extends EDD_CLI {
	/**
	 * Migrate the Commissions to the custom tables
	 *
	 * ## OPTIONS
	 *
	 * --force=<boolean>: If the routine should be run even if the upgrade routine has been run already
	 *
	 * ## EXAMPLES
	 *
	 * wp edd-commissions migrate_commissions
	 * wp edd-commissions migrate_commissions --force
	 */
	public function migrate_commissions( $args, $assoc_args ) {
		global $wpdb;
		$force  = isset( $assoc_args['force'] ) ? true : false;

		$upgrade_completed = edd_has_upgrade_completed( 'migrate_commissions' );

		if ( ! $force && $upgrade_completed ) {
			WP_CLI::error( __( 'The commissions custom database migration has already been run. To do this anyway, use the --force argument.', 'eddc' ) );
		}

		$sql     = "SELECT * FROM $wpdb->posts WHERE post_type = 'edd_commission'";
		$results = $wpdb->get_results( $sql );
		$total   = count( $results );

		if ( ! empty( $total ) ) {

			$progress = new \cli\progress\Bar( 'Migrating Commissions', $total );

			foreach ( $results as $result ) {
				$meta_items = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $result->ID ) );
				$post_meta  = array();
				foreach ( $meta_items as $meta_item ) {
					$post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
				}

				$commission_data = array(

				);

				foreach ( $post_meta as $key => $value ) {

				}

				$progress->tick();
			}

			$progress->finish();
			WP_CLI::line( __( 'Migration complete.', 'eddc' ) );
		} else {
			WP_CLI::line( __( 'No commission records found.', 'eddc' ) );
		}

		//update_option( 'edds_stripe_version', preg_replace( '/[^0-9.].*/', '', EDD_STRIPE_VERSION ) );
		//edd_set_upgrade_complete( 'stripe_customer_id_migration' );

	}
}