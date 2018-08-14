<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Modules;

use Tofandel\Core\Interfaces\SubModule;

class PluginLoader implements SubModule {
	use \Tofandel\Core\Traits\SubModule;

	/**
	 * The hooks of the submodule
	 */
	public function actionsAndFilters() {
		$plugins = wp_get_active_and_valid_plugins();
		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'wplusplus' ) !== false || strpos( $plugin, 'WPlusPlus' ) !== false ) {
				//We load wplusplus core if it's active to load it early
				if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
					require_once( WP_PLUGIN_DIR . '/' . $plugin );
					break;
				}
			}
		}
	}
}