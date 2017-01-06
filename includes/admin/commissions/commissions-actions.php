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
