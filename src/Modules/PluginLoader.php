<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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