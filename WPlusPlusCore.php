<?php

namespace Tofandel;

use Tofandel\Core\Objects\WP_Plugin;

/**
 * Class WPlusPlusCore
 * @package Tofandel
 *
 * Plugin Name: W++ Core
 * Plugin URI: https://github.com/tofandel/wplusplus-core/
 * Description: A powerful wordpress plugin for developers to create forms and so much more!
 * Version: 1.2
 * Author: Adrien Foulon <tofandel@tukan.hu>
 * Author URI: https://tukan.fr/a-propos/#adrien-foulon
 * Text Domain: wpluspluscore
 * Domain Path: /languages/
 * WC tested up to: 4.9
 * Download Url: https://github.com/tofandel/wplusplus-core/
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';

class WPlusPlusCore extends WP_Plugin {
	const MULOADER_DIR = WPMU_PLUGIN_DIR;

	public function actionsAndFilters() {
		//Silence is golden
	}

	public function definitions() {
		global $WPlusPlusCore;

		$WPlusPlusCore = $this;
	}

	public function menusAndSettings() {
		// TODO Redux
	}

	public function upgrade( $last_version ) {
		// Nothing yet
	}

	public function activate() {
		parent::activate();
		//Create the muloader so the core get's loaded before any plugin, thus removing the need to require it in every child plugin
		$perms_dir  = (int) decoct( fileperms( WP_CONTENT_DIR . '/plugins' ) & 0777 ) ?: 0775;
		$perms_file = (int) decoct( fileperms( __FILE__ ) & 0777 ) ?: 0664;

		if ( mkdir( self::MULOADER_DIR, octdec( $perms_dir ), true ) ) {
			chmod( self::MULOADER_DIR, octdec( $perms_dir ) );
			copy( __DIR__ . '/wplusplus-muloader.php.bak', self::MULOADER_DIR . '/wplusplus-muloader.php' );
			chmod( self::MULOADER_DIR . '/wplusplus-muloader.php', octdec( $perms_file ) );
		}
	}

	public function deactivate() {
		//Clean the muloader
		unlink( self::MULOADER_DIR . '/wplusplus-muloader.php' );
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	public function reduxOptions() {
	}
}


global $WPlusPlusCore;

try {
	$WPlusPlusCore = new WPlusPlusCore();
} catch ( \Exception $e ) {
	echo $e->getMessage();
}