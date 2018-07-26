<?php

/**
 * @package     Redux Framework
 * @subpackage  JS Button
 * @author      Kevin Provance (kprovance)
 * @version     1.0.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_action_button' ) ) {

	/**
	 * Main ReduxFramework_action_button class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_action_button {
		public $parent;
		public $field;
		public $value;
		public $extension_url;
		public $extension_dir;

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $field Field sections.
		 * @param       mixed $value Values.
		 * @param       ReduxFramework $parent Parent object.
		 *
		 * @return      void
		 */
		public function __construct( $field = array(), $value = '', $parent ) {

			// Set required variables
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;


			if ( empty( $this->field['buttons'] ) ) {
				$this->field['buttons'] = array(
					array(
						'id'       => $field['id'],
						'text'     => $field['title'],
						'function' => $field['function']
					)
				);
			}

			// Set extension dir & url
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			add_action( 'wp_ajax_redux_action_button_' . $field['id'], [ $this, 'do_action' ] );
		}

		public function do_action() {
			// Verify nonce
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], "redux_{$this->parent->args['opt_name']}_action_button" ) ) {
				die( 0 );
			}

			$button_id = $_POST['button_id'];
			var_dump( $this->field );

			die();
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
			$field_id = $this->field['id'];

			// Button render.
			if ( ! empty( $this->field['buttons'] ) && is_array( $this->field['buttons'] ) ) {
				// primary container
				echo <<<HTML
<div class="redux-action-button-container {$this->field['class']}" id="{$field_id}_container" data-id="{$field_id}" style="display: inline-flex;">
HTML;
				foreach ( $this->field['buttons'] as $idx => $arr ) {
					$button_id    = $arr['id'];
					$button_text  = $arr['text'];
					$button_class = isset( $arr['class'] ) ? $arr['class'] : '';

					echo <<<HTML
<button id="redux_{$button_id}_button" class="hide-if-no-js button redux-action-button {$button_class}" type="button" data-id="{$button_id}">{$button_text}</button>&nbsp;&nbsp;
HTML;
				}

				// Close container
				echo '</div>';
			}
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
			// Set up min files for dev_mode = false.
			$min = Redux_Functions::isMin();

			// Field dependent JS
			wp_enqueue_script(
				'redux-field-action-button-js',
				apply_filters( "redux/action_button/{$this->parent->args['opt_name']}/enqueue/redux-field-action-button-js", $this->extension_url . 'field_action_button' . $min . '.js' ),
				array( 'jquery' ),
				time(),
				true
			);
			wp_localize_script(
				'redux-field-action-button-js',
				'redux_ajax_script',
				array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
			);
		}
	}
}