<?php
/**
 * Commission Object
 *
 * @package     Easy Digital Downloads - Commissions
 * @subpackage  Classes/Discount
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Commission Class
 *
 * @since 3.3
 */
class EDD_Commission {
	/**
	 * Commission ID.
	 *
	 * @since 3.3
	 * @access protected
	 * @var int
	 */
	protected $ID = 0;

	/**
	 * User ID.
	 *
	 * @since 3.3
	 * @access protected
	 * @var int
	 */
	protected $user_ID = 0;

	/**
	 * Commission Rate.
	 *
	 * @since 3.3
	 * @access protected
	 * @var mixed float|int
	 */
	protected $rate;

	/**
	 * Commission Amount.
	 *
	 * @since 3.3
	 * @access protected
	 * @var mixed float|int
	 */
	protected $amount;

	/**
	 * Currency.
	 *
	 * @since 3.3
	 * @access protected
	 * @var string
	 */
	protected $currency;

	/**
	 * Download ID.
	 *
	 * @since 3.3
	 * @access protected
	 * @var int
	 */
	protected $download_ID = 0;

	/**
	 * User ID.
	 *
	 * @since 3.3
	 * @access protected
	 * @var int
	 */
	protected $user_ID = 0;

	/**
	 * Payment ID.
	 *
	 * @since 3.3
	 * @access protected
	 * @var int
	 */
	protected $payment_ID = 0;
}