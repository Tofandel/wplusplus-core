<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

use Tofandel\Core\Interfaces\ShortcodeMapper;
use Tofandel\Core\Interfaces\WP_Plugin;
use Tofandel\Core\Objects\ShortcodeDescriptor;
use Tofandel\Core\Objects\ShortcodeParameter;

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
	/**
	 * @var ShortcodeDescriptor
	 */
	protected static $_info;

	protected static $last_param;

	/**
	 * @var ShortcodeMapper[]
	 */
	protected static $mappers = array();

	public static function setInfo( $name, $description = '', $category = '', $icon = '' ) {
		static::$_info = new ShortcodeDescriptor( static::class );
		static::$_info->setInfo( $name, $description, $category, $icon );

		return static::$_info;
	}

	/**
	 * @param string $name
	 * @param string $title
	 * @param string $type ShortcodeParametersTypes::const
	 * @param string $description
	 * @param string $category
	 *
	 * @return ShortcodeParameter
	 */
	public static function addParameter( $name, $title, $type, $description = '', $category = '' ) {
		return static::$_info->addParameter( $name, $title, $type, $description, $category );
	}

	/**
	 * Function where you define all the parameters with the given functions in this class
	 */
	abstract public static function mapping();

	public static function SubModuleInit( WP_Plugin &$parent = null ) {
		self::ParentSubModuleInit( $parent );

		if ( $mappers = ShortcodeDescriptor::initMappers() ) {
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