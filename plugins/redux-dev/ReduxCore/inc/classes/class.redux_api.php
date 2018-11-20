<?php

/**
 * Redux Framework API Class
 * Makes instantiating a Redux object an absolute piece of cake.
 *
 * @package     Redux_Framework
 * @author      Dovy Paukstys
 * @subpackage  Core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// TODO remove all debug backtrace.
// Don't duplicate me!
if ( ! class_exists( 'Redux', false ) ) {

	/**
	 * Redux API Class
	 * Simple API for Redux Framework
	 *
	 * @since       3.0.0
	 */
	class Redux {

		/**
		 * @var array
		 */
		public static $fields = array();
		/**
		 * @var array
		 */
		public static $sections = array();
		/**
		 * @var array
		 */
		public static $options_defaults = array();
		/**
		 * @var array
		 */
		public static $help = array();
		/**
		 * @var array
		 */
		public static $args = array();
		/**
		 * @var array
		 */
		public static $priority = array();
		/**
		 * @var array
		 */
		public static $errors = array();
		/**
		 * @var array
		 */
		public static $init = array();
		/**
		 * @var array
		 */
		public static $extensions = array();
		/**
		 * @var array
		 */
		public static $uses_extensions = array();
		/**
		 * @var array
		 */
		public static $google_fonts = array();
		/**
		 * @var array
		 */
		public static $extension_paths = array();

		/**
		 * @param $closure
		 * @param $args
		 *
		 * @return mixed
		 */
		public function __call( $closure, $args ) {
			return call_user_func_array( $this->{$closure}->bindTo( $this ), $args );
		}

		/**
		 * @return mixed
		 */
		public function __toString() {
			return call_user_func( $this->{'__toString'}->bindTo( $this ) );
		}

		/**
		 *
		 */
		public static function load() {
			add_action( 'after_setup_theme', array( 'Redux', 'createRedux' ) );
			add_action( 'init', array( 'Redux', 'createRedux' ) );
			add_action( 'switch_theme', array( 'Redux', 'createRedux' ) );

			if ( version_compare( PHP_VERSION, '5.5.0', '<' ) ) {
				include_once ReduxCore::$_dir . 'inc/lib/array_column.php';
			}
		}

		/**
		 * @param string $opt_name
		 */
		public static function init( $opt_name = '' ) {
			if ( ! empty( $opt_name ) ) {
				self::loadRedux( $opt_name );
				remove_action( 'setup_theme', array( 'Redux', 'createRedux' ) );
			}
		}

		/**
		 * @param $opt_name
		 *
		 * @return object|ReduxFramework
		 */
		public static function instance( $opt_name ) {
			return Redux_Instances::get_instance( $opt_name );
		}

		/**
		 * @return array|ReduxFramework[]
		 */
		public static function all_instances() {
			return Redux_Instances::get_all_instances();
		}

		/**
		 * @param $redux_framework
		 */
		public static function loadExtensions( $redux_framework ) {
			if ( $instance_extensions = self::getExtensions( $redux_framework->args['opt_name'], "" ) ) {
				foreach ( $instance_extensions as $name => $extension ) {
					if ( ! class_exists( $extension['class'] ) ) {
						// In case you wanted override your override, hah.
						$extension['path'] = apply_filters( 'redux/extension/' . $redux_framework->args['opt_name'] . '/' . $name, $extension['path'] );
						if ( file_exists( $extension['path'] ) ) {
							require_once $extension['path'];
						}
					}
					if ( ! isset( $redux_framework->extensions[ $name ] ) ) {
						if ( class_exists( $extension['class'] ) ) {
							$redux_framework->extensions[ $name ] = new $extension['class']( $redux_framework );
						} else {
							echo '<div id="message" class="error"><p>No class named <strong>' . esc_html( $extension['class'] ) . '</strong> exists. Please verify your extension path.</p></div>';
						}
					}
				}
			}
		}

		/**
		 * @param      $extension
		 * @param      bool $folder
		 *
		 * @return bool|mixed
		 */
		public static function extensionPath( $extension, $folder = true ) {
			if ( ! isset( self::$extensions[ $extension ] ) ) {
				return false;
			}

			$path = end( self::$extensions[ $extension ] );

			if ( ! $folder ) {
				return $path;
			}

			return str_replace( 'extension_' . $extension . '.php', '', $path );
		}

		/**
		 * @param string $opt_name
		 */
		public static function loadRedux( $opt_name = "" ) {
			if ( empty( $opt_name ) ) {
				return;
			}

			if ( ! class_exists( 'ReduxFramework' ) ) {
				if ( isset( self::$init[ $opt_name ] ) && ! empty( self::$init[ $opt_name ] ) ) {
					return;
				}

				self::$init[ $opt_name ] = 1;

				// Try to load the class if in the same directory, so the user only have to include the Redux API.
				if ( ! class_exists( 'Redux_Options_Defaults' ) ) {
					$file_check = trailingslashit( dirname( __FILE__ ) ) . 'class.redux_options_defaults.php';
					if ( file_exists( dirname( $file_check ) ) ) {
						include_once $file_check;
						$file_check = trailingslashit( dirname( __FILE__ ) ) . 'class.redux_wordpress_data.php';
						if ( file_exists( dirname( $file_check ) ) ) {
							include_once $file_check;
						}
					}
				}

				if ( class_exists( 'Redux_Options_Defaults' ) ) {
					$sections                            = self::constructSections( $opt_name );
					$wordpress_data                      = ( ! class_exists( 'Redux_WordPress_Data' ) ) ? null : new Redux_WordPress_Data( $opt_name );
					$options_defaults_class              = new Redux_Options_Defaults();
					self::$options_defaults[ $opt_name ] = $options_defaults_class->default_values( $opt_name, $sections, $wordpress_data );
					if ( '' === self::$args[ $opt_name ]['global_variable'] && false !== self::$args[ $opt_name ]['global_variable'] ) {
						self::$args[ $opt_name ]['global_variable'] = str_replace( '-', '_', $opt_name );
					}
					if ( self::$args[ $opt_name ]['global_variable'] ) {
						$option_global = self::$args[ $opt_name ]['global_variable'];

						/**
						 * Filter 'redux/options/{opt_name}/global_variable'
						 *
						 * @param array $value option value to set global_variable with
						 */
						global $$option_global;

						$$option_global = apply_filters( 'redux/options/' . $opt_name . '/global_variable', self::$options_defaults[ $opt_name ] );
					}

					return;
				} else {
					// TODO: Translate.
					echo '<div id="message" class="error"><p>Redux Framework is <strong>not installed</strong>. Please install it.</p></div>';

					return;
				}
			}

			$check = self::instance( $opt_name );

			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(	__FUNCTION__, array_column( debug_backtrace(), 'function' ), true	) ]['file']
			);

			if ( isset( $check->api_has_run ) ) {
				return;
			}

			$args     = self::constructArgs( $opt_name );
			$sections = self::constructSections( $opt_name );

			if ( isset( self::$uses_extensions[ $opt_name ] ) && ! empty( self::$uses_extensions[ $opt_name ] ) ) {
				add_action( "redux/extensions/{$opt_name}/before", array( 'Redux', 'loadExtensions' ), 0 );
			}

			$redux                   = new ReduxFramework( $sections, $args );
			$redux->api_has_run      = 1;
			self::$init[ $opt_name ] = 1;

			if ( isset( $redux->args['opt_name'] ) && $redux->args['opt_name'] !== $opt_name ) {
				self::$init[ $redux->args['opt_name'] ] = 1;
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $key
		 * @param string $default
		 */
		public static function get_option( $opt_name = '', $key = '', $default = '' ) {

		}

		/**
		 *
		 */
		public static function createRedux() {
			foreach ( self::$sections as $opt_name => $the_sections ) {
				if ( ! empty( $the_sections ) ) {
					if ( ! self::$init[ $opt_name ] ) {
						self::loadRedux( $opt_name );
					}
				}
			}
		}

		/**
		 * @param $opt_name
		 *
		 * @return array|mixed
		 */
		public static function constructArgs( $opt_name ) {
			$args             = isset( self::$args[ $opt_name ] ) ? self::$args[ $opt_name ] : array();
			$args['opt_name'] = $opt_name;

			if ( ! isset( $args['menu_title'] ) ) {
				$args['menu_title'] = ucfirst( $opt_name ) . ' Options';
			}

			if ( ! isset( $args['page_title'] ) ) {
				$args['page_title'] = ucfirst( $opt_name ) . ' Options';
			}

			if ( ! isset( $args['page_slug'] ) ) {
				$args['page_slug'] = $opt_name . '_options';
			}

			return $args;
		}

		/**
		 * @param $opt_name
		 *
		 * @return array
		 */
		public static function constructSections( $opt_name ) {
			$sections = array();

			if ( ! isset( self::$sections[ $opt_name ] ) ) {
				return $sections;
			}

			foreach ( self::$sections[ $opt_name ] as $section_id => $section ) {
				$section['fields'] = self::constructFields( $opt_name, $section_id );
				$p                 = $section['priority'];

				while ( isset( $sections[ $p ] ) ) {
					$p ++;
				}

				$sections[ $p ] = $section;
			}

			ksort( $sections );

			return $sections;
		}

		/**
		 * @param string $opt_name
		 * @param string $section_id
		 *
		 * @return array
		 */
		public static function constructFields( $opt_name = '', $section_id = '' ) {
			$fields = array();

			if ( ! empty( self::$fields[ $opt_name ] ) ) {
				foreach ( self::$fields[ $opt_name ] as $key => $field ) {
					if ( $field['section_id'] === $section_id ) {
						$p = esc_html( $field['priority'] );

						while ( isset( $fields[ $p ] ) ) {
							echo intval( $p ++ );
						}

						$fields[ $p ] = $field;
					}
				}
			}

			ksort( $fields );

			return $fields;
		}

		/**
		 * @param string $opt_name
		 * @param string $id
		 *
		 * @return bool
		 */
		public static function getSection( $opt_name = '', $id = '' ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				if ( ! isset( self::$sections[ $opt_name ][ $id ] ) ) {
					$id = strtolower( sanitize_html_class( $id ) );
				}

				return isset( self::$sections[ $opt_name ][ $id ] ) ? self::$sections[ $opt_name ][ $id ] : false;
			}

			return false;
		}

		/**
		 * @param string $opt_name
		 * @param array  $sections
		 */
		public static function setSections( $opt_name = '', $sections = array() ) {
			if ( empty( $sections ) ) {
				return;
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(	__FUNCTION__, array_column( debug_backtrace(), 'function' ), true	) ]['file']	);

			if ( ! empty( $sections ) ) {
				foreach ( $sections as $section ) {
					self::setSection( $opt_name, $section );
				}
			}
		}

		/**
		 * @param string $opt_name
		 *
		 * @return array|mixed
		 */
		public static function getSections( $opt_name = '' ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( self::$sections[ $opt_name ] ) ) {
				return self::$sections[ $opt_name ];
			}

			return array();
		}

		/**
		 * @param string $opt_name
		 * @param string $id
		 * @param bool   $fields
		 */
		public static function removeSection( $opt_name = '', $id = '', $fields = false ) {
			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				if ( isset( self::$sections[ $opt_name ][ $id ] ) ) {
					$priority = '';

					foreach ( self::$sections[ $opt_name ] as $key => $section ) {
						if ( $key === $id ) {
							$priority = $section['priority'];
							self::$priority[ $opt_name ]['sections'] --;
							unset( self::$sections[ $opt_name ][ $id ] );
							continue;
						}
						if ( '' !== $priority ) {
							$new_priority                        = $section['priority'];
							$section['priority']                 = $priority;
							self::$sections[ $opt_name ][ $key ] = $section;
							$priority                            = $new_priority;
						}
					}

					if ( isset( self::$fields[ $opt_name ] ) && ! empty( self::$fields[ $opt_name ] ) && true === $fields ) {
						foreach ( self::$fields[ $opt_name ] as $key => $field ) {
							if ( $field['section_id'] === $id ) {
								unset( self::$fields[ $opt_name ][ $key ] );
							}
						}
					}
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param array  $section
		 */
		public static function setSection( $opt_name = '', $section = array() ) {
			if ( empty( $section ) ) {
				return;
			}
			self::check_opt_name( $opt_name );
			self::record_caller( $opt_name, debug_backtrace()[ array_search(__FUNCTION__, array_column(	debug_backtrace(), 'function' ), true	) ]['file']	);

			if ( ! isset( $section['id'] ) ) {
				if ( isset( $section['type'] ) && 'divide' === $section['type'] ) {
					$section['id'] = time();
				} else {
					if ( isset( $section['title'] ) ) {
						$section['id'] = strtolower( sanitize_title( $section['title'] ) );
					} else {
						$section['id'] = time();
					}
				}

				if ( isset( self::$sections[ $opt_name ][ $section['id'] ] ) ) {
					$orig = $section['id'];
					$i    = 0;

					while ( isset( self::$sections[ $opt_name ][ $section['id'] ] ) ) {
						$section['id'] = $orig . '_' . $i;
					}
				}
			}

			if ( ! empty( $opt_name ) && is_array( $section ) && ! empty( $section ) ) {
				if ( ! isset( $section['id'] ) && ! isset( $section['title'] ) ) {
					// TODO:  translate.
					self::$errors[ $opt_name ]['section']['missing_title'] = 'Unable to create a section due to missing id and title.';

					return;
				}

				if ( ! isset( $section['priority'] ) ) {
					$section['priority'] = self::getPriority( $opt_name, 'sections' );
				}

				if ( isset( $section['fields'] ) ) {
					if ( ! empty( $section['fields'] ) && is_array( $section['fields'] ) ) {
						self::processFieldsArray( $opt_name, $section['id'], $section['fields'] );
					}
					unset( $section['fields'] );
				}

				self::$sections[ $opt_name ][ $section['id'] ] = $section;
			} else {
				self::$errors[ $opt_name ]['section']['empty'] = "Unable to create a section due an empty section array or the section variable passed was not an array.";

				return;
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $id
		 * @param bool   $hide
		 */
		public static function hideSection( $opt_name = '', $id = '', $hide = true ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				if ( isset( self::$sections[ $opt_name ][ $id ] ) ) {
					self::$sections[ $opt_name ][ $id ]['hidden'] = $hide;
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $section_id
		 * @param array  $fields
		 */
		public static function processFieldsArray( $opt_name = "", $section_id = "", $fields = array() ) {
			if ( ! empty( $opt_name ) && ! empty( $section_id ) && is_array( $fields ) && ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					if ( ! is_array( $field ) ) {
						continue;
					}

					$field['section_id'] = $section_id;
					self::setField( $opt_name, $field );
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $id
		 *
		 * @return bool
		 */
		public static function getField( $opt_name = '', $id = '' ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				return isset( self::$fields[ $opt_name ][ $id ] ) ? self::$fields[ $opt_name ][ $id ] : false;
			}

			return false;
		}

		/**
		 * @param string $opt_name
		 * @param string $id
		 * @param bool   $hide
		 */
		public static function hideField( $opt_name = '', $id = '', $hide = true ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				if ( isset( self::$fields[ $opt_name ][ $id ] ) ) {
					if ( ! $hide ) {
						self::$fields[ $opt_name ][ $id ]['class'] = str_replace( 'hidden', '', self::$fields[ $opt_name ][ $id ]['class'] );
					} else {
						self::$fields[ $opt_name ][ $id ]['class'] .= 'hidden';
					}
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param array  $field
		 */
		public static function setField( $opt_name = '', $field = array() ) {
			if ( empty( $field ) ) {
				return;
			}
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && is_array( $field ) && ! empty( $field ) ) {
				if ( ! isset( $field['priority'] ) ) {
					$field['priority'] = self::getPriority( $opt_name, 'fields' );
				}

				if ( isset( $field['id'] ) ) {
					self::$fields[ $opt_name ][ $field['id'] ] = $field;
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $id
		 *
		 * @return bool
		 */
		public static function removeField( $opt_name = '', $id = '' ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				if ( isset( self::$fields[ $opt_name ][ $id ] ) ) {
					foreach ( self::$fields[ $opt_name ] as $key => $field ) {
						if ( $key == $id ) {
							$priority = $field['priority'];
							self::$priority[ $opt_name ]['fields'] --;
							unset( self::$fields[ $opt_name ][ $id ] );
							continue;
						}

						if ( isset( $priority ) && $priority != "" ) {
							$newPriority                       = $field['priority'];
							$field['priority']                 = $priority;
							self::$fields[ $opt_name ][ $key ] = $field;
							$priority                          = $newPriority;
						}
					}
				}
			}

			return false;
		}

		/**
		 * @param string $opt_name
		 * @param array  $tab
		 */
		public static function setHelpTab( $opt_name = "", $tab = array() ) {
			if ( empty( $tab ) ) {
				return;
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(
				__FUNCTION__, array_column(
					debug_backtrace(), 'function'
				)
			) ]['file']
			);

			if ( ! empty( $opt_name ) && ! empty( $tab ) ) {
				if ( ! isset( self::$args[ $opt_name ]['help_tabs'] ) ) {
					self::$args[ $opt_name ]['help_tabs'] = array();
				}

				if ( isset( $tab['id'] ) ) {
					self::$args[ $opt_name ]['help_tabs'][] = $tab;
				} else if ( is_array( end( $tab ) ) ) {
					foreach ( $tab as $tab_item ) {
						self::$args[ $opt_name ]['help_tabs'][] = $tab_item;
					}
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $content
		 */
		public static function setHelpSidebar( $opt_name = "", $content = "" ) {
			if ( empty( $content ) ) {
				return;
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(
				__FUNCTION__, array_column(
					debug_backtrace(), 'function'
				)
			) ]['file']
			);

			if ( ! empty( $opt_name ) && ! empty( $content ) ) {
				self::$args[ $opt_name ]['help_sidebar'] = $content;
			}
		}

		/**
		 * @param string $opt_name
		 * @param array  $args
		 */
		public static function setArgs( $opt_name = "", $args = array() ) {
			if ( empty( $args ) ) {
				return;
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(
				__FUNCTION__, array_column(
					debug_backtrace(), 'function'
				)
			) ]['file']
			);

			if ( ! empty( $opt_name ) && ! empty( $args ) && is_array( $args ) ) {
				if ( isset( self::$args[ $opt_name ] ) && isset( self::$args[ $opt_name ]['clearArgs'] ) ) {
					self::$args[ $opt_name ] = array();
				}
				self::$args[ $opt_name ] = wp_parse_args( $args, self::$args[ $opt_name ] );
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $arg
		 */
		public static function setDeveloper( $opt_name = "", $arg = '' ) {
			if ( empty( $arg ) ) {
				return;
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(
				__FUNCTION__, array_column(
					debug_backtrace(), 'function'
				)
			) ]['file']
			);

			if ( ! empty( $opt_name ) && ! empty( $args ) ) {
				self::$args[ $opt_name ]['developer'] = $arg;
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $caller
		 */
		public static function record_caller( $opt_name = "", $caller = "" ) {
			if ( ! empty( $caller ) && ! empty( $opt_name ) && class_exists( "ReduxCore" ) ) {
				if ( ! isset( ReduxCore::$_callers[ $opt_name ] ) ) {
					ReduxCore::$_callers[ $opt_name ] = array();
				}
				if ( strpos( $caller, 'class.redux_api.php' ) !== false ) {
					return;
				}
				if ( ! in_array( $caller, ReduxCore::$_callers[ $opt_name ] ) ) {
					ReduxCore::$_callers[ $opt_name ][] = $caller;
				}
			}

			if ( ! empty( self::$args[ $opt_name ]['callers'] ) && ! in_array( $caller, self::$args[ $opt_name ]['callers'] ) ) {
				self::$args[ $opt_name ]['callers'][] = $caller;
			}
		}

		/**
		 * @param string $opt_name
		 *
		 * @return mixed
		 */
		public static function getArgs( $opt_name = "" ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( self::$args[ $opt_name ] ) ) {
				return self::$args[ $opt_name ];
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $key
		 */
		public static function getArg( $opt_name = "", $key = "" ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $key ) && ! empty( self::$args[ $opt_name ] ) ) {
				return self::$args[ $opt_name ][ $key ];
			} else {
				return;
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $key
		 *
		 * @return mixed
		 */
		public static function getOption( $opt_name = "", $key = "" ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && ! empty( $key ) ) {
				$redux = get_option( $opt_name );

				if ( isset( $redux[ $key ] ) ) {
					return $redux[ $key ];
				} else {
					return null;
				}
			} else {
				return null;
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $key
		 * @param string $option
		 *
		 * @return bool
		 */
		public static function setOption( $opt_name = "", $key = "", $option = "" ) {
			if ( empty( $key ) ) {
				return false;
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(
				__FUNCTION__, array_column(
					debug_backtrace(), 'function'
				)
			) ]['file']
			);

			if ( ! empty( $opt_name ) && ! empty( $key ) ) {
				$redux         = get_option( $opt_name );
				$redux[ $key ] = $option;

				return update_option( $opt_name, $redux );
			} else {
				return false;
			}
		}

		/**
		 * @param $opt_name
		 * @param $type
		 *
		 * @return mixed
		 */
		public static function getPriority( $opt_name, $type ) {
			$priority                             = self::$priority[ $opt_name ][ $type ];
			self::$priority[ $opt_name ][ $type ] += 1;

			return $priority;
		}

		/**
		 * @param string $opt_name
		 */
		public static function check_opt_name( $opt_name = "" ) {

			if ( empty( $opt_name ) || is_array( $opt_name ) ) {
				return;
			}
			if ( ! isset( self::$sections[ $opt_name ] ) ) {
				self::$sections[ $opt_name ]             = array();
				self::$priority[ $opt_name ]['sections'] = 1;
			}
			if ( ! isset( self::$args[ $opt_name ] ) ) {
				self::$args[ $opt_name ]             = array();
				self::$priority[ $opt_name ]['args'] = 1;
			}
			if ( ! isset( self::$fields[ $opt_name ] ) ) {
				self::$fields[ $opt_name ]             = array();
				self::$priority[ $opt_name ]['fields'] = 1;
			}
			if ( ! isset( self::$help[ $opt_name ] ) ) {
				self::$help[ $opt_name ]             = array();
				self::$priority[ $opt_name ]['help'] = 1;
			}
			if ( ! isset( self::$errors[ $opt_name ] ) ) {
				self::$errors[ $opt_name ] = array();
			}
			if ( ! isset( self::$init[ $opt_name ] ) ) {
				self::$init[ $opt_name ] = false;
			}
		}

		/**
		 * Retrieve metadata from a file. Based on WP Core's get_file_data function
		 *
		 * @since 2.1.1
		 *
		 * @param string $file Path to the file
		 *
		 * @return string
		 */
		public static function getFileVersion( $file ) {
			$data = get_file_data( $file, array( 'version' ), 'plugin' );

			return $data[0];
		}

		/**
		 * @param        $opt_name
		 * @param string $name
		 * @param string $class_file
		 * @param string $instance
		 */
		public static function checkExtensionClassFile( $opt_name, $name = "", $class_file = "", $instance = "" ) {
			if ( file_exists( $class_file ) ) {
				self::$uses_extensions[ $opt_name ] = isset( self::$uses_extensions[ $opt_name ] ) ? self::$uses_extensions[ $opt_name ] : array();
				if ( ! in_array( $name, self::$uses_extensions[ $opt_name ] ) ) {
					self::$uses_extensions[ $opt_name ][] = $name;
				}
				self::$extensions[ $name ] = isset( self::$extensions[ $name ] ) ? self::$extensions[ $name ] : array();
				$version                   = Redux_Helpers::get_template_version( $class_file );
				if ( empty( $version ) && ! empty( $instance ) ) {
					if ( isset( $instance->version ) ) {
						$version = $instance->version;
					}
				}
				self::$extensions[ $name ][ $version ] = isset( self::$extensions[ $name ][ $version ] ) ? self::$extensions[ $name ][ $version ] : $class_file;
				$api_check                             = str_replace( 'extension_' . $name, $name . '_api', $class_file );
				if ( file_exists( $api_check ) && ! class_exists( 'Redux_' . ucfirst( $name ) ) ) {
					include_once( $api_check );
				}
			}
		}

		/**
		 * @param $opt_name
		 * @param $path
		 */
		public static function setExtensions( $opt_name, $path ) {
			if ( empty( $path ) ) {
				return;
			}
			if ( version_compare( PHP_VERSION, '5.5.0', '<' ) ) {
				include_once ReduxCore::$_dir . 'inc/lib/array-column.php';
			}
			self::check_opt_name( $opt_name );
			self::record_caller(
				$opt_name, debug_backtrace()[ array_search(
				__FUNCTION__, array_column(
					debug_backtrace(), 'function'
				)
			) ]['file']
			);

			if ( is_dir( $path ) ) {
				$path   = trailingslashit( $path );
				$folder = str_replace( '.php', '', basename( $path ) );
				if ( file_exists( $path . 'extension_' . $folder . '.php' ) ) {
					self::checkExtensionClassFile( $opt_name, $folder, $path . 'extension_' . $folder . '.php' );
				} else {
					$folders = scandir( $path, 1 );
					foreach ( $folders as $folder ) {
						if ( $folder[0] === '.' ) {
							continue;
						}
						if ( file_exists( $path . $folder . '/extension_' . $folder . '.php' ) ) {
							self::checkExtensionClassFile( $opt_name, $folder, $path . $folder . '/extension_' . $folder . '.php' );
						} else if ( is_dir( $path . $folder ) ) {
							self::setExtensions( $opt_name, $path . $folder );
							continue;
						}
					}
				}
			} else if ( file_exists( $path ) ) {
				$name = explode( 'extension_', basename( $path ) );
				if ( isset( $name[1] ) && ! empty( $name[1] ) ) {
					$name = str_replace( '.php', '', $name[1] );
					self::checkExtensionClassFile( $opt_name, $name, $path );
				}
			}
			self::$extension_paths[ $opt_name ] = $path;
		}

		/**
		 *
		 */
		public static function getAllExtensions() {
			$redux = self::all_instances();

			foreach ( $redux as $instance ) {
				if ( ! empty( self::$uses_extensions[ $instance['args']['opt_name'] ] ) ) {
					continue;
				}
				if ( ! empty( $instance['extensions'] ) ) {
					Redux::getInstanceExtensions( $instance['args']['opt_name'], $instance );
				}
			}
		}

		/**
		 * @param       $opt_name
		 * @param array $instance
		 */
		public static function getInstanceExtensions( $opt_name, $instance = array() ) {
			if ( ! empty( self::$uses_extensions[ $opt_name ] ) ) {
				return;
			}

			if ( empty( $instance ) ) {
				$instance = self::instance( $opt_name );
			}

			if ( empty( $instance ) || empty( $instance->extensions ) ) {
				return;
			}

			foreach ( $instance->extensions as $name => $extension ) {
				if ( $name == "widget_areas" ) {
					$new = new Redux_Widget_Areas( $instance );
				}

				if ( isset( self::$uses_extensions[ $opt_name ][ $name ] ) ) {
					continue;
				}

				if ( isset( $extension->extension_dir ) ) {
					Redux::setExtensions( $opt_name, str_replace( $name, '', $extension->extension_dir ) );
				} else if ( isset( $extension->_extension_dir ) ) {
					Redux::setExtensions( $opt_name, str_replace( $name, '', $extension->_extension_dir ) );
				}
			}
		}

		/**
		 * @param string $opt_name
		 * @param string $key
		 *
		 * @return array|bool|mixed
		 */
		public static function getExtensions( $opt_name = "", $key = "" ) {
			if ( empty( $opt_name ) ) {
				Redux::getAllExtensions();

				if ( empty( $key ) ) {
					return self::$extension_paths;
				} else {
					if ( isset( self::$extension_paths[ $key ] ) ) {
						return self::$extension_paths[ $key ];
					}
				}
			} else {
				if ( empty( self::$uses_extensions[ $opt_name ] ) ) {
					Redux::getInstanceExtensions( $opt_name );
				}

				if ( empty( self::$uses_extensions[ $opt_name ] ) ) {
					return false;
				}

				$instance_extensions = array();

				foreach ( self::$uses_extensions[ $opt_name ] as $extension ) {
					$class_file                       = end( self::$extensions[ $extension ] );
					$name                             = str_replace( '.php', '', basename( $extension ) );
					$extension_class                  = 'ReduxFramework_Extension_' . $name;
					$instance_extensions[ $extension ] = array(
						'path'    => $class_file,
						'class'   => $extension_class,
						'version' => Redux_Helpers::get_template_version( $class_file )
					);
				}

				return $instance_extensions;
			}

			return false;
		}

		/**
		 *
		 */
		public static function disable_demo() {
			add_action( 'redux/loaded', 'Redux::remove_demo' );
		}

		/**
		 *
		 */
		public static function remove_demo() {
			if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
				call_user_func( 'remove' . '_filter', 'plugin_row_meta', array(
					ReduxFrameworkPlugin::instance(),
					'plugin_metalinks'
				), null, 2 );

				remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
			}
		}

	}

	Redux::load();
}