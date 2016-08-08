<?php

function eddc_user_paypal_email( $user ) {
	
	// Don't show PayPal field if user has no published products
	if ( class_exists( 'FES_Vendors' ) ) {
	        $vendor = new FES_Vendors;
	        $products = $vendor->get_published_products();
	        if ( $products == false ) return;
	}
	?>
	<h3><?php _e('Easy Digital Downloads Commissions', 'eddc'); ?></h3>
	<table class="form-table">
		<?php if ( current_user_can( 'manage_shop_settings' ) ) : ?>
		<tr>
			<th><label><?php _e('User\'s PayPal Email', 'eddc'); ?></label></th>
			<td>
				<input type="email" name="eddc_user_paypal" id="eddc_user_paypal" class="regular-text" value="<?php echo get_user_meta( $user->ID, 'eddc_user_paypal', true ); ?>" />
				<span class="description"><?php _e('If the user\'s PayPal address is different than their account email, enter it here.', 'eddc'); ?></span>
			</td>
		</tr>
		<tr>
			<th><label><?php _e('User\'s Global Rate', 'eddc'); ?></label></th>
			<td>
				<input type="text" name="eddc_user_rate" id="eddc_user_rate" class="small-text" value="<?php echo get_user_meta( $user->ID, 'eddc_user_rate', true ); ?>" />
				<span class="description"><?php _e('Enter a global commission rate for this user. If a rate is not specified for a product, this rate will be used.', 'eddc'); ?></span>
			</td>
		</tr>
		<?php else : ?>
		<tr>
			<th><label><?php _e('PayPal Email', 'eddc'); ?></label></th>
			<td>
				<input type="email" name="eddc_user_paypal" id="eddc_user_paypal" class="regular-text" value="<?php echo get_user_meta( $user->ID, 'eddc_user_paypal', true ); ?>" />
				<span class="description"><?php _e('If your PayPal address is different than your account email, enter it here.', 'eddc'); ?></span>
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'eddc_user_paypal_email' );
add_action( 'edit_user_profile', 'eddc_user_paypal_email' );


function eddc_save_user_paypal( $user_id ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if( is_email( $_POST['eddc_user_paypal'] ) ) {
		update_user_meta( $user_id, 'eddc_user_paypal', sanitize_text_field( $_POST['eddc_user_paypal'] ) );
	} else {
		delete_user_meta( $user_id, 'eddc_user_paypal' );
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {

		if( ! empty( $_POST['eddc_user_rate'] ) ) {
			update_user_meta( $user_id, 'eddc_user_rate', sanitize_text_field( $_POST['eddc_user_rate'] ) );
		} else {
			delete_user_meta( $user_id, 'eddc_user_rate' );
		}

	}
}
add_action( 'personal_options_update', 'eddc_save_user_paypal' );
add_action( 'edit_user_profile_update', 'eddc_save_user_paypal' );
