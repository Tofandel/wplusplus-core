<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel;

use Tofandel\Core\Interfaces\WP_Plugin as WP_Plugin_Interface;
use Tofandel\Core\Modules\VC_Integration;
use Tofandel\Core\Objects\WP_Plugin;

if ( is_admin() && ! wp_doing_ajax() ) {
	require_once __DIR__ . '/plugins/tgmpa-config.php';
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class WPlusPlusCore
 * @package Tofandel
 *
 * Plugin Name: W++ Core
 * Plugin URI: https://github.com/tofandel/wplusplus-core/
 * Description: A Wordpress Plugin acting as core for other of my plugins and including the Ultimate Redux Framework Bundle and OOP APIs to use it
 * Version: 1.8
 * Author: Adrien Foulon <tofandel@tukan.hu>
 * Author URI: https://tukan.fr/a-propos/#adrien-foulon
 * Text Domain: wppc
 * Domain Path: /languages/
 * Requires PHP: 5.5
 * Requires at least: 4.7
 * Tested up to: 4.9.7
 */

require_once 'functions.php';

class WPlusPlusCore extends WP_Plugin implements WP_Plugin_Interface {
	const MULOADER_DIR = WPMU_PLUGIN_DIR;
	protected $repo_url = 'https://github.com/Tofandel/wplusplus-core/';
	protected $no_redux = true;

	public function actionsAndFilters() {
	}

	public function definitions() {
		if ( wpp_is_plugin_active( 'js_composer/js_composer.php' ) ) {
			$this->setSubModule( new VC_Integration( $this ) );
		}
		$this->registerScript( 'select2', array( 'jquery' ) );
		$this->registerStyle( 'select2' );
	}

	public function menusAndSettings() {
	}

	public function upgrade( $last_version ) {
		// Nothing yet
	}

	public function activated() {
		parent::activated();
		//Create the muloader so the core get's loaded before any plugin, thus removing the need to require it in every child plugin
		$this->mkdir( self::MULOADER_DIR );
		$this->copy( 'wplusplus-muloader.php.bak', self::MULOADER_DIR . '/wplusplus-muloader.php' );
	}

	public function deactivated() {
		//Clean the muloader
		$this->delete_file( self::MULOADER_DIR . '/wplusplus-muloader.php' );
	}

	public function uninstall() {
	}
}


global $WPlusPlusCore;

try {
	$WPlusPlusCore = new WPlusPlusCore();
} catch ( \Exception $e ) {
	echo $e->getMessage();
}