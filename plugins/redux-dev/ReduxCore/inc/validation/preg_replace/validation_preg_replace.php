<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Validation_preg_replace', false ) ) {

	class Redux_Validation_preg_replace extends Redux_Validate {

		/**
		 * Field Validate Function.
		 * Takes the vars and validates them
		 *
		 * @since ReduxFramework 1.0.0
		 */
		function validate() {
			$that = $this;
			$this->value            = preg_replace( $this->field['preg']['pattern'], $that->field['preg']['replacement'], $this->value );
			$this->field['current'] = $this->value;

			$this->sanitize = $this->field;
		}
	}
}