<?php


/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the profiles of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @author      Dovy Paukstys (dovy)
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_users', false ) ) {

	/**
	 * Main ReduxFramework customizer extension class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_extension_users {

		static $version = "1.0.0";

		public $profiles = array();
		public $users_roles = array();
		public $users_role = array();
		public $sections = array();
		public $output = array();
		private $parent;
		public $options = array();
		public $parent_options = array();
		public $parent_defaults = array();
		public $profile_fields = array();
		public $wp_links = array();
		public $options_defaults = array();
		public $localize_data = array();
		public $toReplace = array();
		public $_extension_url;
		public $_extension_dir;
		public $meta = array();
		public $base_url;

		public function __construct( $parent ) {

			global $pagenow;

			$this->pagenows                      = array( 'user-new.php', 'profile.php', 'user-edit.php' );
			$this->parent                        = $parent;
			$this->parent->extensions[ 'users' ] = $this;

			$this->_extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
			$this->_extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->_extension_dir ) );

			add_action( 'admin_notices', array( $this, 'meta_profiles_show_errors' ), 0 );
			add_action( 'admin_enqueue_scripts', array( $this, '_enqueue' ), 20 );

			if ( is_admin() && in_array( $pagenow, $this->pagenows ) ) {
				$this->init();

				add_action( "personal_options_update", array( $this, 'user_meta_save' ) );
				add_action( "edit_user_profile_update", array( $this, 'user_meta_save' ) );

			}

		} // __construct()

		public function add_term_classes( $classes ) {
			$classes[] = 'redux-users';
			$classes[] = 'redux-' . $this->parent->args[ 'opt_name' ];

			if ( $this->parent->args[ 'class' ] != "" ) {
				$classes[] = $this->parent->args[ 'class' ];
			}

			return $classes;
		}

		public function add_term_hide_class( $classes ) {
			$classes[] = 'hide';

			return $classes;
		}

		public function init() {
			if ( ! class_exists( 'Redux_Users' ) ) {
				return;
			}
			global $pagenow;

			$user       = isset( $_GET[ 'user_id' ] ) ? $_GET[ 'user_id' ] : get_current_user_id();
			$this->meta = $this->parent->options = Redux_Users::get_user_meta( array( 'user' => $user ) );

			$this->profiles = apply_filters( 'redux/users/' . $this->parent->args[ 'opt_name' ] . '/profiles', $this->profiles, $this->parent->args[ 'opt_name' ] );

			if ( empty( $this->profiles ) ) {
				$this->profiles = Redux_Users::constructProfiles( $this->parent->args[ 'opt_name' ] );
			}

			if ( empty( $this->profiles ) || ! is_array( $this->profiles ) ) {
				return;
			}

			$this->base_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];


			foreach ( $this->profiles as $bk => $profile ) {

				$profile[ 'roles' ] = isset( $profile[ 'roles' ] ) ? (array) $profile[ 'roles' ] : array();

				if ( ! empty( $profile[ 'sections' ] ) ) {
					$this->sections = $profile[ 'sections' ];
					array_merge( $this->parent->sections, $profile[ 'sections' ] );

					$this->users_roles = isset( $profile[ 'roles' ] ) ? wp_parse_args( $this->users_roles, $profile[ 'roles' ] ) : array( 'read' );

					// Checking to overide the parent variables
					$addField = false;

					foreach ( $profile[ 'roles' ] as $role ) {
						if ( $this->users_role == $role ) {
							$addField = true;
						}
					}

					// Replacing all the fields
					if ( $addField || ( ( is_admin() && in_array( $pagenow, $this->pagenows ) ) || ( ! is_admin() ) ) ) {

						$profileID = 'redux-' . $this->parent->args[ 'opt_name' ] . '-metaterm-' . $profile[ 'id' ];

						if ( isset( $profile[ 'page_template' ] ) && $this->users_role == "page" ) {
							if ( ! is_array( $profile[ 'page_template' ] ) ) {
								$profile[ 'page_template' ] = array( $profile[ 'page_template' ] );
							}

							$this->wp_links[ $profileID ][ 'page_template' ] = isset( $this->wp_links[ $profileID ][ 'page_template' ] ) ? wp_parse_args( $this->wp_links[ $profileID ][ 'page_template' ], $profile[ 'page_template' ] ) : $profile[ 'page_template' ];
						}

						if ( isset( $profile[ 'post_format' ] ) && ( in_array( $this->users_role, $this->users_roles ) || $this->users_role == "" ) ) {
							if ( ! is_array( $profile[ 'post_format' ] ) ) {
								$profile[ 'post_format' ] = array( $profile[ 'post_format' ] );
							}

							//$this->wp_links[ $profileID ]['post_format'] = isset( $this->wp_links[ $profileID ]['post_format'] ) ? wp_parse_args( $this->wp_links['post_format'], $profile['post_format'] ) : $profile['post_format'];
							$this->wp_links[ $profileID ][ 'post_format' ] = isset( $this->wp_links[ $profileID ][ 'post_format' ] ) ? wp_parse_args( $this->wp_links[ $profileID ][ 'post_format' ], $profile[ 'post_format' ] ) : $profile[ 'post_format' ];
						}

						foreach ( $profile[ 'sections' ] as $sk => $section ) {
							if ( isset( $section[ 'fields' ] ) && ! empty( $section[ 'fields' ] ) ) {
								foreach ( $section[ 'fields' ] as $fk => $field ) {
									if ( ! isset( $field[ 'class' ] ) ) {
										$field[ 'class' ]                                              = "";
										$this->profiles[ $bk ][ 'sections' ][ $sk ][ 'fields' ][ $fk ] = $field;
									}

									$this->parent->check_dependencies( $field );

									if ( stripos( $field[ 'class' ], 'redux-field-init' ) === 0 ) {
										//$field['class'] = trim( $field['class'] . ' redux-field-init' );
									}

									if ( $addField || ( ( is_admin() && in_array( $pagenow, $this->pagenows ) ) || ( ! is_admin() ) ) ) {
										if ( empty( $field[ 'id' ] ) ) {
											continue;
										}

										if ( isset( $field[ 'default' ] ) ) {
											$this->options_defaults[ $field[ 'id' ] ] = $field[ 'default' ];
										}
										else {
											$this->options_defaults[ $field[ 'id' ] ] = $this->_field_default( $field );
										}

										foreach ( $profile[ 'roles' ] as $type ) {
											$this->profile_fields[ $type ][ $field[ 'id' ] ] = 1;
										}

										if ( isset( $field[ 'output' ] ) && ! empty( $field[ 'output' ] ) ) {
											$this->output[ $field[ 'id' ] ] = isset( $this->output[ $field[ 'id' ] ] ) ? array_merge( $field[ 'output' ], $this->output[ $field[ 'id' ] ] ) : $field[ 'output' ];
										}

										// Detect what field types are being used
										if ( ! isset( $this->parent->fields[ $field[ 'type' ] ][ $field[ 'id' ] ] ) ) {
											$this->parent->fields[ $field[ 'type' ] ][ $field[ 'id' ] ] = 1;
										}
										else {
											$this->parent->fields[ $field[ 'type' ] ] = array( $field[ 'id' ] => 1 );
										}

										if ( isset( $this->options_defaults[ $field[ 'id' ] ] ) ) {
											$this->toReplace[ $field[ 'id' ] ] = $field;
										}
									}

									if ( ! isset( $this->parent->options[ $field[ 'id' ] ] ) ) {
										$this->parent->sections[ ( count( $this->parent->sections ) - 1 ) ][ 'fields' ][] = $field;
									}

									if ( ! isset( $this->meta[ $field[ 'id' ] ] ) ) {
										$this->meta[ $field[ 'id' ] ] = $this->options_defaults[ $field[ 'id' ] ];
									}

									// Only override if it exists and it's not the default
									if ( isset( $this->meta[ $field[ 'id' ] ] ) && isset( $field[ 'default' ] ) && $this->meta[ $field[ 'id' ] ] == $field[ 'default' ] ) {
										//unset($this->meta[$this->tag_id][$field['id']]);
									}
								}
							}
						}
					}
				}
			}


			$this->parent_options = ''; //$this->parent->options;

			if ( ! empty( $this->toReplace ) ) {
				foreach ( $this->toReplace as $id => $field ) {
					add_filter( "redux/options/{$this->parent->args['opt_name']}/field/{$id}/register", array(
						$this,
						'replace_field'
					) );
				}
			}

			add_filter( "redux/options/{$this->parent->args['opt_name']}/options", array(
				$this,
				'_override_options'
			) );
			//add_filter( "redux/field/{$this->parent->args['opt_name']}/_can_output_css", array(
			//    $this,
			//    '_override_can_output_css'
			//) );
			//add_filter( "redux/field/{$this->parent->args['opt_name']}/output_css", array(
			//    $this,
			//    '_output_css'
			//) );

			if ( is_admin() && in_array( $pagenow, $this->pagenows ) ) {

				$priority = isset( $this->parent->args[ 'user_priority' ] ) ? $this->parent->args[ 'user_priority' ] : 3;

				add_action( "show_user_profile", array( $this, 'add_profiles' ), $priority );
				add_action( "edit_user_profile", array( $this, 'add_profiles' ), $priority );
				add_action( "user_new_form", array( $this, 'add_profiles' ), $priority );

				//add_action( "{$_GET['users']}_add_form_fields", array( $this, 'add_meta_profiles' ) );
			}


		}

		function replace_field( $field ) {
			if ( isset( $this->toReplace[ $field[ 'id' ] ] ) ) {
				$field = $this->toReplace[ $field[ 'id' ] ];
			}

			return $field;
		}

		function _override_can_output_css( $field ) {
			if ( isset( $this->output[ $field[ 'id' ] ] ) ) {
				$field[ 'force_output' ] = true;
			}

			return $field;
		}

		function _output_css( $field ) {
			if ( isset( $this->output[ $field[ 'id' ] ] ) ) {
				$field[ 'output' ] = $this->output[ $field[ 'id' ] ];
			}

			return $field;
		}

		// Make sure the defaults are the defaults
		public function _override_options( $options ) {
			$this->parent->_default_values();
			$this->parent_defaults = $this->parent->options_defaults;

			if ( empty( $this->meta ) ) {
				$user       = isset( $_GET[ 'user_id' ] ) ? $_GET[ 'user_id' ] : get_current_user_id();
				$this->meta = Redux_Users::get_user_meta( array( 'user' => $user ) );
			}

			$data = wp_parse_args( $this->meta, $this->options_defaults );

			foreach ( $data as $key => $value ) {
				if ( isset( $meta[ $key ] ) && $meta[ $key ] != '' ) {
					$data[ $key ] = $meta[ $key ];
					continue;
				}

				if ( isset( $options[ $key ] ) ) {
					if ( isset( $options[ $key ] ) ) {
						$data[ $key ] = $options[ $key ];
					}
				}
			}

			$this->parent->options_defaults = wp_parse_args( $this->options_defaults, $this->parent->options_defaults );

			$options = wp_parse_args( $data, $options );

			return $options;
		}

		public function sanitize_query( $queries ) {

			$clean_queries = array();

			if ( ! is_array( $queries ) ) {
				print( 'h1' );

				return $clean_queries;
			}

			foreach ( $queries as $key => $query ) {

				if ( 'relation' === $key ) {
					$relation = $query;

				}
				elseif ( ! is_array( $query ) ) {
					//print( 'h2' );
					//print_r( $query );

					$clean_queries[ $key ][ 'values' ] = array( $query );

					// First-order clause.
				}
				elseif ( $this->is_first_order_clause( $query ) ) {
					print( 'h3' );
					if ( isset( $query[ 'value' ] ) && array() === $query[ 'value' ] ) {
						unset( $query[ 'value' ] );
					}

					$clean_queries[ $key ] = $query;

					// Otherwise, it's a nested query, so we recurse.
				}
				else {
					print( 'h4' );
					$cleaned_query = $this->sanitize_query( $query );

					if ( ! empty( $cleaned_query ) ) {
						$clean_queries[ $key ] = $cleaned_query;
					}
				}
			}

			//print_r( $clean_queries );
			//exit();

			if ( empty( $clean_queries ) ) {
				return $clean_queries;
			}

			// Sanitize the 'relation' key provided in the query.
			if ( isset( $relation ) && 'OR' === strtoupper( $relation ) ) {
				$clean_queries[ 'relation' ] = 'OR';

				/*
				 * If there is only a single clause, call the relation 'OR'.
				 * This value will not actually be used to join clauses, but it
				 * simplifies the logic around combining key-only queries.
				 */
			}
			elseif ( 1 === count( $clean_queries ) ) {
				$clean_queries[ 'relation' ] = 'OR';

				// Default to AND.
			}
			else {
				$clean_queries[ 'relation' ] = 'AND';
			}

			return $clean_queries;
		}

		protected function is_first_order_clause( $query ) {
			return isset( $query[ 'key' ] ) || isset( $query[ 'value' ] );
		}

		public function _enqueue() {
			global $pagenow;

			//print_r( $this->sanitize_query(
			//    array(
			//        'relation' => 'OR',
			//        'edit_post',
			//    )
			//) );
			//exit();

			if ( in_array( $pagenow, $this->pagenows ) ) {

				if ( $pagenow == "user-new.php" ) {
					$this->parent->args[ 'disable_save_warn' ] = true;
				}

				$this->parent->transients = $this->parent->transients_check = get_transient( $this->parent->args[ 'opt_name' ] . '-transients-users' );

				if ( isset( $this->parent->transients[ 'notices' ] ) ) {
					$this->notices                                = $this->parent->transients[ 'notices' ];
					$this->parent->transients[ 'last_save_mode' ] = "users";
				}

				delete_transient( $this->parent->args[ 'opt_name' ] . '-transients-users' );
				//$this->parent->_enqueue();

				do_action( "redux/users/{$this->parent->args['opt_name']}/enqueue" );

				/**
				 * Redux users CSS
				 * filter 'redux/page/{opt_name}/enqueue/redux-extension-users-css'
				 *
				 * @param string  bundled stylesheet src
				 */
				wp_enqueue_style(
					'redux-extension-users-css',
					apply_filters( "redux/users/{$this->parent->args['opt_name']}/enqueue/redux-extension-users-css", $this->_extension_url . 'extension_users.css' ),
					array( 'redux-admin-css' ),
					self::$version,
					'all'
				);

				/**
				 * Redux users JS
				 * filter 'redux/page/{opt_name}/enqueue/redux-extension-users-js
				 *
				 * @param string  bundled javscript
				 */
				wp_enqueue_script(
					'redux-extension-users-js',
					apply_filters( "redux/users/{$this->parent->args['opt_name']}/enqueue/redux-extension-users-js", $this->_extension_url . 'extension_users' . Redux_Functions::isMin() . '.js' ),
					array( 'jquery', 'redux-js' ),
					self::$version,
					'all'
				);

				// Values used by the javascript
				wp_localize_script(
					'redux-extension-users-js',
					'reduxUsers',
					$this->users_roles
				);

			}
		} // _enqueue()


		// DEPRECATED
		public function _default_values() {
			if ( ! empty( $this->profiles ) && empty( $this->options_defaults ) ) {
				foreach ( $this->profiles as $key => $profile ) {
					if ( empty( $profile[ 'sections' ] ) ) {
						continue;
					}

					// fill the cache
					foreach ( $profile[ 'sections' ] as $sk => $section ) {
						if ( ! isset( $section[ 'id' ] ) ) {
							if ( ! is_numeric( $sk ) || ! isset( $section[ 'title' ] ) ) {
								$section[ 'id' ] = $sk;
							}
							else {
								$section[ 'id' ] = sanitize_title( $section[ 'title' ], $sk );
							}
							$this->profiles[ $key ][ 'sections' ][ $sk ] = $section;
						}
						if ( isset( $section[ 'fields' ] ) ) {
							foreach ( $section[ 'fields' ] as $k => $field ) {

								if ( empty ( $field[ 'id' ] ) && empty ( $field[ 'type' ] ) ) {
									continue;
								}

								if ( in_array( $field[ 'type' ], array( 'ace_editor' ) ) && isset ( $field[ 'options' ] ) ) {
									$this->profiles[ $key ][ 'sections' ][ $sk ][ 'fields' ][ $k ][ 'args' ] = $field[ 'options' ];
									unset ( $this->profiles[ $key ][ 'sections' ][ $sk ][ 'fields' ][ $k ][ 'options' ] );
								}

								if ( $field[ 'type' ] == "section" && isset ( $field[ 'indent' ] ) && $field[ 'indent' ] == "true" ) {
									$field[ 'class' ]                                              = isset( $field[ 'class' ] ) ? $field[ 'class' ] : '';
									$field[ 'class' ]                                              .= "redux-section-indent-start";
									$this->profiles[ $key ][ 'sections' ][ $sk ][ 'fields' ][ $k ] = $field;
								}

								$this->parent->field_default_values( $field );
							}
						}
					}
				}
			}

			if ( empty( $this->meta ) ) {
				$user       = isset( $_GET[ 'user_id' ] ) ? $_GET[ 'user_id' ] : get_current_user_id();
				$this->meta = Redux_Users::get_user_meta( array( 'user' => $user ) );
			}
		} // _default_values()


		public function add_profiles( $user = array() ) {


			if ( empty( $this->profiles ) || ! is_array( $this->profiles ) ) {
				return;
			}

			foreach ( $this->profiles as $key => $profile ) {
				if ( empty( $profile[ 'sections' ] ) ) {
					continue;
				}

				$defaults = array(
					'id'         => "{$key}",
					'section_id' => $key,
					'profiles'   => array(),
				);


				$profile = wp_parse_args( $profile, $defaults );


				if ( isset( $profile[ 'title' ] ) ) {
					$title = $profile[ 'title' ];
				}
				else {
					if ( isset( $profile[ 'sections' ] ) && count( $profile[ 'sections' ] ) == 1 && isset( $profile[ 'sections' ][ 0 ][ 'fields' ] ) && count( $profile[ 'sections' ][ 0 ][ 'fields' ] ) == 1 && isset( $profile[ 'sections' ][ 0 ][ 'fields' ][ 0 ][ 'title' ] ) ) {
						// If only one field in this term
						$title = $profile[ 'sections' ][ 0 ][ 'fields' ][ 0 ][ 'title' ];
					}
					else {
						$title = __( 'Options', 'req-core' );
					}
				}

				// Override the parent args on a metaterm level
				if ( ! isset( $this->orig_args ) || empty( $this->orig_args ) ) {
					$this->orig_args = $this->parent->args;
				}

				if ( isset( $profile[ 'args' ] ) ) {
					$this->parent->args = wp_parse_args( $profile[ 'args' ], $this->orig_args );
				}
				elseif ( $this->parent->args != $this->orig_args ) {
					$this->parent->args = $this->orig_args;
				}

				if ( ! isset( $profile[ 'class' ] ) ) {
					$profile[ 'class' ] = array();
				}

				if ( ! empty( $profile[ 'class' ] ) ) {
					if ( ! is_array( $profile[ 'class' ] ) ) {
						$profile[ 'class' ] = array( $profile[ 'class' ] );
					}
				}

				$profile[ 'class' ] = $this->add_term_classes( $profile[ 'class' ] );

				if ( isset( $profile[ 'post_format' ] ) ) {
					$profile[ 'class' ] = $this->add_term_hide_class( $profile[ 'class' ] );
				}

				global $pagenow;
				if ( strpos( $pagenow, 'edit-' ) !== false ) {

					$profile[ 'style' ]   = 'wp';
					$profile[ 'class' ][] = " edit-page";
					$profile[ 'class' ][] = " redux-wp-style";
				}

				$this->generate_profiles( $user, array( 'args' => $profile ) );

				if ( isset( $profile[ 'roles' ] ) && ! empty( $profile[ 'roles' ] ) ) {
					foreach ( $profile[ 'roles' ] as $profiletype ) {
						if ( $profiletype !== $_GET[ 'users' ] ) {
							continue;
						}

					}
				}
			}


		} // add_meta_profiles()

		function _field_default( $field_id ) {

			//$this->parent->options = wp_parse_args()
			if ( ! isset( $this->parent->options_defaults ) ) {
				$this->parent->options_defaults = $this->parent->_default_values();
			}

			if ( ! isset( $this->parent->options ) || empty( $this->parent->options ) ) {
				$this->parent->get_options();
			}

			$this->options = $this->parent->options;

			if ( isset( $this->parent->options[ $field_id[ 'id' ] ] ) && isset( $this->parent->options_defaults[ $field_id[ 'id' ] ] ) && $this->parent->options[ $field_id[ 'id' ] ] != $this->parent->options_defaults[ $field_id[ 'id' ] ] ) {
				return $this->parent->options[ $field_id[ 'id' ] ];
			}
			else {
				if ( empty( $this->options_defaults ) ) {
					$this->_default_values(); // fill cache
				}

				$data = '';
				if ( ! empty( $this->options_defaults ) ) {
					$data = isset( $this->options_defaults[ $field_id[ 'id' ] ] ) ? $this->options_defaults[ $field_id[ 'id' ] ] : '';
				}

				if ( empty( $data ) && isset( $this->parent->options_defaults[ $field_id[ 'id' ] ] ) ) {
					//$data = $this->parent->options_defaults[$field_id['id']];
					$data = isset( $this->parent->options_defaults[ $field_id[ 'id' ] ] ) ? $this->parent->options_defaults[ $field_id[ 'id' ] ] : '';
				}

				return $data;
			}

		} // _field_default()

		// Function to get and cache the post meta.
		function get_meta( $id ) {
			if ( ! isset( $this->meta[ $id ] ) ) {
				$this->meta[ $id ] = array();
				$oData             = get_post_meta( $id );

				$oData = apply_filters( "redux/users/{$this->parent->args['opt_name']}/get_meta", $oData );

				if ( ! empty( $oData ) ) {
					foreach ( $oData as $key => $value ) {
						if ( count( $value ) == 1 ) {
							$this->meta[ $id ][ $key ] = maybe_unserialize( $value[ 0 ] );
						}
						else {
							$new_value = array_map( 'maybe_unserialize', $value );

							if ( is_array( $new_value ) ) {
								$this->meta[ $id ][ $key ] = $new_value[ 0 ];
							}
							else {
								$this->meta[ $id ][ $key ] = $new_value;
							}
						}
					}
				}

				if ( isset( $this->meta[ $id ][ $this->parent->args[ 'opt_name' ] ] ) ) {
					$data = maybe_unserialize( $this->meta[ $id ][ $this->parent->args[ 'opt_name' ] ] );

					foreach ( $data as $key => $value ) {
						$this->meta[ $id ][ $key ] = $value;
						update_post_meta( $id, $key, $value );
					}

					unset( $this->meta[ $id ][ $this->parent->args[ 'opt_name' ] ] );

					delete_post_meta( $id, $this->parent->args[ 'opt_name' ] );
				}
			}

			return $this->meta[ $id ];
		}

		function get_values( $thePost, $meta_key = "", $def_val = "" ) {
			// Override these values if they differ from the admin panel defaults.  ;)

			if ( isset( $thePost->users_role ) && in_array( $thePost->users_role, $this->users_roles ) ) {
				if ( isset( $this->users_role_values[ $thePost->users_role ] ) ) {
					$meta = $this->profile_fields[ $thePost->users_role ];
				}
				else {
					$defaults = array();
					if ( ! empty( $this->profile_fields[ $thePost->users_role ] ) ) {
						foreach ( $this->profile_fields[ $thePost->users_role ] as $key => $null ) {
							if ( isset( $this->options_defaults[ $key ] ) ) {
								$defaults[ $key ] = $this->options_defaults[ $key ];
							}
						}
					}

					$meta                                         = wp_parse_args( $this->get_meta( $thePost->ID ), $defaults );
					$this->profile_fields[ $thePost->users_role ] = $meta;
				}

				if ( ! empty( $meta_key ) ) {
					if ( ! isset( $meta[ $meta_key ] ) ) {
						$meta[ $meta_key ] = $def_val;
					}

					return $meta[ $meta_key ];
				}
				else {
					return $meta;
				}
			}

			return $def_val;
		}

		function check_edit_visibility( $array = array() ) {
			global $pagenow;

			// Edit page visibility
			if ( strpos( $pagenow, 'edit-' ) !== false ) {
				if ( isset( $array[ 'fields' ] ) ) {
					foreach ( $array[ 'fields' ] as $key => $field ) {
						if ( in_array( $field[ 'id' ], $this->parent->fieldsHidden ) ) {
							// Not visible
						}
						else {
							if ( isset( $field[ 'add_visibility' ] ) && $field[ 'add_visibility' ] ) {
								return true;
							}
						}
					}

					return false;
				}
				if ( isset( $array[ 'add_visibility' ] ) && $array[ 'add_visibility' ] ) {
					return true;
				}

				return false;
			}

			return true;
		}


		function generate_profiles( $type, $metaterm ) {
			global $wpdb;

			if ( isset( $metaterm[ 'args' ][ 'permissions' ] ) && ! empty( $metaterm[ 'args' ][ 'permissions' ] ) && ! $this->parent->current_user_can( $metaterm[ 'args' ][ 'permissions' ] ) ) {
				return;
			}

			if ( isset( $metaterm[ 'args' ][ 'style' ] ) && in_array( $metaterm[ 'args' ][ 'style' ], array(
					'wp',
					'wordpress'
				) )
			) {
				$container_class                 = "redux-wp-style";
				$metaterm[ 'args' ][ 'sidebar' ] = false;
			}
			elseif ( isset( $metaterm[ 'args' ][ 'sidebar' ] ) && ! $metaterm[ 'args' ][ 'sidebar' ] ) {
				$container_class = 'redux-no-sections';
			}
			else {
				$container_class = 'redux-has-sections';
			}

			$class = implode( ' ', $metaterm[ 'args' ][ 'class' ] );
			echo "<div class='{$class}'>";

			$sections = $metaterm[ 'args' ][ 'sections' ];

			wp_nonce_field( 'redux_users_meta_nonce', 'redux_users_meta_nonce' );

			wp_dequeue_script( 'json-view-js' );

			$sidebar = true;
			if ( count( $sections ) == 1 || ( isset( $metaterm[ 'args' ][ 'sidebar' ] ) && $metaterm[ 'args' ][ 'sidebar' ] === false ) ) {
				$sidebar = false; // Show the section dividers or not
			}
			?>
			<div data-opt-name="<?php echo esc_attr( $this->parent->args[ 'opt_name' ] ); ?>"
				 class="redux-container <?php echo( $container_class ); ?> redux-term redux-box-normal redux-term-normal">
				<div class="redux-notices">
					<?php if ( $sidebar ) { ?>
						<div class="saved_notice admin-notice notice-blue" style="display:none;">
							<strong><?php echo apply_filters( "redux-imported-text-{$this->parent->args['opt_name']}", __( 'Settings Imported!', 'req-core' ) ) ?></strong>
						</div>
						<div class="redux-save-warn notice-yellow">
							<strong><?php echo apply_filters( "redux-changed-text-{$this->parent->args['opt_name']}", __( 'Settings have changed, you should save them!', 'req-core' ) ) ?></strong>
						</div>
					<?php } ?>
					<div class="redux-field-errors notice-red">
						<strong> <span></span> <?php echo __( 'error(s) were found!', 'req-core' ) ?>
						</strong>
					</div>
					<div class="redux-field-warnings notice-yellow">
						<strong> <span></span> <?php echo __( 'warning(s) were found!', 'req-core' ) ?>
						</strong>
					</div>
				</div>
				<?php
				echo '<a href="javascript:void(0);" class="expand_options hide" style="display:none;">' . __( 'Expand', 'req-core' ) . '</a>';
				if ( $sidebar ) {
					?>
					<div class="redux-sidebar">
						<ul class="redux-group-menu">
							<?php
							foreach ( $sections as $sKey => $section ) {
								if ( isset( $section[ 'permissions' ] ) && ! empty( $section[ 'permissions' ] ) && ! $this->parent->current_user_can( $section[ 'permissions' ] ) ) {
									continue;
								}
								echo $this->parent->section_menu( $sKey, $section, "_" . $metaterm[ 'args' ][ 'id' ] . "", $sections );
							}
							?>
						</ul>
					</div>
				<?php } ?>

				<div class="redux-main">
					<?php
					$updateLocalize = false;

					foreach ( $sections as $sKey => $section ) {

						if ( ! $this->check_edit_visibility( $section ) ) {
							continue;
						}
						if ( isset( $section[ 'permissions' ] ) && ! empty( $section[ 'permissions' ] ) && ! $this->parent->current_user_can( $section[ 'permissions' ] ) ) {
							continue;
						}

						if ( isset( $section[ 'fields' ] ) && ! empty( $section[ 'fields' ] ) ) {
							if ( isset( $section[ 'args' ] ) ) {
								$this->parent->args = wp_parse_args( $section[ 'args' ], $this->orig_args );
							}
							elseif ( $this->parent->args != $this->orig_args ) {
								$this->parent->args = $this->orig_args;
							}

							$hide               = $sidebar ? "" : ' display-group';
							$section[ 'class' ] = isset( $section[ 'class' ] ) ? " {$section['class']}" : '';

							echo "<div id='{$sKey}_{$metaterm['args']['id']}_section_group' class='redux-group-tab{$section['class']} redux_metaterm_panel{$hide}'>";

							if ( isset( $section[ 'title' ] ) && ! empty( $section[ 'title' ] ) ) {
								echo '<h3 class="redux-section-title">' . $section[ 'title' ] . '</h3>';
							}

							if ( isset( $section[ 'desc' ] ) && ! empty( $section[ 'desc' ] ) ) {
								echo '<div class="redux-section-desc">' . $section[ 'desc' ] . '</div>';
							}

							echo '<table class="form-table"><tbody>';
							foreach ( $section[ 'fields' ] as $fKey => $field ) {

								if ( ! $this->check_edit_visibility( $field ) ) {
									continue;
								}
								if ( isset( $field[ 'permissions' ] ) && ! empty( $field[ 'permissions' ] ) && ! $this->parent->current_user_can( $field[ 'permissions' ] ) ) {
									continue;
								}

								$field[ 'name' ] = $this->parent->args[ 'opt_name' ] . '[' . $field[ 'id' ] . ']';

								$is_hidden = false;
								$ex_style  = '';
								if ( isset( $field[ 'hidden' ] ) && $field[ 'hidden' ] ) {
									$is_hidden = true;
									$ex_style  = ' style="border-bottom: none;"';
								}

								echo '<tr valign="top"' . $ex_style . '>';

								$th = $this->parent->get_header_html( $field );

								if ( $is_hidden ) {
									$str_pos = strpos( $th, 'redux_field_th' );

									if ( $str_pos > - 1 ) {
										$th = str_replace( 'redux_field_th', 'redux_field_th hide', $th );
									}
								}

								if ( $sidebar ) {
									if ( ! ( isset( $metaterm[ 'args' ][ 'sections' ] ) && count( $metaterm[ 'args' ][ 'sections' ] ) == 1 && isset( $metaterm[ 'args' ][ 'sections' ][ 0 ][ 'fields' ] ) && count( $metaterm[ 'args' ][ 'sections' ][ 0 ][ 'fields' ] ) == 1 ) && isset( $field[ 'title' ] ) ) {
										echo '<th scope="row">';
										if ( ! empty( $th ) ) {
											echo $th;
										}
										echo '</th>';
										echo '<td>';
									}
								}
								else {
									echo '<td>' . $th . '';
								}

								if ( $field[ 'type' ] == "section" && $field[ 'indent' ] == "true" ) {
									$field[ 'class' ] = isset( $field[ 'class' ] ) ? $field[ 'class' ] : '';
									$field[ 'class' ] .= "redux-section-indent-start";
								}

								if ( ! isset( $this->meta[ $field[ 'id' ] ] ) ) {
									$this->meta[ $field[ 'id' ] ] = "";
								}

								$this->parent->_field_input( $field, $this->meta[ $field[ 'id' ] ] );
								echo '</td></tr>';
							}
							echo '</tbody></table>';
							echo '</div>';
						}
					}
					?>
				</div>
				<div class="clear"></div>
			</div></div>

			<?php
		} // generate_profiles()

		/**
		 * Save meta profiles
		 * Runs when a post is saved and does an action which the write panel save scripts can hook into.
		 *
		 * @access public
		 *
		 * @param mixed $user_id
		 *
		 * @return void
		 */
		function user_meta_save( $user_id = 0 ) {

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}

			// Check if our nonce is set.
			if ( ! isset( $_POST[ 'redux_users_meta_nonce' ] ) || ! isset ( $_POST[ $this->parent->args[ 'opt_name' ] ] ) ) {
				return false;
			}

			$nonce = $_POST[ 'redux_users_meta_nonce' ];
			// Verify that the nonce is valid.
			// Validate fields (if needed)
			if ( ! wp_verify_nonce( $nonce, 'redux_users_meta_nonce' ) ) {
				return false;
			}

			$check_user_id = $_POST[ 'checkuser_id' ];

			$user       = isset( $_GET[ 'user_id' ] ) ? $_GET[ 'user_id' ] : get_current_user_id();
			$this->meta = Redux_Users::get_user_meta( array( 'user' => $user ) );

			$toSave    = array();
			$toCompare = array();
			$toDelete  = array();

			$field_args = Redux_Users::$fields[ $this->parent->args[ 'opt_name' ] ];

			foreach ( $_POST[ $this->parent->args[ 'opt_name' ] ] as $key => $value ) {

				if ( ! empty( $field_args[ $key ][ 'permissions' ] ) ) {
					foreach ( (array) $field_args[ $key ][ 'permissions' ] as $pk => $pv ) {
						// Do not save anything the user doesn't have permissions for
						if ( isset( $field_args[ $key ] ) && isset( $field_args[ $key ][ 'permissions' ] ) ) {
							if ( user_can( $user_id, $pv ) && user_can( $check_user_id, $pv ) ) {
								break;
								continue;
							}
						}
					}
				}


				// Have to remove the escaping for array comparison
				if ( is_array( $value ) ) {
					foreach ( $value as $k => $v ) {
						if ( ! is_array( $v ) ) {
							$value[ $k ] = stripslashes( $v );
						}
					}
				}

				//parent_options
				if ( isset( $this->options_defaults[ $key ] ) && $value == $this->options_defaults[ $key ] ) {
					$toDelete[ $key ] = $value;
				}
				elseif ( isset( $this->options_defaults[ $key ] ) ) {
					$toSave[ $key ]    = $value;
					$toCompare[ $key ] = isset( $meta[ $key ] ) ? $meta[ $key ] : "";
				}
				else {
					continue;
				}
			}

			$toSave = apply_filters( "redux/{$this->parent->args['opt_name']}/users/save/before_validate", $toSave, $toCompare, $this->sections );

			$validate = $this->parent->_validate_values( $toSave, $toCompare, $this->sections );

			// Validate fields (if needed)
			foreach ( $toSave as $key => $value ) {
				if ( isset( $validate[ $key ] ) && $validate[ $key ] != $toSave[ $key ] ) {
					if ( isset( $this->meta[ $key ] ) && $validate[ $key ] == $this->meta[ $key ] ) {
						unset( $toSave[ $key ] );
					}
					else {
						$toSave[ $key ] = $validate[ $key ];
					}
				}
			}

			if ( ! empty( $this->parent->errors ) || ! empty( $this->parent->warnings ) ) {
				$this->parent->transients[ 'notices' ] = ( isset( $this->parent->transients[ 'notices' ] ) && is_array( $this->parent->transients[ 'notices' ] ) ) ? $this->parent->transients[ 'notices' ] : array();

				if ( ! isset( $this->parent->transients[ 'notices' ][ 'errors' ] ) || $this->parent->transients[ 'notices' ][ 'errors' ] != $this->parent->errors ) {
					$this->parent->transients[ 'notices' ][ 'errors' ] = $this->parent->errors;
					$updateTransients                                  = true;
				}

				if ( ! isset( $this->parent->transients[ 'notices' ][ 'warnings' ] ) || $this->parent->transients[ 'notices' ][ 'warnings' ] != $this->parent->warnings ) {
					$this->parent->transients[ 'notices' ][ 'warnings' ] = $this->parent->warnings;
					$updateTransients                                    = true;
				}

				if ( isset( $updateTransients ) ) {
					$this->parent->transients[ 'notices' ][ 'override' ] = 1;
					set_transient( $this->parent->args[ 'opt_name' ] . '-transients-users', $this->parent->transients );
				}
			}

			$check = $this->profile_fields;


			$toSave = apply_filters( 'redux/users/save', $toSave, $toCompare, $this->sections );
			foreach ( $toSave as $key => $value ) {

				if ( is_array( $value ) ) {
					$still_update = false;
					foreach ( $value as $vk => $vv ) {
						if ( ! empty( $vv ) ) {
							$still_update = true;
						}
					}
					if ( ! $still_update ) {
						continue;
					}
				}

				$prev_value = isset( $this->meta[ $key ] ) ? $this->meta[ $key ] : '';

				if ( isset( $check[ $key ] ) ) {
					unset( $check[ $key ] );
				}


				update_user_meta( $user_id, $key, $value, $prev_value );

			}

			foreach ( $toDelete as $key => $value ) {
				if ( isset( $check[ $key ] ) ) {
					unset( $check[ $key ] );
				}

				$prev_value = isset( $this->meta[ $key ] ) ? $this->meta[ $key ] : '';
				delete_user_meta( $user_id, $key, $prev_value );
			}
			if ( ! empty( $check ) ) {
				foreach ( $check as $key => $value ) {
					delete_user_meta( $user_id, $key );
				}
			}

		} // meta_user_meta_save()

		/**
		 * Show any stored error messages.
		 *
		 * @access public
		 * @return void
		 */
		function meta_profiles_show_errors() {
			if ( isset( $this->notices[ 'errors' ] ) && ! empty( $this->notices[ 'errors' ] ) ) {
				echo '<div id="redux_users_errors" class="error fade">';
				echo '<p><strong><span></span> ' . count( $this->notices[ 'errors' ] ) . ' ' . __( 'error(s) were found!', 'req-core' ) . '</strong></p>';
				echo '</div>';
			}

			if ( isset( $this->notices[ 'warnings' ] ) && ! empty( $this->notices[ 'warnings' ] ) ) {
				echo '<div id="redux_users_warnings" class="error fade" style="border-left-color: #E8E20C;">';
				echo '<p><strong><span></span> ' . count( $this->notices[ 'warnings' ] ) . ' ' . __( 'warnings(s) were found!', 'req-core' ) . '</strong></p>';
				echo '</div>';
			}
		} // meta_profiles_show_errors()

	} // class ReduxFramework_extension_users

} // if ( !class_exists( 'ReduxFramework_extension_users' ) )

# Helper function to bypass WordPress hook priorities.  ;)
if ( ! function_exists( 'create_term_redux_users' ) ) {
	function create_term_redux_users( $profile_id, $tt_id = 0, $users = '' ) {
		$instances = Redux_Instances::get_all_instances();
		foreach ( $_POST as $key => $value ) {
			if ( is_array( $value ) && isset( $instances[ $key ] ) ) {
				$instances[ $key ]->extensions[ 'users' ]->user_meta_save( $profile_id );
			}
		}
	}
}
add_action( 'create_term', 'create_term_redux_users', 4 );
