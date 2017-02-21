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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * EDD_Commission Class
 *
 * @since 3.3
 */
class EDD_Commission {


	/**
	 * Commission ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $ID = 0;


	/**
	 * User ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $user_ID = 0;


	/**
	 * Description (same as post_title).
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $description = null;


	/**
	 * Commission Rate.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         mixed float|int
	 */
	protected $rate = 0.00;


	/**
	 * Commission Type.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $type = null;


	/**
	 * Commission Amount.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         mixed float|int
	 */
	protected $amount = 0.00;


	/**
	 * Currency.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $currency = null;


	/**
	 * Download ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $download_ID = 0;


	/**
	 * Payment ID.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         int
	 */
	protected $payment_ID = 0;


	/**
	 * Status.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $status = null;


	/**
	 * Is Renewal?
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         bool
	 */
	protected $is_renewal = false;


	/**
	 * Download variation (if any).
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         string
	 */
	protected $download_variation = null;


	/**
	 * Array of items that have changed since the last save() was run.
	 * This is for internal use, to allow fewer update_post_meta calls to be run.
	 *
	 * @since       3.3
	 * @access      private
	 * @var         array
	 */
	private $pending;


	/**
	 * Declare the default properties in WP_Post as we can't extend it.
	 *
	 * @since       3.3
	 * @access      protected
	 * @var         mixed
	 */
	protected $post_author           = 0;
	protected $post_date             = '0000-00-00 00:00:00';
	protected $post_date_gmt         = '0000-00-00 00:00:00';
	protected $post_content          = '';
	protected $post_title            = '';
	protected $post_excerpt          = '';
	protected $post_status           = 'publish';
	protected $comment_status        = 'open';
	protected $ping_status           = 'open';
	protected $post_password         = '';
	protected $post_name             = '';
	protected $to_ping               = '';
	protected $pinged                = '';
	protected $post_modified         = '0000-00-00 00:00:00';
	protected $post_modified_gmt     = '0000-00-00 00:00:00';
	protected $post_content_filtered = '';
	protected $post_parent           = 0;
	protected $guid                  = '';
	protected $menu_order            = 0;
	protected $post_mime_type        = '';
	protected $comment_count         = 0;
	protected $filter;
	protected $post_type;


