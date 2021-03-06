<?php
/**
 * Basic Palette Field
 *
 * @package     ReduxFramework
 * @subpackage  Field_Palette
 * @author      Kevin Provance (kprovance)
 * @version     4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ReduxFramework_Palette', false ) ) {

	class ReduxFramework_Palette extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settingss
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			if ( empty( $this->field['palettes'] ) ) {
				echo 'No palettes have been set.';

				return;
			}

			echo '<div id="' . esc_attr( $this->field['id'] ) . '" class="buttonset">';

			foreach ( $this->field['palettes'] as $value => $color_set ) {
				$checked = checked( $this->value, $value, false );

				echo '<input 
						type="radio" 
						value="' . esc_attr( $value ) . '" 
						name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '" 
						class="redux-palette-set ' . esc_attr( $this->field['class'] ) . '" 
						id="' . esc_attr( $this->field['id'] . '-' . $value ) . '"' . esc_html( $checked ) . '>';

				echo '<label for="' . esc_attr( $this->field['id'] . '-' . $value ) . '">';

				foreach ( $color_set as $color ) {
					echo '<span style=background:' . esc_attr( $color ) . '>' . esc_attr( $color ) . '</span>';
				}

				echo '</label>';
				echo '</input>';
			}

			echo '</div>';
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
			$min = Redux_Functions::isMin();

			wp_enqueue_script(
				'redux-field-palette-js',
				ReduxCore::$_url . 'inc/fields/palette/field_palette' . $min . '.js',
				array( 'jquery', 'redux-js', 'jquery-ui-button', 'jquery-ui-core' ),
				$this->timestamp,
				true
			);

			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'redux-field-palette-css',
					ReduxCore::$_url . 'inc/fields/palette/field_palette.css',
					array(),
					$this->timestamp,
					'all'
				);
			}
		}
	}
}
