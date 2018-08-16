<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Shortcode {
	use SubModule;
	use Initializable;

	//protected static $atts = array();
	protected static $_name;

	/**
	 * WP_Shortcode constructor.
	 */
	public static function __StaticInit() {
		if ( ! static::$reflectionClass->implementsInterface( \Tofandel\Core\Interfaces\WP_Shortcode::class ) ) {
			return;
		}

		static::getName();

		new \Tofandel\Core\Objects\WP_Shortcode( static::$_name, [
			static::class,
			'shortcode'
		], static::$default_attributes );
	}

	/**
	 * @param array $attributes
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	//abstract public function shortcode( $attributes, $content, $name );

	/**
	 * @throws \ReflectionException
	 */
	public static function getName() {
		if ( ! empty( static::$_name ) ) {
			return static::$_name;
		}
		if ( ! isset( static::$reflectionClass ) ) {
			static::$reflectionClass = new \ReflectionClass( static::class );
		}

		return static::$_name = static::$reflectionClass->getShortName();
	}

	/**
	 * @return array
	 */
	public static function getNames() {
		$name = static::getName();

		return array( $name, strtolower( $name ) );
	}

}