<?php

namespace Tofandel\Core\Traits;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Public_Shortcode {
	use Initializable {
		__init__ as __baseInit__;
	}

	protected static $builder_atts = array();
	protected static $atts = array();
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

	public static function setParamBase( $param_name, $type, $label, $values = false ) {
		static::$last_param                            = $param_name;
		static::$builder_atts['params'][ $param_name ] = array(
			'type'       => $type,
			'heading'    => $label,
			'param_name' => $param_name,
			'value'      => $values,
			'std'        => static::$atts[ $param_name ]
		);
	}

	public static function setParamInfo( $param_name = '' ) {
		if ( empty( $param_name ) ) {
			$param_name = static::$last_param;
		}
		if (!isset(static::$atts[$param_name])) {
			return;
		}

	}

	/**
	 * WP_Shortcode constructor.
	 */
	public static function __init__() {
		self::__baseInit__();

		static::$_name = strtolower( static::$reflectionClass->getShortName() );

		foreach ( static::$atts as $att => $def ) {
			if ( method_exists( static::class, $att ) ) {
				static::class::$att( $att, $def );
			}
		}

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