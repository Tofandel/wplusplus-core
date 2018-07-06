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
 * @subpackage  Spectrum Color Picker
 * @author      Kevin Provance (kprovance)
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_spectrum' ) ) {

	/**
	 * Main ReduxFramework_spectrum class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_spectrum {

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

			$this->field['options']['show_input']             = isset( $this->field['options']['show_input'] ) ? $this->field['options']['show_input'] : true;
			$this->field['options']['show_initial']           = isset( $this->field['options']['show_initial'] ) ? $this->field['options']['show_initial'] : true;
			$this->field['options']['show_alpha']             = isset( $this->field['options']['show_alpha'] ) ? $this->field['options']['show_alpha'] : true;
			$this->field['options']['show_palette']           = isset( $this->field['options']['show_palette'] ) ? $this->field['options']['show_palette'] : true;
			$this->field['options']['show_palette_only']      = isset( $this->field['options']['show_palette_only'] ) ? $this->field['options']['show_palette_only'] : false;
			$this->field['options']['max_palette_size']       = isset( $this->field['options']['max_palette_size'] ) ? $this->field['options']['max_palette_size'] : 10;
			$this->field['options']['show_selection_palette'] = isset( $this->field['options']['show_selection_palette'] ) ? $this->field['options']['show_selection_palette'] : true;
			$this->field['options']['allow_empty']            = isset( $this->field['options']['allow_empty'] ) ? $this->field['options']['allow_empty'] : true;
			$this->field['options']['clickout_fires_change']  = isset( $this->field['options']['clickout_fires_change'] ) ? $this->field['options']['clickout_fires_change'] : false;
			$this->field['options']['choose_text']            = isset( $this->field['options']['choose_text'] ) ? $this->field['options']['choose_text'] : 'Choose';
			$this->field['options']['cancel_text']            = isset( $this->field['options']['cancel_text'] ) ? $this->field['options']['cancel_text'] : 'Cancel';
			$this->field['options']['show_buttons']           = isset( $this->field['options']['show_buttons'] ) ? $this->field['options']['show_buttons'] : true;
			$this->field['options']['container_class']        = isset( $this->field['options']['container_class'] ) ? $this->field['options']['container_class'] : 'redux-colorpicker-container';
			$this->field['options']['replacer_class']         = isset( $this->field['options']['replacer_class'] ) ? $this->field['options']['replacer_class'] : 'redux-colorpicker-replacer';
			$this->field['options']['use_extended_classes']   = isset( $this->field['options']['use_extended_classes'] ) ? $this->field['options']['use_extended_classes'] : false;
			$this->field['options']['palette']                = isset( $this->field['options']['palette'] ) ? $this->field['options']['palette'] : null;
			$this->field['options']['input_text']             = isset( $this->field['options']['input_text'] ) ? $this->field['options']['input_text'] : '';

			// Convert empty array to null, if there.
			$this->field['options']['palette'] = empty( $this->field['options']['palette'] ) ? null : $this->field['options']['palette'];
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
			global $wp_filesystem;

			$defaults = array(
				'color' => '#000000',
				'alpha' => 1,
				'rgba'  => ''
			);

			$this->value = wp_parse_args( $this->value, $defaults );

			$field_id = $this->field['id'];

			$dev_mode = $this->parent->args['dev_mode'];

			$dev_tag = '';
			if ( true == $dev_mode ) {

				$dev_tag = ' data-dev-mode="' . $this->parent->args['dev_mode'] . '"
                            data-version="' . ReduxFramework_extension_spectrum::$version . '"';
			}

			$classes_tag = '';
			$class_css   = Redux_Helpers::cleanFilePath( get_stylesheet_directory() ) . '/redux-spectrum.css';

			if ( true == $this->field['options']['use_extended_classes'] ) {

				$container_class = $this->field['options']['container_class'];
				$replacer_class  = $this->field['options']['replacer_class'];

				if ( ! file_exists( $class_css ) ) {
					$css_data = '/* CSS file to set your Spectrum Container and Replacer CSS
Please visit http://docs.reduxframework.com/premium-extensions/spectrum 
for more information on how to use this file. 
Auto generated on ' . date( 'l jS \of F Y h:i:s A' ) . ' */
                            
.' . $container_class . ' {
    
}

