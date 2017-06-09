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

		$commissions_db      = edd_commissions()->commissions_db;
		if ( ! $commissions_db->table_exists( $commissions_db->table_name ) ) {
			@$commissions_db->create_table();
		}

		$commissions_meta_db = edd_commissions()->commission_meta_db;
		if ( ! $commissions_meta_db->table_exists( $commissions_meta_db->table_name ) ) {
			@$commissions_meta_db->create_table();
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

				$download        = new EDD_Download( $post_meta['_download_id'] );
				$commission_info = isset( $post_meta['_edd_commission_info'] ) ? $post_meta['_edd_commission_info'] : array();
				if ( empty( $commission_info ) ) {
					continue; // We got some bad records, just move on
				}

				$commission_price_id = false;
				if ( ! empty( $post_meta['_edd_commission_download_variation'] ) ) {
					$prices = $download->get_prices();
					foreach ( $prices as $price_id => $price ) {
						if ( $price['name'] === $post_meta['_edd_commission_download_variation'] ) {
							$commission_price_id = $price_id;
						}
					}
				}

				if ( ! empty( $post_meta['_edd_commission_payment_id'] ) ) {

					$payment    = new EDD_Payment( $post_meta['_edd_commission_payment_id'] );
					$cart_index = 0;
					foreach ( $payment->cart_details as $index => $item ) {

						if ( (int) $item['id'] !== (int) $download->ID ) {
							continue;
						}

						if ( false !== $commission_price_id ) {
							if ( (int) $item['item_number']['options']['price_id'] !== (int) $commission_price_id ) {
								continue;
							}
						}

						$cart_index = $index;
						break;

					}

				}

				$commission_data = array(
					'user_id'       => $commission_info['user_id'],
					'amount'        => $commission_info['amount'],
					'status'        => $post_meta['_edd_commission_status'],
					'download_id'   => $download->ID,
					'payment_id'    => $payment->ID,
					'cart_index'    => $cart_index,
					'price_id'      => $commission_price_id,
					'dated_created' => $result->post_date,
					'date_paid'     => '',
					'type'          => $commission_info['type'],
					'rate'          => $commission_info['rate'],
					'currency'      => $commission_info['currency'],
				);

				$commission_id = edd_commissions()->commissions_db->insert( $commission_data, 'commission' );
				if ( ! empty( $commission_id ) ) {
					$commission = new EDD_Commission( $commission_id );

					// Unset the now defunct post meta items so they don't get set.
					unset( $post_meta['_edd_commission_info'] );
					unset( $post_meta['_download_id'] );
					unset( $post_meta['_edd_commission_payment_id'] );
					unset( $post_meta['_edd_commission_description'] );
					unset( $post_meta['_edd_commission_status'] );
					unset( $post_meta['_user_id'] );
					unset( $post_meta['_edd_commission_download_variation'] );

					foreach ( $post_meta as $key => $value ) {
						$commission->update_meta( $key, $value );
					}
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