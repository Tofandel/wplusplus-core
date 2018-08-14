<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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