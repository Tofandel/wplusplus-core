<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Interfaces;


/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
interface WP_Theme extends WP_Plugin {
	public function dirUrl( $folder = '' );

	/**
	 * Prepare theme internationalisation
	 */
	public function loadTextdomain();

}