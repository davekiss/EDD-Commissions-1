<?php

/**
 * Tests for EDD_Commission Object.
 *
 * @group objects
 */
class Tests_EDD_Commission extends EDDC_UnitTestCase {
	/**
	 * Commission fixture.
	 *
	 * @access protected
	 * @var int
	 * @static
	 */
	protected static $commission_id = 0;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
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
}