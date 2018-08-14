<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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