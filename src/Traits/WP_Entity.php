<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

use DateTimeZone;
use Exception;
use ReflectionMethod;
use Tofandel\Core\Objects\WP_DateTime;
use Tofandel\Core\Objects\WP_MetaData;
use Tofandel\Core\Utils\Cache_Helper;
use WP_Error;

/**
 * Class WP_Entity
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 */
trait WP_Entity {

	/**
	 * ID for this object.
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Core data changes for this object.
	 *
	 * @var array
	 */
	protected $changes = array();

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @var bool
	 */
	protected $object_read = false;

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'data';

	/**
	 * Extra data for this object. Name value pairs (name + default value).
	 * Used as a standard way for sub classes (like product types) to add
	 * additional information to an inherited class.
	 *
	 * @var array
	 */
	protected $extra_data = array();

	/**
	 * Set to _data on construct so we can track and reset data if needed.
	 *
	 * @var array
	 */
	protected $default_data = array();

	/**
	 * Contains a reference to the data store for this class.
	 *
	 * @var object
	 */
	protected $data_store;

	/**
	 * Stores meta in cache for future reads.
	 * A group must be set to to enable caching.
	 *
	 * @var string
	 */
	protected $cache_group = '';

	/**
	 * Stores additional meta data.
	 *
	 * @var WP_MetaData[]
	 */
	protected $meta_data = null;

	/**
	 * Default constructor.
	 *
	 * @param int|object|array $read ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $read = 0 ) {
		$this->data         = array_merge( $this->data, $this->extra_data );
		$this->default_data = $this->data;
	}

	/**
	 * Only store the object ID to avoid serializing the data object instance.
	 *
	 * @return array
	 */
	public function __sleep() {
		return array( 'id' );
	}

	/**
	 * Re-run the constructor with the object ID.
	 *
	 * If the object no longer exists, remove the ID.
	 */
	public function __wakeup() {
		try {
			$this->__construct( absint( $this->id ) );
		} catch ( Exception $e ) {
			$this->set_id( 0 );
			$this->set_object_read( true );
		}
	}

	/**
	 * When the object is cloned, make sure meta is duplicated correctly.
	 *
	 */
	public function __clone() {
		$this->maybe_read_meta_data();
		if ( ! empty( $this->meta_data ) ) {
			foreach ( $this->meta_data as $array_key => $meta ) {
				$this->meta_data[ $array_key ] = clone $meta;
				if ( ! empty( $meta->id ) ) {
					$this->meta_data[ $array_key ]->id = null;
				}
			}
		}
	}

	/**
	 * Get the data store.
	 *
	 * @return object
	 */
	public function get_data_store() {
		return $this->data_store;
	}

	/**
	 * Returns the unique ID for this object.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Delete an object, set the ID to 0, and return result.
	 *
	 *
	 * @param  bool $force_delete Should the date be deleted permanently.
	 *
	 * @return bool result
	 */
	public function delete( $force_delete = false ) {
		if ( $this->data_store ) {
			$this->data_store->delete( $this, array( 'force_delete' => $force_delete ) );
			$this->set_id( 0 );

			return true;
		}

		return false;
	}

	/**
	 * Save should create or update based on object existence.
	 *
	 * @return int
	 */
	public function save() {
		if ( $this->data_store ) {
			// Trigger action before saving to the DB. Allows you to adjust object props before save.
			do_action( 'wpp_before_' . $this->object_type . '_object_save', $this, $this->data_store );

			if ( $this->get_id() ) {
				$this->data_store->update( $this );
			} else {
				$this->data_store->create( $this );
			}
		}

		return $this->get_id();
	}

	/**
	 * Change data to JSON format.
	 *
	 * @return string Data in JSON format.
	 */
	public function __toString() {
		return json_encode( $this->get_data() );
	}

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 */
	public function get_data() {
		return array_merge( array( 'id' => $this->get_id() ), $this->data, array( 'meta_data' => $this->get_meta_data() ) );
	}

	/**
	 * Returns array of expected data keys for this object.
	 *
	 * @return array
	 */
	public function get_data_keys() {
		return array_keys( $this->data );
	}

	/**
	 * Returns all "extra" data keys for an object (for sub objects like product types).
	 *
	 * @return array
	 */
	public function get_extra_data_keys() {
		return array_keys( $this->extra_data );
	}

