<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Tofandel\Core\Objects;

defined( 'ABSPATH' ) || exit;

use JsonSerializable;

/**
 * Wraps an array (meta data for now) and tells if there was any changes.
 *
 * The main idea behind this class is to avoid doing unneeded
 * SQL updates if nothing changed.
 *
 * @property int $id
 * @property mixed $value
 *
 */
class WP_MetaData implements JsonSerializable {

	/**
	 * Current data for metadata
	 *
	 * @var array
	 */
	protected $current_data;

	/**
	 * Metadata data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @param array $meta Data to wrap behind this function.
	 */
	public function __construct( $meta = array() ) {
		$this->current_data = $meta;
		$this->apply_changes();
	}

	/**
	 * When converted to JSON.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->get_data();
	}

	/**
	 * Merge changes with data and clear.
	 */
	public function apply_changes() {
		$this->data = $this->current_data;
	}

	/**
	 * Creates or updates a property in the metadata object.
	 *
	 * @param string $key Key to set.
	 * @param mixed $value Value to set.
	 */
	public function __set( $key, $value ) {
		$this->current_data[ $key ] = $value;
	}

	/**
	 * Checks if a given key exists in our data. This is called internally
	 * by `empty` and `isset`.
	 *
	 * @param string $key Key to check if set.
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return array_key_exists( $key, $this->current_data );
	}

	/**
	 * Returns the value of any property.
	 *
	 * @param string $key Key to get.
	 *
	 * @return mixed Property value or NULL if it does not exists
	 */
	public function __get( $key ) {
		if ( array_key_exists( $key, $this->current_data ) ) {
			return $this->current_data[ $key ];
		}

		return null;
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 */
	public function get_changes() {
		$changes = array();
		foreach ( $this->current_data as $id => $value ) {
			if ( ! array_key_exists( $id, $this->data ) || $value !== $this->data[ $id ] ) {
				$changes[ $id ] = $value;
			}
		}

		return $changes;
	}

	/**
	 * Return all data as an array.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}
}
