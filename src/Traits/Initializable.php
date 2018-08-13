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

global $static_initializables;

$static_initializables = array();

trait Initializable {
	/**
	 * @var \ReflectionClass
	 */
	protected static $reflectionClass;

	/**
	 * Do the child initialisation in this
	 */
	abstract static function __StaticInit();

	/**
	 * Initializes a class
	 *
	 * @throws \ReflectionException
	 */
	public final static function __StaticInit__() {
		global $static_initializables;

		$class                   = new \ReflectionClass( static::class );
		static::$reflectionClass = $class;
		if ( ! isset( $static_initializables [ $class->getName() ] ) ) {
			if ( is_callable( [ static::class, '__StaticInit' ] ) ) {
				static::__StaticInit();
			}
			$static_initializables[ $class->getName() ] = $class;
		}
	}
}