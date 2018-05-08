<?php

namespace Tofandel\Traits;

global $initializables;

$initializables = array();

trait Initializable {

	/**
	 * Returns the singleton instanced plugin.
	 *
	 * @return object
	 * @throws \ReflectionException
	 */
	public static function __init__() {
		global $initializables;

		$class = new \ReflectionClass( static::class );
		if ( ! array_key_exists( $class->getName(), $initializables ) ) {
			$instance = $class->newInstanceWithoutConstructor();
			$instance->init();
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