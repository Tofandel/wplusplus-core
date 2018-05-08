<?php
/**
 * Adrien Foulon <tofandel@tukan.hu>
 * Copyright © 2018 - All Rights Reserved
 */

namespace Tofandel\Traits;

global $singletons;

$singletons = array();

trait Singleton {

	/**
	 * Returns the singleton instanced plugin.
	 *
	 * @return object
	 */
	public static final function __init__() {
		global $singletons;
		$class = get_called_class();
		if ( ! array_key_exists( $class, $singletons ) ) {
			$singletons[ $class ] = new $class();
		}

		return $singletons[ $class ];
	}

	/**
	 * @return array
	 */
	public static final function getSingletons() {
		global $singletons;

		return $singletons;
	}

}