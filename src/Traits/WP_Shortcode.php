<?php
/**
 * Copyright (c) 2018.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Tofandel\Core\Traits;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Shortcode {
	use Initializable {
		__init__ as __baseInit__;
	}

	//protected static $atts = array();
	protected static $_name;

	/**
	 * WP_Shortcode constructor.
	 */
	public static function __init__() {
		self::__baseInit__();

		if ( ! static::$reflectionClass->implementsInterface( \Tofandel\Core\Interfaces\WP_Shortcode::class ) ) {
			return;
		}

		static::getName();

		new \Tofandel\Core\Objects\WP_Shortcode( static::$_name, [ static::class, 'shortcode' ], static::$atts );
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