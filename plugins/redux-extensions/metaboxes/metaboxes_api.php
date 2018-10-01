<?php

/**
 * Redux Framework API Class
 * Makes instantiating a Redux object an absolute piece of cake.
 *
 * @package     Redux_Framework
 * @author      Dovy Paukstys
 * @subpackage  Core
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'Redux_Metaboxes', false ) ) {

	/**
	 * Redux Metaboxes API Class
	 * Simple API for Redux Framework
	 *
	 * @since       1.0.0
	 */
	class Redux_Metaboxes {

		public static $boxes = array();
		public static $sections = array();
		public static $fields = array();
		public static $priority = array();
		public static $errors = array();
		public static $init = array();
		public static $hasRun = false;
		public static $args = array();

		public static function load() {
			add_action( 'init', 'Redux_Metaboxes::_enqueue' );
		}

		public static function _enqueue() {
			// Check and run instances of Redux where the opt_name hasn't been run.
			global $pagenow;
			$pagenows = array( 'post-new.php', 'post.php' );
			if ( ! empty( self::$sections ) && in_array( $pagenow, $pagenows ) ) {
				$instances = ReduxFrameworkInstances::get_all_instances();
				foreach ( self::$fields as $opt_name => $fields ) {
					if ( ! isset( $instances[ $opt_name ] ) ) {
						Redux::setArgs( $opt_name, array( 'menu_type' => 'hidden' ) );
						Redux::setSections( $opt_name, array(
							array(
								'id'     => 'EXTENSION_FAKE_ID' . $opt_name,
								'fields' => $fields,
								'title'  => 'N/A'
							)
						) );
						Redux::setExtensions( $opt_name, dirname( __DIR__ ) );
						Redux::init( $opt_name );
					}
				}
				$instances = ReduxFrameworkInstances::get_all_instances();
				foreach ( $instances as $opt_name => $instance ) {
					add_action( 'admin_enqueue_scripts', array( $instance, '_enqueue' ), 1 );
				}
				//self::filterMetaboxes();
			}
		}


		public static function filterMetaboxes() {
			if ( self::$hasRun == true ) {
				return;
			}
			if ( ! class_exists( 'ReduxFramework' ) ) {
				echo '<div id="message" class="error"><p>Redux Framework is <strong>not installed</strong>. Please install it.</p></div>';

				return;
			}
			foreach ( self::$boxes as $opt_name => $theBoxes ) {
				if ( ! self::$init[ $opt_name ] ) {
					add_filter( 'redux/metaboxes/' . $opt_name . '/boxes', function ( $boxes ) use ( $opt_name, $theBoxes ) {
						$boxes[ $opt_name ] = $theBoxes;

						return $boxes;
					} );
				}
			}
			self::$hasRun = true;
		}


		public static function constructArgs( $opt_name ) {
			$args             = self::$args[ $opt_name ];
			$args['opt_name'] = $opt_name;
			if ( ! isset( $args['menu_title'] ) ) {
				$args['menu_title'] = ucfirst( $opt_name ) . ' Options';
			}
			if ( ! isset( $args['page_title'] ) ) {
				$args['page_title'] = ucfirst( $opt_name ) . ' Options';
			}
			if ( ! isset( $args['page_slug'] ) ) {
				$args['page_slug'] = $opt_name . '_options';
			}

			return $args;
		}

		public static function constructBoxes( $opt_name ) {

			$boxes = array();
			if ( ! isset( self::$boxes[ $opt_name ] ) ) {
				return $boxes;
			}

			foreach ( self::$boxes[ $opt_name ] as $box_id => $box ) {
				$box['sections'] = self::constructSections( $opt_name, $box['id'] );
				$boxes[]         = $box;
			}
			ksort( $boxes );

			return $boxes;
		}

		public static function constructSections( $opt_name, $box_id ) {
			$sections = array();
			if ( ! isset( self::$sections[ $opt_name ] ) ) {
				return $sections;
			}

			foreach ( self::$sections[ $opt_name ] as $section_id => $section ) {
				if ( $section['box_id'] == $box_id ) {
					$p = $section['priority'];
					while ( isset( $sections[ $p ] ) ) {
						$p ++;
					}
					$section['fields'] = self::constructFields( $opt_name, $section_id );
					$sections[ $p ]    = $section;
				}

			}

			ksort( $sections );

			return $sections;
		}

		public static function constructFields( $opt_name = "", $section_id = "" ) {
			$fields = array();
			if ( ! isset( self::$fields[ $opt_name ] ) ) {
				return $fields;
			}

			foreach ( self::$fields[ $opt_name ] as $key => $field ) {
				if ( $field['section_id'] == $section_id ) {
					$p = $field['priority'];
					while ( isset( $fields[ $p ] ) ) {
						$p ++;
					}
					$fields[ $p ] = $field;
				}
			}

			ksort( $fields );

			return $fields;
		}

		public static function getSection( $opt_name = '', $id = '' ) {
			self::check_opt_name( $opt_name );
			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				if ( ! isset( self::$sections[ $opt_name ][ $id ] ) ) {
					$id = strtolower( sanitize_html_class( $id ) );
				}

				return isset( self::$sections[ $opt_name ][ $id ] ) ? self::$sections[ $opt_name ][ $id ] : false;
			}

			return false;
		}

		public static function setSection( $opt_name = '', $section = array() ) {
			self::check_opt_name( $opt_name );


			if ( ! empty( $opt_name ) && is_array( $section ) && ! empty( $section ) ) {

				if ( ! isset( $section['id'] ) ) {
					if ( isset( $section['title'] ) ) {
						$section['id'] = strtolower( sanitize_html_class( $section['title'] ) );
					} else {
						$section['id'] = "section";
					}

					if ( isset( self::$sections[ $opt_name ][ $section['id'] ] ) ) {
						$orig = $section['id'];
						$i    = 0;
						while ( isset( self::$sections[ $opt_name ][ $section['id'] ] ) ) {
							$section['id'] = $orig . '_' . $i;
						}
					}
				}

				if ( ! isset( $section['priority'] ) ) {
					$section['priority'] = self::getPriority( $opt_name, 'sections' );
				}


				if ( isset( $section['fields'] ) ) {
					if ( ! empty( $section['fields'] ) && is_array( $section['fields'] ) ) {
						self::processFieldsArray( $opt_name, $section['id'], $section['fields'] );
					}
					unset( $section['fields'] );
				}
				self::$sections[ $opt_name ][ $section['id'] ] = $section;

			} else {
				self::$errors[ $opt_name ]['section']['empty'] = "Unable to create a section due an empty section array or the section variable passed was not an array.";

				return;
			}
		}

		public static function processSectionsArray( $opt_name = "", $box_id = "", $sections = array() ) {
			if ( ! empty( $opt_name ) && ! empty( $box_id ) && is_array( $sections ) && ! empty( $sections ) ) {
				foreach ( $sections as $section ) {
					if ( ! is_array( $section ) ) {
						continue;
					}
					$section['box_id'] = $box_id;
					if ( ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
						$section['fields'] = array();
					}

					self::setSection( $opt_name, $section );
				}
			}
		}

		public static function processFieldsArray( $opt_name = "", $section_id = "", $fields = array() ) {
			if ( ! empty( $opt_name ) && ! empty( $section_id ) && is_array( $fields ) && ! empty( $fields ) ) {

				foreach ( $fields as $field ) {
					if ( ! is_array( $field ) ) {
						continue;
					}
					$field['section_id'] = $section_id;
					self::setField( $opt_name, $field );
				}
			}
		}

		public static function getField( $opt_name = '', $id = '' ) {
			self::check_opt_name( $opt_name );
			if ( ! empty( $opt_name ) && ! empty( $id ) ) {
				return isset( self::$fields[ $opt_name ][ $id ] ) ? self::$fields[ $opt_name ][ $id ] : false;
			}

			return false;
		}

		public static function setField( $opt_name = '', $field = array() ) {
			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && is_array( $field ) && ! empty( $field ) ) {

				if ( ! isset( $field['priority'] ) ) {
					$field['priority'] = self::getPriority( $opt_name, 'fields' );
				}
				self::$fields[ $opt_name ][ $field['id'] ] = $field;
			}
		}

		public static function setBox( $opt_name = "", $box = array() ) {

			self::check_opt_name( $opt_name );

			if ( ! empty( $opt_name ) && is_array( $box ) && ! empty( $box ) ) {
				if ( ! isset( $box['id'] ) ) {
					if ( isset( $box['title'] ) ) {
						$box['id'] = strtolower( sanitize_html_class( $box['title'] ) );
					} else {
						$box['id'] = "box";
					}

					if ( isset( self::$boxes[ $opt_name ][ $box['id'] ] ) ) {
						$orig = $box['id'];
						$i    = 0;
						while ( isset( self::$boxes[ $opt_name ][ $box['id'] ] ) ) {
							$box['id'] = $orig . '_' . $i;
						}
					}
				}

				if ( isset( $box['sections'] ) ) {
					if ( ! empty( $box['sections'] ) && is_array( $box['sections'] ) ) {
						self::processSectionsArray( $opt_name, $box['id'], $box['sections'] );
					}
					unset( $box['sections'] );
				}
				self::$boxes[ $opt_name ][ $box['id'] ] = $box;

			} else {
				self::$errors[ $opt_name ]['box']['empty'] = "Unable to create a box due an empty box array or the box variable passed was not an array.";

				return;
			}

		}

		public static function setBoxes( $opt_name = "", $boxes = array() ) {
			if ( ! empty( $boxes ) && is_array( $boxes ) ) {
				foreach ( $boxes as $box ) {
					Redux_Metaboxes::setBox( $opt_name, $box );
				}
			}
		}

		public static function getBoxes( $opt_name = "" ) {
			self::check_opt_name( $opt_name );
			if ( ! empty( $opt_name ) && ! empty( self::$boxes[ $opt_name ] ) ) {
				return self::$boxes[ $opt_name ];
			}

			return false;
		}

		public static function getBox( $opt_name = "", $key = "" ) {
			self::check_opt_name( $opt_name );
			if ( ! empty( $opt_name ) && ! empty( $key ) && ! empty( self::$boxes[ $opt_name ] ) && isset( self::$boxes[ $opt_name ][ $key ] ) ) {
				return self::$boxes[ $opt_name ][ $key ];
			}

			return false;
		}

		public static function getPriority( $opt_name, $type ) {
			$priority                             = self::$priority[ $opt_name ][ $type ];
			self::$priority[ $opt_name ][ $type ] += 1;

			return $priority;
		}

		public static function check_opt_name( $opt_name = "" ) {
			if ( empty( $opt_name ) || is_array( $opt_name ) ) {
				return;
			}
			if ( ! isset( self::$boxes[ $opt_name ] ) ) {
				self::$boxes[ $opt_name ] = array();
			}
			if ( ! isset( self::$priority[ $opt_name ] ) ) {
				self::$priority[ $opt_name ]['args'] = 1;
			}
			if ( ! isset( self::$sections[ $opt_name ] ) ) {
				self::$sections[ $opt_name ]             = array();
				self::$priority[ $opt_name ]['sections'] = 1;
			}
			if ( ! isset( self::$fields[ $opt_name ] ) ) {
				self::$fields[ $opt_name ]             = array();
				self::$priority[ $opt_name ]['fields'] = 1;
			}
			if ( ! isset( self::$errors[ $opt_name ] ) ) {
				self::$errors[ $opt_name ] = array();
			}
			if ( ! isset( self::$init[ $opt_name ] ) ) {
				self::$init[ $opt_name ] = false;
			}
		}
	}

	Redux_Metaboxes::load();

}
