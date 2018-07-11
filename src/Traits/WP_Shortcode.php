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

		static::$_name = strtolower( static::$reflectionClass->getShortName() );

		new \Tofandel\Core\Objects\WP_Shortcode( static::$_name, [ static::class, 'shortcode' ], static::$atts );
		//add_shortcode( self::$_name, [ self::class, 'do_shortcode' ] );
	}

	/**
	 * @param array $attributes
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	abstract public static function shortcode( $attributes, $content, $name );

	/**
	 * @return mixed
	 */
	public function getName() {
		return static::$_name;
	}

}