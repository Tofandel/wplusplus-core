<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

use Tofandel\Core\Interfaces\ShortcodeMapper;
use Tofandel\Core\Interfaces\WP_Plugin;
use Tofandel\Core\Objects\ShortcodeParameter;
use Tofandel\Core\ShortcodeMappers\VC_Mapper;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author  Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Mapped_Shortcode {
	use WP_Shortcode {
		SubModuleInit as ParentSubModuleInit;
	}

	//Define this
	public static $atts = array();

	//Don't touch
	protected static $_info;

	protected static $last_param;

	/**
	 * @var ShortcodeMapper[]
	 */
	protected static $mappers = array();

	public static function setInfo( $name, $description = '', $category = '', $icon = '' ) {
		self::$_info              = new \stdClass();
		self::$_info->name        = $name;
		self::$_info->description = $description;
		self::$_info->category    = $category;
		self::$_info->icon        = $icon;
	}

	public static function setParam( ShortcodeParameter $param ) {
		static::$_info->params[ $param->getName() ] = $param;
	}

	/**
	 * Function where you define all the parameters with the given functions in this class
	 */
	abstract public static function mapping();

	/**
	 * @return ShortcodeMapper[]
	 */
	public static function initMappers() {
		static $mappers;

		if ( ! isset( $mappers ) ) {
			self::$mappers = apply_filters( 'wpp_shortcode_mappers', array( VC_Mapper::class ) );
			foreach ( self::$mappers as $mapper ) {
				if ( $mapper::shouldMap() ) {
					$mappers[] = $mapper;
				}
			}
		}

		return $mappers;
	}


	public static function SubModuleInit( WP_Plugin &$parent = null ) {
		self::ParentSubModuleInit( $parent );

		if ( $mappers = self::initMappers() ) {
			ShortcodeParameter::setDefaultAttributes( static::$atts );
			self::mapping();
			foreach ( $mappers as $mapper ) {
				$mapper::map( self::$_info );
			}
		}

		new \Tofandel\Core\Objects\WP_Shortcode( static::getName(), [
			static::class,
			'shortcode'
		], static::$atts );
	}

	/**
	 * @param array  $attributes
	 * @param string $content
	 * @param string $name of the shortcode
	 *
	 * @return string
	 */
	//abstract public function shortcode( $attributes, $content, $name );

}