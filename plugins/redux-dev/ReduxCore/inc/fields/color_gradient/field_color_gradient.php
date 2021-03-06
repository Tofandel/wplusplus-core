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
 * @subpackage  Field_Color_Gradient
 * @author      Kevin Provance (kprovance)
 * @author      Dovy Paukstys
 * @version     4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_Color_Gradient', false ) ) {

	/**
	 * Main ReduxFramework_color_gradient class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_Color_Gradient extends Redux_Field {

		public function set_defaults() {
			// No errors please.
			$defaults = array(
				'from' => '',
				'to'   => '',
			);

			$this->value = Redux_Functions::parse_args( $this->value, $defaults );

			$defaults = array(
				'preview'        => false,
				'preview_height' => '150px',
			);

			$this->field = wp_parse_args( $this->field, $defaults );

			if ( ReduxCore::$_pro_loaded ) {
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				$this->field = apply_filters( 'redux/pro/color_gradient/field/set_defaults', $this->field );

				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				$this->value = apply_filters( 'redux/pro/color_gradient/value/set_defaults', $this->value );
			}
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			if ( ReduxCore::$_pro_loaded ) {
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				echo apply_filters( 'redux/pro/color_gradient/render/gradient_type', null ); // WPCS: XSS ok.
			}

			$mode_arr = array(
				'from',
				'to',
			);

			foreach ( $mode_arr as $idx => $mode ) {
				$uc_mode = ucfirst( $mode );

				echo '<div class="colorGradient ' . esc_html( $mode ) . 'Label">';
				echo '<strong>' . esc_html( $uc_mode . ' ' ) . '</strong>&nbsp;';
				echo '<input ';
				echo 'data-id="' . esc_attr( $this->field['id'] ) . '"';
				echo 'id="' . esc_attr( $this->field['id'] ) . '-' . esc_attr( $mode ) . '"';
				echo 'name="' . esc_attr( $this->field['name'] ) . esc_attr( $this->field['name_suffix'] ) . '[' . esc_attr( $mode ) . ']"';
				echo 'value="' . esc_attr( $this->value[ $mode ] ) . '"';
				echo 'class="color-picker redux-color redux-color-init ' . esc_attr( $this->field['class'] ) . '"';
				echo 'type="text"';
				echo 'data-default-color="' . esc_attr( $this->field['default'][ $mode ] ) . '"';

				if ( ReduxCore::$_pro_loaded ) {
					$data = array(
						'field' => $this->field,
						'index' => $mode,
					);

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					echo esc_html( apply_filters( 'redux/pro/render/color_alpha', $data ) );
				}

				echo '/>';

				echo '<input type="hidden" class="redux-saved-color" id="' . esc_attr( $this->field['id'] ) . '-' . esc_attr( $mode ) . '-saved-color" value="">';

				if ( ! isset( $this->field['transparent'] ) || false !== $this->field['transparent'] ) {
					$trans_checked = '';

					if ( 'transparent' === $this->value[ $mode ] ) {
						$trans_checked = ' checked="checked"';
					}

					echo '<label for="' . esc_attr( $this->field['id'] ) . '-' . esc_html( $mode ) . '-transparency" class="color-transparency-check">';
					echo '<input type="checkbox" class="checkbox color-transparency ' . esc_attr( $this->field['class'] ) . '" id="' . esc_attr( $this->field['id'] ) . '-' . esc_attr( $mode ) . '-transparency" data-id="' . esc_attr( $this->field['id'] ) . '-' . esc_attr( $mode ) . '" value="1"' . esc_html( $trans_checked ) . '> ' . esc_html__( 'Transparent', 'redux-framework' );
					echo '</label>';
				}

				echo '</div>';
			}

			if ( ReduxCore::$_pro_loaded ) {
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				echo apply_filters( 'redux/pro/color_gradient/render/preview', null ); // WPCS: XSS ok.

				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				echo apply_filters( 'redux/pro/color_gradient/render/extra_inputs', null ); // WPCS: XSS ok.
			}
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_script(
				'redux-field-color-gradient-js',
				ReduxCore::$_url . 'inc/fields/color_gradient/field_color_gradient' . Redux_Functions::isMin() . '.js',
				array( 'jquery', 'wp-color-picker', 'redux-js' ),
				$this->timestamp,
				true
			);

			if ( ReduxCore::$_pro_loaded ) {
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				do_action( 'redux/pro/color_gradient/enqueue' );
			}

			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style( 'redux-color-picker-css' );

				wp_enqueue_style(
					'redux-field-color_gradient-css',
					ReduxCore::$_url . 'inc/fields/color_gradient/field_color_gradient.css',
					array(),
					$this->timestamp,
					'all'
				);
			}
		}

		public function css_style( $data ) {
			if ( ReduxCore::$_pro_loaded ) {

				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				$pro_data = apply_filters( 'redux/pro/color_gradient/output', $data );

				return $pro_data;
			}
		}
	}
}
