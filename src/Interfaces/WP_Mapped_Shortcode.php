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

/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 12/08/2018
 * Time: 16:19
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