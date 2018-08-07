<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 22/05/2018
 * Time: 16:26
 */

namespace Tofandel\Core\Interfaces;

use Tofandel\Core\Modules\ReduxFramework;


/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 * A text domain constant will be defined as SHORTCLASSNAME_TD
 */
interface WP_Plugin {
	/**
	 * @param SubModule $module
	 */
	public function setSubModule( $module );

	/**
	 * @param SubModule[] $modules
	 */
	public function setSubModules( array $modules );

	/**
	 * @param string $shortcode
	 */
	public function setShortcode( $shortcode );

	/**
	 * @param array $shortcodes
	 */
	public function setShortcodes( array $shortcodes );

	/**
	 * Add the tables and settings and any plugin variable specifics here
	 *
	 * @return void
	 */
	public function definitions();

	/**
	 * Add actions and filters here
	 */
	public function actionsAndFilters();

	/**
	 * Searchs if a file exists in the plugin folder (minified or not)
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool $cache
	 * @param string|false $folder
	 *
	 * @return string
	 */
	public function searchFile( $name, $type = '', $cache = false, $folder = false );

	/**
	 * @param string $folder
	 *
	 * @return string Path to the plugin's folder
	 */
	public function folder( $folder = '' );

	/**
	 * @param string $file
	 *
	 * @return string Web url to the file
	 */
	public function fileUrl( $file = '' );

	/**
	 * @param string $folder
	 *
	 * @return string Web url to the folder
	 */
	public function dirUrl( $folder = '' );

	/**
	 * @param string $js Filename (optional extension)
	 * @param array $require
	 * @param bool $localize
	 * @param bool $in_footer
	 *
	 * @return string
	 */
	public function addScript( $js, $require = array(), $localize = false, $in_footer = false );

	public function registerScript( $js, $require = array(), $localize = false, $in_footer = false );

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return string
	 */
	public function addStyle( $css, $media = 'all' );

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return string
	 */
	public function registerStyle( $css, $media = 'all' );

	/**
	 * Prepare plugin internationalisation
	 */
	public function loadTextdomain();

	/**
	 * Returns the version of the plugin
	 * @return string
	 */
	public function getVersion();

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getSlug();

	/**
	 * @return string
	 */
	public function getDownloadUrl();

	/**
	 * @return string
	 */
	public function getLicenceEmail();

	/**
	 * @return string
	 */
	public function getLicenceKey();

	/**
	 * @return string
	 */
	public function getReduxOptName();

	/**
	 * Called function on plugin activation
	 */
	public function activated();

	public function mkdir( $folder );

	/**
	 * @param ReduxFramework $framework
	 *
	 * Add menus, sub-menus and settings page in this function
	 *
	 * @see https://docs.reduxframework.com/core/redux-api/ For a complete documentation on how to use redux framework
	 */
	public function reduxInit( ReduxFramework $framework );

	/**
	 * Called function on plugin deactivation
	 * Options and plugin data should only be removed in the uninstall function
	 */
	public function deactivated();

	public function getProductID();

	public function getBuyUrl();
}