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
interface StaticSubModule {
	/**
	 * SubModule constructor.
	 *
	 * @param WP_Plugin|null $parent
	 */
	public static function SubModuleInit( WP_Plugin &$parent = null );

	/**
	 * @return string
	 */
	public static function getTextDomain();

	/**
	 * Called function on plugin activation
	 */
	public static function activated();

	/**
	 * Called function on plugin deactivation
	 */
	public static function deactivated();

	/**
	 * The hooks of the submodule
	 */
	public static function actionsAndFilters();

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public static function upgrade( $last_version );
}