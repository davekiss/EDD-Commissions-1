<?php
/**
 * Stripe Payment Gateway integration
 *
 * This file holds all functions make commissions work with the Stripe extension
 *
 * @copyright   Copyright (c) 2016, Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */

function eddc_maybe_setup_stripe() {
	if ( edd_is_test_mode() ) {
		$prefix = 'test_';
	} else {
		$prefix = 'live_';
	}

	$secret_key = edd_get_option( $prefix . 'secret_key', '' );
	$public_key = edd_get_option( $prefix . 'publishable_key', '' );

	if( class_exists( '\Stripe\Stripe' ) ) {
		\Stripe\Stripe::setApiKey( $secret_key );
	}
}

function eddc_stripe_account_setup_step_1( $data ) {
	$response = array();
	if ( empty( $data['country'] ) ) {
		$response['errors']['invalid-country'] = __( 'Country cannot be empty', 'eddc' );
	}

	if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
		$response['errors']['invalid-email'] = __( 'Email cannot be empty', 'eddc' );
	}

	if ( 'false' === $data['tos'] ) {
		$response['errors']['no-tos'] = __( 'You must agree to the Stripe Connected Account Agreement', 'eddc' );
	}

	if ( empty( $data['user_id'] ) || get_current_user_id() != $data['user_id'] ) {
		$response['errors']['invalid-user-id'] = __( 'Invalid User ID', 'eddc' );
	}

	if ( empty( $response['errors'] ) ) {
		eddc_maybe_setup_stripe();

		$stripe_account = \Stripe\Account::create(array(
		  'managed' => true,
		  'country' => sanitize_text_field( $data['country'] ),
		  'email' => sanitize_text_field( $data['email'] ),
		) );

		if ( ! empty( $stripe_account->id ) ) {
			$stripe_data = array(
				'account'     => $stripe_account->id,
				'secret'      => $stripe_account->keys->secret,
				'publishable' => $stripe_account->keys->publishable,
				'type'        => $data['type']
			);

			$stripe_account->tos_acceptance->date = current_time( 'timestamp' );
			$stripe_account->tos_acceptance->ip   = edd_get_ip();
			$stripe_account->legal_entity->type   = $data['type'];
			$stripe_account->save();

			update_user_meta( $data['user_id'], 'eddc_user_stripe', $stripe_data );
		} else {
			$response['errors']['failed-to-create'] = __( 'Failed to create account', 'eddc' );
		}
	}

	$response['success'] = ! empty( $response['errors'] ) ? false : true;
	if ( $response['success'] ) {
		ob_start;
		eddc_stripe_step_2_form( $data['user_id'] );
		$response['next_form'] = ob_get_clean();
	}

	echo json_encode( $response );
	die();
}
add_action( 'edd_stripe_account_step_1', 'eddc_stripe_account_setup_step_1', 10, 1 );

function eddc_stripe_step_2_form( $user_id ) {
	$stripe_data = get_user_meta( $user_id, 'eddc_user_stripe', true );

	if ( ! empty( $stripe_data['account'] ) ) {
		eddc_maybe_setup_stripe();
		$account = \Stripe\Account::retrieve( $stripe_data['account'] );
		$country_specs = \Stripe\CountrySpec::retrieve( $account->country );
		$country_specs = $country_specs->__toArray( true );
		$fields = $country_specs['verification_fields'][ $stripe_data['type'] ]['minimum'];

		foreach ( $fields as $field ) {
			$hook_field = str_replace( '.', '-', $field );
			do_action( 'eddc_stripe_field_' . $hook_field, $field, $account, $country_specs );
		}
		?>
		<div class="eddc-stripe-messages">

		</div>
		<p class="eddc-stripe-account-update-details">
			<input type="button" value="<?php _e( 'Update Commissions Account', 'eddc' ); ?>" name="eddc_stripe_update" id="eddc_stripe_update" />
			<input type="hidden" value="<?php echo $stripe_data['account']; ?>" name="eddc_stripe_update_account" id="eddc_stripe_update_account" />
			<input type="hidden" value="<?php echo $account->country; ?>" name="eddc_stripe_update_country" id="eddc_stripe_update_country" />
			<input type="hidden" value="<?php echo get_current_user_id(); ?>" name="eddc_stripe_update_user_id" id="eddc_stripe_update_user_id" />
		</p>
		<?php
	}
}

