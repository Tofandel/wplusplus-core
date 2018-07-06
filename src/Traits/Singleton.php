<?php
/**
 * Adrien Foulon <tofandel@tukan.hu>
 * Copyright © 2018 - All Rights Reserved
 */

namespace Tofandel\Core\Traits;

global $singletons;

$singletons = array();

trait Singleton {

	/**
	 * Returns the singleton instanced plugin.
	 *
	 * @return object
	 *
	 * @throws \ReflectionException
	 */
	public static final function __init__() {
		global $singletons;

		$class = new \ReflectionClass( static::class );
		if ( ! isset( $singletons[ $class->getName() ] ) ) {
			//$singletons[ $class->getName() ] = $class->newInstanceWithoutConstructor();
			$singletons[ $class->getName() ] = $class->newInstance();
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