<?php
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