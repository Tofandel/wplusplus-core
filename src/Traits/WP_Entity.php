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

namespace Tofandel\Core\Traits;

/**
 * Class WP_Entity
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 * @property int $ID
 */
trait WP_Entity {
	protected $_meta = array();
	/**
	 * @var int $ID
	 */
	private $ID = null;
	protected $_object_type = null;

	public function isNew() {
		return empty( $this->ID );
	}

	public function set_transient( $transient, $time = 3600, $val = null ) {
		if ( isset( $val ) ) {
			$this->setOverride( $transient, $val );
		}
		set_object_transient( $this->_object_type, $this->ID, $transient, $this->get( 'transient' ), $time );
	}


	public function get_transient( $transient ) {
		$val = get_object_transient( $this->_object_type, $this->ID, $transient );
		if ( $val !== false ) {
			$this->setOverride( $transient, $val );
		}

		return $this->get( $transient );
	}

	public function delete_transient( $transient ) {
		delete_object_transient( $this->_object_type, $this->ID, $transient );
	}

	/**
	 * @param bool $set
	 *
	 * @return bool
	 */
	public function isModified( $set = true ) {
		if ( ! $set ) {
			$this->_meta = array();

			return false;
		} else {
			return ! empty( $this->_meta );
		}
	}

	/**
	 * @return array
	 */
	public function getNewData() {
		return $this->_meta;
	}

	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getEntityId( $key = '' ) {
		return str_replace( '\\', '-', get_class( $this ) ) . '-' . $this->ID . ( ! empty( $key ) ? '_' . $key : '' );
	}

	/**
	 * @param $between
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getEntityName( $between = '-', $key = '' ) {
		$c = explode( '\\', get_class( $this ) );

		return array_pop( $c ) . $between . ( ! empty( $this->name ) ? $this->name : $this->ID ) . ( ! empty( $key ) ? '_' . $key : '' );
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		return $this->get( $name );
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 * @return bool
	 */
	public function __set( $name, $value ) {
		return $this->set( $name, $value );
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		if ( func_num_args() > 1 ) {
			if ( ( $val = call_user_func_array( [
					$this,
					'getArray'
				], array( '_meta' ) + func_get_args() ) ) !== null ) {
				return $val;
			}

			return call_user_func_array( [ $this, 'getArray' ], func_get_args() );
		}


		if ( isset( $this->_meta[ $name ] ) ) {
			return $this->_meta[ $name ];
		}

		return isset( $this->{$name} ) ? $this->{$name} : null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function set( $name, $value ) {
		if ( func_num_args() > 2 ) {
			return call_user_func_array( [ $this, 'setArray' ], func_get_args() );
		} else {

			if ( $this->{$name} != $value ) {
				$this->_meta[ $name ] = $value;

				return true;

			} elseif ( isset( $this->_meta[ $name ] ) ) {
				unset( $this->_meta[ $name ] );
			}

			return false;
		}
	}

	public function __clone() {
		$this->ID = null;
		$this->applyModifications();
	}

	public function applyModifications() {
		foreach ( $this->_meta as $key => $data ) {
			$this->{$key} = $data;
		}
		$this->_meta = array();
	}

	public function __isset( $name ) {
		return isset( $this->$name ) || isset( $this->_meta[ $name ] );
	}

	public function exists() {
		return ! empty( $this->get( 'id' ) );
	}

	public function setOverride( $name, $val ) {
		$this->{$name} = $val;
	}

	/**
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function setArray() {
		if ( func_num_args() < 2 ) {
			throw new \Exception( 'Not enough arguments for setArray' );
		}
		$ref = &$this->_meta;
		for ( $i = 0; $i < func_num_args() - 2; ++ $i ) {
			$key = func_get_arg( $i );
			if ( isset( $ref->{$key} ) ) {
				$ref = &$ref->$key;
			} elseif ( isset( $ref[ $key ] ) ) {
				$ref = &$ref[ $key ];
			} else {
				$ref[ $key ] = array();
				$ref         = &$ref[ $key ];
			}
		}
		$key   = func_get_arg( func_num_args() - 2 );
		$value = func_get_arg( func_num_args() - 1 );

		if ( is_array( $ref ) ) {
			$ref[ $key ] = $value;

			return true;
		} elseif ( is_object( $ref ) ) {
			$ref->$key = $value;

			return true;
		}

		return false;
	}

	/**
	 * @return mixed|null
	 * @throws \Exception
	 */
	public function getArray() {
		if ( func_num_args() < 1 ) {
			throw new \Exception( 'Not enough arguments for setArray' );
		}
		$ref = &$this;
		for ( $i = 0; $i < func_num_args() - 1; ++ $i ) {
			$key = func_get_arg( $i );
			if ( is_object( $ref ) && isset( $ref->{$key} ) ) {
				$ref = &$ref->$key;
			} elseif ( is_array( $ref ) && isset( $ref[ $key ] ) ) {
				$ref = &$ref[ $key ];
			} else {
				return null;
			}
		}

		$key = func_get_arg( func_num_args() - 1 );
		if ( is_array( $ref ) ) {
			return isset( $ref[ $key ] ) ? $ref[ $key ] : null;
		} elseif ( is_object( $ref ) ) {
			return isset( $ref->$key ) ? $ref->$key : null;
		}

		return null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function add( $name, $value ) {
		if ( func_num_args() > 2 ) {
			return call_user_func_array( [ $this, 'setArray' ], func_get_args() );
		} else {

			if ( is_array( $value ) ) {
				$val = $this->get( $name );
				if ( empty( $val ) || ! is_array( $val ) ) {
					$val = array();
				}
				$this->_meta[ $name ] = array_merge( $val, $value );
				$diff                 = array_diff( $this->_meta[ $name ], $val );
				$diff_key             = array_diff_key( $this->_meta[ $name ], $val );
				if ( empty( $diff ) && empty( $diff_key ) ) {
					unset( $this->_meta[ $name ] );

					return false;
				}

				return true;
			} elseif ( $this->{$name} != $value ) {
				$this->_meta[ $name ] = $this->{$name} + $value;

				return true;
			} elseif ( isset( $this->_meta[ $name ] ) ) {
				unset( $this->_meta[ $name ] );
			}

			return false;
		}
	}
}