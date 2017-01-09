<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add Commissions link
 *
 * @since       1.0
 * @return      void
*/
function eddc_add_commissions_link() {
	global $eddc_commissions_page;

	$eddc_commissions_page = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Commissions', 'eddc' ), __( 'Commissions', 'eddc' ), 'edit_products', 'edd-commissions', 'eddc_commissions_page' );
}
add_action( 'admin_menu', 'eddc_add_commissions_link', 10 );


/**
 * Process commission actions for single view
 *
 * @since 3.3
 * @return void
 */
function eddc_process_commission_update() {
	if ( empty( $_GET['commission'] ) || empty( $_GET['action'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'eddc_commission_nonce' ) ) {
		return;
	}

	$action = sanitize_text_field( $_GET['action'] );
	$id     = absint( $_GET['commission'] );

	switch( $action ) {
		case 'mark_as_paid':
			eddc_set_commission_status( $id, 'paid' );
			break;
		case 'mark_as_unpaid':
			eddc_set_commission_status( $id, 'unpaid' );
			break;
		case 'mark_as_revoked':
			eddc_set_commission_status( $id, 'revoked' );
			break;
		case 'mark_as_accepted':
			eddc_set_commission_status( $id, 'unpaid' );
			break;
	}

	wp_redirect( add_query_arg( array( 'action' => false, '_wpnonce' => false, 'edd-message' => $action ) ) );
	exit;
}
add_action( 'admin_init', 'eddc_process_commission_update', 1 );


/**
 * Update commission data
 *
 * @since 3.3
 * @return void
 */
function eddc_update_commission() {
	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		return;
	}

	if ( ! isset( $_POST['eddc_user'] ) && ! isset( $_POST['eddc_download'] ) && ! isset( $_POST['eddc_rate'] ) && ! isset( $_POST['eddc_amount'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['eddc_update_commission_nonce'], 'eddc_update_commission' ) ) {
		wp_die( __( 'Nonce verification failed', 'eddc' ), __( 'Error', 'eddc' ), array( 'response' => 403 ) );
	}

	$commission_id   = (int) $_POST['commission_id'];
	$commission_data = get_post_meta( $commission_id, '_edd_commission_info', true);

	$rate = str_replace( '%', '', $_POST['eddc_rate'] );
	if ( $rate < 1 ) {
		$rate = $rate * 100;
	}

	$amount = str_replace( '%', '', $_POST['eddc_amount'] );

	$commission_data['rate']    = (float) $rate;
	$commission_data['amount']  = (float) $amount;
	$commission_data['user_id'] = absint( $_POST['eddc_user'] );

	update_post_meta( $commission_id, '_edd_commission_info', $commission_data );
	update_post_meta( $commission_id, '_user_id', absint( $_POST['eddc_user'] ) );
	update_post_meta( $commission_id, '_download_id', absint( $_POST['eddc_download'] ) );

	wp_redirect( add_query_arg( array( 'edd-message' => 'update' ) ) );
	exit;
}
add_action( 'admin_init', 'eddc_update_commission', 1 );


/**
 * Delete a commission
 *
 * @since 3.3
 * @return void
 */
function eddc_delete_commission( $args ) {
	$commission_id = absint( $_POST['commission_id'] );
	$payment_id    = get_post_meta( $commission_id, '_edd_commission_payment_id', true );
	$confirm       = ! empty( $args['eddc-commission-delete-comfirm'] ) ? true : false;
	$nonce         = $args['_wpnonce'];

	if ( ! current_user_can( 'edit_shop_payments', $payment_id ) ) {
		wp_die( __( 'You do not have permission to edit this commission', 'eddc' ), __( 'Error', 'eddc' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $nonce, 'delete-commission' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'eddc' ) );
	}

	if ( ! $confirm ) {
		edd_set_error( 'commission-delete-no-confirm', __( 'Please confirm you want to delete this commission', 'eddc' ) );
	}

	if ( edd_get_errors() ) {
		wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission_id ) );
		exit;
	}

	wp_delete_post( $commission_id );

	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-commissions&edd-message=delete' ) );
	exit;
}
add_action( 'edd_delete_commission', 'eddc_delete_commission' );
