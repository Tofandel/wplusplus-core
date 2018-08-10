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
 * Date: 04/07/2018
 * Time: 15:46
 */

namespace Tofandel\Core\Interfaces;


/**
 * Class ReduxConfig
 * A Proxy Class to configure Redux more easily
 *
 * @package Tofandel\Core\Interfaces
 */
interface ReduxConfig {
	/**
	 * ReduxConfig constructor.
	 *
	 * @param $opt_name
	 * @param null $args
	 */
	public function __construct( $opt_name, $args = null );

	/**
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function setArgs( $args = array() );

	/**
	 * @param array $field
	 *
	 * @return mixed
	 */
	public function setField( $field = array() );

	/**
	 * @param array $tab
	 *
	 * @return mixed
	 */
	public function setHelpTab( $tab = array() );

	/**
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function setHelpSidebar( $content = "" );

	/**
	 * @param string $key
	 * @param string $option
	 *
	 * @return mixed
	 */
	public function setOption( $key = "", $option = "" );

	/**
	 * @param array $sections
	 *
	 * @return mixed
	 */
	public function setSections( $sections = array() );

	/**
	 * @param array $section
	 *
	 * @return mixed
	 */
	public function setSection( $section = array() );
}