<?php
/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @subpackage  Field_codemirror
 * @author      Taha Paksu (tpaksu)
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_Field_codemirror' ) ) {

	/**
	 * Main ReduxFramework_Field_codemirror class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_codemirror extends ReduxFramework {

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		function __construct( $field = array(), $value = '', $parent ) {

			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			// Set default args for this field to avoid bad indexes. Change this to anything you use.
			$this->defaults = array(
				'enqueue_frontend' => true,
				'editor_options'   => array(
					'theme'        => 'default',
					'mode'         => null,
					'htmlMode'     => false,
					'lineNumbers'  => true,
					'lineWrapping' => true,
					'lint'         => false,
					'hint'         => false,
					'autohint'     => false,
					'addModeClass' => true,
					'value'        => ''
				)
			);
			if ( isset( $this->field["editor_options"]["hint"] ) && $this->field["editor_options"]["hint"] === true ) {
				// if autocomplete key is not defined, define it as "Ctrl + Space"
				if ( ! isset( $this->field["editor_options"]["extraKeys"] ) ) {
					$this->field["editor_options"]["extraKeys"] = array( "Ctrl-Space" => "autocomplete" );
				}
			}

			if ( isset( $this->field["editor_options"]["lint"] ) && $this->field["editor_options"]["lint"] === true ) {
				// set fixed gutter
				if ( ! isset( $this->field["editor_options"]["fixedGutter"] ) ) {
					$this->field["editor_options"]["fixedGutter"] = true;
				}
				// set gutter style
				if ( ! isset( $this->field["editor_options"]["gutters"] ) ) {
					$this->field["editor_options"]["gutters"] = array( "CodeMirror-lint-markers" );
				}
			}

			$this->field = self::parse_args_r( $this->field, $this->defaults );

			// get mode options, dependencies
			$this->modes  = json_decode( file_get_contents( $this->extension_dir . "lib/codemirror_modes.json" ), true );
			$this->addons = json_decode( file_get_contents( $this->extension_dir . "lib/codemirror_addons.json" ), true );
		}// __construct

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since ReduxFramework 0.0.4
		 */
		function render() {
			$placeholder = ! isset( $this->field['placeholder'] ) ? "" : $this->field['placeholder'];
			echo '<textarea id="' . $this->field['id'] . '" name="' . $this->field['name'] . '" class="redux_codemirror" placeholder="' . $placeholder . '">' . $this->value . '</textarea>';
		}// render

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since ReduxFramework 0.0.4
		 */
		function enqueue() {
			// loading base stylesheets
			wp_enqueue_style(
				'codemirror-css',
				$this->extension_url . 'lib/codemirror.css',
				time(),
				true
			);

			// loading theme stylesheet
			$codemirror_themes = array();
			if ( $theme_folder = opendir( $this->extension_dir . 'theme' ) ) {
				while ( false !== ( $theme = readdir( $theme_folder ) ) ) {
					if ( strlen( $theme ) > 4 && substr( $theme, - 4 ) == ".css" ) {
						$codemirror_themes[] = str_replace( ".css", "", $theme );
					}
				}
			}
			if ( ! isset( $this->field['editor_options']["theme"] ) || ! in_array( $this->field['editor_options']["theme"], $codemirror_themes ) ) {
				wp_enqueue_style( 'codemirror-editor-theme', $this->extension_url . 'theme/' . $this->defaults['editor_options']['theme'] . '.css', time(), true );
			} else {
				wp_enqueue_style( 'codemirror-editor-theme', $this->extension_url . 'theme/' . $this->field['editor_options']['theme'] . '.css', time(), true );
			}

			// enqueuing the field stylesheet at last to enable user override
			wp_enqueue_style( 'codemirror-editor-css', $this->extension_url . 'field_codemirror.css', time(), true );

			// loading base scripts
			wp_enqueue_script( 'codemirror-editor-js', $this->extension_url . 'field_codemirror.js', null, time(), true );
			wp_enqueue_script( 'codemirror-js', $this->extension_url . 'lib/codemirror.js', null, time(), true );


			// loading addon scripts
			if ( isset( $this->field['editor_options']["addon"] ) && ! empty( $this->field['editor_options']["addon"] ) ) {
				// supporting multiple modes loading
				if ( is_array( $this->field['editor_options']['addon'] ) ) {
					$required_addons = $this->field['editor_options']["addon"];
				} else {
					$required_addons = array( $this->field['editor_options']['addon'] );
				}
				// convert all to lowercase
				$required_addons = array_map( function ( $addon ) {
					return strtolower( $addon );
				}, $required_addons );
				foreach ( $required_addons as $addon ) {
					$this->_enqueue_addon( $addon );
				}
			}

			// loading mode script
			if ( isset( $this->field['editor_options']["mode"] ) && ! empty( $this->field['editor_options']["mode"] ) ) {
				// supporting multiple modes loading
				if ( is_array( $this->field['editor_options']['mode'] ) ) {
					$required_modes = $this->field['editor_options']["mode"];
				} else {
					$required_modes = array( $this->field['editor_options']['mode'] );
				}
				// convert all to lowercase
				$required_modes = array_map( function ( $mode ) {
					return strtolower( $mode );
				}, $required_modes );
				// check dependencies, load them and enqueue the mode script/css
				foreach ( $required_modes as $reqmode ) {
					$this->_check_mode_dependencies( $reqmode );
					$this->_enqueue_mode( $reqmode );
				}
			}

		}// enqueue

		/**
		 * Check Mode Dependencies Function.
		 * If this mode depends on any other mode, this function registers/enqueues the depended scripts/css
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		function _check_mode_dependencies( $mode ) {
			$mode_filtered_node = array_values( array_filter( $this->modes, function ( $modenode ) use ( $mode ) {
				return $modenode["mode"] == $mode;
			} ) );
			$mode_filtered_node = $mode_filtered_node[0];
			if ( $mode_filtered_node != null ) {
				// enqueue remote dependencies
				if ( is_array( $mode_filtered_node["remote_dependencies"] ) && count( $mode_filtered_node["remote_dependencies"] ) > 0 ) {
					foreach ( $mode_filtered_node["remote_dependencies"][0] as $dependency_type => $dependency_array ) {
						if ( is_array( $mode_filtered_node["remote_dependencies"][0][ $dependency_type ] ) && count( $mode_filtered_node["remote_dependencies"][0][ $dependency_type ] ) > 0 ) {
							if ( $this->field['editor_options'][ $dependency_type ] === true ) {
								foreach ( $dependency_array as $index => $remote_dependency ) {
									$remote_slug = $mode_filtered_node["mode"] . "-" . $index;
									$this->_enqueue_remote( $remote_slug, $remote_dependency );
								}
							}
						}
					}
				}

				// enqueue lints
				if ( $this->field["editor_options"]["lint"] === true && isset( $mode_filtered_node["lint"] ) ) {
					$this->_enqueue_lint( $mode_filtered_node["lint"] );
				}

				// enqueue hints
				if ( $this->field["editor_options"]["hint"] === true && isset( $mode_filtered_node["hint"] ) ) {
					$this->_enqueue_hint( $mode_filtered_node["hint"] );
				}

				// enqueue other dependencies
				if ( isset( $mode_filtered_node["dependencies"] ) && is_array( $mode_filtered_node["dependencies"] ) && count( $mode_filtered_node["dependencies"] ) > 0 ) {
					foreach ( $mode_filtered_node["dependencies"] as $dependency ) {
						$this->_check_mode_dependencies( $dependency ); // loop through values
						$this->_enqueue_mode( $dependency );
					}
				}
			}
		}// _check_mode_dependencies

		/**
		 * Enqueue Remote Function.
		 * This will register/enqueue the remote dependencies of the mode
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		function _enqueue_remote( $slug, $remote_url ) {
			wp_enqueue_script( 'codemirror-remote-' . $slug . 'js', $remote_url, null, time(), true );
		}// _enqueue_remote

		/**
		 * Enqueue Mode Function.
		 * This will register/enqueue the mode/depended upon mode
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		function _enqueue_mode( $mode ) {
			wp_enqueue_script( 'codemirror-mode-' . $mode . 'js', $this->extension_url . 'mode/' . $mode . '/' . $mode . '.js', null, time(), true );
		}// _enqueue_mode

		/**
		 * Enqueue Lint Function.
		 * This will register/enqueue the lint script/css of the mode
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		function _enqueue_lint( $mode ) {
			// flag for first lint that's found and requested, will be used for loading 'lint.js'
			static $available_lint_found = false;
			if ( $available_lint_found == false ) {
				$available_lint_found = true;
				// enqueue lint script and stylesheet.
				wp_enqueue_style( 'codemirror-editor-lint-css', $this->extension_url . 'addon/lint/lint.css', time(), true );
				wp_enqueue_script( 'codemirror-editor-lint-js', $this->extension_url . 'addon/lint/lint.js', null, time(), true );
			}

			// enqueue lint script.
			wp_enqueue_script( 'codemirror-editor-lint-' . $mode . '-js', $this->extension_url . 'addon/lint/' . $mode . '-lint.js', null, time(), true );

		}// _enqueue_lint

		/**
		 * Enqueue Lint Function.
		 * This will register/enqueue the hint (autocomplete) script/css of the mode
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		function _enqueue_hint( $mode ) {
			// flag for first hint that's found and requested, will be used for loading 'show-hint.js'
			static $available_hint_found = false;
			if ( $available_hint_found == false ) {
				$available_hint_found = true;
				// enqueue hint script and stylesheet.
				wp_enqueue_style( 'codemirror-editor-hint-css', $this->extension_url . 'addon/hint/show-hint.css', time(), true );
				wp_enqueue_script( 'codemirror-editor-hint-js', $this->extension_url . 'addon/hint/show-hint.js', null, time(), true );
			}

			// enqueue hint script.
			wp_enqueue_script( 'codemirror-editor-hint-' . $mode . '-js', $this->extension_url . 'addon/hint/' . $mode . '-hint.js', null, time(), true );

		}// _enqueue_hint

		/**
		 * Enqueue Codemirror Addon.
		 * This will register/enqueue the addon script/css/settings
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		function _enqueue_addon( $addon ) {
			//echo "<pre>".var_export($this->addons,true)."</pre>";
			if ( isset( $this->addons[ $addon ] ) ) {
				//echo $addon."<br>";
				// load dependencies which needs to load before the addon
				if ( isset( $this->addons[ $addon ]["depends-before"] ) &&
				     is_array( $this->addons[ $addon ]["depends-before"] )
				) {
					foreach ( $this->addons[ $addon ]["depends-before"] as $index => $addon_depended ) {
						$this->_enqueue_addon( $addon_depended );
					}
				}
				// load editor option keys

				if ( isset( $this->addons[ $addon ]["settings"] ) &&
				     is_array( $this->addons[ $addon ]["settings"] )
				) {
					//var_dump($this->addons[$addon]["settings"]);
					foreach ( $this->addons[ $addon ]["settings"] as $settings_key => $settings_value ) {
						if ( isset( $this->field['editor_options'][ $settings_key ] ) ) {
							if ( is_array( $this->field['editor_options'][ $settings_key ] ) ) {
								$this->field['editor_options'][ $settings_key ] = array_unique( array_merge( $this->field['editor_options'][ $settings_key ], $settings_value ) );
							} else {
								$this->field['editor_options'][ $settings_key ] = $settings_value;
							}
						} else {
							$this->field['editor_options'][ $settings_key ] = $settings_value;
						}
					}
				}
				// load stylesheets
				if ( isset( $this->addons[ $addon ]["css"] ) &&
				     is_array( $this->addons[ $addon ]["css"] )
				) {
					foreach ( $this->addons[ $addon ]["css"] as $index => $addon_stylesheet ) {
						wp_enqueue_style( 'codemirror-editor-' . $addon . '-' . $index . '-css', $this->extension_url . $addon_stylesheet, time(), true );
					}
				}

				// load addon base script
				if ( isset( $this->addons[ $addon ]["file"] ) ) {
					if ( ! is_array( $this->addons[ $addon ]["file"] ) ) {
						wp_enqueue_script(
							'codemirror-addon-' . $addon . 'js',
							$this->extension_url . $this->addons[ $addon ]["file"],
							null, time(), true );
					} else {
						foreach ( $this->addons[ $addon ]["file"] as $addon_file_index => $addon_file ) {
							wp_enqueue_script(
								'codemirror-addon-' . $addon . '-' . $addon_file_index . 'js',
								$this->extension_url . $addon_file,
								null, time(), true );
						}
					}
				}
				// load dependencies which needs to load after the addon
				if ( isset( $this->addons[ $addon ]["depends-after"] ) &&
				     is_array( $this->addons[ $addon ]["depends-after"] )
				) {
					foreach ( $this->addons[ $addon ]["depends-after"] as $index => $addon_depended ) {
						$this->_enqueue_addon( $addon_depended );
					}
				}
			}
		}// _enqueue_addon

		/**
		 * Functions to pass data from the PHP to the JS at render time.
		 *
		 * @return array Params to be saved as a javascript object accessable to the UI.
		 * @since  Redux_Framework 3.1.1
		 */
		function localize( $field, $value = "" ) {
			return $this->field;
		}// localize

		/**
		 * Output Function.
		 * Used to enqueue to the front-end
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function output() {

			if ( $this->field['enqueue_frontend'] ) {

			}

		}

		/**
		 * Recursive argument parsing
		 * This acts like a multi-dimensional version of wp_parse_args() (minus
		 * the querystring parsing - you must pass arrays). Taken from BuddyPress Core.
		 *
		 * @since CodeMirror_extension 1.0.0
		 */
		public static function parse_args_r( &$a, $b ) {
			$a = (array) $a;
			$b = (array) $b;
			$r = $b;

			foreach ( $a as $k => &$v ) {
				if ( is_array( $v ) && isset( $r[ $k ] ) ) {
					$r[ $k ] = self::parse_args_r( $v, $r[ $k ] );
				} else {
					$r[ $k ] = $v;
				}
			}

			return $r;
		}// parse_args_r
	}
}


?>