.' . $replacer_class . ' {
    
}
';
					$wp_filesystem->put_contents( $class_css, $css_data, FS_CHMOD_FILE );
				}

				$classes_tag = ' data-container-class="' . $container_class . '"
                                data-replacer-class="' . $replacer_class . '"';
			} else {
				if ( file_exists( $class_css ) ) {
					unlink( $class_css );
				}
			}

			// Color picker container
			echo '<div 
                      class="redux-spectrum-container' . $this->field['class'] . '" 
                      data-id="' . $field_id . '"' .
			     $dev_tag . '
                      data-show-input="' . $this->field['options']['show_input'] . '"
                      data-show-initial="' . $this->field['options']['show_initial'] . '"
                      data-show-alpha="' . $this->field['options']['show_alpha'] . '"
                      data-show-palette="' . $this->field['options']['show_palette'] . '"
                      data-show-palette-only="' . $this->field['options']['show_palette_only'] . '"
                      data-show-selection-palette="' . $this->field['options']['show_selection_palette'] . '"
                      data-max-palette-size="' . $this->field['options']['max_palette_size'] . '"
                      data-allow-empty="' . $this->field['options']['allow_empty'] . '"
                      data-clickout-fires-change="' . $this->field['options']['clickout_fires_change'] . '"
                      data-choose-text="' . $this->field['options']['choose_text'] . '"
                      data-cancel-text="' . $this->field['options']['cancel_text'] . '"
                      data-input-text="' . $this->field['options']['input_text'] . '"
                      data-show-buttons="' . $this->field['options']['show_buttons'] . '"' .
			     $classes_tag . '
                      data-palette="' . urlencode( json_encode( $this->field['options']['palette'] ) ) . '"
                  >';

			// Colour picker layout
			$opt_name = $this->parent->args['opt_name'];

			if ( '' == $this->value['color'] ) {
				$color = '';
			} else {
				$color = 'rgba(' . Redux_Helpers::hex2rgba( $this->value['color'] ) . ',' . $this->value['alpha'] . ')';
			}

			echo '<input
                        name="' . $opt_name . '[' . $field_id . '][color]"
                        id="' . $field_id . '-color"
                        class="redux-spectrum"
                        type="text"
                        value="' . $this->value['color'] . '"
                        data-color="' . $color . '"
                        data-id="' . $field_id . '"
                        data-current-color="' . $this->value['color'] . '"
                        data-block-id="' . $field_id . '"
                      />';

			echo '<input
                        type="hidden"
                        class="redux-hidden-color"
                        data-id="' . $field_id . '-color"
                        id="' . $field_id . '-color"
                        value="' . $this->value['color'] . '"
                      />';

			// Hidden input for alpha channel
			echo '<input
                        type="hidden"
                        class="redux-hidden-alpha"
                        data-id="' . $field_id . '-alpha"
                        name="' . $opt_name . '[' . $field_id . '][alpha]' . '"
                        id="' . $field_id . '-alpha"
                        value="' . $this->value['alpha'] . '"
                      />';

			// Hidden input for rgba
			echo '<input
                        type="hidden"
                        class="redux-hidden-rgba"
                        data-id="' . $field_id . '-rgba"
                        name="' . $opt_name . '[' . $field_id . '][rgba]' . '"
                        id="' . $field_id . '-rgba"
                        value="' . $this->value['rgba'] . '"
                      />';

			echo '</div>';
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

			$extension = ReduxFramework_extension_spectrum::getInstance();

			// Set up min files for dev_mode = false.
			$min = Redux_Functions::isMin();

			// Spectrum colour picker
			wp_enqueue_script(
				'redux-spectrum-js',
				$this->extension_url . 'vendor/redux-spectrum' . $min . '.js',
				array( 'jquery' ),
				time(),
				true
			);

			// Spectrum CSS
			wp_enqueue_style(
				'redux-spectrum-css',
				$this->extension_url . 'vendor/redux-spectrum.css',
				time(),
				true
			);

			// Field dependent JS
			wp_enqueue_script(
				'redux-field-spectrum-js',
				$this->extension_url . 'field_spectrum' . $min . '.js',
				array( 'jquery', 'redux-spectrum-js' ),
				time(),
				true
			);

			// Field CSS
			if ( function_exists( 'redux_enqueue_style' ) ) {
				redux_enqueue_style(
					$this->parent,
					'redux-field-spectrum-css',
					$this->extension_url . 'field_spectrum.css',
					$this->extension_dir,
					array(),
					time()
				);
			} else {
				wp_enqueue_style(
					'redux-field-spectrum-css',
					$this->extension_url . 'field_spectrum.css',
					time(),
					true
				);
			}
//            wp_enqueue_style(
//                'redux-field-spectrum-css', 
//                $this->extension_url . 'field_spectrum.css', 
//                time(), 
//                true
//            );

			// Color picker class
			if ( true == $this->field['options']['use_extended_classes'] ) {
				$css_file = Redux_Helpers::cleanFilePath( get_stylesheet_directory() ) . '/redux-spectrum.css';
				if ( file_exists( $css_file ) ) {
					wp_enqueue_style(
						'redux-spectrum-class-css',
						Redux_Helpers::cleanFilePath( get_stylesheet_directory_uri() ) . '/redux-spectrum.css',
						time(),
						true
					);
				}
			}
		}

		/**
		 * getColorVal.  Returns formatted color val in hex or rgba.
		 *
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      private
		 * @return      string
		 */
		private function getColorVal() {

			// No notices
			$color = '';

			// Must be an array
			if ( is_array( $this->value ) ) {

				// Enum array to parse values
				foreach ( $this->value as $id => $val ) {

					// Sanitize alpha
					if ( $id == 'alpha' ) {
						$alpha = ! empty( $val ) ? $val : 1;
					} elseif ( $id == 'color' ) {
						$color = ! empty( $val ) ? $val : '';
					} elseif ( $id == 'rgba' ) {
						$rgba = ! empty( $val ) ? $val : '';
					}
				}

				// Only build rgba output if alpha ia less than 1
				if ( $alpha < 1 && $alpha <> '' ) {
					$color = $rgba;
				}
			}

			return $color;
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
				$style = '';

				$mode = ( isset( $this->field['mode'] ) && ! empty( $this->field['mode'] ) ? $this->field['mode'] : 'color' );

				$color_val = $this->getColorVal();

				$style .= $mode . ':' . $color_val . ';';

				if ( ! empty( $this->field['output'] ) && ( true == $this->field['output'] ) ) {
					$css                     = Redux_Functions::parseCSS( $this->field['output'], $style, $color_val );
					$this->parent->outputCSS .= $css;
				}

				if ( ! empty( $this->field['compiler'] ) && ( true == $this->field['compiler'] ) ) {
					$css                       = Redux_Functions::parseCSS( $this->field['compiler'], $style, $color_val );
					$this->parent->compilerCSS .= $css;
				}
			}
		}
	}
}
