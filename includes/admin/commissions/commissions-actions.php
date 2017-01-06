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
