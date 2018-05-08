<?php

namespace Tofandel\Traits;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Shortcode {
	use Initializable;

	protected static $atts;
	private static $_name;

	/**
	 * WP_Shortcode constructor.
	 *
	 * @throws \ReflectionException
	 */
	public static function init() {
		$class       = new \ReflectionClass( static::class );
		self::$_name = strtolower( $class->getShortName() );

		new \Instances\WP_Shortcode( self::$_name, [ self::class, 'shortcode' ], self::$atts );
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
		return self::$_name;
	}

}