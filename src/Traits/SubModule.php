<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 26/07/2018
 * Time: 01:58
 */

namespace Tofandel\Core\Traits;

use Tofandel\Core\Interfaces\WP_Plugin;

trait SubModule {
	protected $parent;

	public function __construct( WP_Plugin $parent = null ) {
		$this->parent = $parent;
	}

	/**
	 * Called function on plugin activation
	 */
	public abstract function activated();

	/**
	 * The hooks of the submodule
	 */
	public abstract function actionsAndFilters();

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public abstract function upgrade( $last_version );
}