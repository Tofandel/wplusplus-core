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

global $initializables;

$initializables = array();

trait Initializable {
	/**
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass;

	/**
	 * Returns the initialized plugin.
	 *
	 * @return static
	 * @throws \ReflectionException
	 */
	public static function __init__() {
		global $initializables;

		$class                   = new \ReflectionClass( static::class );
		static::$reflectionClass = $class;
		if ( ! isset( $initializables [ $class->getName() ] ) ) {
			$instance = $class->newInstanceWithoutConstructor();
			if ( method_exists( $instance, '__init' ) ) {
				$instance->__init();
			}
			if ( method_exists( $instance, 'init' ) ) {
				$instance->init();
			}
			$initializables[ $class->getName() ] = $class;
		}

		return $initializables[ $class->getName() ];
	}

	/**
	 * @return array
	 */
	public static function getInitializables() {
		global $initializables;

		return $initializables;
	}

	abstract protected function __init();

}