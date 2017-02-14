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

	/**
	 * Array of items that have changed since the last save() was run.
	 * This is for internal use, to allow fewer update_post_meta calls to be run.
	 *
	 * @since 3.3
	 * @access private
	 * @var array
	 */
	private $pending;

	/**
	 * Declare the default properties in WP_Post as we can't extend it.
	 *
	 * @since 3.3
	 * @access protected
	 * @var mixed
	 */
	protected $post_author = 0;
	protected $post_date = '0000-00-00 00:00:00';
	protected $post_date_gmt = '0000-00-00 00:00:00';
	protected $post_content = '';
	protected $post_title = '';
	protected $post_excerpt = '';
	protected $post_status = 'publish';
	protected $comment_status = 'open';
	protected $ping_status = 'open';
	protected $post_password = '';
	protected $post_name = '';
	protected $to_ping = '';
	protected $pinged = '';
	protected $post_modified = '0000-00-00 00:00:00';
	protected $post_modified_gmt = '0000-00-00 00:00:00';
	protected $post_content_filtered = '';
	protected $post_parent = 0;
	protected $guid = '';
	protected $menu_order = 0;
	protected $post_mime_type = '';
	protected $comment_count = 0;
	protected $filter;
	protected $post_type;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 * @access protected
	 *
	 * @param int $id Commission ID.
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$id = absint( $id );
		$commission = WP_Post::get_instance( $id );

		if ( $commission ) {
			$this->setup_commission( $commission );
		} else {
			return false;
		}
	}

	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since 3.3
	 * @access public
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$key = sanitize_key( $key );

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else if ( property_exists( $this, $key ) ) {
			return $this->{$key};
		} else {
			return new WP_Error( 'edd-commissions-invalid-property', sprintf( __( 'Can\'t get property %s', 'eddc' ), $key ) );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since 3.3
	 * @access public
	 *
	 * @see set()
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( $key, $value ) {
		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys ) ) {
			return false;
		}

		$this->pending[ $key ] = $value;

		// Dispatch to setter method if value needs to be sanitized
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
		}
	}

	/**
	 * Magic __isset method to allow empty checks on protected elements
	 *
	 * @since 3.3
	 * @access public
	 *
	 * @param string $key The attribute to get
	 * @return boolean If the item is set or not
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
		} else {
			return null;
		}
	}

	/**
	 * Converts the instance of the EDD_Discount object into an array for special cases.
	 *
	 * @since 3.3
	 * @access public
	 *
	 * @return array EDD_Discount object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}
}