	/**
	 * Constructor.
	 *
	 * @since       3.3
	 * @access      protected
	 * @param       int $id Commission ID.
	 */
	public function __construct( $id = false ) {
		if ( empty( $id ) ) {
			return false;
		}

		$id         = absint( $id );
		$commission = WP_Post::get_instance( $id );

		$this->setup_commission( $commission );
	}


	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       mixed $key
	 * @return      mixed
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} elseif ( property_exists( $this, $key ) ) {
			return $this->{$key};
		} else {
			return new WP_Error( 'edd-commissions-invalid-property', sprintf( __( 'Can\'t get property %s', 'eddc' ), $key ) );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since       3.3
	 * @access      public
	 * @see         set()
	 * @param       string $key Property name.
	 * @param       mixed $value Property value.
	 */
	public function __set( $key, $value ) {
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
	 * @since       3.3
	 * @access      public
	 * @param       string $key The attribute to get
	 * @return      boolean If the item is set or not
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
	 * @since       3.3
	 * @access      public
	 * @return      array EDD_Discount object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}


	/**
	 * Setup object vars with commission WP_Post object.
	 *
	 * @since       3.3
	 * @access      private
	 * @param       object $commission WP_Post instance of the commission.
	 * @return      bool Object var initialisation successful or not.
	 */
	private function setup_commission( $commission = null ) {
		$this->pending = array();

		if ( null == $commission ) {
			return false;
		}

		if ( ! is_object( $commission ) ) {
			return false;
		}

		if ( is_wp_error( $commission ) ) {
			return false;
		}

		if ( ! is_a( $commission, 'WP_Post' ) ) {
			return false;
		}

		if ( 'edd_commission' !== $commission->post_type ) {
			return false;
		}

		/**
		 * Fires before the instance of the EDD_Commission object is set up.
		 *
		 * @since 3.3
		 *
		 * @param object EDD_Commission      EDD_Commission instance of the commission object.
		 * @param object WP_Post $commission WP_Post instance of the commission object.
		 */
		do_action( 'eddc_pre_setup_commission', $this, $commission );

		/**
		 * Setup all object variables
		 */
		$this->ID          = absint( $commission->ID );
		$this->user_ID     = $this->setup_user_ID();
		$this->description = $commission->post_title;
		$this->rate        = $this->setup_rate();
		$this->type        = $this->setup_type();
		$this->amount      = $this->setup_amount();
		$this->currency    = $this->setup_currency();
		$this->download_ID = $this->setup_download_ID();
		$this->payment_ID  = $this->setup_payment_ID();
		$this->status      = $this->setup_status();
		$this->is_renewal  = $this->setup_is_renewal();
		$this->download_variation = $this->setup_download_variation();

		/**
		 * Setup discount object vars with WP_Post vars
		 */
		foreach ( get_object_vars( $commission ) as $key => $value ) {
			$this->{$key} = $value;
		}

		/**
		 * Fires after the instance of the EDD_Commission object is set up. Allows extensions to add items to this object via hook.
		 *
		 * @since       3.3
		 * @param       object EDD_Commission EDD_Commission instance of the commission object.
		 * @param       object WP_Post $commission WP_Post instance of the commission object.
		 */
		do_action( 'eddc_after_setup_commission', $this, $commission );

		return true;
	}


	/**
	 * Setup Functions
	 */


	/**
	 * Setup commission user ID.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int User ID.
	 */
	private function setup_user_ID() {
		$user_ID = $this->get_meta( 'user_id', true );
		return $user_ID;
	}


	/**
	 * Setup commission rate.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int User ID.
	 */
	private function setup_rate() {
		$rate = $this->get_meta( 'rate', true );
		return $rate;
	}


	/**
	 * Setup commission type.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      string Commission type.
	 */
	private function setup_type() {
		$type = $this->get_meta( 'type', true );
		return $type;
	}


	/**
	 * Setup commission amount.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int User ID.
	 */
	private function setup_amount() {
		$amount = $this->get_meta( 'amount', true );
		return $amount;
	}


	/**
	 * Setup commission currency.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int User ID.
	 */
	private function setup_currency() {
		$currency = $this->get_meta( 'currency', true );
		return $currency;
	}


	/**
	 * Setup commission download ID.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int User ID.
	 */
	private function setup_download_ID() {
		$download_ID = $this->get_meta( 'download_id', true );
		return $download_ID;
	}


	/**
	 * Setup commission payment ID.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      int User ID.
	 */
	private function setup_payment_ID() {
		$payment_ID = $this->get_meta( 'payment_id', true );
		return $payment_ID;
	}


	/**
	 * Setup the paid status of a commission.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      string Status.
	 */
	private function setup_status() {
		$status = 'unpaid';
		$terms  = get_the_terms( $this->ID, 'edd_commission_status' );

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$status = $term->slug;
				break;
			}
		}

		return $status;
	}


	/**
	 * Setup the property that determines whether the commission is a renewal or not.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      bool Is renewal?
	 */
	private function setup_is_renewal() {
		$is_renewal = $this->get_meta( 'is_renewal', true );
		return (bool) $is_renewal;
	}


	/**
	 * Setup the download variation (if any).
	 *
	 * @since       3.3
	 * @access      private
	 * @return      string Download variation.
	 */
	private function setup_download_variation() {
		$download_variation = $this->get_meta( 'download_variation', true );
		return $download_variation;
	}


	/**
	 * Helper method to retrieve meta data associated with the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $key Meta key.
	 * @param       bool $single Return single item or array.
	 */
	public function get_meta( $key = '', $single = true ) {
		$commission_info = array( 'user_id', 'rate', 'amount', 'currency' );

		if ( in_array( $key, $commission_info ) ) {
			$meta = get_post_meta( $this->ID, '_edd_commission_info', true );
			if ( isset( $meta[ $key ] ) ) {
				return $meta[ $key ];
			}
		}

		if ( 'download_id' == $key ) {
			$download_ID = get_post_meta( $this->ID, '_download_id', true );
			return $download_ID;
		}

		if ( 'type' === $key ) {
			$download_ID = get_post_meta( $this->ID, '_download_id', true );
			return eddc_get_commission_type( $download_ID );
		}

		$meta = get_post_meta( $this->ID, '_edd_commission_' . $key, $single );
		return $meta;
	}


	/**
	 * Retrieve the paid status of a commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      string Status.
	 */
	public function get_status() {
		/**
		 * Allow the paid status of a commission to be filtered.
		 *
		 * @since       3.3
		 * @param       string $status Paid status of a commission.
		 * @param       int $ID Commission ID.
		 */
		return apply_filters( 'eddc_get_commission_status', $this->status, $this->ID );
	}


	/**
	 * Update the status of a commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $new_status New status.
	 * @return      void
	 */
	public function set_status( $new_status = 'unpaid' ) {
		do_action( 'eddc_pre_set_commission_status', $this->ID, $new_status, $this->status );
		wp_set_object_terms( $this->ID, $new_status, 'edd_commission_status', false );
		$this->status = $new_status;
		do_action( 'eddc_set_commission_status', $this->ID, $new_status, $this->status );
	}


	/**
	 * Retrieve whether or not this commission is a renewal.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      bool Is renewal?
	 */
	public function get_is_renewal() {
		/**
		 * Allow the renewal flag of a commission to be filtered.
		 *
		 * @since       3.3
		 * @param       string $is_renewal Is the commission a renewal?
		 * @param       int $ID Commission ID.
		 */
		return apply_filters( 'eddc_commission_is_renewal', $this->is_renewal, $this->ID );
	}


	/**
	 * Retrieve the description (post_title) for the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      string Commission description.
	 */
	public function get_description() {
		/**
		 * Allow the description of a commission to be filtered.
		 *
		 * @since       3.3
		 * @param       string $description Commission description.
		 * @param       int $ID Commission ID.
		 */
		return apply_filters( 'eddc_commission_description', $this->description, $this->ID );
	}


	/**
	 * Set the 'post_title' to be the same as the description for a commission.
	 *
	 * @since       3.3
	 * @access      private
	 * @param       string $key Class property.
	 * @param       string $value Value for the class property.
	 * @return      void
	 */
	private function set_description( $key, $value ) {
		$this->post_title  = $value;
		$this->description = $value;
	}


	/**
	 * Set the 'description' to be the same as the post_title for a commission.
	 *
	 * @since       3.3
	 * @access      private
	 * @param       string $key Class property.
	 * @param       string $value Value for the class property.
	 * @return      void
	 */
	private function set_post_title( $key, $value ) {
		$this->post_title  = $value;
		$this->description = $value;
	}


	/**
	 * Check if a commission exists.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      bool Commission exists.
	 */
	public function exists() {
		if ( $this->ID > 0 ) {
			return true;
		}

		return false;
	}


	/**
	 * Create a new commission. If the commission already exists in the database, update it.
	 *
	 * @since       3.3
	 * @access      private
	 * @return      mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new commission.
	 */
	private function add() {
		/**
		 * Allow the commission information to be filtered.
		 *
		 * @since       3.3
		 * @param       array $args {
		 *     Filterable metadata.
		 *
		 *     @type int             $user_ID  User ID.
		 *     @type mixed int|float $rate     Commission rate.
		 *     @type mixed int|float $amount   Commission amount.
		 *     @type string          $currency Currency (e.g. USD).
		 * }
		 * @param       int $ID Commission ID.
		 * @param       int $payment_ID Payment ID linked to the commission.
		 * @param       int $download_ID Download ID linked to the commission.
		 */
		$commission_info = apply_filters( 'edd_commission_info', array(
			'user_id'  => $this->user_ID,
			'rate'     => $this->rate,
			'amount'   => $this->amount,
			'currency' => $this->currency,
		), $this->ID, $this->payment_ID, $this->download_ID );

		/**
		 * Allow the arguments passed to `wp_insert_post` to be filtered.
		 *
		 * @since       3.3
		 * @param       array $args {
		 *     @type string $post_title    Post title.
		 *     @type string $post_status   Post status.
		 *     @type string $post_type     Post type
		 *     @type string $post_date     Post date.
		 *     @type string $post_date_gmt Post date in the GMT timezone.
		 * }
		 */
		$args = apply_filters( 'eddc_insert_commission_args', array(
			'post_title'    => $this->post_title,
			'post_status'   => 'publish',
			'post_type'     => 'edd_commission',
			'post_date'     => ! empty( $this->date ) ? $this->date : null,
			'post_date_gmt' => ! empty( $this->date ) ? get_gmt_from_date( $this->date ) : null
		) );

		// Create a blank edd_commission post
		$commission_id = wp_insert_post( $args );

		if ( ! empty( $commission_id ) ) {
			$this->ID  = $commission_id;
		}

		return $this->ID;
	}


	/**
	 * Once object variables has been set, an update is needed to persist them to the database.
	 *
	 * @since       3.3
	 * @access      public
	 * @return      bool True if the save was successful, false if it failed or wasn't needed.
	 */
	public function save() {
		$saved = false;

		if ( empty( $this->ID ) ) {
			$commission_id = $this->add();

			if ( false === $commission_id ) {
				$saved = false;
			} else {
				$this->ID = $commission_id;
			}
		}

		/**
		 * Save all the object variables that have been updated to the database.
		 */
		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				if ( 'status' == $key ) {
					$this->update_status( $value );
				}

				$this->update_meta( $key, $value );

				if ( 'description' == $key ) {
					wp_update_post( array(
						'ID'         => $this->ID,
						'post_title' => $value
					) );
				}
			}

			$saved = true;
		}

		if ( true == $saved ) {
			$this->setup_commission( WP_Post::get_instance( $this->ID ) );

			/**
			 * Fires after each meta update allowing developers to hook their own items saved in $pending.
			 *
			 * @since       3.3
			 * @param       object EDD_Commission Instance of EDD_Commission object.
			 * @param       string $key Meta key.
			 */
			do_action( 'eddc_commission_save', $this->ID, $this );
		}

		/**
		 * Update the commission in the object cache.
		 */
		$cache_key = md5( 'eddc_commission' . $this->ID );
		wp_cache_set( $cache_key, $this, 'commissions' );

		return $saved;
	}


	/**
	 * Helper method to update meta data associated with the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $key Meta key.
	 * @param       string $value Meta value.
	 * @param       string $prev_value Previous meta value.
	 * @return      int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $key = '', $value = '', $prev_value = '' ) {
		if ( empty( $key ) || '' == $key ) {
			return false;
		}

		$key = sanitize_key( $key );

		if ( ! in_array( $key, array( 'user_id', 'type', 'rate', 'amount', 'currency', 'is_renewal', 'download_id', 'download_variation', 'payment_id' ) ) ) {
			return;
		}

		$value = apply_filters( 'eddc_update_commission_meta_' . $key, $value, $this->ID );

		$commission_info = array( 'user_id', 'type', 'rate', 'amount', 'currency' );

		// User ID is stored in two meta keys
		if ( 'user_id' == $key ) {
			update_post_meta( $this->ID, '_user_id', absint( $value ) );
		}

		if ( in_array( $key, $commission_info ) ) {
			$commission_data = $this->get_meta( 'info' );
			if ( empty( $commission_data ) ) {
				$commission_data = array();
			}
			switch ( $key ) {
				case 'rate' :
				case 'amount':
					$commission_data[ $key ] = (float) $value;
					break;
				case 'user_id' :
					$commission_data[ $key ] = absint( $value );
					break;
				default:
					$commission_data[ $key ] = $value;
					break;
			}

			return update_post_meta( $this->ID, '_edd_commission_info', $commission_data, $prev_value );
		}

		if ( 'download_id' == $key ) {
			return update_post_meta( $this->ID, '_' . $key, $value, $prev_value );
		}

		$updated = update_post_meta( $this->ID, '_edd_commission_' . $key, $value, $prev_value );

		if ( true == $updated ) {
			/**
			 * Update the commission in the object cache.
			 */
			$cache_key = md5( 'eddc_commission' . $this->ID );
			wp_cache_set( $cache_key, $this, 'commissions' );
		}

		return $updated;
	}


	/**
	 * Update the status of the commission.
	 *
	 * @since       3.3
	 * @access      public
	 * @param       string $new_status New status
	 * @return      void
	 */
	public function update_status( $new_status = '' ) {
		if ( empty( $new_status ) ) {
			return false;
		}

		/**
		 * Fires before the status of the commission is updated.
		 *
		 * @since       2.7
		 * @param       int $ID Commission ID.
		 * @param       string $new_status New status.
		 * @param       string $status Commission status.
		 */
		do_action( 'eddc_pre_set_commission_status', $this->ID, $new_status, $this->status );

		$updated = wp_set_object_terms( $this->ID, $new_status, 'edd_commission_status', false );

		if ( is_wp_error( $updated ) ) {
			return false;
		}

		$this->status = $new_status;

		/**
		 * Fires after the status of the commission is updated.
		 *
		 * @since       2.7
		 * @param       int $ID Commission ID.
		 * @param       string $new_status New status.
		 * @param       string $status Commission status.
		 */
		do_action( 'eddc_set_commission_status', $this->ID, $new_status, $this->status );

		return true;
	}
}
