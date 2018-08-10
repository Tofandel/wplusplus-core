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
 * Date: 22/05/2018
 * Time: 16:26
 */

namespace Tofandel\Core\Interfaces;

/**
 * Interface SubModule
 * @package Tofandel\Core\Interfaces
 */
interface SubModule {
	/**
	 * SubModule constructor.
	 *
	 * @param WP_Plugin|null $parent
	 */
	public function __construct( WP_Plugin &$parent = null );

	/**
	 * @return string
	 */
	public function getTextDomain();

	/**
	 * Called function on plugin activation
	 */
	public function activated();

	/**
	 * Called function on plugin deactivation
	 */
	public function deactivated();

	/**
	 * The hooks of the submodule
	 */
	public function actionsAndFilters();

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public function upgrade( $last_version );
}