<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package  ReduxFramework
 * @author   Lee Mason (leemason)
 * @version  1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_support_faq', false ) ) {

	/**
	 * Main ReduxFramework_support_faq class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_support_faq {

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since         1.0.0
		 * @access        public
		 *
		 * @param array $field
		 * @param string $value
		 * @param $parent
		 */
		function __construct( $field = array(), $value = '', $parent ) {

			//parent::__construct( $parent->sections, $parent->args );
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since         1.0.0
		 * @access        public
		 * @return        void
		 */
		public function render() {

			// defaults
			$defaults = array(
				'raw'            => array(),
				'json'           => array(
					'expiration' => DAY_IN_SECONDS,
					'source'     => array(),
				),
				'rss'            => array(
					'expiration' => DAY_IN_SECONDS,
					'source'     => array(),
				),
				'compiled_json'  => array(),
				'compiled_rss'   => array(),
				'force_no_cache' => false
			);

			// Merge values
			$this->field = wp_parse_args( $this->field, $defaults );


			if ( $this->field['json']['source'] != '' && ! is_array( $this->field['json']['source'] ) || is_array( $this->field['json']['source'] ) && ! empty( $this->field['json']['source'] ) ) {
				$cache = get_transient( 'redux-json-' . $this->field['id'] );
				if ( $cache && $this->field['force_no_cache'] == false ) {
					$this->field['compiled_json'] = $cache;
				} else {
					//get json
					if ( ! is_array( $this->field['json']['source'] ) ) {
						$response = wp_remote_get( $this->field['json']['source'] );
						if ( ! is_wp_error( $response ) ) {
							$this->field['compiled_json'] = json_decode( $response['body'], true );
							set_transient( 'redux-json-' . $this->field['id'], $this->field['compiled_json'] );
						}
					} else {
						$content = array();
						foreach ( $this->field['json']['source'] as $url ) {
							$response = wp_remote_get( $url );
							if ( ! is_wp_error( $response ) ) {
								$content = array_merge( $content, json_decode( $response['body'], true ) );
							}
						}
						$this->field['compiled_json'] = $content;
						set_transient( 'redux-json-' . $this->field['id'], $content );
					}
				}
			}


			if ( $this->field['rss']['source'] != '' && ! is_array( $this->field['rss']['source'] ) || is_array( $this->field['rss']['source'] ) && ! empty( $this->field['rss']['source'] ) ) {
				$cache = get_transient( 'redux-rss-' . $this->field['id'] );
				if ( $cache && $this->field['force_no_cache'] == false ) {
					$this->field['compiled_rss'] = $cache;
				} else {
					include_once( ABSPATH . WPINC . '/feed.php' );
					//get rss
					if ( ! is_array( $this->field['rss']['source'] ) ) {
						$rss = fetch_feed( $this->field['rss']['source'] );
						if ( ! is_wp_error( $rss ) ) {
							$rss_items = $rss->get_items( 0, 99999 );
							$content   = array();
							foreach ( $rss_items as $item ) {
								$content[ $item->get_title() ] = $item->get_content();
							}
							$this->field['compiled_rss'] = $content;
							set_transient( 'redux-rss-' . $this->field['id'], $this->field['compiled_rss'] );
						}
					} else {
						$content = array();
						foreach ( $this->field['rss']['source'] as $url ) {
							$rss = fetch_feed( $url );
							if ( ! is_wp_error( $rss ) ) {
								$rss_items = $rss->get_items( 0, 99999 );
								foreach ( $rss_items as $item ) {
									$content[ $item->get_title() ] = $item->get_content();
								}
							}
						}
						$this->field['compiled_rss'] = $content;
						set_transient( 'redux-rss-' . $this->field['id'], $content );
					}
				}
			}


			// Assignment, make it eaasier to read.
			$fieldID = $this->field['id'];


			// Output defaults to div, so JS can read it.
			// Broken up for readability, coz I'm the one who has to debug it!
			echo '<fieldset id="' . $fieldID . '" class="redux-support-faq-container"><div class="support-faq-accordion">';

			if ( ! empty( $this->field['raw'] ) ) {
				foreach ( $this->field['raw'] as $question => $answer ) {
					echo '<h3>' . $question . '</h3>';
					echo '<div class="support-faq-answer">' . $answer . '</div>';
				}
			}
			if ( ! empty( $this->field['compiled_json'] ) ) {
				foreach ( $this->field['compiled_json'] as $question => $answer ) {
					echo '<h3>' . $question . '</h3>';
					echo '<div class="support-faq-answer">' . $answer . '</div>';
				}
			}
			if ( ! empty( $this->field['compiled_rss'] ) ) {
				foreach ( $this->field['compiled_rss'] as $question => $answer ) {
					echo '<h3>' . $question . '</h3>';
					echo '<div class="support-faq-answer">' . $answer . '</div>';
				}
			}

			// Close da div, main!
			echo '</div></fieldset>';
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since         1.0.0
		 * @access        public
		 * @return        void
		 */
		public function enqueue() {
			static $enqueued = false;

			//Don't enqueue more than once
			if ( $enqueued ) {
				return;
			}
			$enqueued = true;
			wp_enqueue_script(
				'support-faq-js',
				$this->extension_url . 'field_support_faq.js',
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-accordion' ),
				ReduxFramework_extension_support_faq::$version,
				true
			);

			if ( function_exists( 'redux_enqueue_style' ) ) {
				redux_enqueue_style(
					$this->parent,
					'redux-field-support-faq-css',
					$this->extension_url . 'field_support_faq.css',
					$this->extension_dir,
					array(),
					ReduxFramework_extension_support_faq::$version
				);
			} else {
				wp_enqueue_style(
					'redux-field-support-faq-css',
					$this->extension_url . 'field_support_faq.css',
					array(),
					ReduxFramework_extension_support_faq::$version,
					true
				);
			}
		}
	}
}
