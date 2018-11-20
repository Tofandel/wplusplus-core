<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ReduxFramework_number', false ) ) {

	class ReduxFramework_number extends Redux_Field {
		public function __construct( array $field = array(), $value = '', $parent ) {
			parent::__construct( $field, $value, $parent );
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since ReduxFramework 1.0.0
		 */
		function render() {
			if ( ! empty( $this->field[ 'data' ] ) && empty( $this->field[ 'options' ] ) ) {
				if ( empty( $this->field[ 'args' ] ) ) {
					$this->field[ 'args' ] = array();
				}

				$this->field[ 'options' ] = $this->parent->get_wordpress_data( $this->field[ 'data' ], $this->field[ 'args' ], $this->value );
				$this->field[ 'class' ]   .= " hasOptions ";
			}

			if ( empty( $this->value ) && ! empty( $this->field[ 'data' ] ) && ! empty( $this->field[ 'options' ] ) ) {
				$this->value = $this->field[ 'options' ];
			}

			$qtip_title = isset( $this->field[ 'text_hint' ][ 'title' ] ) ? 'qtip-title="' . $this->field[ 'text_hint' ][ 'title' ] . '" ' : '';
			$qtip_text  = isset( $this->field[ 'text_hint' ][ 'content' ] ) ? 'qtip-content="' . $this->field[ 'text_hint' ][ 'content' ] . '" ' : '';

			$readonly     = ( isset( $this->field[ 'readonly' ] ) && $this->field[ 'readonly' ] ) ? ' readonly="readonly"' : '';
			$autocomplete = ( isset( $this->field[ 'autocomplete' ] ) && $this->field[ 'autocomplete' ] == false ) ? ' autocomplete="off"' : '';

			$placeholder = ( isset( $this->field[ 'placeholder' ] ) && ! is_array( $this->field[ 'placeholder' ] ) ) ? ' placeholder="' . esc_attr( $this->field[ 'placeholder' ] ) . '" ' : '';

			$max = $min = $step = '';
			if ( ! empty( $this->field[ 'max' ] ) && is_numeric( $this->field[ 'max' ] ) ) {
				$max = 'max="' . $this->field[ 'max' ] . '" "';
			}
			if ( ! empty( $this->field[ 'min' ] ) && is_numeric( $this->field[ 'min' ] ) ) {
				$min = 'min="' . $this->field[ 'min' ] . '" "';
			}
			if ( ! empty( $this->field[ 'step' ] ) && is_numeric( $this->field[ 'step' ] ) ) {
				$step = 'step="' . $this->field[ 'step' ] . '" "';
			}

			echo '<input ' . esc_attr( $qtip_title ) . esc_attr( $qtip_text ) . $max . $min . $step . 'type="number" id="' . esc_attr( $this->field[ 'id' ] ) . '" name="' . esc_attr( $this->field[ 'name' ] . $this->field[ 'name_suffix' ] ) . '" ' . esc_attr( $placeholder ) . 'value="' . esc_attr( $this->value ) . '" class="number ' . esc_attr( $this->field[ 'class' ] ) . '"' . $readonly . $autocomplete . ' />';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since ReduxFramework 3.0.0
		 */
		public function enqueue() {
		}

	}

}