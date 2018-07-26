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

			// Set extension dir & url
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			add_action( 'wp_ajax_redux_action_button', [ $this, 'do_action' ] );
		}

		public function do_action() {
			// Verify nonce
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], "redux_{$this->parent->args['opt_name']}_action_button" ) ) {
				die( 0 );
			}

			$field_id = $_POST['field_id'];
			var_dump( $this->field );
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

			// primary container
			echo <<<HTML
<div class="redux-action-button-container {$this->field['class']}" id="{$field_id}_container" data-id="{$field_id}" style="width: 0;">
HTML;

			// Button render.
			if ( isset( $this->field['buttons'] ) && is_array( $this->field['buttons'] ) ) {
				echo
				'<div class="redux-action-button-container" id="redux-action-button-container" style="display: inline-flex;">';

				foreach ( $this->field['buttons'] as $idx => $arr ) {
					$button_text  = $arr['text'];
					$button_class = $arr['class'];

					echo <<<HTML
<input id="{$field_id}_input" class="hide-if-no-js button {$button_class}" type="button" value="{$button_text}">&nbsp;&nbsp;
HTML;
				}

				echo '</div>';
			}

			// Close container
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
			// Make sure script data exists first
			if ( isset( $this->field['script'] ) && ! empty( $this->field['script'] ) ) {

				// URI location of script to enqueue
				$script_url = isset( $this->field['script']['url'] ) ? $this->field['script']['url'] : '';

				// Get deps, if any
				$script_dep = isset( $this->field['script']['dep'] ) ? $this->field['script']['dep'] : array();

				// Get ver, if any
				$script_ver = isset( $this->field['script']['ver'] ) ? $this->field['script']['ver'] : time();

				// Script location in HTML
				$script_footer = isset( $this->field['script']['in_footer'] ) ? $this->field['script']['in_footer'] : true;

				// If a script exists, enqueue it.
				if ( $script_url != '' ) {
					wp_enqueue_script(
						'redux-action-button-' . $this->field['id'] . '-js',
						$script_url,
						$script_dep,
						$script_ver,
						$script_footer
					);
				}

				if ( isset( $this->field['enqueue_ajax'] ) && $this->field['enqueue_ajax'] ) {
					wp_localize_script(
						'redux-action-button-' . $this->field['id'] . '-js',
						'redux_ajax_script',
						array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
					);
				}
			}

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
		}
	}
}