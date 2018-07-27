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
 * @subpackage  JS Button
 * @subpackage  Wordpress
 * @author      Kevin Provance (kprovance)
 * @version     1.0.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_action_button' ) ) {


	/**
	 * Main ReduxFramework_extension_multi_media extension class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_extension_action_button {

		public static $version = '1.0.3';

		// Protected vars
		protected $parent;
		public $extension_url;
		public $extension_dir;
		public static $theInstance;
		public static $ext_url;
		private $sections;

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

			// Set parent object
			$this->parent = $parent;

			// Set extension dir
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
				self::$ext_url       = $this->extension_url;
			}

			// Set field name
			$this->field_name = 'action_button';

			// Set instance
			self::$theInstance = $this;

			$this->setAjaxHooks( $this->parent->sections );

			// Adds the local field
			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				&$this,
				'overload_field_path'
			) );
		}

		public function do_action() {
			// Verify nonce
			$field_id  = $_POST['field_id'];
			$button_id = $_POST['button_id'];
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], "redux_{$this->parent->args['opt_name']}_action_button_" . $field_id ) ) {
				die( - 1 );
			}
			header( 'Content-Type: application/json' );

			if ( isset( $this->sections[ $field_id ] ) ) {
				$section = $this->sections[ $field_id ];
				foreach ( $section['buttons'] as $button ) {
					if ( $button['id'] != $button_id ) {
						continue;
					}
					$msg = '';
					if ( is_callable( $button['function'] ) ) {
						$msg = call_user_func( $button['function'] );
					} elseif ( is_string( $button['function'] ) ) {
						do_action( $button['function'] );
					}
					$msg = $msg ?: $button['message'];
					if ( empty( $msg ) ) {
						global $WPlusPlusCore;
						$msg = sprintf( __( 'Action %s has been executed', $WPlusPlusCore->getTextDomain() ), $button['id'] );
					}
					die( json_encode( array( 'success' => true, 'message' => $msg ) ) );
				}
			}

			die( json_encode( array( 'success' => false ) ) );
		}

		private function setAjaxHooks( $sections ) {
			if ( is_array( $sections ) ) {
				foreach ( $sections as $section ) {
					if ( isset( $section['type'] ) && $section['type'] == $this->field_name ) {
						if ( empty( $section['buttons'] ) ) {
							$section['buttons'] = array(
								array(
									'id'       => $section['id'],
									'text'     => $section['title'],
									'function' => $section['function']
								)
							);
						}
						$this->sections[ $section['id'] ] = $section;
						add_action( 'wp_ajax_redux_action_button_' . $section['id'], [ $this, 'do_action' ] );
					}
					if ( isset( $section['fields'] ) ) {
						$this->setAjaxHooks( $section['fields'] );
					}
				}
			}
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