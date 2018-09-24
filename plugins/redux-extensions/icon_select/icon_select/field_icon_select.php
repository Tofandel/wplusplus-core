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
 * @package     ReduxFramework
 * @author      Dovy Paukstys
 * @version     1.0.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_icon_select', false ) ) {

	/**
	 * Main ReduxFramework_icon_select class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_icon_select extends ReduxFramework_extension_icon_select {

		/**
		 * Field Constructor.
		 *
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		function __construct( $field = array(), $value = '', $parent ) {

			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}

			$defaults = array(
				'options'          => array(),
				'stylesheet'       => '',
				'output'           => true,
				'prefix'           => '',
				'selector'         => '',
				'height'           => '250px',
				'enqueue'          => true,
				'enqueue_frontend' => true
			);

			$this->field = wp_parse_args( $this->field, $defaults );

			if ( empty( $this->field['options'] ) && $this->field['stylesheet'] != '' ) {

				global $wp_filesystem;

				$this->field['stylesheet']       = ReduxFramework::$_dir . 'assets/css/vendor/elusive-icons/elusive-icons.css';
				$this->field['enqueue']          = false;
				$this->field['enqueue_frontend'] = true;
				$this->field['selector']         = "el-icon-";
				$this->field['prefix']           = "el";
			}

			if ( empty( $this->field['options'] ) && ! empty( $this->field['stylesheet'] ) && ! empty( $this->field['selector'] ) ) {
				if ( stripos( $this->field['stylesheet'], "//" ) === false ) {
					// Initialize the Wordpress filesystem, no more using file_put_contents function
					if ( empty( $wp_filesystem ) ) {
						require_once( ABSPATH . '/wp-admin/includes/file.php' );
						WP_Filesystem();
					}

					$toParse              = $wp_filesystem->get_contents( $this->field['stylesheet'] );
					$this->stylesheet_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->field['stylesheet'] ) );
				} else {
					$this->stylesheet_url = $this->field['stylesheet'];
					$toParse              = wp_remote_get( $this->field['stylesheet'] );
					$toParse              = $toParse['body'];
				}

				preg_match_all( "/(" . $this->field['selector'] . ".*?):before/", $toParse, $output_array );

				foreach ( $output_array[1] as $class ) {
					$this->field['options'][ $class ] = $class;
				}
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
			if ( ! empty( $this->field['options'] ) ) {
				$style = '';

				if ( ! empty( $this->field['height'] ) ) {
					$style .= 'height: ' . $this->field['height'];

					if ( is_numeric( $this->field['height'] ) ) {
						$style .= 'px';
					}

					$style .= ';';
				}

				echo '<div class="redux-icon-container" style="' . $style . '">';
				echo '<ul class="redux-icon-select">';

				$x = 1;
				foreach ( $this->field['options'] as $k => $v ) {
					if ( ! empty( $this->field['prefix'] ) ) {
						$k = $this->field['prefix'] . ' ' . $k;
					}

					$selected = ( checked( $this->value, $k, false ) != '' ) ? ' redux-icon-select-selected' : '';

					echo '<li class="redux-icon-select">';
					echo '<label class="' . $selected . ' redux-icon-select' . $this->field['id'] . '_' . $x . '" for="' . $this->field['id'] . '_' . $k . '">';

					echo '<input type="radio" class="' . $this->field['class'] . '" id="' . $this->field['id'] . '_' . $k . '" name="' . $this->field['name'] . $this->field['name_suffix'] . '" value="' . $k . '" ' . checked( $this->value, $k, false ) . '/>';

					echo '<i title="' . $v . '" class="' . $k . '" /></i>';

					echo '</label>';
					echo '</li>';
					$x ++;
				}

				echo '</ul>';
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
			static $enqueued = false;

			//Don't enqueue more than once
			if ( $enqueued ) {
				return;
			}
			$enqueued = true;

			$min = Redux_Functions::isMin();

			wp_enqueue_script(
				'redux-field-icon-select-js',
				$this->extension_url . 'field_icon_select' . $min . '.js',
				array( 'jquery' ),
				ReduxFramework_extension_icon_select::$version,
				true
			);

			if ( function_exists( 'redux_enqueue_style' ) ) {
				redux_enqueue_style(
					$this->parent,
					'redux-field-icon-select-css',
					$this->extension_url . 'field_icon_select.css',
					$this->extension_dir,
					array(),
					ReduxFramework_extension_icon_select::$version
				);
			} else {
				wp_enqueue_style(
					'redux-field-icon-select-css',
					$this->extension_url . 'field_icon_select.css',
					ReduxFramework_extension_icon_select::$version,
					true
				);
			}

			if ( isset( $this->stylesheet_url ) && $this->field['enqueue'] ) {
				wp_register_style(
					$this->field['id'] . '-webfont',
					$this->stylesheet_url,
					array(),
					ReduxFramework_extension_icon_select::$version,
					'all'
				);

				wp_enqueue_style( $this->field['id'] . '-webfont' );
			}

		}

		/**
		 * Output Function.
		 *
		 * Used to enqueue to Webfont to the front-end
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function output() {
			if ( isset( $this->stylesheet_url ) && $this->field['enqueue_frontend'] ) {
				wp_enqueue_style(
					'redux-' . $this->field['selector'] . '-webfont',
					$this->stylesheet_url,
					array(),
					ReduxFramework_extension_icon_select::$version,
					'all'
				);
			}
		}
	}
}