	/**
	 * Filter null meta values from array.
	 *
	 *
	 * @param mixed $meta Meta value to check.
	 *
	 * @return bool
	 */
	protected function filter_null_meta( $meta ) {
		return ! is_null( $meta->value );
	}

	/**
	 * Get All Meta Data.
	 *
	 * @return array of objects.
	 */
	public function get_meta_data() {
		$this->maybe_read_meta_data();

		return array_values( array_filter( $this->meta_data, array( $this, 'filter_null_meta' ) ) );
	}

	/**
	 * Check if the key is an internal one.
	 *
	 * @param  string $key Key to check.
	 *
	 * @return bool   true if it's an internal key, false otherwise
	 */
	protected function is_internal_meta_key( $key ) {
		$internal_meta_key = ! empty( $key ) && $this->data_store && in_array( $key, $this->data_store->get_internal_meta_keys() );

		if ( ! $internal_meta_key ) {
			return false;
		}

		$has_setter_or_getter = is_callable( array( $this, 'set_' . $key ) ) || is_callable( array(
				$this,
				'get_' . $key
			) );

		if ( ! $has_setter_or_getter ) {
			return false;
		}
		/* translators: %s: $key Key to check */
		wpp_doing_it_wrong( __FUNCTION__, sprintf( __( 'Generic add/update/get meta methods should not be used for internal meta data, including "%s". Use getters and setters.' ), $key ), '1.0' );

		return true;
	}

	/**
	 * Get Meta Data by Key.
	 *
	 *
	 * @param  string $key     Meta Key.
	 * @param  bool   $single  return first found meta with key, or all with $key.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = true, $context = 'view' ) {
		if ( $this->is_internal_meta_key( $key ) ) {
			$function = 'get_' . $key;

			if ( is_callable( array( $this, $function ) ) ) {
				return $this->{$function}();
			}
		}

		$this->maybe_read_meta_data();
		$meta_data  = $this->get_meta_data();
		$array_keys = array_keys( wp_list_pluck( $meta_data, 'key' ), $key );
		$value      = $single ? '' : array();

		if ( ! empty( $array_keys ) ) {
			// We don't use the $this->meta_data property directly here because we don't want meta with a null value (i.e. meta which has been deleted via $this->delete_meta_data()).
			if ( $single ) {
				$value = $meta_data[ current( $array_keys ) ]->value;
			} else {
				$value = array_intersect_key( $meta_data, array_flip( $array_keys ) );
			}

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $key, $value, $this );
			}
		}

		return $value;
	}

	/**
	 * See if meta data exists, since get_meta always returns a '' or array().
	 *
	 *
	 * @param  string $key Meta Key.
	 *
	 * @return boolean
	 */
	public function meta_exists( $key = '' ) {
		$this->maybe_read_meta_data();
		$array_keys = wp_list_pluck( $this->get_meta_data(), 'key' );

		return in_array( $key, $array_keys );
	}

	/**
	 * Set all meta data from array.
	 *
	 * @since 2.6.0
	 *
	 * @param array $data Key/Value pairs.
	 */
	public function set_meta_data( $data ) {
		if ( ! empty( $data ) && is_array( $data ) ) {
			$this->maybe_read_meta_data();
			foreach ( $data as $meta ) {
				$meta = (array) $meta;
				if ( isset( $meta[ 'key' ], $meta[ 'value' ], $meta[ 'id' ] ) ) {
					$this->meta_data[] = new WP_MetaData( array(
						'id'    => $meta[ 'id' ],
						'key'   => $meta[ 'key' ],
						'value' => $meta[ 'value' ],
					) );
				}
			}
		}
	}

	/**
	 * Add meta data.
	 *
	 * @param string $key    Meta key.
	 * @param string $value  Meta value.
	 * @param bool   $unique Should this be a unique key?.
	 *
	 */
	public function add_meta_data( $key, $value, $unique = false ) {
		if ( $this->is_internal_meta_key( $key ) ) {
			$function = 'set_' . $key;

			if ( is_callable( array( $this, $function ) ) ) {
				$this->{$function}( $value );

				return;
			}
		}

		$this->maybe_read_meta_data();
		if ( $unique ) {
			$this->delete_meta_data( $key );
		}
		$this->meta_data[] = new WP_MetaData( array(
			'key'   => $key,
			'value' => $value,
		) );
	}

