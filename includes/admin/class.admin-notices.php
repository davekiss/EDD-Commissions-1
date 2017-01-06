<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDDC_Admin_Notices {

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function notices() {
		if( ! isset( $_GET['page'] ) || $_GET['page'] != 'edd-commissions' ) {
			return;
		}

		if( empty( $_GET['edd-message'] ) ) {
			return;
		}

		$type    = 'updated';
		$message = '';

		switch( strtolower( $_GET['edd-message'] ) ) {

			case 'delete' :

				$message = __( 'Commission deleted successfully', 'eddc' );

				break;

		}

		if ( ! empty( $message ) ) {
			echo '<div class="' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
		}

	}

}
$eddc_admin_notices = new EDDC_Admin_Notices;
