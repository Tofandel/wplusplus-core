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
	public static function mapShortcode();

	public static function setInfo( $name, $description = '', $category = '', $icon = '' );

	public static function setParam( ShortcodeParameter $param );
}