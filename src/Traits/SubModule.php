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
	/**
	 * @var \Tofandel\Core\Objects\WP_Plugin
	 */
	protected $parent;

	public function __construct( WP_Plugin &$parent = null ) {
		$this->parent = $parent;
	}

	/**
	 * @return string
	 */
	public function getTextDomain() {
		return $this->parent->getTextDomain();
	}

	/**
	 * Called function on plugin activation
	 */
	public function activated() {

	}

	/**
	 * Called function on plugin deactivation
	 */
	public function deactivated() {

	}

	/**
	 * The hooks of the submodule
	 */
	public function actionsAndFilters() {

	}

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public function upgrade( $last_version ) {

	}
}