	/**
	 * Update meta data by key or ID, if provided.
	 *
	 *
	 * @param  string $key     Meta key.
	 * @param  string $value   Meta value.
	 * @param  int    $meta_id Meta ID.
	 *
	 */
	public function update_meta_data( $key, $value, $meta_id = 0 ) {
		if ( $this->is_internal_meta_key( $key ) ) {
			$function = 'set_' . $key;

			if ( is_callable( array( $this, $function ) ) ) {
				$this->{$function}( $value );

				return;
			}
		}

		$this->maybe_read_meta_data();

		$array_key = $meta_id ? array_keys( wp_list_pluck( $this->meta_data, 'id' ), $meta_id ) : '';

		if ( $array_key ) {
			$meta        = $this->meta_data[ current( $array_key ) ];
			$meta->key   = $key;
			$meta->value = $value;
		} else {
			$this->add_meta_data( $key, $value, true );
		}
	}

	/**
	 * Delete meta data.
	 *
	 * @since 2.6.0
	 *
	 * @param string $key Meta key.
	 */
	public function delete_meta_data( $key ) {
		$this->maybe_read_meta_data();
		$array_keys = array_keys( wp_list_pluck( $this->meta_data, 'key' ), $key );

		if ( $array_keys ) {
			foreach ( $array_keys as $array_key ) {
				$this->meta_data[ $array_key ]->value = null;
			}
		}
	}

	/**
	 * Delete meta data.
	 *
	 * @since 2.6.0
	 *
	 * @param int $mid Meta ID.
	 */
	public function delete_meta_data_by_mid( $mid ) {
		$this->maybe_read_meta_data();
		$array_keys = array_keys( wp_list_pluck( $this->meta_data, 'id' ), $mid );

		if ( $array_keys ) {
			foreach ( $array_keys as $array_key ) {
				$this->meta_data[ $array_key ]->value = null;
			}
		}
	}

	/**
	 * Read meta data if null.
	 *
	 */
	protected function maybe_read_meta_data() {
		if ( is_null( $this->meta_data ) ) {
			$this->read_meta_data();
		}
	}

	/**
	 * Read Meta Data from the database. Ignore any internal properties.
	 * Uses it's own caches because get_metadata does not provide meta_ids.
	 *
	 * @since 2.6.0
	 *
	 * @param bool $force_read True to force a new DB read (and update cache).
	 */
	public function read_meta_data( $force_read = false ) {
		$this->meta_data = array();
		$cache_key       = '';
		$cached_meta     = array();

		if ( ! $this->get_id() ) {
			return;
		}

		if ( ! $this->data_store ) {
			return;
		}

		if ( ! empty( $this->cache_group ) ) {
			// Prefix by group allows invalidation by group until https://core.trac.wordpress.org/ticket/4476 is implemented.
			$cache_key = Cache_Helper::get_cache_prefix( $this->cache_group ) . Cache_Helper::get_cache_prefix( 'object_' . $this->get_id() ) . 'object_meta_' . $this->get_id();
		}

		if ( ! $force_read && ! empty( $cache_key ) ) {
			$cached_meta = wp_cache_get( $cache_key, $this->cache_group );
		}

		$raw_meta_data = ! empty( $cached_meta ) ? $cached_meta : $this->data_store->read_meta( $this );
		if ( $raw_meta_data ) {
			foreach ( $raw_meta_data as $meta ) {
				$this->meta_data[] = new WP_MetaData( array(
					'id'    => (int) $meta->meta_id,
					'key'   => $meta->meta_key,
					'value' => maybe_unserialize( $meta->meta_value ),
				) );
			}

			if ( empty( $cached_meta ) && ! empty( $cache_key ) ) {
				wp_cache_set( $cache_key, $raw_meta_data, $this->cache_group );
			}
		}
	}

	/**
	 * Update Meta Data in the database.
	 *
	 * @since 2.6.0
	 */
	public function save_meta_data() {
		if ( ! $this->data_store || is_null( $this->meta_data ) ) {
			return;
		}
		foreach ( $this->meta_data as $array_key => $meta ) {
			if ( is_null( $meta->value ) ) {
				if ( ! empty( $meta->id ) ) {
					$this->data_store->delete_meta( $this, $meta );
					unset( $this->meta_data[ $array_key ] );
				}
			} elseif ( empty( $meta->id ) ) {
				$meta->id = $this->data_store->add_meta( $this, $meta );
				$meta->apply_changes();
			} elseif ( $meta->get_changes() ) {
				$this->data_store->update_meta( $this, $meta );
				$meta->apply_changes();
			}
		}
		if ( ! empty( $this->cache_group ) ) {
			$cache_key = Cache_Helper::get_cache_prefix( $this->cache_group ) . Cache_Helper::get_cache_prefix( 'object_' . $this->get_id() ) . 'object_meta_' . $this->get_id();
			wp_cache_delete( $cache_key, $this->cache_group );
		}
	}

