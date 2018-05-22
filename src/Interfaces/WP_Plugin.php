<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 22/05/2018
 * Time: 16:26
 */

namespace Tofandel\Interfaces;


/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 * A text domain constant will be defined as SHORTCLASSNAME_TD
 */
interface WP_Plugin {
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

	public function optionGroup( $option_group );

	public function pluginName();

	/**
	 * @param string $page_title The page title
	 * @param callable $function The function to display the page
	 * @param string $capability The capability required to see the page
	 * @param string $icon_url Can be a Dashicon helper class, a Base64 encoded SVG or 'none' if style is added via CSS
	 * @param null $position The position the menu should appear.
	 * @param array $stylesheets An array of css filenames to be included on that page ('.css' or '.min.css' are not necessary) must be in plugin's css folder
	 * @param array $javascripts An array of js filenames to be included on that page ('.js' or '.min.js' are not necessary) must be in plugin's js folder
	 *
	 * @return string Menu's hook
	 */
	public function addMenuPage( $page_title, $function, $capability = 'manage_options', $icon_url = '', $position = null, array $stylesheets = array(), array $javascripts = array() );

	/**
	 * @param string $parent_slug
	 * @param string $page_title
	 * @param callable $function
	 * @param string $capability
	 * @param array $stylesheets
	 * @param array $javascripts
	 *
	 * @return string Menu's hook
	 */
	public function addSubmenuPage( $parent_slug, $page_title, $function, $capability = 'manage_options', array $stylesheets = array(), array $javascripts = array() );

	public function enqueueMenuScripts();

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

	public function enqueueMenuStyles();

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

	public function webPath( $folder = '' );

	/**
	 * Prepare plugin internationalisation
	 */
	public function loadTextdomain();

	/**
	 * Returns the text domain for internationalisation
	 * @return string
	 */
	public function textDomain();

	/**
	 * Returns the list of version information for the plugin
	 */
	public function getVersion();

	public function checkCompat();

	public function disabled_notice();

	/**
	 * Called function on plugin activation
	 */
	public function activate();

	public function mkdir( $folder );

	/**
	 * Add menus, sub-menus and settings page in this function
	 */
	public function menusAndSettings();

	/**
	 * Called function on plugin deactivation
	 * Options and plugin data should only be removed in the uninstall function
	 */
	public function deactivate();
}