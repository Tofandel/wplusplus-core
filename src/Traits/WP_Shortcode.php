<?php

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

	protected static $atts = array();
	protected static $_name;

	/**
	 * WP_Shortcode constructor.
	 */
	public static function __init__() {
		self::__baseInit__();

		if ( ! static::$reflectionClass->implementsInterface( \Tofandel\Core\Interfaces\WP_Shortcode::class ) ) {
			return;
		}

		static::$_name = static::$reflectionClass->getShortName();

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
	 * @return mixed
	 */
	public function getName() {
		return static::$_name;
	}

}