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
			$instance = $class->newInstance();
			if ( method_exists( $instance, '__init' ) ) {
				$instance->__init();
			}
			if ( method_exists( $instance, 'init' ) ) {
				$instance->init();
			}
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