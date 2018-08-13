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
 * Date: 10/08/2018
 * Time: 09:10
 */

namespace Tofandel\Core\Interfaces;


/**
 * Class WP_VC_Shortcode
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
interface WP_VC_Shortcode extends WP_Shortcode {
	/**
	 * Init the $vc_params static here
	 */
	public static function initVCParams();

	/**
	 * You can do other things on init here as well
	 */
	public static function __StaticInit();
}