<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

use Tofandel\Core\Objects\ShortcodeParameter;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_Mapped_Shortcode {
	use WP_Shortcode;
	//Define this
	protected static $default_attributes = array();

	//Don't touch
	protected static $_info = array();
	protected static $_name;
	protected static $_params;

	protected static $last_param;

	protected function __init() {
	}

	public static function setInfo( $name, $description = '', $category = '', $icon = '' ) {
		self::$_info['name'] = $name;
		self::$_info['description'] = $description;
		self::$_info['category'] = $category;
		self::$_info['icon'] = $icon;
	}

	public static function setParam( ShortcodeParameter $param ) {
		static::$_info['params'][ $param->getName() ] = $param;
	}

	abstract public static function mapShortcode();

	/**
	 * WP_Shortcode constructor.
	 */
	public static function __StaticInit() {
		self::__baseInit__();

		//if ( ! static::$reflectionClass->implementsInterface( \Tofandel\Core\Interfaces\WP_Shortcode::class ) ) {
		//	return;
		//}
		global $pagenow;

		if ( ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( ! empty( $_REQUEST['action'] ) && wp_doing_ajax() && strpos( $_REQUEST['action'], 'vc_' ) === 0 ) ) ) {
			ShortcodeParameter::setDefaultAttributes( static::$default_attributes );
			static::mapShortcode();
			/*
			add_action( 'vc_before_mapping', function () {
				vc_map( static::$vc_params );
			} );*/
		}

		new \Tofandel\Core\Objects\WP_Shortcode( static::getName(), [
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
	 *
	 */
	public static function mapToVc() {
	}

	/**
	 *
	 */
	public static function mapToDoc() {

	}

}