	/**
	 * Set ID.
	 *
	 *
	 * @param int $id ID.
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set all props to default values.
	 *
	 */
	public function set_defaults() {
		$this->data    = $this->default_data;
		$this->changes = array();
		$this->set_object_read( false );
	}

	/**
	 * Set object read property.
	 *
	 *
	 * @param boolean $read Should read?.
	 */
	public function set_object_read( $read = true ) {
		$this->object_read = (bool) $read;
	}

	/**
	 * Get object read property.
	 *
	 * @return boolean
	 */
	public function get_object_read() {
		return (bool) $this->object_read;
	}

	/**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 *
	 * @param array $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 *
	 * @return bool|WP_Error
	 */
	public function set_props( $props ) {
		$errors = new WP_Error();

		foreach ( $props as $prop => $value ) {
			try {
				if ( 'meta_data' === $prop ) {
					continue;
				}
				$setter = "set_$prop";
				if ( ! is_null( $value ) && is_callable( array( $this, $setter ) ) ) {
					$reflection = new ReflectionMethod( $this, $setter );

					if ( $reflection->isPublic() ) {
						$this->{$setter}( $value );
					}
				}
			} catch ( Exception $e ) {
				$errors->add( $e->getCode(), $e->getMessage() );
			}
		}

		return count( $errors->get_error_codes() ) ? $errors : true;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * This stores changes in a special array so we can track what needs saving
	 * the the DB later.
	 *
	 *
	 * @param string $prop  Name of prop to set.
	 * @param mixed  $value Value of the prop.
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->data ) ) {
			if ( true === $this->object_read ) {
				if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
					$this->changes[ $prop ] = $value;
				}
			} else {
				$this->data[ $prop ] = $value;
			}
		}
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Merge changes with data and clear.
	 */
	public function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes );
		$this->changes = array();
	}

	/**
	 * Prefix for action and filter hooks on data.
	 *
	 * @return string
	 */
	protected function get_hook_prefix() {
		return 'wpp_' . $this->object_type . '_get_';
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 *
	 * @param  string $prop    Name of prop to get.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return mixed
	 */
	protected function get_prop( $prop, $context = 'view' ) {
		$value = null;

		if ( array_key_exists( $prop, $this->data ) ) {
			$value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->data[ $prop ];

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this );
			}
		}

		return $value;
	}

	/**
	 * Sets a date prop whilst handling formatting and datetime objects.
	 *
	 *
	 * @param string         $prop  Name of prop to set.
	 * @param string|integer $value Value of the prop.
	 */
	protected function set_date_prop( $prop, $value ) {
		try {
			if ( empty( $value ) ) {
				$this->set_prop( $prop, null );

				return;
			}

			if ( is_a( $value, 'WC_DateTime' ) ) {
				$datetime = $value;
			} elseif ( is_numeric( $value ) ) {
				// Timestamps are handled as UTC timestamps in all cases.
				$datetime = new WP_DateTime( "@{$value}", new DateTimeZone( 'UTC' ) );
			} else {
				// Strings are defined in local WP timezone. Convert to UTC.
				if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
					$offset    = ! empty( $date_bits[ 7 ] ) ? iso8601_timezone_to_offset( $date_bits[ 7 ] ) : wpp_timezone_offset();
					$timestamp = gmmktime( $date_bits[ 4 ], $date_bits[ 5 ], $date_bits[ 6 ], $date_bits[ 2 ], $date_bits[ 3 ], $date_bits[ 1 ] ) - $offset;
				} else {
					$timestamp = wpp_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wpp_string_to_timestamp( $value ) ) ) );
				}
				$datetime = new WP_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );
			}

			// Set local timezone or offset.
			if ( get_option( 'timezone_string' ) ) {
				$datetime->setTimezone( new DateTimeZone( wp_timezone_string() ) );
			} else {
				$datetime->set_utc_offset( wpp_timezone_offset() );
			}

			$this->set_prop( $prop, $datetime );
		} catch ( Exception $e ) {
		}
	}
}