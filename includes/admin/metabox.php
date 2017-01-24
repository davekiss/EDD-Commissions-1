<?php

function eddc_add_commission_meta_box() {

	if( current_user_can( 'manage_shop_settings' ) ) {
		add_meta_box( 'edd_download_commissions', __( 'Commission', 'edd' ), 'eddc_render_commissions_meta_box', 'download', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'eddc_add_commission_meta_box', 100 );

// render the download information meta box
function eddc_render_commissions_meta_box() {
	global $post;

	// Use nonce for verification
	echo '<input type="hidden" name="edd_download_commission_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	$enabled = get_post_meta( $post->ID, '_edd_commisions_enabled', true ) ? true : false;
	$meta    = get_post_meta( $post->ID, '_edd_commission_settings', true );
	$type    = isset( $meta['type']    ) ? $meta['type']    : 'percentage';
	$display = $enabled ? '' : ' style="display:none";';

	// Convert to array
	$user_id = isset( $meta['user_id'] ) ? $meta['user_id'] : '';
	$amounts = isset( $meta['amount']  ) ? $meta['amount']  : '';
	$users   = array_map( 'trim', explode( ',', $user_id ) );
	$amounts = array_map( 'trim', explode( ',', $amounts ) );
	$rates   = array();

	foreach ( $users as $i => $user_id ) {
		$rates[ $i ] = array(
			'user_id' => $user_id,
			'amount'  => $amounts[ $i ]
		);
	}

	/**
	 * TODO: Remove once issue/148 is merged.
	 * Issue 148 reworks the logic behind when/where to load admin scripts,
	 * allowing them to load on individual download pages. To prevent a merge
	 * conflict, it is not being re-added here. As such, this injected script
	 * is left for testing purposes.
	 */
	wp_register_script( 'eddc-admin-scripts', EDDC_PLUGIN_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), EDD_COMMISSIONS_VERSION, true );
 	wp_enqueue_script( 'eddc-admin-scripts' );

	do_action( 'eddc_metabox_before', $post->ID );

	echo '<tr>';
		echo '<td class="edd_field_type_text" colspan="2">';
			do_action( 'eddc_metabox_before_commissions_enabled', $post->ID );
			echo '<input type="checkbox" name="edd_commisions_enabled" id="edd_commisions_enabled" value="1" ' . checked( true, $enabled, false ) . '/>&nbsp;';
			echo '<label for="edd_commisions_enabled">' . __( 'Check to enable commissions', 'eddc' ) . '</label>';
			do_action( 'eddc_metabox_after_commissions_enabled', $post->ID );
		echo '</td>';
	echo '</tr>';

	echo '<tr' . $display . ' class="eddc_toggled_row">';
		echo '<td class="edd_field_type_select">';
			do_action( 'eddc_metabox_before_type', $post->ID );
			echo '<label for="edd_commission_settings[type]"><strong>' . __( 'Type', 'eddc' ) . '</strong></label><br/>';
			echo '<p>';
				echo '<input type="radio" name="edd_commission_settings[type]" value="percentage"' . checked( $type, 'percentage', false ) . '/>&nbsp;' . __( 'Percentage', 'eddc' );
				echo '<br/ >';
				echo '<input type="radio" name="edd_commission_settings[type]" value="flat"' . checked( $type, 'flat', false ) . '/>&nbsp;' . __( 'Flat', 'eddc' ) . '<br/>';
			echo '</p>';
			echo '<p>' . __( 'Select the type of commission(s) to record.', 'eddc' ) . '</p>';
			do_action( 'eddc_metabox_after_type', $post->ID );
		echo '</td>';
	echo '</tr>';

	echo '</table>';

	echo '<div' . $display . ' id="eddc_commission_rates_wrapper" class="edd_meta_table_wrap eddc_toggled_row">';
		echo '<p><strong>' . __( 'Commission Rates', 'eddc' ) . '</strong></p>';
		echo '<table class="widefat edd_repeatable_table" width="100%" cellpadding="0" cellspacing="0">';
			echo '<thead>';
				echo '<tr>';
					echo '<th class="eddc-commission-rate-user">' . __( 'User', 'eddc' ) . '</th>';
					echo '<th class="eddc-commission-rate-rate">' . __( 'Rate', 'eddc' ) . '</th>';
					echo '<th class="eddc-commission-rate-remove"></th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				if ( ! empty( $rates ) ) :
					foreach ( $rates as $key => $value ) :
						echo '<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="' . esc_attr( $key ) . '">';
							echo '<td>';
								echo EDD()->html->user_dropdown( array(
									'name'        => 'edd_commission_settings[rates][' . $key . '][user_id]',
									'id'          => 'edd_commission_user_' . $key,
									'selected'    => $value['user_id']
								) );
							echo '</td>';
							echo '<td>';
								echo '<input type="text" name="edd_commission_settings[rates][' . $key . '][amount]" id="edd_commission_amount_' . $key . '" value="' . $value['amount'] . '" . placeholder="' . __( 'Rate for this user', 'eddc' ) . '"/>';
							echo '</td>';
							echo '<td>';
								echo '<a href="#" class="edd_remove_repeatable" style="background: url(' . admin_url('/images/xit.gif') . ') no-repeat;">&times;</a>';
							echo '</td>';
						echo '</tr>';
					endforeach;
				else :
					echo '<tr class="edd_repeatable_upload_wrapper edd_repeatable_row" data-key="1">';
						echo '<td>';
							echo EDD()->html->user_dropdown( array(
								'name'        => 'edd_commission_settings[rates][1][user_id]',
								'id'          => 'edd_commission_user_1'
							) );
						echo '</td>';
						echo '<td>';
							echo '<input type="text" name="edd_commission_settings[rates][1][amount]" id="edd_commission_amount_1" placeholder="' . __( 'Rate for this user', 'eddc' ) . '"/>';
						echo '</td>';
						echo '<td>';
							echo '<a href="#" class="edd_remove_repeatable" style="background: url(' . admin_url('/images/xit.gif') . ') no-repeat;">&times;</a>';
						echo '</td>';
					echo '</tr>';
				endif;
				echo '<tr>';
					echo '<td class="submit" colspan="4" style="float: none; clear:both; background: #fff;">';
						echo '<a class="button-secondary edd_add_repeatable" style="margin: 6px 0 10px;">' .  __( 'Add New Commission Rate', 'eddc' ) . '</a>';
					echo '</td>';
				echo '</tr>';
			echo '</tbody>';
		echo '</table>';
		echo '<p class="description">' . __( 'Configure the commission rates for your users. ', 'eddc' ) . '</p>';
	echo '</div>';

	do_action( 'eddc_metabox_after', $post->ID );

	echo '</table>';
}

// Save data from meta box
function eddc_download_meta_box_save( $post_id ) {
	global $post;

	// verify nonce
	if ( ! isset( $_POST['edd_download_commission_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_commission_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// Check for auto save / bulk edit
	if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return $post_id;
	}

	if ( isset( $_POST['edd_commisions_enabled'] ) ) {

		update_post_meta( $post_id, '_edd_commisions_enabled', true );

		$new  = isset( $_POST['edd_commission_settings'] ) ? $_POST['edd_commission_settings'] : false;
		$type = ! empty( $_POST['edd_commission_settings']['type'] ) ? $_POST['edd_commission_settings']['type'] : 'percentage';

		if ( ! empty( $_POST['edd_commission_settings']['rates'] ) && is_array( $_POST['edd_commission_settings']['rates'] ) ) {
			$users   = array();
			$amounts = array();

			foreach( $_POST['edd_commission_settings']['rates'] as $rate ) {
				$amounts[] = $rate['amount'];
				$users[]   = $rate['user_id'];
			}

			$new['user_id'] = implode( ',', $users );
			$new['amount']  = implode( ',', $amounts );

			// No need to store this value since we're saving as a string
			unset( $_POST['edd_commission_settings']['rates'] );
		}

		if ( $new ) {
			if( ! empty( $new['amount'] ) ) {
				$new['amount'] = str_replace( '%', '', $new['amount'] );
				$new['amount'] = str_replace( '$', '', $new['amount'] );

				$values           = explode( ',', $new['amount'] );
				$sanitized_values = array();

				foreach ( $values as $key => $value ) {

					switch( $type ) {
						case 'flat':
							$value = $value < 0 || ! is_numeric( $value ) ? 0 : $value;
							break;
						case 'percentage':
						default:
							if ( $value < 0 || ! is_numeric( $value ) ) {
								$value = 0;
							}

							$value = $value < 1 ? $value * 100 : $value;
							break;
					}

					$sanitized_values[ $key ] = round( $value, 2 );

				}

				$new_values    = implode( ',', $sanitized_values );
				$new['amount'] = trim( $new_values );
			}
		}
		update_post_meta( $post_id, '_edd_commission_settings', $new );

	} else {
		delete_post_meta( $post_id, '_edd_commisions_enabled' );
	}
}
add_action( 'save_post', 'eddc_download_meta_box_save' );
