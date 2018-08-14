<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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