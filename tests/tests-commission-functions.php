<?php

class Tests_EDD_Commissions_Functions extends WP_UnitTestCase {
	protected $object;

	public function setUp() {
		parent::setUp();
		$this->_payment_id = EDD_Helper_Payment::create_simple_payment();
		$this->_payment = new EDD_Payment( $this->_payment_id );
		$this->_download_id = $this->_payment->downloads[0]['id'];
		$this->_user     = get_user_by( 'login', 'subscriber' );
		$this->_author   = get_user_by( 'login', 'author' );
	}

	public function tearDown() {
		parent::tearDown();
		EDD_Helper_Download::delete_download( $this->_download_id );
	}

	public function test_eddc_calculate_payment_commissions(){

		// Set the product's rates
		$commissions_config = array(
			'type'    => 'percentage',
			'amount'  => '3,0',
			'user_id' => $this->_user->ID . ',' . $this->_author->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commisions_enabled', 'commissions_enabled' );
		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$expected = array(
			array(
				'recipient'           => $this->_user->ID,
				'commission_amount'   => ( 3 / 100 ) * $this->_payment->cart_details[0]['price'],
				'rate'                => 3,
				'download_id'         => $this->_download_id,
				'payment_id'          => $this->_payment->ID,
				'currency'            => $this->_payment->currency,
				'has_variable_prices' => false,
				'price_id'            => NULL,
				'variation'           => NULL,
				'cart_item'           => $this->_payment->cart_details[0]
			),
			array(
				'recipient'           => $this->_author->ID,
				'commission_amount'   => ( 0 / 100 ) * $this->_payment->cart_details[0]['price'],
				'rate'                => 0,
				'download_id'         => $this->_download_id,
				'payment_id'          => $this->_payment->ID,
				'currency'            => $this->_payment->currency,
				'has_variable_prices' => false,
				'price_id'            => NULL,
				'variation'           => NULL,
				'cart_item'           => $this->_payment->cart_details[0]
			)
		);

		$actual = eddc_calculate_payment_commissions( $this->_payment->ID );

		$this->assertSame( $expected, $actual );
	}

	public function test_get_recipient_rate_item_level() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '3',
			'user_id' => $this->_user->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 3, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '0',
			'user_id' => $this->_user->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 0, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );

	}

	public function test_get_recipient_rate_user_level() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => $this->_user->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 2, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );

		// Set the user rate to 0
		update_user_meta( $this->_user->ID, 'eddc_user_rate', 0 );

		$this->assertEquals( 0, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );

	}

	public function test_get_recipient_rate_global_level() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', '' );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => $this->_user->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 1, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );

	}

	public function test_get_recipient_rate_item_level_multiuser() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', 2 );
		update_user_meta( $this->_author->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '3,0',
			'user_id' => $this->_user->ID . ',' . $this->_author->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 3, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );
		$this->assertEquals( 0, eddc_get_recipient_rate( $this->_download_id, $this->_author->ID ) );

		// Swap the order, just to be sure
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '0,3',
			'user_id' => $this->_author->ID . ',' . $this->_user->ID,
		);

		$this->assertEquals( 3, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );
		$this->assertEquals( 0, eddc_get_recipient_rate( $this->_download_id, $this->_author->ID ) );

	}

	public function test_get_recipient_rate_user_level_multiuser() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', 2 );
		update_user_meta( $this->_author->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => $this->_user->ID . ',' . $this->_author->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 2, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );
		$this->assertEquals( 2, eddc_get_recipient_rate( $this->_download_id, $this->_author->ID ) );

		// Now rely on the user level for only 1 of the users
		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '1',
			'user_id' => $this->_user->ID . ',' . $this->_author->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 1, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );
		$this->assertEquals( 2, eddc_get_recipient_rate( $this->_download_id, $this->_author->ID ) );
	}

	public function test_get_recipient_rate_global_level_multiuser() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', '' );
		update_user_meta( $this->_author->ID, 'eddc_user_rate', '' );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => $this->_user->ID . ',' . $this->_author->ID,
		);

		update_post_meta( $this->_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 1, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );
		$this->assertEquals( 1, eddc_get_recipient_rate( $this->_download_id, $this->_author->ID ) );

		// Now rely on a user having a rate, and the other being global
		update_user_meta( $this->_author->ID, 'eddc_user_rate', 2 );
		$this->assertEquals( 1, eddc_get_recipient_rate( $this->_download_id, $this->_user->ID ) );
		$this->assertEquals( 2, eddc_get_recipient_rate( $this->_download_id, $this->_author->ID ) );
	}

	public function test_get_recipient_rate_user_no_download() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', 2 );
		$this->assertEquals( 2, eddc_get_recipient_rate( 0, $this->_user->ID ) );

		update_user_meta( $this->_user->ID, 'eddc_user_rate', 0 );
		$this->assertEquals( 0, eddc_get_recipient_rate( 0, $this->_user->ID ) );
	}

	public function test_get_recipient_rate_global_no_download() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( $this->_user->ID, 'eddc_user_rate', '' );
		$this->assertEquals( 1, eddc_get_recipient_rate( 0, $this->_user->ID ) );
	}

}