/** Stripe account fields */
function eddc_stripe_external_account_field( $field_id, $stripe_account, $country_specs ) {
	?>
	<p>
		<label for="eddc_stripe_bank_acct_holder"><?php _e( 'Bank account holder name', 'eddc' ); ?></label>
		<input type="text" id="eddc_stripe_bank_acct_holder" name="bank_account[account_holder_name]" value="" />
	</p>
	<p>
		<label for="eddc_stripe_bank_acct_holder_type"><?php _e( 'Bank account holder type', 'eddc' ); ?></label>
		<select id="eddc_stripe_bank_acct_holder_type" name="bank_account[account_holder_type]">
			<option value="individual"><?php _e( 'Individual', 'eddc' ); ?></option>
			<option value="company"><?php _e( 'Company', 'eddc' ); ?></option>
		</select>
	</p>
	<p>
		<label for="eddc_stripe_bank_acct_num"><?php _e( 'Account Number', 'eddc' ); ?></label>
		<input type="text" id="eddc_stripe_bank_acct_num" name="bank_account[account_number]" value="" />
	</p>
	<p>
		<label for="eddc_stripe_bank_routing_num"><?php _e( 'Routing Number', 'eddc' ); ?></label>
		<input type="text" id="eddc_stripe_bank_routing_num" name="bank_account[routing_number]" value="" />
	</p>
	<p>
		<label for="eddc_stripe_bank_currency"><?php _e( 'Currency', 'eddc' ); ?></label>
		<select name="bank_account[eddc_stripe_bank_currency]" id="eddc_stripe_bank_currency">
			<?php echo $edd_currencies = edd_get_currencies(); ?>
			<?php foreach ( $country_specs['supported_payment_currencies'] as $currency ) : ?>
				<option value="<?php echo $currency; ?>">
					<?php $currency = strtoupper( $currency ); ?>
					<?php if ( array_key_exists( $currency, $edd_currencies ) ) : ?>
						<?php echo $edd_currencies[ $currency ]; ?>
					<?php endif; ?>
					<?php echo $currency; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}
add_action( 'eddc_stripe_field_external_account', 'eddc_stripe_external_account_field', 10, 3 );

