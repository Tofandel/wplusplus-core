<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Interfaces;

/**
 * Interface WP_Shortcode
 * @package Tofandel\Core\Interfaces
 */
interface WP_Shortcode {
	/**
	 * The shortcode initialisation function
	 */
	public static function __StaticInit();

	/**
	 * The shortcode logic function
	 *
	 * @param array $atts
	 * @param string $content
	 * @param string $name
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name );

	/**
	 * @return string
	 */
	public static function getName();
}