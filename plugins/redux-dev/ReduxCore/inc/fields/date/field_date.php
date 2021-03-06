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
 * @subpackage  Field_Date
 * @author      Dovy Paukstys
 * @author      Kevin Provance (kprovance)
 * @version     4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_Date', false ) ) {

	/**
	 * Main ReduxFramework_date class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_Date extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since         1.0.0
		 * @access        public
		 * @return        void
		 */
		public function render() {
			$placeholder = ( isset( $this->field['placeholder'] ) ) ? ' placeholder="' . $this->field['placeholder'] . '" ' : '';

			echo '<input 
					data-id="' . esc_attr( $this->field['id'] ) . '" 
					type="text" 
					id="' . esc_attr( $this->field['id'] ) . '-date" 
					name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '"' . esc_attr( $placeholder ) . '
					value="' . esc_attr( $this->value ) . '" 
					class="redux-datepicker regular-text ' . esc_attr( $this->field['class'] ) . '" />';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since         1.0.0
		 * @access        public
		 * @return        void
		 */
		public function enqueue() {
			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'redux-field-date-css',
					ReduxCore::$_url . 'inc/fields/date/field_date.css',
					array(),
					$this->timestamp,
					'all'
				);
			}

			wp_enqueue_script(
				'redux-field-date-js',
				ReduxCore::$_url . 'inc/fields/date/field_date' . Redux_Functions::isMin() . '.js',
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'redux-js' ),
				$this->timestamp,
				true
			);
		}
	}
}
