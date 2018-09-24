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
 * @subpackage  Social Profiles
 * @subpackage  Wordpress
 * @author      Kevin Provance (kprovance)
 * @version     1.0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_social_profiles', false ) ) {


	/**
	 * Main ReduxFramework social profiles extension class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_extension_social_profiles {

		public static $version = '1.0.8';

		// Protected vars
		protected $parent;
		public $extension_url;
		public $extension_dir;
		public static $theInstance;
		public static $ext_url;
		public $field_id = '';
		public $field = array();
		public $opt_name = '';

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

			// Set parent object
			$this->parent = $parent;

			// Set extension dir
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
				self::$ext_url       = $this->extension_url;
			}

			// Set field name
			$this->field_name = 'social_profiles';

			// Set instance
			self::$theInstance = $this;

			// Adds the local field
			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				&$this,
				'overload_field_path'
			) );

			include_once( 'social_profiles/inc/defaults.php' );
			include_once( 'social_profiles/inc/class.functions.php' );

			reduxSocialProfilesFunctions::init( $parent );

			$this->field = reduxSocialProfilesFunctions::getField( $parent );
			//var_dump($this->field);
			//die();
			$this->field_id = $this->field['id'];
			$this->opt_name = $parent->args['opt_name'];

			$upload_dir = reduxSocialProfilesFunctions::$upload_dir;

			if ( ! is_dir( $upload_dir ) ) {
				$parent->filesystem->execute( 'mkdir', $upload_dir );
			}

			if ( ! class_exists( 'reduxLoadSocialWidget' ) ) {
				$enable = apply_filters( 'redux/extensions/social_profiles/' . $this->opt_name . '/widget/enable', true );

				if ( $enable ) {
					include_once( 'social_profiles/inc/widget.php' );
					new reduxLoadSocialWidget( $parent, $this->field_id );
				}
			}

			if ( ! class_exists( 'reduxSocialProfilesShortcode' ) ) {
				$enable = apply_filters( 'redux/extensions/social_profiles/' . $this->opt_name . '/shortcode/enable', true );

				if ( $enable ) {
					include_once( 'social_profiles/inc/shortcode.php' );
					new reduxSocialProfilesShortcode( $parent, $this->field_id );
				}
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			add_filter( "redux/options/{$this->parent->args['opt_name']}/defaults", array( $this, 'set_defaults' ) );
			add_action( 'redux/validate/' . $this->parent->args['opt_name'] . '/before_validation', array(
				$this,
				'save_me'
			), 0, 3 );
			add_filter( 'redux/metaboxes/save/before_validate', array( $this, 'save_me' ), 0, 3 );

			// Reset hooks
			add_action( 'redux/validate/' . $this->parent->args['opt_name'] . '/defaults', array(
				$this,
				'reset_defaults'
			), 0, 3 );
			add_action( 'redux/validate/' . $this->parent->args['opt_name'] . '/defaults_section', array(
				$this,
				'reset_defaults_section'
			), 0, 3 );

		}

		public function reset_defaults_section( $defaults = array() ) {

			$curTab = $_COOKIE['redux_current_tab'];
			$tabNum = $this->parent->field_sections['social_profiles'][ $this->field_id ];

			if ( $curTab == $tabNum ) {

				if ( ! empty( $this->field_id ) && isset( $this->parent->options_defaults[ $this->field_id ] ) /* && !empty($this->parent->options_defaults[$this->field_id]) */ ) {
					$data = reduxSocialProfilesFunctions::get_default_data();

					reduxSocialProfilesFunctions::write_data_file( $data );
				}
			}

			$defaults[ $this->field_id ] = reduxSocialProfilesFunctions::read_data_file();

			return $defaults;
		}

		public function reset_defaults( $defaults = array() ) {
			if ( ! empty( $this->field_id ) && isset( $this->parent->options_defaults[ $this->field_id ] ) /*&& !empty($this->parent->options_defaults[$this->field_id])*/ ) {
				$data = reduxSocialProfilesFunctions::get_default_data();

				reduxSocialProfilesFunctions::write_data_file( $data );

				$defaults[ $this->field_id ] = $data;
			}

			return $defaults;
		}

		public function set_defaults( $defaults = array() ) {
			if ( empty( $this->field_id ) ) {
				return $defaults;
			}

			$comp_file = reduxSocialProfilesFunctions::get_data_path();

			if ( ! file_exists( $comp_file ) ) {
				$data = reduxSocialProfilesFunctions::get_default_data();

				reduxSocialProfilesFunctions::write_data_file( $data );

				$this->parent->options[ $this->field_id ] = $data;
			}

			return $defaults;
		}

		public function save_me( $saved_options = array(), $changed_values = array() ) {

			if ( empty( $this->field ) ) {
				$this->field    = reduxSocialProfilesFunctions::getField();
				$this->field_id = $this->field['id'];
			}

			if ( ! isset( $saved_options[ $this->field_id ] ) || empty( $saved_options[ $this->field_id ] ) || ( is_array( $saved_options[ $this->field_id ] ) && $changed_values == $saved_options ) || ! array_key_exists( $this->field_id, $saved_options ) ) {
				return $saved_options;
			}

			// We'll use the reset hook instead
			if ( ! empty( $saved_options['defaults'] ) || ! empty( $saved_options['defaults-section'] ) ) {
				return $saved_options;
			}

			$first_value = reset( $saved_options[ $this->field_id ] ); // First Element's Value

			if ( isset( $first_value['data'] ) ) {
				$raw_data = $saved_options[ $this->field_id ];

				$save_data = array();

				// Enum through saved data
				foreach ( $raw_data as $id => $val ) {
					if ( is_array( $val ) ) {
						if ( ! isset( $val['data'] ) ) {
							return;
						}

						$data = json_decode( rawurldecode( $val['data'] ), true );

						$save_data[] = array(
							'id'         => $data['id'],
							'icon'       => $data['icon'],
							'enabled'    => $data['enabled'],
							'url'        => $data['url'],
							'color'      => $data['color'],
							'background' => $data['background'],
							'order'      => $data['order'],
							'name'       => $data['name'],
							'label'      => $data['label'],
						);
					}
				}

				$save_file = false;

				if ( ! isset( $old_options[ $this->field_id ] ) || ( isset( $old_options[ $this->field_id ] ) && ! empty( $old_options[ $this->field_id ] ) ) ) {
					$save_file = true;
				}

				if ( ! empty( $old_options[ $this->field_id ] ) && $saved_options[ $this->field_id ] != $old_options[ $this->field_id ] ) {
					$save_file = true;
				}

				if ( $save_file ) {
					reduxSocialProfilesFunctions::write_data_file( $save_data );
				}
				//print_r($save_data);
				//die;
				$saved_options[ $this->field_id ] = $save_data;
			}

			return $saved_options;
		}

		public function enqueue_styles() {
			// Set up min files for dev_mode = false.
			$min = Redux_Functions::isMin();

			// font-awesome
			wp_enqueue_style(
				'font-awesome',
				$this->extension_url . 'social_profiles/vendor/font-awesome' . $min . '.css',
				array(),
				ReduxFramework_extension_social_profiles::$version
			);

			// Field CSS
			wp_enqueue_style(
				'redux-field-social-profiles-frontend-css',
				$this->extension_url . 'social_profiles/css/field_social_profiles_frontend.css',
				array(),
				ReduxFramework_extension_social_profiles::$version
			);
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

if ( ! function_exists( 'redux_social_profile_value_from_id' ) ) {
	/**
	 * Returns social profile value from passed profile ID.
	 *
	 * @since       1.0.0
	 * @access      public
	 *
	 * @param       string $opt_name Redux Framework opt_name.
	 * @param       string $id Profile ID.
	 * @param       string $value Spcial profile value to return (icon, name, background, color, url, or order)
	 *
	 * @return      string Returns HTML string when $echo is set to false.  Otherwise true.
	 */
	function redux_social_profile_value_from_id( $opt_name, $id, $value ) {
		if ( empty( $opt_name ) || empty( $id ) || empty( $value ) ) {
			return;
		}

		$redux           = ReduxFrameworkInstances::get_instance( $opt_name );
		$social_profiles = $redux->extensions['social_profiles'];

		$redux_options = get_option( $social_profiles->opt_name );
		$settings      = $redux_options[ $social_profiles->field_id ];

		foreach ( $settings as $idx => $arr ) {
			if ( $arr['id'] == $id ) {
				if ( $arr['enabled'] ) {
					if ( isset( $arr[ $value ] ) ) {
						return $arr[ $value ];
					}
				} else {
					return;
				}
			}
		}
	}
}

if ( ! function_exists( 'redux_render_icon_from_id' ) ) {
	/**
	 * Renders social icon from passed profile ID.
	 *
	 * @since       1.0.0
	 * @access      public
	 *
	 * @param       string $opt_name Redux Framework opt_name.
	 * @param       string $id Profile ID.
	 * @param       boolean $echo Echos icon HTML when true.  Returns icon HTML when false
	 *
	 * @return      string Returns HTML string when $echo is set to false.  Otherwise true.
	 */
	function redux_render_icon_from_id( $opt_name, $id, $echo = true, $a_class = '' ) {
		if ( empty( $opt_name ) || empty( $id ) ) {
			return;
		}

		include_once( 'social_profiles/inc/class.functions.php' );

		$redux           = ReduxFrameworkInstances::get_instance( $opt_name );
		$social_profiles = $redux->extensions['social_profiles'];

		$redux_options = get_option( $social_profiles->opt_name );
		$settings      = $redux_options[ $social_profiles->field_id ];

		foreach ( $settings as $idx => $arr ) {
			if ( $arr['id'] == $id ) {
				if ( $arr['enabled'] ) {

					if ( $echo ) {
						echo '<a class="' . $a_class . '" href="' . $arr['url'] . '">';
						reduxSocialProfilesFunctions::render_icon( $arr['icon'], $arr['color'], $arr['background'], '', true );
						echo '</a>';

						return true;
					} else {
						$html = '<a class="' . $a_class . '"href="' . $arr['url'] . '">';

						$html .= reduxSocialProfilesFunctions::render_icon( $arr['icon'], $arr['color'], $arr['background'], '', false );
						$html .= '</a>';

						return $html;
					}
				}
			}
		}
	}
}