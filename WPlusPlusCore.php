<?php

namespace Tofandel;

use Tofandel\Classes\WP_Plugin;

/**
 * Class wplusplus
 * @package Tofandel
 *
 * Plugin Name: WPlusPlus
 * Plugin URI: https://github.com/tofandel/wplusplus
 * Description: A powerful wordpress plugin for developers to create forms and so much more!
 * Version: 1.0
 * Author: Adrien Foulon <tofandel@tukan.hu>
 * Author URI: https://tukan.fr/a-propos/#adrien-foulon
 * Text Domain: wplusplus
 * Domain Path: /languages/
 * WC tested up to: 4.8
 * Download Url: https://github.com/tofandel/wplusplus/
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';

class WPlusPlusCore extends WP_Plugin {
	const MULOADER_DIR = ABSPATH . 'mu-plugins/wplusplus-muloader';

	public function actionsAndFilters() {
		//Silence is golden
	}

	public function definitions() {

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
		mkdir( self::MULOADER_DIR );
		copy( __DIR__ . '/wplusplus-muloader.php.bak', self::MULOADER_DIR . '/wplusplus-muloader.php' );
	}

	public function deactivate() {
		//Clean the muloader
		rmdir( self::MULOADER_DIR );
		unlink( self::MULOADER_DIR . '/wplusplus-muloader.php' );
	}
}