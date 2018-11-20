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
 * @subpackage  Accordion Section
 * @subpackage  Wordpress
 * @author      Kevin Provance (kprovance)
 * @version:    1.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_accordion', false ) ) {


	/**
	 * Main ReduxFramework_extension_accordion extension class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_extension_accordion extends Redux_Abstract_Extension {
		public static $version = '1.0.1';

		// Protected vars
		public static $ext_url;
		public $field_id = '';

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
			parent::__construct( $parent, __FILE__ );
			self::$ext_url = $this->_extension_url;

			$this->add_field( 'accordion' );

			// Uncomment when customizer works - kp
			//include_once($this->extension_dir . 'multi-media/inc/class.customizer.php');
			//new ReduxColorSchemeCustomizer($parent, $this->extension_dir);

		}

		static public function getExtURL() {
			return self::$ext_url;
		}
	}
}