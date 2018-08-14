<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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