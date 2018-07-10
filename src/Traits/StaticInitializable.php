<?php

namespace Tofandel\Core\Traits;

trait StaticInitializable {
	/**
	 * Initializes a class
	 *
	 * @return object
	 * @throws \ReflectionException
	 */
	abstract public static function __init__();
}