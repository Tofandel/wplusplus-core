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
 * @subpackage  Redux CSS Layout
 * @author      Kevin Provance (kprovance)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_css_layout', false ) ) {

	/**
	 * Main ReduxFramework_css_layout class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_css_layout {

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $field Field sections.
		 * @param       array $value Values.
		 * @param       array $parent Parent object.
		 *
		 * @return      void
		 */
		public function __construct( $field = array(), $value = '', $parent ) {
			// Set required variables
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			// Set extension dir & url
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
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {

			// Again, no errors, please
			$default = array(
				'margin-unit'      => 'px',
				'border-unit'      => 'px',
				'radius-unit'      => 'px',
				'padding-unit'     => 'px',
				'units'            => array( '%', 'px', 'in', 'cm', 'mm', 'em', 'rem', 'ex', 'pt', 'pc' ),
				'output-shorthand' => false
			);

			// Merge
			$this->field = wp_parse_args( $this->field, $default );

			// Default units
			$def_margin_unit  = $this->field['margin-unit'];
			$def_border_unit  = $this->field['border-unit'];
			$def_padding_unit = $this->field['padding-unit'];
			$def_radius_unit  = $this->field['radius-unit'];

			// Centralize the values
			ReduxCssLayoutFunctions::$units            = $this->field['units'];
			ReduxCssLayoutFunctions::$output_shorthand = $this->field['output-shorthand'];

			// No errors, please.
			$default = array(
				'margin-top'    => '',
				'margin-bottom' => '',
				'margin-left'   => '',
				'margin-right'  => '',

				'border-top'    => '',
				'border-bottom' => '',
				'border-left'   => '',
				'border-right'  => '',

				'padding-top'    => '',
				'padding-bottom' => '',
				'padding-left'   => '',
				'padding-right'  => '',

				'border-radius' => '',
				'border-color'  => '#ffffff',
				'border-style'  => 'solid',
			);

			// Suffix array
			$suffix_arr = array( 'top', 'right', 'bottom', 'left' );

			// Prefix array
			$prefix_arr = array(
				'margin'  => $def_margin_unit,
				'border'  => $def_border_unit,
				'padding' => $def_padding_unit
			);

			// The magic happens here
			foreach ( $prefix_arr as $key => $val ) {
				if ( isset( $this->value[ $key ] ) && ! empty( $this->value[ $key ] ) ) {
					$style_all = $this->value[ $key ];

					if ( strpos( $style_all, ' ' ) > - 1 ) {
						$style_arr = explode( ' ', $style_all );
					} else {
						$style_arr[0] = $style_all;
						$style_arr[1] = $style_all;
						$style_arr[2] = $style_all;
						$style_arr[3] = $style_all;
					}

					foreach ( $suffix_arr as $k => $v ) {
						if ( isset( $style_arr[ $k ] ) && ! empty( $style_arr[ $k ] ) ) {
							$style = isset( $this->value[ $key . '-' . $v ] ) ? $this->value[ $key . '-' . $v ] : isset( $style_arr[ $k ] ) ? $style_arr[ $k ] : '0';

							$local_unit = ReduxCssLayoutFunctions::getUnit( $style );
							$unit       = ! empty( $local_unit ) ? $local_unit : $val;

							$this->value[ $key . '-' . $v ] = ReduxCssLayoutFunctions::stripAlphas( $style ) . $unit;
						}
					}
				} else {
					$short = '';
					foreach ( $suffix_arr as $k => $v ) {
						if ( isset( $this->value[ $key . '-' . $v ] ) && ! empty( $this->value[ $key . '-' . $v ] ) ) {
							$style = $this->value[ $key . '-' . $v ];

							$local_unit                     = ReduxCssLayoutFunctions::getUnit( $style );
							$unit                           = ! empty( $local_unit ) ? $local_unit : $val;
							$this->value[ $key . '-' . $v ] = ReduxCssLayoutFunctions::stripAlphas( $style ) . $unit;
							$short                          .= $this->value[ $key . '-' . $v ] . ' ';
						} else {
							$short .= '0 ';
						}
					}
					$this->value[ $key ] = trim( $short );
				}
			}

			// Merge values
			$this->value = wp_parse_args( $this->value, $default );
			$this->saveDefaults( $this->value );

			// Validate options
			$this->field['options']['margin_enabled']  = isset( $this->field['options']['margin_enabled'] ) ? $this->field['options']['margin_enabled'] : true;
			$this->field['options']['border_enabled']  = isset( $this->field['options']['border_enabled'] ) ? $this->field['options']['border_enabled'] : true;
			$this->field['options']['padding_enabled'] = isset( $this->field['options']['padding_enabled'] ) ? $this->field['options']['padding_enabled'] : true;
			$this->field['options']['radius_enabled']  = isset( $this->field['options']['radius_enabled'] ) ? $this->field['options']['radius_enabled'] : true;
			$this->field['options']['color_enabled']   = isset( $this->field['options']['color_enabled'] ) ? $this->field['options']['color_enabled'] : true;
			$this->field['options']['style_enabled']   = isset( $this->field['options']['style_enabled'] ) ? $this->field['options']['style_enabled'] : true;

			// Validate border-color
			$this->field['default']['border-color'] = isset( $this->field['default']['border-color'] ) ? $this->field['default']['border-color'] : '#ffffff';

			// Validate placeholder
			$placeholder = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : '-';

			// Set function level variables
			$fieldName = $this->field['name'];
			$fieldID   = $this->field['id'];

			// Set select2 HTML for JS, if any
			if ( isset( $this->field['select2'] ) ) {
				$select2_params = json_encode( $this->field['select2'] );
				$select2_params = htmlspecialchars( $select2_params, ENT_QUOTES );

				echo '<input type="hidden" class="select2_params" value="' . $select2_params . '">';
			}

			// Layout Container
			echo '<div
                    class="redux-css-layout-container ' . $this->field['class'] . '"
                    id="' . $fieldID . '"
                    data-margin-unit="' . $def_margin_unit . '"
                    data-border-unit="' . $def_border_unit . '"
                    data-padding-unit="' . $def_padding_unit . '"
                    data-radius-unit="' . $def_radius_unit . '"
                    data-units="' . urlencode( json_encode( $this->field['units'] ) ) . '"
                    data-dev-mode="' . $this->parent->args['dev_mode'] . '"
                    data-version="' . ReduxFramework_extension_css_layout::$version . '"
                  >';

			// Margin div
			$margin_enabled = '';
			$margin_style   = '';

			// If margin in not enabled, grey it out
			if ( $this->field['options']['margin_enabled'] == false ) {
				$margin_enabled = 'disabled';
				$margin_style   = 'style="color: rgba(51,51,51,.5);"';
			}

			// Start margin div and apply styling, if any.
			echo '<div class="redux-css-layout-margin">';
			echo '<div class="redux-css-margin-caption" ' . $margin_style . '>margin</div>';

			// Hidden shorthand input
			echo '<input
                    type="hidden"
                    name="' . $fieldName . '[margin]"
                    value="' . $this->value['margin'] . '"
                    class="redux-css-margin-shorthand"
            />';

			echo '<input
                    type="text"
                    class="redux-css-margin redux-css-margin-top css-layout-input"
                    name="' . $fieldName . '[margin-top]"
                    id="' . $fieldID . '-margin-top"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['margin-top'], $def_margin_unit ) . '"' .
			     $margin_enabled . '
                  />';

			echo '<input
                    type="text"
                    class="redux-css-margin redux-css-margin-right css-layout-input"
                    name="' . $fieldName . '[margin-right]"
                    id="' . $fieldID . '-margin-right"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['margin-right'], $def_margin_unit ) . '"' .
			     $margin_enabled . '
                  />';

			echo '<input
                    type="text"
                    class="redux-css-margin redux-css-margin-left css-layout-input"
                    name="' . $fieldName . '[margin-left]"
                    id="' . $fieldID . '-margin-left"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['margin-left'], $def_margin_unit ) . '"' .
			     $margin_enabled . '
                  />';

			echo '<input
                    type="text"
                    class="redux-css-margin redux-css-margin-bottom css-layout-input"
                    name="' . $fieldName . '[margin-bottom]"
                    id="' . $fieldID . '-margin-bottom"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['margin-bottom'], $def_margin_unit ) . '"' .
			     $margin_enabled . '
                  />';

			// Border div
			$border_enabled = '';
			$border_style   = '';
			if ( $this->field['options']['border_enabled'] == false ) {
				$border_enabled = 'disabled';
				$border_style   = 'style="color: rgba(51,51,51,.5);"';
			}

			$div_style = '';
			if ( $this->value['border-radius'] != '' ) {
				$div_style = 'border-radius: ' . $this->value['border-radius'] . ';';
			}

			if ( $this->value['border-color'] != '' ) {
				$div_style .= 'border-color: ' . $this->value['border-color'] . ';';
			}

			if ( $this->value['border-style'] != '' ) {
				$div_style .= 'border-style: ' . $this->value['border-style'] . ';';
			}

			if ( $div_style != '' ) {
				$div_style = 'style="' . $div_style . '"';
			}

			echo '<div class="redux-css-layout-border" ' . $div_style . '">';
			echo '<div class="redux-css-border-caption"' . $border_style . '>border</div>';

			// Hidden shorthand input
			echo '<input
                    type="hidden"
                    name="' . $fieldName . '[border]"
                    value="' . $this->value['border'] . '"
                    class="redux-css-border-shorthand"
            />';

			echo '<input
                    type="text"
                    class="redux-css-border redux-css-border-top css-layout-input"
                    name="' . $fieldName . '[border-top]"
                    id="' . $fieldID . '-border-top"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['border-top'], $def_border_unit ) . '"' .
			     $border_enabled . '
                 />';

			echo '<input
                    type="text"
                    class="redux-css-border redux-css-border-bottom css-layout-input"
                    name="' . $fieldName . '[border-bottom]"
                    id="' . $fieldID . '-border-bottom"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['border-bottom'], $def_border_unit ) . '"' .
			     $border_enabled . '
                 />';

			echo '<input
                    type="text"
                    class="redux-css-border redux-css-border-left css-layout-input"
                    name="' . $fieldName . '[border-left]"
                    id="' . $fieldID . '-border-left"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['border-left'], $def_border_unit ) . '"' .
			     $border_enabled . '
                 />';

			echo '<input
                    type="text"
                    class="redux-css-border redux-css-border-right css-layout-input"
                    name="' . $fieldName . '[border-right]"
                    id="' . $fieldID . '-border-right"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['border-right'], $def_border_unit ) . '"' .
			     $border_enabled . '
                 />';

			// Padding div
			$padding_enabled = '';
			$padding_style   = '';
			if ( $this->field['options']['padding_enabled'] == false ) {
				$padding_enabled = 'disabled';
				$padding_style   = 'style="color: rgba(51,51,51,.5);"';
			}

			echo '<div class="redux-css-layout-padding">';
			echo '<div class="redux-css-padding-caption" ' . $padding_style . '>padding</div>';

			// Hidden shorthand input
			echo '<input
                    type="hidden"
                    name="' . $fieldName . '[padding]"
                    value="' . $this->value['padding'] . '"
                    class="redux-css-padding-shorthand"
            />';

			echo '<input
                    type="text"
                    class="redux-css-padding redux-css-padding-top css-layout-input"
                    name="' . $fieldName . '[padding-top]"
                    id="' . $fieldID . '-padding-top"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['padding-top'], $def_padding_unit ) . '"' .
			     $padding_enabled . '
                  />';

			echo '<input
                    type="text"
                    class="redux-css-padding redux-css-padding-bottom css-layout-input"
                    name="' . $fieldName . '[padding-bottom]"
                    id="' . $fieldID . '-padding-bottom"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['padding-bottom'], $def_padding_unit ) . '"' .
			     $padding_enabled . '
                 />';

			echo '<input
                    type="text"
                    class="redux-css-padding redux-css-padding-left css-layout-input"
                    name="' . $fieldName . '[padding-left]"
                    id="' . $fieldID . '-padding-left"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['padding-left'], $def_padding_unit ) . '"' .
			     $padding_enabled . '
                 />';

			echo '<input
                    type="text"
                    class="redux-css-padding redux-css-padding-right css-layout-input"
                    name="' . $fieldName . '[padding-right]"
                    id="' . $fieldID . '-padding-right"
                    placeholder="' . $placeholder . '"
                    value="' . ReduxCssLayoutFunctions::fixResult( $this->value['padding-right'], $def_padding_unit ) . '"' .
			     $padding_enabled . '
                 />';

			// Center block
			echo '<div class="redux-css-layout-center">';
			echo '<div class="redux-css-center-caption"><img src="' . $this->extension_url . 'img/redux_logo_x32.png' . '"></div>';

			echo '</div>';  // Close center div
			echo '</div>';  // Close padding div
			echo '</div>';  // Close border div
			echo '</div>';  // Close margin div

			// Apply HR if any of the border tools are visible
			if ( $this->field['options']['radius_enabled'] == true || $this->field['options']['color_enabled'] == true || $this->field['options']['style_enabled'] == true ) {
				echo '<hr class="redux-css-layout">';
			}

			// Open border tools div
			echo '<div class="redux-css-layout-border-style">';

			// Display radius input if it's enabled.
			if ( $this->field['options']['radius_enabled'] == true ) {

				// Set disabled style, if necessary
				$radius_style = '';
				$radius_unit  = '';
				if ( $this->field['options']['border_enabled'] == false ) {
					$radius_style = 'style="color: rgba(51,51,51,.5);"';
				}

				// Render radius input
				echo '<div class="input_wrapper">';
				echo '<label ' . $radius_style . ' class="redux-css-layout-radius-label">Border radius';
				echo '</label>';
				echo '<input
                        type="text" ' .
				     $border_enabled . '
                        name="' . $fieldName . '[border-radius]"
                        class="redux-css-layout-input-radius css-layout-input"
                        id="redux-css-layout-input-radius-' . $fieldID . '"
                        value="' . ReduxCssLayoutFunctions::fixResult( $this->value['border-radius'], $def_radius_unit ) . '"
                      />';
				echo '</div>';
			}

			// If border styles enabled...
			if ( $this->field['options']['style_enabled'] == true ) {

				// Set border styles array
				$options = array(
					'solid'  => 'Solid',
					'dashed' => 'Dashed',
					'dotted' => 'Dotted',
					'double' => 'Double',
					'groove' => 'Groove',
					'ridge'  => 'Ridge',
					'inset'  => 'Inset',
					'outset' => 'Outset',
					'none'   => 'None'
				);

				// Render the style select box
				echo '<div class="input_wrapper">';
				echo '<label ' . $radius_style . ' class="redux-css-layout-style-label">Border style';
				echo '</label>';
				echo '<select
                        original-title="' . __( 'Style', 'redux-framework' ) . '"
                        id="' . $this->field['id'] . '[border-style]"
                        name="' . $this->field['name'] . '[border-style]' . $this->field['name_suffix'] . '"
                        class="redux-style-css-layout-border" rows="6" data-id="' . $this->field['id'] . '"
                      >';

				foreach ( $options as $k => $v ) {
					echo '<option value="' . $k . '"' . selected( $this->value['border-style'], $k, false ) . '>' . $v . '</option>';
				}

				echo '</select>';
				echo '</div>';

			}

			// If border color picker is enabled, then...
			if ( $this->field['options']['color_enabled'] == true ) {

				// Render it
				echo '<div class="input_wrapper">';
				echo '<label ' . $radius_style . ' class="redux-css-layout-color-label">Border color';
				echo '</label>';
				echo '<input
                        id="' . $this->field['id'] . '-border-color"
                        name="' . $this->field['name'] . '[border-color]' . $this->field['name_suffix'] . '"
                        value="' . $this->value['border-color'] . '"
                        class="redux-color redux-color-css-layout-border redux-color-init"
                        type="text" data-default-color="' . $this->field['default']['border-color'] . '"
                      />';
				echo '</div>';
			}

			echo '</div>';
			echo '</div>';  // Close container
		}

		/**
		 * Enqueue Function.
		 *
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {
			static $enqueued = false;

			//Don't enqueue more than once
			if ( $enqueued ) {
				return;
			}
			$enqueued = true;
			// Set up min files for dev_mode = false.
			$min = Redux_Functions::isMin();

			// Field dependent JS
			wp_enqueue_script(
				'redux-field-css_layout-js',
				$this->extension_url . 'field_css_layout' . $min . '.js',
				array( 'jquery', 'wp-color-picker', 'select2-js' ),
				ReduxFramework_extension_css_layout::$version,
				true
			);

			wp_enqueue_style( 'select2-css' );

			wp_enqueue_style(
				'redux-color-picker-css',
				ReduxFramework::$_url . 'assets/css/color-picker/color-picker.css',
				array( 'wp-color-picker' ),
				ReduxFramework_extension_css_layout::$version,
				'all'
			);

			wp_enqueue_style(
				'redux-field-css_layout-css',
				$this->extension_url . 'field_css_layout.css',
				array(),
				ReduxFramework_extension_css_layout::$version,
				'all'
			);
		}

		/**
		 * getCSS.  Returns formatted CSS based on color picker table args.
		 *
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      string
		 */
		private function getCSS() {

			// No notices
			$css = '';

			// Must be an array
			if ( is_array( $this->value ) ) {

				$use_short = isset( $this->field['output-shorthand'] ) ? $this->field['output-shorthand'] : false;

				$output = '';
				// Enum array to parse values
				foreach ( $this->value as $id => $val ) {

					// Find margin values
					if ( preg_match_all( "/(margin)/is", $id, $matches ) ) {
						if ( ! empty( $val ) ) {
							if ( strtolower( $id ) !== 'margin' && $use_short == false ) {
								$output .= $id . ':' . $val . ';';
								continue;
							} else if ( strtolower( $id ) == 'margin' && $use_short == true ) {
								$output .= $id . ':' . $val . ';';
								continue;
							}
						}
					}

					// Find border values
					if ( preg_match_all( "/(border)/is", $id, $matches ) ) {
						if ( ! empty( $val ) ) {
							if ( strtolower( $id ) !== 'border' && $use_short == false ) {
								$output .= $id . ':' . $val . ';';
								continue;
							} else if ( strtolower( $id ) == 'border' && $use_short == true ) {
								$output .= $id . ':' . $val . ';';
								continue;
							}
						}
					}

					// Find padding values
					if ( preg_match_all( "/(padding)/is", $id, $matches ) ) {
						if ( ! empty( $val ) ) {
							if ( strtolower( $id ) !== 'padding' && $use_short == false ) {
								$output .= $id . ':' . $val . ';';
								continue;
							} else if ( strtolower( $id ) == 'padding' && $use_short == true ) {
								$output .= $id . ':' . $val . ';';
								continue;
							}
						}
					}
				}
			}

			return $output;
		}

		/**
		 * Output Function.
		 *
		 * Used to enqueue to the front-end
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function output() {
			if ( ! empty( $this->value ) ) {

				// Compile output data
				if ( ! empty( $this->field['output'] ) && ( true == $this->field['output'] ) ) {
					$css                     = $this->getCSS();
					$keys                    = implode( ",", $this->field['output'] );
					$this->parent->outputCSS .= $keys . "{" . $css . '}';
				}

				// Compile, um...compiler data
				if ( ! empty( $this->field['compiler'] ) && ( true == $this->field['compiler'] ) ) {
					$css                       = $this->getCSS();
					$keys                      = implode( ",", $this->field['compiler'] );
					$this->parent->compilerCSS .= $keys . "{" . $css . '}';
				}
			}
		}

		private function saveDefaults( $data ) {
			$opt_name = $this->parent->args['opt_name'];

			// Get all options from database
			$redux_options = get_option( $opt_name );

			// Append ID to variable that holds the current scheme ID data
			$redux_options[ ReduxCssLayoutFunctions::$_field_id ] = $data;

			// Save the modified settings
			update_option( $opt_name, $redux_options );
		}
	}
}