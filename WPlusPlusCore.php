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
 * Version: 1.9.2
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

		//If we have another plugin using the core (which is the point of this plugin)
		//Then we can hide the core and make it update with the latest version when this other plugin is updating/
		add_action( 'upgrader_process_complete', [ $this, 'WPPBundledUpgrade' ], 10, 2 );
		//add_action( 'site_transient_update_plugins', [ $this, 'WPPBundledUpdate' ] );
		add_action( 'pre_current_active_plugins', [ $this, 'maybe_hide_plugin' ] );
		add_filter( 'all_plugins', [ $this, 'multisite_maybe_hide_plugin' ] );
	}


	public function multisite_maybe_hide_plugin( $plugins ) {
		if ( count( self::getSingletons() ) > 1 ) {
			$updates = get_transient( 'update_plugins' );
			if ( ! isset( $updates->response[ $this->getPluginFile() ] ) ) {
				unset( $plugins[ $this->getPluginFile() ] );
			}
		}

		return $plugins;
	}

	public function maybe_hide_plugin() {
		global $wp_list_table;
		if ( count( self::getSingletons() ) > 1 && isset( $wp_list_table->items[ $this->getPluginFile() ] ) ) {
			$updates = get_transient( 'update_plugins' );
			if ( ! isset( $updates->response[ $this->getPluginFile() ] ) ) {
				unset( $wp_list_table->items[ $this->getPluginFile() ] );
			}
		}
	}

	/**
	 * @param $plugins
	 *
	 * @return bool
	 * @throws \ReflectionException
	 */
	private function searchPlugins( $plugins ) {
		$non_core = self::getSingletons();
		$class    = new \ReflectionClass( static::class );
		$class->getName();
		unset( $non_core[ $class->getName() ] );

		foreach ( $non_core as $plugin ) {
			/**
			 * @var WP_Plugin $plugin
			 */
			$file = $plugin->getPluginFile();
			if ( in_array( $file, $plugins ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param \WP_Upgrader $upgrader
	 *
	 * @param array $info
	 *
	 * @return bool|array
	 * @throws \ReflectionException
	 */
	public function WPPBundledUpgrade( $upgrader, $info = array() ) {
		//Todo: We should also probably upgrade all other plugins as they might break as well,
		// even though it should become less and less frequent as the plugin matures and will try to keep backward compatibility
		if ( $info['action'] == 'update' && $info['type'] == 'plugin' && $this->searchPlugins( $info['plugins'] ) ) {
			$plugin  = $this->getPluginFile();
			$current = get_site_transient( 'update_plugins' );
			if ( ! isset( $current->response[ $plugin ] ) ) {
				return false;
			}
			// Get the URL to the zip file
			$r = $current->response[ $plugin ];

			add_filter( 'upgrader_pre_install', array( $upgrader, 'deactivate_plugin_before_upgrade' ), 10, 2 );
			add_filter( 'upgrader_clear_destination', array( $upgrader, 'delete_old_plugin' ), 10, 4 );
			//'source_selection' => array($upgrader, 'source_selection'), //there's a trac ticket to move up the directory for zip's which are made a bit differently, useful for non-.org plugins.
			add_action( 'upgrader_process_complete', 'wp_clean_plugins_cache', 9, 0 );
			$upgrader->run( array(
				'package'                     => $r->package,
				'destination'                 => WP_PLUGIN_DIR,
				'clear_destination'           => true,
				'abort_if_destination_exists' => false,
				'clear_working'               => true,
				'is_multi'                    => true,
				'hook_extra'                  => array(
					'plugin' => $plugin,
					'type'   => 'plugin',
					'action' => 'update',
				),
			) );
			// Cleanup our hooks, in case something else does a upgrade on this connection.
			remove_action( 'upgrader_process_complete', 'wp_clean_plugins_cache', 9 );
			remove_filter( 'upgrader_pre_install', array( $upgrader, 'deactivate_plugin_before_upgrade' ) );
			remove_filter( 'upgrader_clear_destination', array( $upgrader, 'delete_old_plugin' ) );

			if ( ! $upgrader->result || is_wp_error( $upgrader->result ) ) {
				return $upgrader->result;
			}

			// Force refresh of plugin update information
			wp_clean_plugins_cache( true );

			//The plugin will get deactivated, we set a transient that the mu-plugin will use
			//to try to reactivate the plugin on the next wordpress loading and abort if a fatal error occurs
			set_transient( 'wpp_reactivate_core', $this->getPluginFile(), 60 * 20 );

			return true;
		}

		return false;
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
		$this->copy( 'wplusplus-muloader.php.bak', self::MULOADER_DIR . '/wplusplus-muloader.php' );
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