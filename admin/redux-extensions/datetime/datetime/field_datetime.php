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
 * @package  ReduxFramework
 * @author   Kevin Provance
 * @version  1.0.4
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_datetime' ) ) {

	/**
	 * Main ReduxFramework_datetime class
	 *
	 * @since       3.1.5
	 */
	class ReduxFramework_datetime {

		/**
		 * Field Constructor.
		 *
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since        1.0.0
		 * @access        public
		 * @return        void
		 */
		function __construct( $field = array(), $value = '', $parent ) {

			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}
		}

		/**
		 * Field Render Function.
		 *
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since        1.0.0
		 * @access        public
		 * @return        void
		 */
		public function render() {

			// defaults
			$defaults = array(
				'date-format'   => 'mm-dd-yy',
				'time-format'   => 'hh:mm TT z',
				'split'         => false,
				'separator'     => ' ',
				'date-picker'   => true,
				'time-picker'   => true,
				'control-type'  => 'slider',
				'num-of-months' => 1,

				// DO NOT CHANGE THESE!!!!
				// It will make this file's javascript sister
				// cry like a deflowered virgin on prom night.
				'timezone-list' => null,
				'timezone'      => '0',
				'hour-min'      => 0,
				'hour-max'      => 23,
				'minute-min'    => 0,
				'minute-max'    => 59,
				'date-min'      => - 1,
				'date-max'      => - 1,
			);

			// Merge values
			$this->field = wp_parse_args( $this->field, $defaults );

			$num_of_months = $this->field['num-of-months'];
			if ( $num_of_months == 0 ) {
				$num_of_months = 1;
			}

			// validate min/max values
			$hour_min = $this->field['hour-min'];
			$hour_max = $this->field['hour-max'];
			$min_min  = $this->field['minute-min'];
			$min_max  = $this->field['minute-max'];

			if ( $hour_min < 0 || $hour_min > 23 ) {
				$hour_min = 0;
			}

			if ( $hour_max < 0 || $hour_max > 23 ) {
				$hour_max = 23;
			}

			if ( $min_min < 0 || $min_min > 59 ) {
				$min_min = 0;
			}

			if ( $min_max < 0 || $min_max > 59 ) {
				$min_max = 59;
			}

			// Validate min date month
			if ( is_array( $this->field['date-min'] ) ) {
				if ( isset( $this->field['date-min']['month'] ) ) {
					if ( $this->field['date-min']['month'] < 1 || $this->field['date-min']['month'] > 12 ) {
						$this->field['date-min']['month'] = 1;
					}
				}

				if ( isset( $this->field['date-min']['day'] ) ) {
					if ( $this->field['date-min']['day'] < 1 || $this->field['date-min']['day'] > 31 ) {
						$this->field['date-min']['day'] = 1;
					}
				}
			}

			// Validate max date month
			if ( is_array( $this->field['date-max'] ) ) {
				if ( isset( $this->field['date-max']['month'] ) ) {
					if ( $this->field['date-max']['month'] < 1 || $this->field['date-max']['month'] > 12 ) {
						$this->field['date-max']['month'] = 1;
					}
				}

				// Validate max date day (imperfect, so we'll just use 31)
				if ( isset( $this->field['date-max']['day'] ) ) {
					if ( $this->field['date-max']['day'] < 1 || $this->field['date-max']['day'] > 31 ) {
						$this->field['date-max']['day'] = 1;
					}
				}
			}

			// Assignment, make it eaasier to read.
			$fieldID     = $this->field['id'];
			$fieldName   = $this->field['name'];
			$split       = $this->field['split'];
			$controlType = $this->field['control-type'];

			// Sanitize width
			// Sanitize default value
			if ( true == $split ) {
				if ( ! is_array( $this->value ) ) {
					$this->value         = array();
					$this->value['time'] = '';
					$this->value['date'] = '';
				}
			} else {
				if ( is_array( $this->value ) ) {
					$this->value = '';
				}
			}

			// dummy check, in case something other than select or slider
			// is entered.
			switch ( $controlType ) {
				case 'select':
				case 'slider':

					break;
				default:
					$controlType = 'slider';
			}

			// Set placeholder based on mode
			if ( true == $split ) {
				$date_placeholder = isset( $this->field['placeholder']['date'] ) ? $this->field['placeholder']['date'] : __( 'Date', 'redux-framework' );
				$time_placeholder = isset( $this->field['placeholder']['time'] ) ? $this->field['placeholder']['time'] : __( 'Time', 'redux-framework' );
			} else {
				$date_placeholder = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : __( 'Date / Time', 'redux-framework' );
			}

			// Output defaults to div, so JS can read it.
			// Broken up for readability, coz I'm the one who has to debug it!
			echo '<div id="' . $fieldID . '" class="redux-datetime-container" ' . '
                       data-dev-mode="' . $this->parent->args['dev_mode'] . '"
                       data-version="' . ReduxFramework_extension_datetime::$version . '"
                       data-id="' . $fieldID . '" ' . '
                       data-mode="' . $split . '" ' . '
                       data-separator="' . $this->field['separator'] . '" ' . '
                       data-control-type="' . $controlType . '" ' . '
                       data-rtl="' . is_rtl() . '" ' . '
                       data-num-of-months="' . $num_of_months . '" ' . '
                       data-hour-min="' . $hour_min . '" ' . '
                       data-hour-max="' . $hour_max . '" ' . '
                       data-minute-min="' . $min_min . '" ' . '
                       data-minute-max="' . $min_max . '" ' . '
                       data-date-min="' . urlencode( json_encode( $this->field['date-min'] ) ) . '" ' . '
                       data-date-max="' . urlencode( json_encode( $this->field['date-max'] ) ) . '" ' . '
                       data-timezone="' . $this->field['timezone'] . '" ' . '
                       data-timezone-list="' . urlencode( json_encode( $this->field['timezone-list'] ) ) . '" ' . '
                       data-date-picker="' . $this->field['date-picker'] . '" ' . '
                       data-time-picker="' . $this->field['time-picker'] . '" ' . '
                       data-time-format="' . $this->field['time-format'] . '" ' . '
                       data-date-format="' . $this->field['date-format'] . '">';

			// If split mode is on, output two text boxes
			if ( true == $split ) {
				echo '<div class="redux-date-input input_wrapper">';
				echo '<label class="redux-date-input-label">' . $date_placeholder . '</label>';
				echo ' <input data-id="' . $fieldID . '" type="text" id="' . $fieldID . '-date" name="' . $fieldName . '[date]" placeholder="' . $date_placeholder . '" value="' . $this->value['date'] . '" class="redux-date-picker ' . $this->field['class'] . '" />&nbsp;&nbsp;';
				echo '</div>';

				echo '<div class="redux-time-input input_wrapper">';
				echo '<label class="redux-time-input-label">' . $time_placeholder . '</label>';
				echo ' <input data-id="' . $fieldID . '" type="text" id="' . $fieldID . '-time" name="' . $fieldName . '[time]" placeholder="' . $time_placeholder . '" value="' . $this->value['time'] . '" class="redux-time-picker ' . $this->field['class'] . '" />';
				echo '</div>';

				// Otherwise, just one.
			} else {
				echo '<div class="redux-datetime-input single_wrapper">';
				echo '<label class="redux-datetime-input-label">' . $date_placeholder . '</label>';
				echo ' <input data-id="' . $fieldID . '" type="text" id="' . $fieldID . '-date" name="' . $fieldName . '" placeholder="' . $date_placeholder . '" value="' . $this->value . '" class="redux-date-picker ' . $this->field['class'] . '" />';
				echo '</div>';
			}

			// Close da div, main!
			echo '</div>';
		}

		/**
		 * Enqueue Function.
		 *
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since        1.0.0
		 * @access        public
		 * @return        void
		 */
		public function enqueue() {

			$extension = ReduxFramework_extension_datetime::getInstance();

			$min = Redux_Functions::isMin();

			wp_enqueue_script(
				'redux-datetime-slider-js',
				$this->extension_url . 'vendor/jquery-ui-sliderAccess' . $min . '.js',
				array( 'jquery' ),
				'0.3', /* ...off would be nice! */
				true
			);

			wp_enqueue_script(
				'redux-datetime-js',
				$this->extension_url . 'vendor/jquery-ui-timepicker-addon' . $min . '.js',
				array(
					'jquery',
					'jquery-ui-datepicker',
					'jquery-ui-widget',
					'jquery-ui-slider',
					'redux-datetime-slider-js'
				),
				'1.6.3', /* ...off would be nice! */
				true
			);

			wp_enqueue_script(
				'redux-field-datetime-js',
				$this->extension_url . 'field_datetime' . $min . '.js',
				array( 'jquery', 'redux-datetime-js' ),
				ReduxFramework_extension_datetime::$version,
				true
			);

			wp_enqueue_style(
				'redux-field-datetime-css',
				$this->extension_url . 'field_datetime.css',
				array(),
				time(),
				'all'
			);
		}
	}
}