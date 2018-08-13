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

global $singletons;

$singletons = array();

trait Singleton {
	/**
	 * Returns the singleton instanced class.
	 *
	 * @return object
	 *
	 * @throws \ReflectionException
	 */
	public static final function __StaticInit() {
		global $singletons;

		$class = new \ReflectionClass( static::class );
		if ( ! isset( $singletons[ $class->getName() ] ) ) {
			//$singletons[ $class->getName() ] = $class->newInstanceWithoutConstructor();
			$instance = $class->newInstance();
			if ( method_exists( $instance, '__init' ) ) {
				$instance->__init();
			}
			//if ( method_exists( $instance, 'init' ) ) {
			//	$instance->init();
			//}
			$singletons[ $class->getName() ] = $instance;
		}

		return $singletons[ $class->getName() ];
	}

	/**
	 * @return array
	 */
	public static final function getSingletons() {
		global $singletons;

		return $singletons;
	}
}