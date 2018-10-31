<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

global $singletons, $singletons_hierarchy;

$singletons           = array();
$singletons_hierarchy = array();

/**
 * Trait Singleton
 * @package Tofandel\Core\Traits
 */
trait Singleton {
	/**
	 * Returns the singleton instanced class.
	 *
	 * @return object
	 *
	 */
	public static final function __StaticInit() {
		global $singletons, $singletons_hierarchy;

		try {
			$class = new \ReflectionClass( static::class );
		} catch ( \ReflectionException $e ) {
			die( $e->getMessage() );
		}
		if ( ! isset( $singletons[ $class->getName() ] ) ) {
			//$singletons[ $class->getName() ] = $class->newInstanceWithoutConstructor();
			$instance = $class->newInstance();
			if ( method_exists( $instance, '__init' ) ) {
				$instance->__init();
			}
			//if ( method_exists( $instance, 'init' ) ) {
			//	$instance->init();
			//}

			$parent = $class->getParentClass();
			if ( $parent ) {
				$singletons_hierarchy[ $parent->getName() ][ $class->getName() ] = &$instance;
				while ( $parent = $parent->getParentClass() ) {
					$singletons_hierarchy[ $parent->getName() ][ $class->getName() ] = &$instance;
				}
			}

			$singletons_hierarchy[ $class->getName() ] = &$instance;

			$singletons[ $class->getName() ] = &$instance;
		}

		return $singletons[ $class->getName() ];
	}

	public static final function InitFromConstructor( $that ) {
		global $singletons, $singletons_hierarchy;

		try {
			$class = new \ReflectionClass( $that );
		} catch ( \ReflectionException $e ) {
			die( $e->getMessage() );
		}
		if ( ! isset( $singletons[ $class->getName() ] ) ) {
			//$singletons[ $class->getName() ] = $class->newInstanceWithoutConstructor();
			if ( method_exists( $that, '__init' ) ) {
				$that->__init();
			}
			//if ( method_exists( $instance, 'init' ) ) {
			//	$instance->init();
			//}
			$parent = $class->getParentClass();
			if ( $parent ) {
				$singletons_hierarchy[ $parent->getName() ][ $class->getName() ] = &$that;
				while ( $parent = $parent->getParentClass() ) {
					$singletons_hierarchy[ $parent->getName() ][ $class->getName() ] = &$that;
				}
			}
			$singletons_hierarchy[ $class->getName() ] = &$that;
			$singletons[ $class->getName() ]           = &$that;
		}
	}

	/**
	 * @param bool|string $class
	 *
	 * @return array
	 */
	public static final function getSingletons( $class = false ) {
		if ( ! empty( $class ) ) {
			global $singletons_hierarchy;

			return $singletons_hierarchy[ $class ];
		} else {
			global $singletons;

			return $singletons;
		}
	}
}