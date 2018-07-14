<?php

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