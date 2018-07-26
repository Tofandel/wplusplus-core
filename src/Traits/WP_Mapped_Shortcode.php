<?php

namespace Tofandel\Core\Traits;

use Tofandel\Core\Objects\ShortcodeParameter;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Mapped_Shortcode {
	use Initializable {
		__init__ as __baseInit__;
	}

	protected static $builder_atts = array();
	protected static $atts = array();
	protected static $params = array();
	protected static $_name;

	protected static $last_param;

	public static function builder() {

	}

	public static function setBuilderInfo( $name, $description = '', $category = '', $icon = '' ) {
		self::$builder_atts['name']        = $name;
		self::$builder_atts['description'] = $description;
		self::$builder_atts['category']    = $category;
		self::$builder_atts['icon']        = $icon;
	}

	public static function setParam( ShortcodeParameter $param ) {
		$params[ $param->getName() ] = $param;
	}

	/**
	 * WP_Shortcode constructor.
	 */
	public static function __init__() {
		self::__baseInit__();

		if ( ! static::$reflectionClass->implementsInterface( \Tofandel\Core\Interfaces\WP_Shortcode::class ) ) {
			return;
		}

		static::$_name = static::$reflectionClass->getShortName();

		foreach ( static::$atts as $att => $def ) {
			if ( method_exists( static::class, $att ) ) {
				static::class::$att( $att, $def );
			}
		}

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

	public function mapToVc() {
	}

	public function mapToDoc() {

	}
}