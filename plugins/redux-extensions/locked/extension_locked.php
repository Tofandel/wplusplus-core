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
 * @author      Dovy Paukstys (dovy)
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_locked', false ) ) {


	/**
	 * Main ReduxFramework custom_field extension class
	 *
	 * @since       3.1.6
	 */
	class ReduxFramework_extension_locked extends Redux_Abstract_Extension {
		// Protected vars
		public static $version = "2.0.0";

		/**
		 * ReduxFramework_extension_locked constructor.
		 *
		 * @param ReduxFramework $parent
		 */
		public function __construct( $parent ) {
			parent::__construct( $parent, __FILE__ );

			add_filter( "redux/field/{$this->parent->args['opt_name']}/render/after", array(
				$this,
				'check_locked_field'
			), 1, 2 );

			add_filter( "redux/page/{$this->parent->args['opt_name']}/section/before", array(
				$this,
				'check_locked_section'
			) );

			add_action( "redux/page/{$this->parent->args['opt_name']}/enqueue", array( $this, 'enqueue' ) );
		}

		public function check_locked_field( $render, $field ) {
			if ( isset( $field['locked'] ) && $field['locked'] ) {
				global $pagenow;
				$message = "";
				if ( $field['locked'] !== true && $pagenow != "customize.php" ) {
					$message = $field['locked'];
				}
				$render = '<div class="redux-field-locked"><div class="redux-locked-inner' . ( empty( $message ) ? ' empty' : '' ) . '"><div class="el el-lock">&nbsp;</div>' . $message . '</div></div>' . $render;
			}

			return $render;
		}

		public function check_locked_section( $section ) {
			if ( isset( $section['locked'] ) && $section['locked'] ) {
				$message = "";
				if ( $section['locked'] !== true ) {
					$message = $section['locked'];
				}
				echo '<div class="redux-section-locked"><div class="redux-locked-inner' . ( empty( $message ) ? ' empty' : '' ) . '"><div class="el el-lock">&nbsp;</div>' . $message . '</div></div>';
			}
		}

		public function enqueue() {
			static $enqueued = false;

			//Don't enqueue more than once
			if ( $enqueued ) {
				return;
			}
			$enqueued = true;

			wp_enqueue_style(
				'redux-locked-css',
				$this->_extension_url . 'extension_locked.css',
				array(),
				self::$version,
				'all'
			);
		}

	} // class
} // if
