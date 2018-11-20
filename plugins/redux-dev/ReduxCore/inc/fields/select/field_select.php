<?php
/**
 * Select Box Field
 *
 * @package     ReduxFramework
 * @subpackage  Field_Section
 * @author      Dovy Paukstys & Kevin Provance (kprovance)
 * @version     3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ReduxFramework_Select', false ) ) {

	class ReduxFramework_Select extends Redux_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since ReduxFramework 1.0.0
		 */
		public function render() {
			$sortable = ( isset( $this->field['sortable'] ) && $this->field['sortable'] ) ? ' select2-sortable"' : '';

			if ( ! empty( $sortable ) ) { // Dummy proofing  :P.
				$this->field['multi'] = true;
			}

			if ( ! empty( $this->field['data'] ) && empty( $this->field['options'] ) ) {
				if ( empty( $this->field['args'] ) ) {
					$this->field['args'] = array();
				}

				if ( 'elusive-icons' === $this->field['data'] || 'elusive-icon' === $this->field['data'] || 'elusive' === $this->field['data'] ) {
					$icons_file = ReduxCore::$_dir . 'inc/fields/select/elusive-icons.php';

					/**
					 * Filter 'redux-font-icons-file}'
					 *
					 * @param  array $icon_file File for the icons
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					$icons_file = apply_filters( 'redux-font-icons-file', $icons_file );

					/**
					 * Filter 'redux/{opt_name}/field/font/icons/file'
					 *
					 * @param  array $icon_file File for the icons
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					$icons_file = apply_filters( "redux/{$this->parent->args['opt_name']}/field/font/icons/file", $icons_file );

					if ( file_exists( $icons_file ) ) {
						require_once $icons_file;
					}
				}

				$this->field['options'] = $this->parent->get_wordpress_data( $this->field['data'], $this->field['args'], $this->value );
			}

			if ( ! empty( $this->field['data'] ) && ( 'elusive-icons' === $this->field['data'] || 'elusive-icon' === $this->field['data'] || 'elusive' === $this->field['data'] ) ) {
				$this->field['class'] .= ' font-icons';
			}

			if ( ! empty( $this->field['options'] ) || ( isset( $this->field['ajax'] ) && $this->field['ajax'] ) ) {
				$multi = ( isset( $this->field['multi'] ) && $this->field['multi'] ) ? ' multiple="multiple"' : '';

				if ( ! empty( $this->field['width'] ) ) {
					$width = ' style="' . esc_attr( $this->field['width'] ) . '"';
				} else {
					$width = ' style="width: 40%;"';
				}

				$name_brackets = '';
				if ( ! empty( $multi ) ) {
					$name_brackets = '[]';
				}

				$placeholder = ( isset( $this->field['placeholder'] ) ) ? esc_attr( $this->field['placeholder'] ) : esc_html__( 'Select an item', 'redux-framework' );

				$select2_width = 'resolve';
				if ( '' !== $multi ) {
					$select2_width = '100%';
				}
				$this->select2_config['width']      = $select2_width;
				$this->select2_config['allowClear'] = true;

				if ( isset( $this->field['ajax'] ) && $this->field['ajax'] ) {
					$this->select2_config['ajax']               = true;
					$this->select2_config['minimumInputLength'] = 1;
					$this->select2_config['ajax_url']           = "?action=redux_{$this->parent->args['opt_name']}_select2";
					$this->select2_config['nonce']              = wp_create_nonce( "redux_{$this->parent->args['opt_name']}_select2" );
					$this->select2_config['data']               = $this->field['data'];
				}

				if ( isset( $this->field['select2'] ) ) {
					$this->field['select2'] = wp_parse_args( $this->field['select2'], $this->select2_config );
				} else {
					$this->field['select2'] = $this->select2_config;
				}

				$this->field['select2'] = Redux_Functions::sanitize_camel_case_array_keys( $this->field['select2'] );

				$select2_data = Redux_Functions::create_data_string( $this->field['select2'] );

				if ( isset( $this->field['multi'] ) && $this->field['multi'] && isset( $this->field['sortable'] ) && $this->field['sortable'] && ! empty( $this->value ) && is_array( $this->value ) ) {
					$orig_option            = $this->field['options'];
					$this->field['options'] = array();

					foreach ( $this->value as $value ) {
						$this->field['options'][ $value ] = $orig_option[ $value ];
					}

					if ( count( $this->field['options'] ) < count( $orig_option ) ) {
						foreach ( $orig_option as $key => $value ) {
							if ( ! in_array( $key, $this->field['options'], true ) ) {
								$this->field['options'][ $key ] = $value;
							}
						}
					}
				}

				$sortable = ( isset( $this->field['sortable'] ) && $this->field['sortable'] ) ? ' select2-sortable"' : '';

				echo '<select ' .
					esc_html( $multi ) . ' 
			        id="' . esc_attr( $this->field['id'] ) . '-select" 
			        data-placeholder="' . esc_attr( $placeholder ) . '" 
			        name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . esc_attr( $name_brackets ) . '" 
			        class="redux-select-item ' . esc_attr( $this->field['class'] ) . esc_attr( $sortable ) . '"' .
					$width . ' 
			        rows="6"' .
					esc_attr( $select2_data ) . '>'; // WPCS: XSS ok.

				echo '<option></option>';

				foreach ( $this->field['options'] as $k => $v ) {
					if ( is_array( $v ) ) {
						echo '<optgroup label="' . esc_attr( $k ) . '">';

						foreach ( $v as $opt => $val ) {
							$this->make_option( $opt, $val, $k );
						}

						echo '</optgroup>';

						continue;
					}

					$this->make_option( $k, $v );
				}

				echo '</select>';
			} else {
				echo '<strong>' . esc_html__( 'No items of this type were found.', 'redux-framework' ) . '</strong>';
			}
		}

		private function make_option( $id, $value, $group_name = '' ) {
			if ( is_array( $this->value ) ) {
				$selected = ( is_array( $this->value ) && in_array( $id, $this->value, true ) ) ? ' selected="selected"' : '';
			} else {
				$selected = selected( $this->value, $id, false );
			}

			echo '<option value="' . esc_attr( $id ) . '" ' . esc_html( $selected ) . '>' . esc_attr( $value ) . '</option>';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since ReduxFramework 1.0.0
		 */
		public function enqueue() {
			wp_enqueue_style( 'select2-css' );

			if ( isset( $this->field['sortable'] ) && $this->field['sortable'] ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
			}

			wp_enqueue_script(
				'redux-field-select-js',
				ReduxCore::$_url . 'inc/fields/select/field_select' . Redux_Functions::isMin() . '.js',
				array( 'jquery', 'select2-js', 'redux-js' ),
				$this->timestamp,
				true
			);

			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'redux-field-select-css',
					ReduxCore::$_url . 'inc/fields/select/field_select.css',
					array(),
					$this->timestamp,
					'all'
				);
			}
		}

		public function ajax_callback() {
			if ( isset( $_REQUEST['data'] ) ) {
				$options = $this->parent->get_wordpress_data( sanitize_text_field( wp_unslash( $_REQUEST['data'] ) ) ); // WPCS: CSRF ok.
			}

			echo wp_json_encode( $options );

			die();
		}
	}
}
