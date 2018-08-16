<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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
			$static_initializables[ $class->getName() ] = $class;
			if ( is_callable( [ static::class, '__StaticInit' ] ) ) {
				static::__StaticInit();
			}
		}
	}
}