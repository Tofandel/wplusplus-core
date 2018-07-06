<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     Redux Framework
 * @subpackage  Redux Color Schemes
 * @subpackage  Wordpress
 * @author      Kevin Provance  (kprovance)
 * @author      Dovy
 * @version     2.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_color_scheme' ) ) {


	/**
	 * Main ReduxFramework color_scheme extension class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_extension_color_scheme {

		public static $version = '2.4.0';

		// Protected vars
		protected $parent;
		public $extension_url;
		public $extension_dir;
		public static $theInstance;
		public static $ext_url;
		public $field_id = '';
		public $output_transparent = false;
		private $class_css = '';

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $parent Parent settings.
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {

			$redux_ver = ReduxFramework::$_version;

			//TODO on release

//            DO NOT REMOVE, COMMENT OUT OR EDIT THESE THREE LINES!!!!!
//            Doing so could cause errors, notices, and/or your computer to cry!            
//            Why?  Older version of Redux - pre 3.5.5 - do not have the necessary
//            helper functions to make this extension work.
			if ( version_compare( $redux_ver, '3.5.5' ) < 0 ) {
				wp_die( 'The Redux Color Scheme extension required Redux Framework version 3.5.5 or higher.<br/><br/>You are running Redux Framework version ' . $redux_ver );
			}

			// Set parent object
			$this->parent = $parent;

			// Set extension dir
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
				self::$ext_url       = $this->extension_url;
			}

			// Set field name
			$this->field_name = 'color_scheme';

			// Set instance
			self::$theInstance = $this;

			$this->class_css = Redux_Helpers::cleanFilePath( get_stylesheet_directory() ) . '/redux-color-schemes.css';

			// Adds the local field
			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				&$this,
				'overload_field_path'
			) );

			add_filter( "redux/options/{$this->parent->args['opt_name']}/defaults", array( $this, 'set_defaults' ) );

			// Ajax hooks
			add_action( 'wp_ajax_redux_color_schemes', array( $this, 'parse_ajax' ) );
			add_action( 'wp_ajax_nopriv_redux_color_schemes', array( $this, 'parse_ajax' ) );

			// Reset hooks
			add_action( 'redux/validate/' . $this->parent->args['opt_name'] . '/defaults', array(
				$this,
				'reset_defaults'
			), 0, 3 );
			add_action( 'redux/validate/' . $this->parent->args['opt_name'] . '/defaults_section', array(
				$this,
				'reset_defaults_section'
			), 0, 3 );

			// Save filter
			add_action( 'redux/validate/' . $this->parent->args['opt_name'] . '/before_validation', array(
				$this,
				'save_hook'
			), 0, 3 );

			// Register hook - to get field id and prep helper
			add_action( 'redux/options/' . $this->parent->args['opt_name'] . '/field/' . $this->field_name . '/register', array(
				$this,
				'register_field'
			) );

			include_once $this->extension_dir . 'color_scheme/inc/class.color_scheme_functions.php';
			ReduxColorSchemeFunctions::init( $parent );

			$field          = ReduxColorSchemeFunctions::getField( $parent );
			$this->field_id = $field['id'];

			// Prep storage
			$upload_dir = ReduxColorSchemeFunctions::$upload_dir;

			// Create uploads/redux_scheme_colors/ folder
			if ( ! is_dir( $upload_dir ) ) {
				$parent->filesystem->execute( 'mkdir', $upload_dir );
			}
		}

		public function set_defaults( $defaults = array() ) {
			if ( empty( $this->field_id ) ) {
				return $defaults;
			}

			$x            = get_option( $this->parent->args['opt_name'] );
			$color_opts   = isset( $x[ $this->field_id ] ) ? $x[ $this->field_id ] : array();
			$wrong_format = false;

			if ( ! isset( $color_opts['color_scheme_name'] ) ) {
				$wrong_format = true;

				$data = ReduxColorSchemeFunctions::data_array_from_scheme( 'Default' );

				if ( ! empty( $data ) && isset( $x[ $this->field_id ] ) ) {
					$x[ $this->field_id ] = $data;

					update_option( $this->parent->args['opt_name'], $x );
				}
			}

			ReduxColorSchemeFunctions::$_parent = $this->parent;

			$otVal                    = ReduxColorSchemeFunctions::getOutputTransparentVal();
			$this->output_transparent = $otVal;

			ReduxColorSchemeFunctions::converttoDB();

			$scheme_key  = ReduxColorSchemeFunctions::getSchemeKey();
			$scheme_data = get_option( $scheme_key );

			$scheme_data_exists = empty( $scheme_data ) ? false : true;

			$default_exists = in_array( 'default', array_map( 'strtolower', ReduxColorSchemeFunctions::getSchemeNames() ) );

			if ( ! $scheme_data_exists || ! $default_exists || $wrong_format ) {
				$data = $this->getDefaultData();

				//  Add to (and/or create) JSON scheme file
				ReduxColorSchemeFunctions::setSchemeData( 'Default', $data );

				// Set default scheme
				ReduxColorSchemeFunctions::setCurrentSchemeID( 'Default' );

				$data = ReduxColorSchemeFunctions::data_array_from_scheme( 'Default' );

				$this->parent->options[ $this->field_id ] = $data;

				$defaults[ $this->field_id ] = $data;
			}

			return $defaults;
		}

		/**
		 * Field Register. Sets the whole smash up.
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $data Field data.
		 *
		 * @return      void
		 */
		public function register_field( $data ) {

			// Include color_scheme helper
			include_once $this->extension_dir . 'color_scheme/inc/class.color_scheme_functions.php';

			if ( isset( $data['output_transparent'] ) ) {
				$this->output_transparent = $data['output_transparent'];
			}

			$this->field_id                       = $data['id'];
			ReduxColorSchemeFunctions::$_field_id = $data['id'];

			// Set helper parent object
			ReduxColorSchemeFunctions::$_parent = $this->parent;

			// Prep storage
			$upload_dir = ReduxColorSchemeFunctions::$upload_dir;

			// Set upload_dir cookie
			setcookie( 'redux_color_scheme_upload_dir', $upload_dir, 0, "/" );
		}

		public function reset_defaults( $defaults = array() ) {
			// Check if reset_all was fired
			$this->reset_all();
			$defaults[ $this->field_id ] = ReduxColorSchemeFunctions::data_array_from_scheme( 'Default' );
			$this->test                  = $defaults[ $this->field_id ];

			return $defaults;
		}

		public function reset_defaults_section( $defaults = array() ) {
			// Get current tab/section number
			$curTab = $_COOKIE['redux_current_tab'];

			// Get the tab/section number field is used on
			$tabNum = $this->parent->field_sections['color_scheme'][ $this->field_id ];

			// If they match...
			if ( $curTab == $tabNum ) {
				// Reset data
				$this->reset_all();
			}
			$defaults[ $this->field_id ] = ReduxColorSchemeFunctions::data_array_from_scheme( 'Default' );

			return $defaults;
		}

		/**
		 * Save Changes Hook. What to do when changes are saved
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $data Saved data.
		 *
		 * @return      void
		 */
		public function save_hook( $saved_options = array(), $old_options = array() ) {

			if ( ! isset( $saved_options[ $this->field_id ] ) || empty( $saved_options[ $this->field_id ] ) || ( is_array( $saved_options[ $this->field_id ] ) && $old_options == $saved_options ) || ! array_key_exists( $this->field_id, $saved_options ) ) {
				return $saved_options;
			}

			// We'll use the reset hook instead
			if ( ! empty( $saved_options['defaults'] ) || ! empty( $saved_options['defaults-section'] ) ) {
				return $saved_options;
			}

			$first_value = reset( $saved_options[ $this->field_id ] ); // First Element's Value

			// Parse the JSON to an array
			if ( isset( $first_value['data'] ) ) {

				ReduxColorSchemeFunctions::$_parent   = $this->parent;
				ReduxColorSchemeFunctions::$_field_id = $this->field_id;

				ReduxColorSchemeFunctions::setCurrentSchemeID( $saved_options['redux-scheme-select'] );

				// Get the current field ID
				$raw_data = $saved_options[ $this->field_id ];

				// Create new array
				$save_data = array();

				// Enum through saved data
				foreach ( $raw_data as $id => $val ) {

					if ( $id !== 'color_scheme_name' ) {
						if ( is_array( $val ) ) {

							if ( ! isset( $val['data'] ) ) {
								continue;
							}

							$data = json_decode( rawurldecode( $val['data'] ), true );

							// Sanitize everything
							$color = isset( $data['color'] ) ? $data['color'] : '';
							$alpha = isset( $data['alpha'] ) ? $data['alpha'] : 1;

							$id    = isset( $data['id'] ) ? $data['id'] : $id;
							$title = isset( $data['title'] ) ? $data['title'] : $id;

							$grp = isset( $data['group'] ) ? $data['group'] : '';

							if ( $color == '' || $color == 'transparent' ) {
								$rgba = $this->output_transparent ? 'transparent' : '';
							} else {
								$rgba = Redux_Helpers::hex2rgba( $color, $alpha );
							}

							// Create array of saved data
							$save_data[] = array(
								'id'    => $id,
								'title' => $title,
								'color' => $color,
								'alpha' => $alpha,
								'group' => $grp,
								'rgba'  => $rgba
							);
						} else {
							$save_data[] = array(
								'id'    => $id,
								'value' => $val,
								'type'  => 'select'
							);
						}
					}
				}

				$new_scheme = array();

				$new_scheme['color_scheme_name'] = ReduxColorSchemeFunctions::getCurrentSchemeID();

				// Enum through values and assign them to new array
				foreach ( $save_data as $item => $val ) {
					if ( isset( $val['id'] ) ) {
						$new_scheme[ $val['id'] ] = $val;
					}

				}

				// Filter for DB save
				// Don't need to save select arrays to database,
				// just the id => value.
				$database_data = $new_scheme;

				foreach ( $database_data as $k => $v ) {
					if ( isset( $v['type'] ) ) {
						$val = $v['value'];

						unset( $database_data[ $k ] );

						$database_data[ $k ] = $val;
					}
				}

				$saved_options[ $this->field_id ] = $database_data;

				// Check if we should save this compared to the old data
				$save_scheme = false;

				// Doesn't exist or is empty
				if ( ! isset( $old_options[ $this->field_id ] ) || ( isset( $old_options[ $this->field_id ] ) && ! empty( $old_options[ $this->field_id ] ) ) ) {
					$save_scheme = true;
				}

				// Isn't empty and isn't the same as the new array
				if ( ! empty( $old_options[ $this->field_id ] ) && $saved_options[ $this->field_id ] != $old_options[ $this->field_id ] ) {
					$save_scheme = true;
				}

				if ( $save_scheme ) {
					$scheme = ReduxColorSchemeFunctions::getCurrentSchemeID();
					ReduxColorSchemeFunctions::setSchemeData( $scheme, $save_data );

				}

			}

			return $saved_options;

		}

		/**
		 * Reset data. Restores colour picker to default values
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      void
		 */
		private function reset_data() {
			ReduxColorSchemeFunctions::$_parent   = $this->parent;
			ReduxColorSchemeFunctions::$_field_id = $this->field_id;

			// Get default data
			$data = $this->getDefaultData();

			//  Add to (and/or create) JSON scheme file
			ReduxColorSchemeFunctions::setSchemeData( 'Default', $data );

			// Set default scheme
			ReduxColorSchemeFunctions::setCurrentSchemeID( 'Default' );

			// Set the database with default settings
			//ReduxColorSchemeFunctions::setDatabaseData();
		}

		/**
		 * Reset All Hook. Todo list when all data is reset
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $data All data from Framework.
		 *
		 * @return      void
		 */
		public function reset_all( $data = array() ) {
			if ( ! empty( $this->field_id ) && isset( $this->parent->options_defaults[ $this->field_id ] ) && ! empty( $this->parent->options_defaults[ $this->field_id ] ) ) {
				ReduxColorSchemeFunctions::$_parent   = $this->parent;
				ReduxColorSchemeFunctions::$_field_id = $this->field_id;

				$this->reset_data();
			}
		}

		/**
		 * Reset Section Hook. Todo list when section data is reset
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $data All data from Framework.
		 *
		 * @return      void
		 */
		public function reset_section( $data = array() ) {

			// Make sure field is in use
			if ( ! empty( $this->field_id ) ) {

				ReduxColorSchemeFunctions::$_parent   = $this->parent;
				ReduxColorSchemeFunctions::$_field_id = $this->field_id;

				// Get current tab/section number
				$curTab = $_COOKIE['redux_current_tab'];

				// Get the tab/section number field is used on
				$tabNum = Redux_Helpers::tabFromField( $this->parent, $this->field_id );

				// If they match...
				if ( $curTab == $tabNum ) {
					// Reset data
					$this->reset_data();
				}
			}
		}

		/**
		 * AJAX evaluator. Detemine course of action based on AJAX callback
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function parse_ajax() {

			// Verify nonce
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], "redux_{$this->parent->args['opt_name']}_color_schemes" ) ) {
				die( 0 );
			}

			$parent = $this->parent;

			// Do action
			if ( isset( $_REQUEST['type'] ) ) {

				// Save scheme
				if ( $_REQUEST['type'] == "save" ) {
					$this->save_scheme( $parent );

					// Delete scheme
				} elseif ( $_REQUEST['type'] == "delete" ) {
					$this->delete_scheme( $parent );

					// Scheme change
				} elseif ( $_REQUEST['type'] == "update" ) {
					$this->get_scheme_html( $parent );

					// Export scheme file
				} elseif ( $_REQUEST['type'] == "export" ) {
					$this->download_schemes();
				}
			}
		}

		/**
		 * Download Scheme File.
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      void
		 */
		private function download_schemes() {
			ReduxColorSchemeFunctions::$_parent   = $this->parent;
			ReduxColorSchemeFunctions::$_field_id = $this->field_id;

			// Read contents of scheme file
			$content = ReduxColorSchemeFunctions::readSchemeFile();
			$content = json_encode( $content );

			// Set header info
			header( 'Content-Description: File Transfer' );
			header( 'Content-type: application/txt' );
			header( 'Content-Disposition: attachment; filename="redux_schemes_' . $this->parent->args['opt_name'] . '_' . $this->field_id . '_' . date( 'm-d-Y' ) . '.json"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );

			// File download
			echo $content;

			// 2B ~! 2B
			die;
		}

		/**
		 * Save Scheme. Saved individual scheme to JSON scheme file
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      void
		 */
		private function save_scheme( $parent ) {
			ReduxColorSchemeFunctions::$_parent   = $parent;
			ReduxColorSchemeFunctions::$_field_id = $this->field_id;

			// Get scheme name
			$scheme_name = $_REQUEST['scheme_name'];

			// Check for duplicates
			$names = ReduxColorSchemeFunctions::getSchemeNames();
			foreach ( $names as $idx => $name ) {
				$name     = strtolower( $name );
				$tmp_name = strtolower( $scheme_name );

				if ( $name == $tmp_name ) {
					echo 'fail';
					die();
				}
			}

			// Get scheme data
			$scheme_data = $_REQUEST['scheme_data'];

			// Get field ID
			$field_id = $_REQUEST['field_id'];

			$scheme_data = rawurldecode( $scheme_data );
			$scheme_data = json_decode( $scheme_data, true );

			// Save scheme to file.  If successful...
			if ( true == ReduxColorSchemeFunctions::setSchemeData( $scheme_name, $scheme_data ) ) {

				// Update scheme selector
				echo ReduxColorSchemeFunctions::getSchemeSelectHTML( $scheme_name );
			}

			die(); // a horrible death!
		}

		/**
		 * Delete Scheme. Delete individual scheme from JSON scheme file
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      void
		 */
		private function delete_scheme( $parent ) {

			// Get deleted scheme ID
			$scheme_id = $_REQUEST['scheme_id'];

			// Get field ID
			$field_id = $_REQUEST['field_id'];

			// If scheme ID was passed (and why wouldn't it be??  Hmmm??)
			if ( $scheme_id ) {
				ReduxColorSchemeFunctions::$_field_id = $field_id;
				ReduxColorSchemeFunctions::$_parent   = $parent;

				// Get entire scheme file
				$schemes = ReduxColorSchemeFunctions::readSchemeFile();

				// If we got a good read...
				if ( ! false == $schemes ) {

					// If scheme name exists...
					if ( isset( $schemes[ $scheme_id ] ) ) {

						// Unset it.
						unset( $schemes[ $scheme_id ] );

						// Save the scheme data, minus the deleted scheme.  Upon success...
						if ( true == ReduxColorSchemeFunctions::writeSchemeFile( $schemes ) ) {

							// Set default scheme
							ReduxColorSchemeFunctions::setCurrentSchemeID( 'Default' );

							// Update field ID
							ReduxColorSchemeFunctions::$_field_id = $field_id;

							// Meh TODO
							ReduxColorSchemeFunctions::setDatabaseData();

							echo "success";
						} else {
							echo "Failed to write JSON file to server.";
						}
					} else {
						echo "Scheme name does not exist in JSON string.  Aborting.";
					}
				} else {
					echo "Failed to read JSON scheme file, or file is empty.";
				}
			} else {
				echo "No scheme ID passed.  Aborting.";
			}

			die(); // rolled a two.
		}

		/**
		 * Gets the new scheme based on selection.
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      void
		 */
		private function get_scheme_html( $parent ) {

			// Get the selected scheme name
			$scheme_id = $_POST['scheme_id'];

			// Get the field ID
			$field_id = $_POST['field_id'];

			// Get the field class
			$field_class = isset( $_POST['field_class'] ) ? $_POST['field_class'] : '';

			ReduxColorSchemeFunctions::$_parent = $parent;

			// Set the updated field ID
			ReduxColorSchemeFunctions::$_field_id = $field_id;

			// Set the updated field class
			ReduxColorSchemeFunctions::$_field_class = $field_class;

			// Get the colour picket layout HTML
			$html = ReduxColorSchemeFunctions::getCurrentColorSchemeHTML( $scheme_id );

			// Print!
			echo $html;

			die(); //another day
		}


		/**
		 * getDefaultData Function.
		 *
		 * Retrieves array of default data for colour picker.
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      array Default values from config.
		 */
		private function getDefaultData() {

			$defOpts  = $this->parent->options_defaults[ $this->field_id ];
			$sections = $this->parent->sections;
			$data     = array();

			foreach ( $sections as $num => $arr ) {
				if ( isset( $arr['fields'] ) ) {
					foreach ( $arr['fields'] as $num2 => $arr2 ) {
						if ( $arr2['id'] == $this->field_id ) {

							// Select fields
							if ( isset( $arr2['select'] ) ) {
								foreach ( $arr2['select'] as $k => $v ) {
									$data[] = array(
										'id'    => $v['id'],
										'value' => $v['default'],
										'type'  => 'select'
									);
								}
								continue;
							}
						}
					}
				}
			}

			foreach ( $defOpts as $k => $v ) {

				$title = isset( $v['title'] ) ? $v['title'] : $v['id'];
				$color = isset( $v['color'] ) ? $v['color'] : '';
				$alpha = isset( $v['alpha'] ) ? $v['alpha'] : 1;
				$grp   = isset( $v['group'] ) ? $v['group'] : '';

				if ( $color == '' || $color == 'transparent' ) {
					$rgba = $this->output_transparent ? 'transparent' : '';
				} else {
					$rgba = Redux_Helpers::hex2rgba( $color, $alpha );
				}

				$data[] = array(
					'id'    => $v['id'],
					'title' => $title,
					'color' => $color,
					'alpha' => $alpha,
					'group' => $grp,
					'rgba'  => $rgba
				);
			}

			return $data;
		}

		static public function getInstance() {
			return self::$theInstance;
		}

		static public function getExtURL() {
			return self::$ext_url;
		}

		// Forces the use of the embeded field path vs what the core typically would use
		public function overload_field_path( $field ) {
			return dirname( __FILE__ ) . '/' . $this->field_name . '/field_' . $this->field_name . '.php';
		}

	}
}
