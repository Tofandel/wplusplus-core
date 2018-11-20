<?php
/**
 * Textarea Field
 *
 * @package     Redux Framework
 * @subpackage  Textarea Field
 * @author      Dovy Paukstys & Kevin Provance (kprovance)
 * @version     4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ReduxFramework_Textarea', false ) ) {

	class ReduxFramework_Textarea extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since ReduxFramework 1.0.0
		 * */
		public function render() {
			$this->field['placeholder'] = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : '';
			$this->field['rows']        = isset( $this->field['rows'] ) ? $this->field['rows'] : 6;

			$readonly = ( isset( $this->field['readonly'] ) && $this->field['readonly'] ) ? ' readonly="readonly"' : '';
			?>
			<textarea <?php echo esc_html( $readonly ); ?>
					name="<?php echo esc_attr( $this->field['name'] . $this->field['name_suffix'] ); ?>"
					id="<?php echo esc_attr( $this->field['id'] ); ?>-textarea"
					placeholder="<?php echo esc_attr( $this->field['placeholder'] ); ?>"
					class="large-text <?php echo esc_attr( $this->field['class'] ); ?>"
					rows="<?php echo esc_attr( $this->field['rows'] ); ?>"><?php echo esc_textarea( $this->value ); ?></textarea>
			<?php
		}

		public function sanitize( $field, $value ) {
			if ( ! isset( $value ) || empty( $value ) ) {
				$value = '';
			} else {
				$value = esc_textarea( $value );
			}

			return $value;
		}
	}
}
