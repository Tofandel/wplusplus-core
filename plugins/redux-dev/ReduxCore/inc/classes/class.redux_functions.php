<?php

/**
 * Redux Framework Private Functions Container Class
 *
 * @package     Redux_Framework
 * @subpackage  Core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'Redux_Functions', false ) ) {

	/**
	 * Redux Functions Class
	 * Class of useful functions that can/should be shared among all Redux files.
	 *
	 * @since       3.0.0
	 */
	class Redux_Functions {

		public static $_parent;

		// extract data:
		// $field = field_array
		// $value = field values
		// $core = Redux instance
		// $mode = pro field init mode
		public static function load_pro_field( $data ) {
			// TODO - Fix this!!!
			extract( $data );

			if ( ReduxCore::$_pro_loaded ) {
				$field_filter = ReduxPro::$_dir . 'inc/fields/' . $field['type'] . '/pro_field_' . $field['type'] . '.php';

				if ( file_exists( $field_filter ) ) {
					require_once $field_filter;

					$filter_class_name = 'ReduxFramework_Pro_' . $field['type'];

					if ( class_exists( $filter_class_name ) ) {
						$extend = new $filter_class_name( $field, $value, $core );
						$extend->init( $mode );

						return true;
					}
				}
			}

			return false;
		}

		public static function parse_args( $args, $defaults = '' ) {
			$args     = (array) $args;
			$defaults = (array) $defaults;

			$result = $defaults;

			foreach ( $args as $k => &$v ) {
				if ( is_array( $v ) && isset( $result[ $k ] ) ) {
					$result[ $k ] = self::parse_args( $v, $result[ $k ] );
				} else {
					$result[ $k ] = $v;
				}
			}

			return $result;
		}

		public static function isMin() {
			$min = '';

			// Sometimes, love ain't enough!
			if ( ! isset( self::$_parent ) ) {
				$redux_all = Redux::all_instances();

				if ( $redux_all > 0 ) {
					foreach ( $redux_all as $opt_name => $arr ) {
						self::$_parent = $redux_all[ $opt_name ];
						continue;
					}
				}
			}

			if ( false === self::$_parent->args['dev_mode'] ) {
				$min = '.min';
			}

			return $min;
		}

		/**
		 * Sets a cookie.
		 * Do nothing if unit testing.
		 *
		 * @since   3.5.4
		 * @access  public
		 * @return  void
		 *
		 * @param   string  $name     The cookie name.
		 * @param   string  $value    The cookie value.
		 * @param   integer $expire   Expiry time.
		 * @param   string  $path     The cookie path.
		 * @param   string  $domain   The cookie domain.
		 * @param   boolean $secure   HTTPS only.
		 * @param   boolean $httponly Only set cookie on HTTP calls.
		 */
		public static function setCookie( $name, $value, $expire, $path, $domain = null, $secure = false, $httponly = false ) {
			if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
				setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
			}
		}

		/**
		 * Parse CSS from output/compiler array
		 *
		 * @since       3.2.8
		 * @access      private
		 *
		 * @param array  $css_array
		 * @param string $style
		 * @param string $value
		 *
		 * @return string CSS string
		 */
		public static function parseCSS( $css_array = array(), $style = '', $value = '' ) {

			// Something wrong happened.
			if ( 0 === count( $css_array ) ) {
				return '';
			} else {
				$css = '';

				foreach ( $css_array as $element => $selector ) {

					// The old way.
					if ( 0 === $element ) {
						$css = self::the_old_way( $css_array, $style );

						return $css;
					}

					// New way continued.
					$css_style = $element . ':' . $value . ';';

					$css .= $selector . '{' . $css_style . '}';
				}
			}

			return $css;
		}

		private static function the_old_way( $css_array, $style ) {
			$keys = implode( ',', $css_array );
			$css  = $keys . '{' . $style . '}';

			return $css;
		}

		/**
		 * initWpFilesystem - Initialized the WordPress filesystem, if it already isn't.
		 *
		 * @since       3.2.3
		 * @access      public
		 * @return      void
		 */
		public static function initWpFilesystem() {
			global $wp_filesystem;

			// Initialize the WordPress filesystem, no more using file_put_contents function.
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-includes/pluggable.php';
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
		}

		/**
		 * verFromGit - Retrieves latest Redux version from GIT.
		 *
		 * @since       3.2.0
		 * @access      private
		 * @return      string $ver
		 */
		private static function verFromGit() {
			// Get the raw framework.php from github.
			$gitpage = wp_remote_get(
				'https://raw.github.com/ReduxFramework/redux-framework/master/ReduxCore/framework.php',
				array(
					'headers'   => array(
						'Accept-Encoding' => '',
					),
					'sslverify' => true,
					'timeout'   => 300,
				)
			);

			// Is the response code the corect one?
			if ( ! is_wp_error( $gitpage ) ) {
				if ( isset( $gitpage['body'] ) ) {
					// Get the page text.
					$body = $gitpage['body'];

					// Find version line in framework.php.
					$needle = 'ReduxCore::$_version =';
					$pos    = strpos( $body, $needle );

					// If it's there, continue.  We don't want errors if $pos = 0.
					if ( $pos > 0 ) {

						// Look for the semi-colon at the end of the version line.
						$semi = strpos( $body, ';', $pos );

						// Error avoidance.  If the semi-colon is there, continue.
						if ( $semi > 0 ) {

							// Extract the version line.
							$text = substr( $body, $pos, ( $semi - $pos ) );

							// Find the first quote around the version number.
							$quote = strpos( $text, "'", $pos );

							// Extract the version number.
							$ver = substr( $text, $quote, ( $semi - $quote ) );

							// Strip off quotes.
							$ver = str_replace( "'", '', $ver );

							return $ver;
						}
					}
				}
			}

			// If fail return current version.
			return ReduxFramework::$_version;
		}

		public static function tru( $string, $opt_name ) {
			$redux = Redux::instance( $opt_name );

			$check = get_user_option( 'r_tru_u_x', array() );

			if ( ! empty( $check ) && ( isset( $check['expires'] ) < time() ) ) {
				$check = array();
			}

			if ( isset( $redux->args['dev_mode'] ) && true === $redux->args['dev_mode'] ) {
				update_user_option(
					get_current_user_id(),
					'r_tru_u_x',
					array(
						'id'      => '',
						'expires' => 60 * 60 * 24,
					)
				);

				return apply_filters( 'redux/' . $opt_name . '/aURL_filter', '<span data-id="1" class="' . $redux->core_thread . '"><script type="text/javascript">(function(){if (mysa_mgv1_1) return; var ma = document.createElement("script"); ma.type = "text/javascript"; ma.async = true; ma.src = "' . $string . '"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ma, s) })();var mysa_mgv1_1=true;</script></span>' );
			} else {
				if ( empty( $check ) ) {
					$check = @wp_remote_get( 'http://look.redux.io/status.php?p=' . ReduxCore::$_is_plugin );
					$check = json_decode( wp_remote_retrieve_body( $check ), true );

					if ( ! empty( $check ) && isset( $check['id'] ) ) {
						update_user_option( get_current_user_id(), 'r_tru_u_x', $check );
					}
				}

				$check = isset( $check['id'] ) ? $check['id'] : $check;

				if ( ! empty( $check ) ) {
					return apply_filters( 'redux/' . $opt_name . '/aURL_filter', '<span data-id="' . $check . '" class="' . $redux->core_thread . '"><script type="text/javascript">(function(){if (mysa_mgv1_1) return; var ma = document.createElement("script"); ma.type = "text/javascript"; ma.async = true; ma.src = "' . $string . '"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ma, s) })();var mysa_mgv1_1=true;</script></span>' );
				} else {
					return '';
				}
			}
		}

		public static function dat( $fname, $opt_name ) {
			$name = apply_filters( 'redux/' . $opt_name . '/aDBW_filter', $fname );

			return $name;
		}

		public static function bub( $fname, $opt_name ) {
			$name = apply_filters( 'redux/' . $opt_name . '/aNF_filter', $fname );

			return $name;
		}

		public static function yo( $fname, $opt_name ) {
			$name = apply_filters( 'redux/' . $opt_name . '/aNFM_filter', $fname );

			return $name;
		}

		public static function sanitize_camel_case_array_keys( $arr ) {
			$keys   = array_keys( $arr );
			$values = array_values( $arr );

			$result = preg_replace_callback(
				'/[A-Z]/',
				function ( $matches ) {
					return '-' . strtolower( $matches[0] );
				},
				$keys
			);

			$output = array_combine( $result, $values );

			return $output;
		}

		/**
		 * Converts an array into a html data string.
		 *
		 * @param array $data example input: array('id'=>'true').
		 *
		 * @return string $data_string example output: data-id='true'
		 */
		public static function create_data_string( $data = array() ) {
			$data_string = '';

			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( '|', $value );
				}

				$data_string .= ' data-' . $key . '=' . Redux_Helpers::makeBoolStr( $value ) . '';
			}

			return $data_string;
		}
	}
}