function eddc_stripe_address_city_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->address->city;
	?>
	<p>
	<label for="eddc_stripe_city"><?php _e( 'Business City', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_city" name="legal_entity[address][city]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-address-city', 'eddc_stripe_address_city_field', 10, 3 );

function eddc_stripe_address_line1_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->address->line1;
	?>
	<p>
	<label for="eddc_stripe_line1"><?php _e( 'Business Address', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_line1" name="legal_entity[address][line1]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-address-line1', 'eddc_stripe_address_line1_field', 10, 3 );

function eddc_stripe_address_line2_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->address->line2;
	?>
	<p>
	<label for="eddc_stripe_line2"><?php _e( 'Business Address', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_line2" name="legal_entity[address][line2]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-address-line1', 'eddc_stripe_address_line1_field', 10, 3 );

function eddc_stripe_address_postal_code_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->address->postal_code;
	?>
	<p>
	<label for="eddc_stripe_postal_code"><?php _e( 'Business Postal code', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_postal_code" name="legal_entity[address][postal_code]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-address-postal_code', 'eddc_stripe_address_postal_code_field', 10, 3 );

function eddc_stripe_address_state_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->address->state;
	?>
	<p>
	<label for="eddc_stripe_state"><?php _e( 'Business State', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_state" name="legal_entity[address][state]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-address-state', 'eddc_stripe_address_state_field', 10, 3 );

function eddc_stripe_personal_address_city_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->personal_address->city;
	?>
	<p>
	<label for="eddc_stripe_pa_city"><?php _e( 'Personal City', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_pa_city" name="legal_entity[personal_address][city]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-personal_address-city', 'eddc_stripe_personal_address_city_field', 10, 3 );

function eddc_stripe_personal_address_line1_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->personal_address->line1;
	?>
	<p>
	<label for="eddc_stripe_pa_line1"><?php _e( 'Personal Address', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_pa_line1" name="legal_entity[personal_address][line1]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-personal_address-line1', 'eddc_stripe_personal_address_line1_field', 10, 3 );

function eddc_stripe_personal_address_line2_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->personal_address->line2;
	?>
	<p>
	<label for="eddc_stripe_pa_line2"><?php _e( 'Personal Address', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_pa_line2" name="legal_entity[personal_address][line2]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-personal_address-line1', 'eddc_stripe_personal_address_line1_field', 10, 3 );

function eddc_stripe_personal_address_postal_code_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->personal_address->postal_code;
	?>
	<p>
	<label for="eddc_stripe_pa_postal_code"><?php _e( 'Personal Postal code', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_pa_postal_code" name="legal_entity[personal_address][postal_code]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-personal_address-postal_code', 'eddc_stripe_personal_address_postal_code_field', 10, 3 );

function eddc_stripe_personal_address_state_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->personal_address->state;
	?>
	<p>
	<label for="eddc_stripe_pa_state"><?php _e( 'Personal State', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_pa_state" name="legal_entity[personal_address][state]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-personal_address-state', 'eddc_stripe_personal_address_state_field', 10, 3 );

function eddc_stripe_dob_field( $field_id, $stripe_account, $country_specs ) {
	$current_day = $stripe_account->legal_entity->dob->day;
	$current_month = $stripe_account->legal_entity->dob->month;
	$current_year = $stripe_account->legal_entity->dob->year;
	?>
	<p>
	<label><?php _e( 'Date of Birth (dd/mm/yy)', 'eddc' ); ?></label><br />
	<select class="select edd-select" name="legal_entity[dob][day]" id="eddc_stripe_dob_day" data-key="legal_entity.dob.day">
		<?php $i = 1; ?>
		<?php while ( $i <= 31 ) : ?>
			<option <?php selected( $i, $current_day, true ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php $i++; ?>
		<?php endwhile; ?>
	</select>
	<select class="select edd-select" name="legal_entity[dob][month]" id="eddc_stripe_dob_month" data-key="legal_entity.dob.month">
		<?php $i = 1; ?>
		<?php while ( $i <= 12 ) : ?>
			<option <?php selected( $i, $current_month, true ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php $i++; ?>
		<?php endwhile; ?>
	</select>
	<select class="select edd-select" name="legal_entity[dob][year]" id="eddc_stripe_dob_year" data-key="legal_entity.dob.year">
		<?php $i = date( 'Y' ); ?>
		<?php $limit = $i - 80; ?>
		<?php while ( $i >= $limit ) : ?>
			<option <?php selected( $i, $current_year, true ); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php $i--; ?>
		<?php endwhile; ?>
	</select>
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-dob-day', 'eddc_stripe_dob_field', 10, 3 );

function eddc_stripe_first_name_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->first_name;
	?>
	<p>
	<label for="eddc_stripe_first_name"><?php _e( 'First Name', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_first_name" name="legal_entity[first_name]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-first_name', 'eddc_stripe_first_name_field', 10, 3 );

function eddc_stripe_last_name_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->last_name;
	?>
	<p>
	<label for="eddc_stripe_last_name"><?php _e( 'Last Name', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_last_name" name="legal_entity[last_name]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-last_name', 'eddc_stripe_last_name_field', 10, 3 );

function eddc_stripe_ssn_last_4_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->ssn_last_4;
	?>
	<p>
	<label for="eddc_stripe_ssn_last_4"><?php _e( 'Last 4 of SSN', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_ssn_last_4" name="legal_entity[ssn_last_4]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-ssn_last_4', 'eddc_stripe_ssn_last_4_field', 10, 3 );

function eddc_stripe_business_name_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->business_name;
	?>
	<p>
	<label for="eddc_stripe_business_name"><?php _e( 'Business Name', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_business_name" name="legal_entity[business_name]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-business_name', 'eddc_stripe_business_name_field', 10, 3 );

function eddc_stripe_business_tax_id_field( $field_id, $stripe_account, $country_specs ) {
	$current_value = $stripe_account->legal_entity->business_tax_id;
	?>
	<p>
	<label for="eddc_stripe_business_tax_id"><?php _e( 'Business Tax ID', 'eddc' ); ?></label>
	<input type="text" id="eddc_stripe_business_tax_id" name="legal_entity[business_tax_id]" value="<?php echo $current_value; ?>"  data-key="<?php echo $field_id; ?>" />
	</p>
	<?php
}
add_action( 'eddc_stripe_field_legal_entity-business_tax_id', 'eddc_stripe_business_tax_id_field', 10, 3 );

function eddc_stripe_account_setup_step_2( $data ) {
	$response = array();
	if ( empty( $data['user_id'] ) || get_current_user_id() != $data['user_id'] ) {
		$response['errors']['invalid-user-id'] = __( 'Invalid User ID', 'eddc' );
	}

	if ( empty( $response['errors'] ) ) {
		eddc_maybe_setup_stripe();

		$stripe_data = get_user_meta( $data['user_id'], 'eddc_user_stripe', true );

		if ( ! empty( $stripe_data['account'] ) ) {
			eddc_maybe_setup_stripe();
			$stripe_account = \Stripe\Account::retrieve( $stripe_data[ 'account' ] );
			$account_data = array();
			if ( ! empty( $stripe_account->id ) ) {
				foreach ( $data['field_data']['legal_entity'] as $key => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $key2 => $value2 ) {
							if ( ! empty( $value2 ) ) {
								$account_data[ $key ][ $key2 ] = $value2;
							}
						}
					} else {
						if ( ! empty( $value ) ) {
							$account_data[ $key ] = $value;
						}
					}
				}
				$stripe_account->legal_entity = $account_data;
				$stripe_account->external_account = $data['token'];
				$stripe_account->save();

				update_user_meta( $data['user_id'], 'eddc_user_stripe', $stripe_data );
			} else {
				$response['errors']['failed-to-create'] = __( 'No account found', 'eddc' );
			}
		}
	}

	$response['success'] = ! empty( $response['errors'] ) ? false : true;
	$response['message'] = $response['success'] ? __( 'Account successfully updated', 'eddc' ) : false;

	echo json_encode( $response );
	die();
}
add_action( 'edd_stripe_account_step_2', 'eddc_stripe_account_setup_step_2', 10, 1 );