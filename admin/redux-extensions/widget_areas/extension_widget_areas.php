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
 * @package     Redux_Framework
 * @subpackage  Premium Extensions
 * @author      Dovy Paukstys (dovy)
 * @version     1.1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_widget_areas' ) ) {

	class ReduxFramework_extension_widget_areas {

		public static $version = "1.1.2";

		// Protected vars
		public $extension_url;
		public $extension_dir;

		// Protected vars
		protected $parent;

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $parent Redux_Options class instance
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {

			$this->parent = $parent;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			if ( ! class_exists( 'Redux_Widget_Areas' ) ) {
				require_once( dirname( __FILE__ ) . '/class.redux_widget_areas.php' );
				new Redux_Widget_Areas( $this->parent );
			}


			// Allow users to extend if they want
			do_action( 'redux/widget_areas/' . $parent->args['opt_name'] . '/construct' );

		}

	} // class

} // if
