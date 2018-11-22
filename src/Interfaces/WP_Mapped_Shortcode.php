<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Interfaces;

use Tofandel\Core\Objects\ShortcodeParameter;


/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
interface WP_Mapped_Shortcode extends WP_Shortcode {
	/**
	 * Function where you define all the parameters with the given functions in this class
	 */
	public static function mapping();

	public static function setInfo( $name, $description = '', $category = '', $icon = '' );

	/**
	 * @param string $name
	 * @param string $title
	 * @param string $type ShortcodeParametersTypes::const
	 * @param string $description
	 * @param string $category
	 *
	 * @return ShortcodeParameter
	 */
	public static function addParameter( $name, $title, $type, $description = '', $category = '' );
}