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

if ( ! class_exists( 'ReduxFramework_text', false ) ) {

	class ReduxFramework_text extends Redux_Field {

		public static function makeDescriptor() {
			self::makeBaseDescriptor();
			self::$descriptor->setInfo( 'Text', __( 'A simple text field' ), ReduxFramework::$_url . '/icons/text.png' );

			//TODO maybe do something cleaner for the level
			self::$descriptor->addField( 'text_hint.title', __( "Text hint's title" ), RDT::STRING );
			self::$descriptor->addField( 'text_hint.content', __( "Text hint's title" ), RDT::STRING );
			self::$descriptor->addField( 'autocomplete', __( "Autocomplete" ), RDT::BOOL );
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since ReduxFramework 1.0.0
		 */
		public function render() {
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

			if ( isset( $this->field[ 'options' ] ) && ! empty( $this->field[ 'options' ] ) ) {
				$placeholder = '';

				if ( isset( $this->field[ 'placeholder' ] ) ) {
					$placeholder = $this->field[ 'placeholder' ];
				}

				foreach ( $this->field[ 'options' ] as $k => $v ) {
					if ( ! empty( $placeholder ) ) {
						$placeholder = ( is_array( $this->field[ 'placeholder' ] ) && isset( $this->field[ 'placeholder' ][ $k ] ) ) ? ' placeholder="' . esc_attr( $this->field[ 'placeholder' ][ $k ] ) . '" ' : '';
					}

					echo '<div class="input_wrapper">';
					echo '<label for="' . esc_attr( $this->field[ 'id' ] . '-text-' . $k ) . '">' . esc_html( $v ) . '</label> ';
					echo '<input ' . esc_attr( $qtip_title ) . esc_attr( $qtip_text ) . 'type="text" name="' . esc_attr( $this->field[ 'name' ] . $this->field[ 'name_suffix' ] . '[' . $k ) . ']' . '" ' . esc_attr( $placeholder ) . 'value="' . esc_attr( $this->value[ $k ] ) . '" class="regular-text ' . esc_attr( $this->field[ 'class' ] ) . '"' . $readonly . $autocomplete . ' /><br />';
					echo '</div>';
				}
			} else {
				$placeholder = ( isset( $this->field[ 'placeholder' ] ) && ! is_array( $this->field[ 'placeholder' ] ) ) ? ' placeholder="' . esc_attr( $this->field[ 'placeholder' ] ) . '" ' : '';

				echo '<input ' . esc_attr( $qtip_title ) . esc_attr( $qtip_text ) . 'type="text" id="' . esc_attr( $this->field[ 'id' ] ) . '" name="' . esc_attr( $this->field[ 'name' ] . $this->field[ 'name_suffix' ] ) . '" ' . esc_attr( $placeholder ) . 'value="' . esc_attr( $this->value ) . '" class="regular-text ' . esc_attr( $this->field[ 'class' ] ) . '"' . $readonly . $autocomplete . ' />';
			}
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since ReduxFramework 3.0.0
		 */
		function enqueue() {
			if ( $this->parent->args[ 'dev_mode' ] ) {
				wp_enqueue_style(
					'redux-field-text-css', ReduxCore::$_url . 'inc/fields/text/field_text.css', array(), $this->timestamp, 'all'
				);
			}
		}

	}

}