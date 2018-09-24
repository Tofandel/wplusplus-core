<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'reduxSocialProfilesFunctions', false ) ) {

	class reduxSocialProfilesFunctions {
		static public $_parent;
		static public $_field_id;
		static public $_field;
		static public $upload_dir;
		static public $upload_url;

		public static function init( $parent ) {
			self::$_parent = $parent;

			if ( empty( self::$_field_id ) ) {
				self::$_field    = self::getField( $parent );
				self::$_field_id = self::$_field['id'];
			}

			// Make sanitized upload dir DIR
			self::$upload_dir = Redux_Helpers::cleanFilePath( ReduxFramework::$_upload_dir . 'social-profiles/' );

			// Make sanitized upload dir URL
			self::$upload_url = Redux_Helpers::cleanFilePath( ReduxFramework::$_upload_url . 'social-profiles/' );

			Redux_Functions::initWpFilesystem();
		}

		public static function read_data_file() {
			$file = self::get_data_path();

			if ( file_exists( $file ) ) {

				// Get the contents of the file and stuff it in a variable
				$data = self::$_parent->filesystem->execute( 'get_contents', $file );

				//  Error or null, set the result to false
				if ( false == $data || null == $data ) {
					$arrData = false;

					// Otherwise decode the json object and return it.
				} else {
					$arr     = json_decode( $data, true );
					$arrData = $arr;
				}
			} else {
				$arrData = false;
			}

			return $arrData;
		}

		public static function write_data_file( $arrData, $file = '' ) {
			if ( ! is_dir( self::$upload_dir ) ) {
				return false;
			}

			$file = ( '' === $file ) ? self::get_data_path() : self::$upload_dir . $file;

			// Encode the array data
			$data = json_encode( $arrData );

			// Write to its file on the server, return the return value
			// True on success, false on error.
			$ret_val = self::$_parent->filesystem->execute( 'put_contents', $file, array( 'content' => $data ) );

			return $ret_val;

		}

		public static function get_data_path() {
			return Redux_Helpers::cleanFilePath( self::$upload_dir . '/' . self::$_parent->args['opt_name'] . '-' . self::$_field_id . '.json' );
		}

		public static function getField( $parent = array() ) {
			global $pagenow, $post;

			if ( is_admin() && ( $pagenow == "post-new.php" || $pagenow == "post.php" ) ) {

				$inst = ReduxFrameworkInstances::get_instance( self::$_parent->args['opt_name'] );

				$ext = $inst->extensions;

				if ( isset( $ext['metaboxes'] ) ) {
					$obj   = $ext['metaboxes'];
					$boxes = ( $obj->boxes );

					if ( isset( $post->post_type ) ) {
						foreach ( $boxes as $idx => $sections ) {
							foreach ( $sections['sections'] as $i => $fields ) {
								foreach ( $fields['fields'] as $n => $f ) {
									if ( $f['type'] == 'social_profiles' && in_array( $post->post_type, $sections['post_types'] ) ) {
										return $f;
									}
								}
							}
						}
					}
				}
			} else {
				if ( ! empty( $parent ) ) {
					self::$_parent = $parent;
				}

				if ( isset( self::$_parent->field_sections['social_profiles'] ) ) {
					return reset( self::$_parent->field_sections['social_profiles'] );
				}

				$arr = self::$_parent;

				foreach ( $arr as $part => $bla ) {
					if ( $part == 'sections' ) {
						foreach ( $bla as $section => $field ) {

							foreach ( $field as $arg => $val ) {
								if ( $arg == 'fields' ) {
									foreach ( $val as $k => $v ) {
										foreach ( $v as $id => $x ) {
											if ( $id == 'type' ) {
												if ( $x == 'social_profiles' ) {
													return $v;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		public static function add_extra_icons( $defaults ) {
			if ( empty( self::$_field ) ) {
				self::$_field = self::getField();
			}

			if ( isset( self::$_field['icons'] ) && ! empty( self::$_field['icons'] ) ) {
				$cur_count = count( $defaults );

				foreach ( self::$_field['icons'] as $idx => $arr ) {

					$skip_add = false;
					foreach ( $defaults as $i => $v ) {
						if ( $arr['id'] == $v['id'] ) {

							$defaults[ $i ] = array_replace( $defaults[ $i ], $arr );
							$skip_add       = true;
							continue;
						}
					}

					if ( ! $skip_add ) {
						$arr['order']           = $cur_count;
						$defaults[ $cur_count ] = $arr;
						$cur_count ++;
					}
				}
			}

			return $defaults;
		}

		private static function get_includes( $val ) {
			if ( empty( self::$_field ) ) {
				self::$_field = self::getField();
			}

			if ( isset( self::$_field['include'] ) && is_array( self::$_field['include'] ) && ! empty( self::$_field['include'] ) ) {
				$icons = self::$_field['include'];
				//var_dump($icons);
				//var_dump($val);
				$new_arr = array();

				$idx = 0;
				foreach ( $val as $arr ) {
					foreach ( $icons as $icon ) {
						if ( $arr['id'] == $icon ) {
							$arr['order']    = $idx;
							$new_arr[ $idx ] = $arr;
							$idx ++;
							continue;
						}
					}
				}
			} else {
				$new_arr = $val;
			}

			return $new_arr;

		}

		public static function get_default_data() {
			$data = reduxSocialProfilesDefaults::get_social_media_defaults();
			$data = self::get_includes( $data );
			$data = self::add_extra_icons( $data );

			return $data;
		}

		/**
		 * static function to render the social icon
		 */
		public static function render_icon( $icon, $color, $background, $title, $echo = true ) {
			if ( $color || $background ) {
				if ( $color == '' ) {
					$color = 'transparent';
				}

				if ( $background == '' ) {
					$background = 'transparent';
				}

				$inline = "style='color:" . $color . ";background-color:" . $background . ";'";
			} else {
				$inline = "";
			}

			$str = '<i class="fa ' . $icon . '" ' . $inline . ' title="' . $title . '"></i>';

			if ( $echo ) {
				echo $str;
			} else {
				return $str;
			}
		}
	}
}