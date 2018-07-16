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
 * @package     Redux Framework
 * @subpackage  Repeater
 * @subpackage  Wordpress
 * @author      Dovy Paukstys (dovy)
 * @author      Kevin Provance (kprovance)
 * @version     1.0.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_repeater' ) ) {


	/**
	 * Main ReduxFramework css_layout extension class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_extension_repeater {

		public static $version = '1.0.6';

		// Protected vars
		/**
		 * @var ReduxFramework
		 */
		protected $parent;
		public $extension_url;
		public $extension_dir;
		public static $theInstance;
		public $field_id = '';
		private $class_css = '';

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       ReduxFramework $parent Parent settings.
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {

			$redux_ver = ReduxFramework::$_version;

			// Set parent object
			$this->parent = $parent;

			// Set extension dir
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
			}

			// Set field name
			$this->field_name = 'repeater';

			// Set instance
			self::$theInstance = $this;

			// Adds the local field
			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				&$this,
				'overload_field_path'
			) );
		}

		static public function getInstance() {
			return self::$theInstance;
		}

		public function TODO_validate_values( $plugin_options, $options, $sections ) {
			foreach ( $options['fields'] as $fkey => $field ) {

				if ( isset ( $field['type'] ) && ( $field['type'] == 'checkbox' || $field['type'] == 'checkbox_hide_below' || $field['type'] == 'checkbox_hide_all' ) ) {
					if ( ! isset ( $plugin_options[ $field['id'] ] ) ) {
						$plugin_options[ $field['id'] ] = 0;
					}
				}


				if ( isset( $this->parent->extensions[ $field['type'] ] ) && method_exists( $this->parent->extensions[ $field['type'] ], '_validate_values' ) ) {
					$plugin_options = $this->parent->extensions[ $field['type'] ]->_validate_values( $plugin_options, $field, $sections );

				}

				// Default 'not_empty 'flag to false.
				$isNotEmpty = false;

				// Make sure 'validate' field is set.
				if ( isset ( $field['validate'] ) ) {

					// Make sure 'validate field' is set to 'not_empty' or 'email_not_empty'
					//if ( $field['validate'] == 'not_empty' || $field['validate'] == 'email_not_empty' || $field['validate'] == 'numeric_not_empty' ) {
					if ( strtolower( substr( $field['validate'], - 9 ) ) == 'not_empty' ) {

						// Set the flag.
						$isNotEmpty = true;
					}
				}

				// Check for empty id value

				if ( ! isset ( $field['id'] ) || ! isset ( $plugin_options[ $field['id'] ] ) || ( isset ( $plugin_options[ $field['id'] ] ) && $plugin_options[ $field['id'] ] == '' ) ) {

					// If we are looking for an empty value, in the case of 'not_empty'
					// then we need to keep processing.
					if ( ! $isNotEmpty ) {

						// Empty id and not checking for 'not_empty.  Bail out...
						if ( ! isset( $field['validate_callback'] ) ) {
							continue;
						}
						//continue;
					}
				}

				// Force validate of custom field types
				if ( isset ( $field['type'] ) && ! isset ( $field['validate'] ) && ! isset( $field['validate_callback'] ) ) {
					if ( $field['type'] == 'color' || $field['type'] == 'color_gradient' ) {
						$field['validate'] = 'color';
					} elseif ( $field['type'] == 'date' ) {
						$field['validate'] = 'date';
					}
				}

				if ( isset ( $field['validate'] ) ) {
					$validate = 'Redux_Validation_' . $field['validate'];

					if ( ! class_exists( $validate ) ) {
						/**
						 * filter 'redux-validateclass-load'
						 *
						 * @deprecated
						 *
						 * @param        string             validation class file path
						 * @param string $validate validation class name
						 */
						$class_file = apply_filters( "redux-validateclass-load", self::$_dir . "inc/validation/{$field['validate']}/validation_{$field['validate']}.php", $validate ); // REMOVE LATER

						/**
						 * filter 'redux/validate/{opt_name}/class/{field.validate}'
						 *
						 * @param        string                validation class file path
						 * @param string $class_file validation class file path
						 */
						$class_file = apply_filters( "redux/validate/{$this->parent->args['opt_name']}/class/{$field['validate']}", self::$_dir . "inc/validation/{$field['validate']}/validation_{$field['validate']}.php", $class_file );

						if ( $class_file ) {
							if ( file_exists( $class_file ) ) {
								require_once $class_file;
							}
						}
					}

					if ( class_exists( $validate ) ) {

						//!DOVY - DB saving stuff. Is this right?
						if ( empty ( $options[ $field['id'] ] ) ) {
							$options[ $field['id'] ] = '';
						}

						if ( isset ( $plugin_options[ $field['id'] ] ) && is_array( $plugin_options[ $field['id'] ] ) && ! empty ( $plugin_options[ $field['id'] ] ) ) {
							foreach ( $plugin_options[ $field['id'] ] as $key => $value ) {
								$before = $after = null;
								if ( isset ( $plugin_options[ $field['id'] ][ $key ] ) && ( ! empty ( $plugin_options[ $field['id'] ][ $key ] ) || $plugin_options[ $field['id'] ][ $key ] == '0' ) ) {
									if ( is_array( $plugin_options[ $field['id'] ][ $key ] ) ) {
										$before = $plugin_options[ $field['id'] ][ $key ];
									} else {
										$before = trim( $plugin_options[ $field['id'] ][ $key ] );
									}
								}

								if ( isset ( $options[ $field['id'] ][ $key ] ) && ( ! empty ( $plugin_options[ $field['id'] ][ $key ] ) || $plugin_options[ $field['id'] ][ $key ] == '0' ) ) {
									$after = $options[ $field['id'] ][ $key ];
								}

								$validation = new $validate ( $this, $field, $before, $after );
								if ( ! empty ( $validation->value ) || $validation->value == '0' ) {
									$plugin_options[ $field['id'] ][ $key ] = $validation->value;
								} else {
									unset ( $plugin_options[ $field['id'] ][ $key ] );
								}

								if ( isset ( $validation->error ) ) {
									$this->parent->errors[] = $validation->error;
								}

								if ( isset ( $validation->warning ) ) {
									$this->parent->warnings[] = $validation->warning;
								}
							}
						} else {
							if ( isset( $plugin_options[ $field['id'] ] ) ) {
								if ( is_array( $plugin_options[ $field['id'] ] ) ) {
									$pofi = $plugin_options[ $field['id'] ];
								} else {
									$pofi = trim( $plugin_options[ $field['id'] ] );
								}
							} else {
								$pofi = null;
							}

							$validation                     = new $validate ( $this, $field, $pofi, $options[ $field['id'] ] );
							$plugin_options[ $field['id'] ] = $validation->value;

							if ( isset ( $validation->error ) ) {
								$this->parent->errors[] = $validation->error;
							}

							if ( isset ( $validation->warning ) ) {
								$this->parent->warnings[] = $validation->warning;
							}
						}

						continue;
					}
				}
				if ( isset ( $field['validate_callback'] ) && ( is_callable( $field['validate_callback'] ) || ( is_string( $field['validate_callback'] ) && function_exists( $field['validate_callback'] ) ) ) ) {
					$callback = $field['validate_callback'];
					unset ( $field['validate_callback'] );

					$plugin_option                  = isset( $plugin_options[ $options['id'] ] ) ? $plugin_options[ $options['id'] ] : null;
					$option                         = isset( $options[ $options['id'] ] ) ? $options[ $options['id'] ] : null;
					$callbackvalues                 = call_user_func( $callback, $field, $plugin_option, $option );
					$plugin_options[ $field['id'] ] = $callbackvalues['value'];

					if ( isset ( $callbackvalues['error'] ) ) {
						$this->parent->errors[] = $callbackvalues['error'];
					}
					// TODO - This warning message is failing. Hmm.
					// No it isn't.  Problem was in the sample-config - kp
					if ( isset ( $callbackvalues['warning'] ) ) {
						$this->parent->warnings[] = $callbackvalues['warning'];
					}
				}
			}

			return $plugin_options;
		}

		// Forces the use of the embeded field path vs what the core typically would use
		public function overload_field_path( $field ) {
			return dirname( __FILE__ ) . '/' . $this->field_name . '/field_' . $this->field_name . '.php';
		}

	} // class
} // if
