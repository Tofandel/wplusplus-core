<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
		static::__StaticInit__();
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