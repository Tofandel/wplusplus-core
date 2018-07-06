<?php
/**
 * @package     Redux Framework
 * @subpackage  Redux Color Schemes
 * @author      Kevin Provance (kprovance)
 * @version     2.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//print_r($GLOBALS['rdxIO']);

if ( ! class_exists( 'ReduxColorSchemeFunctions' ) ) {
	class ReduxColorSchemeFunctions {

		// public variables
		static public $_parent;
		static public $_field_id;
		static public $_field_class;
		static public $_field;
		static public $upload_dir;
		static public $upload_url;
		static public $groups;
		static public $select;

		/**
		 * wpFilesystemInit Function.
		 *
		 * Init WP filesystem, just in case.
		 *
		 * @since       1.0.0
		 * @access      static public
		 * @return      void
		 */
		static public function init( $parent ) {
			self::$_parent = $parent;

			if ( empty( self::$_field_id ) ) {
				self::$_field    = self::getField( $parent );
				self::$_field_id = self::$_field['id'];
			}

			// Make sanitized upload dir DIR
			self::$upload_dir = Redux_Helpers::cleanFilePath( ReduxFramework::$_upload_dir . 'color-schemes/' );

			// Make sanitized upload dir URL
			self::$upload_url = Redux_Helpers::cleanFilePath( ReduxFramework::$_upload_url . 'color-schemes/' );

			Redux_Functions::initWpFilesystem();
		}

		public static function tooltipsInUse( $field ) {
			$blocks = $field['default'];

			foreach ( $blocks as $idx => $arr ) {
				if ( isset( $arr['tooltip'] ) ) {
					return true;
				}
			}
		}

		public static function convertToDB() {
			$upload_dir = Redux_Helpers::cleanFilePath( ReduxFramework::$_upload_dir . 'color-schemes/' );

			$cur_scheme_file = Redux_Helpers::cleanFilePath( $upload_dir . '/' . self::$_parent->args['opt_name'] . '_' . self::$_field_id . '.json' );

			if ( is_dir( $upload_dir ) ) {
				if ( file_exists( $cur_scheme_file ) ) {
					$data = self::$_parent->filesystem->execute( 'get_contents', $cur_scheme_file );
					if ( ! empty( $data ) ) {
						$data = json_decode( $data, true );

						update_option( self::getSchemeKey(), $data );

						self::$_parent->filesystem->execute( 'delete', $cur_scheme_file );
					}
				}
			}
		}

		public static function getSchemeKey() {
			return 'redux_cs_' . self::$_parent->args['opt_name'] . '_' . self::$_field_id;
		}

		/**
		 * getGroupNames Function.
		 *
		 * Get the list of groups names for the color scheme table.
		 *
		 * @since       2.0.0
		 * @access      static public
		 * @return      array Array of group names.
		 */
		static public function getGroupNames() {
			if ( empty( self::$_field ) ) {
				self::$_field = self::getField();
			}

			if ( isset( self::$_field['groups'] ) ) {
				if ( is_array( self::$_field['groups'] ) && ! empty( self::$_field['groups'] ) ) {
					return self::$_field['groups'];
				}
			}
		}

		static public function getOutputTransparentVal() {
			if ( empty( self::$_field ) ) {
				self::$_field = self::getField();
			}

			if ( isset( self::$_field['output_transparent'] ) ) {
				if ( ! empty( self::$_field['output_transparent'] ) ) {
					return self::$_field['output_transparent'];
				}
			}
		}

		static private function getSelectNames() {
			if ( empty( self::$_field ) ) {
				self::$_field = self::getField();
			}

			if ( isset( self::$_field['select'] ) ) {
				if ( is_array( self::$_field['select'] ) && ! empty( self::$_field['select'] ) ) {
					return self::$_field['select'];
				}
			}
		}

		static public function getField( $parent = array() ) {

			if ( ! empty( $parent ) ) {
				self::$_parent = $parent;
			}

			if ( isset( $parent->field_sections['color_scheme'] ) ) {
				return reset( $parent->field_sections['color_scheme'] );
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
											if ( $x == 'color_scheme' ) {
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

		/**
		 * getSchemeSelectHTML Function.
		 *
		 * Output scheme dropdown selector.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $selected Selected scheme name
		 *
		 * @return      string HTML of dropdown selector.
		 */
		static public function getSchemeSelectHTML( $selected ) {

			$html = '<select name="' . esc_attr( self::$_parent->args['opt_name'] ) . '[redux-scheme-select]" id="redux-scheme-select-' . esc_attr( self::$_field_id ) . '" class="redux-scheme-select">';
			$html .= ReduxColorSchemeFunctions::getSchemeListHTML( $selected );
			$html .= '</select>';

			return $html;
		}

		/**
		 * setCurrentSchemeID Function.
		 *
		 * Set current scheme ID, if one isn't specified.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $id Scheme name to set.
		 *
		 * @return      void
		 */
		static public function setCurrentSchemeID( $id ) {

			// Get opt name, for database
			$opt_name = self::$_parent->args['opt_name'];

			// Get all options from database
			$redux_options = get_option( $opt_name, array() );
			if ( ! is_array( $redux_options ) ) {
				$redux_options = array();
			}
			// Append ID to variable that holds the current scheme ID data
			$redux_options['redux-scheme-select'] = $id;

			// Save the modified settings
			update_option( $opt_name, $redux_options );
		}

		static public function getTooltipToggleState() {

			// Retrieve the opt_name, needed for databasae
			$opt_name = self::$_parent->args['opt_name'];

			// Get the entire options array
			$redux_options = get_option( $opt_name );

			return isset( $redux_options['redux-color-scheme-tooltip-toggle'] ) ? $redux_options['redux-color-scheme-tooltip-toggle'] : true;
		}

		/**
		 * getCurrentSchemeID Function.
		 *
		 * Gets the current schem ID from the database.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $id Scheme name to set.
		 *
		 * @return      string Current scheme ID.
		 */
		static public function getCurrentSchemeID() {

			// Retrieve the opt_name, needed for databasae
			$opt_name = self::$_parent->args['opt_name'];

			// Get the entire options array
			$redux_options = get_option( $opt_name );

			// If the current scheme key exists...
			if ( isset( $redux_options['redux-scheme-select'] ) ) {

				// yank it out and return it.
				return $redux_options['redux-scheme-select'];
			} else {

				// Otherwise, return 0/false.
				return 'Default';
			}
		}

		/**
		 * getSchemeListHTML Function.
		 *
		 * Get the list of schemes for the selector.
		 *
		 * @since       1.0.0
		 * @access      static private
		 *
		 * @param       string $sel Scheme name to select.
		 *
		 * @return      string HTML option values.
		 */
		static private function getSchemeListHTML( $sel = '' ) {
			// no errors, please.
			$html = '';

			// Retrieves the list of saved schemes into an array variable
			$dropdown_values = self::getSchemeNames();

			// If the dropdown array has items...
			if ( ! empty( $dropdown_values ) ) {

				// Sort them alphbetically.
				asort( $dropdown_values );
			}

			// trim the selected item
			trim( $sel );

			// If it's empty
			if ( '' == $sel ) {

				// Make the current scheme id the selected value
				$selected = self::getCurrentSchemeID();
			} else {

				// Otherwise, set it to the value passed to this function.
				$selected = $sel;
			}

			// Enum through the dropdown array and append the necessary HTML for the selector.
			foreach ( $dropdown_values as $k ) {
				$html .= '<option value="' . $k . '"' . selected( $k, $selected, false ) . '>' . $k . '</option>';
			}

			// Send it all packin'.
			return $html;
		}

		/**
		 * renderSelects Function.
		 *
		 * Returns select HTML.
		 *
		 * @since       1.0.4
		 * @access      static private
		 *
		 * @param       array $arr Array of select fields to render.
		 * @param       array $data Array of scheme data.
		 *
		 * @return      string HTML of select fields.
		 */
		static private function renderSelects( $arr, $data ) {

			$html = '';
			foreach ( $arr as $k => $v ) {
				$id = $v['id'];

				if ( isset( $v['width'] ) && ! empty( $v['width'] ) ) {
					$size = $v['width'];
				} else {
					$size = '40%';
				}

				$width = ' style="width: ' . $size . ';"';

				$html .= '<label class="redux-color-scheme-opt-select-title">' . $v['title'] . '</label>';

				$html .= '<select name="' . self::$_parent->args['opt_name'] . '[' . self::$_field_id . '][' . $id . ']" id="redux-color-scheme-opt-select-' . $id . '"' . $width . ' class="redux-color-scheme-opt-select">';

				foreach ( $v['options'] as $opt_id => $opt_val ) {
					$data[ $id ]['value'] = isset( $data[ $id ]['value'] ) ? $data[ $id ]['value'] : '';
					$html                 .= '<option value="' . $opt_id . '"' . selected( $opt_id, $data[ $id ]['value'], false ) . '>' . $opt_val . '</option>';
				}

				$html .= '</select>';
				$html .= '<label class="redux-color-scheme-opt-select-desc">' . $v['desc'] . '</label>';
				$html .= '<hr class="redux-color-scheme-select-close-hr">';
				$html .= '<br/>';
			}

			return $html;
		}

		static private function do_diff( $first_array, $second_array ) {
			function my_serialize( &$arr, $pos ) {
				$arr = serialize( $arr );
			}

			function my_unserialize( &$arr, $pos ) {
				$arr = unserialize( $arr );
			}

			//make a copy
			$first_array_s  = $first_array;
			$second_array_s = $second_array;

			// serialize all sub-arrays
			array_walk( $first_array_s, 'my_serialize' );
			array_walk( $second_array_s, 'my_serialize' );

			// array_diff the serialized versions
			$diff = array_diff( $first_array_s, $second_array_s );

			// unserialize the result
			array_walk( $diff, 'my_unserialize' );

			// you've got it!
			//print_r($diff);
			return $diff;
		}

		/**
		 * getCurrentColorSchemeHTML Function.
		 *
		 * Returns colour pickers HTML table.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $scheme_id Scheme name of HTML to return.
		 *
		 * @return      string HTML of colour picker table.
		 */
		static public function getCurrentColorSchemeHTML( $scheme_id = false ) {

			// If scheme_id is false
			if ( ! $scheme_id ) {

				// Attempt to get the current scheme
				$scheme_id = ReduxColorSchemeFunctions::getCurrentSchemeID();

				// dummy check, because this shit happens!
				$arrSchemes = self::getSchemeNames();
				if ( ! in_array( $scheme_id, $arrSchemes ) ) {
					$scheme_id = 'Default';
					self::setCurrentSchemeID( 'Default' );
				}
			}

			// Set oft used variables.
			$opt_name    = esc_attr( self::$_parent->args['opt_name'] );
			$field_id    = esc_attr( self::$_field_id );
			$field_class = esc_attr( self::$_field_class );

			// get the default options
			//$defOpts = self::$_parent->options_defaults[$field_id];
			$field = self::getField();

			$field['output_transparent'] = isset( $field['output_transparent'] ) ? $field['output_transparent'] : '';
			$is_accordion                = isset( $field['accordion'] ) ? $field['accordion'] : true;

			$defOpts = $field['default'];

			// Create array of element ids from default options
			if ( ! empty( $defOpts ) ) {
				$idArr = array();

				foreach ( $defOpts as $kk => $vv ) {
					$idArr[] = $vv['id'];
				}
			}

			// Get last saved default
			$saved_def = get_option( 'redux_' . $opt_name . '_' . $field_id . '_color_scheme' );

			// Compare key counts between saved and current defaults to check
			// for changes in color scheme.
			if ( $saved_def != false ) {
				//if (count($defOpts) <> count($saved_def) ) {

				// Get the new color inputs
				$arr_diff = self::do_diff( $defOpts, $saved_def );

				if ( ! empty( $arr_diff ) ) {
					update_option( 'redux_' . $opt_name . '_' . $field_id . '_color_scheme', $defOpts );
				}                //}
			} else {
				update_option( 'redux_' . $opt_name . '_' . $field_id . '_color_scheme', $defOpts );
			}

			// get current scheme data
			$scheme = self::getSchemeData( $scheme_id );

			// If new color inputs exist...
			if ( ! empty( $arr_diff ) ) {
				foreach ( $arr_diff as $key => $val ) {
					if ( ! empty( $val ) && isset( $val['id'] ) ) {

						$val['title'] = isset( $val['title'] ) ? $val['title'] : $val['id'];
						$val['color'] = isset( $val['color'] ) ? $val['color'] : '';
						$val['alpha'] = isset( $val['alpha'] ) ? $val['alpha'] : 1;

						$trans        = $field['output_transparent'];
						$res          = ( $val['color'] == '' || $val['color'] == 'transparent' ) ? $trans : Redux_Helpers::hex2rgba( $val['color'], $val['alpha'] );// . ',' . $val['alpha'];
						$val['rgba']  = isset( $val['rgba'] ) ? $val['rgba'] : $res;
						$val['group'] = isset( $val['group'] ) ? $val['group'] : '';

						$scheme[ $val['id'] ] = $val;
					}
				}

				// Get list of scheme names
				$scheme_names = self::getSchemeNames();

				// Update is saved scheme with new picker data
				foreach ( $scheme_names as $idx => $name ) {
					self::setSchemeData( $name, $scheme );
				}

				// update the database
				self::setDatabaseData( $scheme_id );
			}

			// If it's not empty then...
			if ( ! empty( $scheme ) ) {

				// init arrays
				$groups     = array();
				$grp_desc   = array();
				$groups[''] = array();
				$sel_grps   = array();

				if ( ! isset( self::$select ) ) {
					self::$select = self::getSelectNames();
				}

				// Enum select fields into groups array for later render
				if ( isset( self::$select ) ) {
					foreach ( self::$select as $selArrNum => $selArr ) {
						$sel_grp = $selArr['group'];
						if ( ! array_key_exists( $sel_grp, $sel_grps ) ) {
							$sel_grps[ $sel_grp ] = array();
							array_push( $sel_grps[ $sel_grp ], $selArr );
						} else {
							array_push( $sel_grps[ $sel_grp ], $selArr );
						}
					}
				}

				// Enum groups names
				$group_arr = self::getGroupNames();

				if ( ! $group_arr == null ) {
					foreach ( $group_arr as $group_name => $description ) {
						$groups[ $group_name ] = array();

						if ( is_array( $description ) ) {
							$grp_desc[ $group_name ]           = isset( $description['desc'] ) ? $description['desc'] : '';
							$grp_grpdesc[ $group_name ]        = isset( $description['group_desc'] ) ? $description['group_desc'] : '';
							$grp_hidden[ $group_name ]         = isset( $description['hidden'] ) ? $description['hidden'] : false;
							$grp_accordion_open[ $group_name ] = isset( $description['accordion_open'] ) ? $description['accordion_open'] : false;

						} else {
							$grp_desc[ $group_name ]           = $description;
							$grp_hidden[ $group_name ]         = false;
							$grp_accordion_open[ $group_name ] = false;
							$grp_grpdesc[ $group_name ]        = false;
						}
					}
				}

				// Assing color pickers to their specified group
				foreach ( $scheme as $id => $arr ) {
					if ( is_array( $arr ) ) {
						if ( ! empty( $arr['group'] ) && $group_arr != null ) {
							if ( array_key_exists( $arr['group'], $group_arr ) ) {
								array_push( $groups[ $arr['group'] ], $arr );
							} else {
								array_push( $groups[''], $arr );
							}
						} else {
							array_push( $groups[''], $arr );
						}
					}
				}

				$open_icon  = '';
				$close_icon = '';

				if ( $is_accordion ) {
					$open_icon  = apply_filters( 'redux/extension/color_scheme/' . self::$_parent->args['opt_name'] . '/icon/open', 'dashicons dashicons-arrow-down' );
					$close_icon = apply_filters( 'redux/extension/color_scheme/' . self::$_parent->args['opt_name'] . '/icon/close', 'dashicons dashicons-arrow-up' );
				}

				// open the list
				$html = '<ul class="redux-scheme-layout" data-open-icon="' . $open_icon . '" data-close-icon="' . $close_icon . '">';

				// Enumerate groups
				foreach ( $groups as $title => $schemeArr ) {

					if ( $title == '' ) {
						if ( empty( $schemeArr ) ) {
							continue;
						}

						$kill_me = false;
						if ( ! empty( $schemeArr ) ) {
							foreach ( $schemeArr as $idx => $data ) {
								if ( ! array_key_exists( 'color', $data ) ) {
									$kill_me = true;
									break;
								}
							}
							if ( $kill_me ) {
								continue;
							}
						}
					}

					$addHR      = false;
					$is_hidden  = false;
					$class_hide = '';
					$is_open    = '';

					if ( isset( $grp_hidden[ $title ] ) && $grp_hidden[ $title ] !== '' ) {
						$is_hidden  = $grp_hidden[ $title ];
						$class_hide = ( $is_hidden == true ) ? ' hidden ' : '';
						$is_open    = $grp_accordion_open[ $title ];
					}

					$add_class = '';
					if ( $is_accordion ) {
						$add_class = ' accordion ';
					}

					$html .= '<div class="redux-color-scheme-group' . $add_class . $class_hide . '">';

					if ( ! $is_hidden ) {

						if ( $is_accordion ) {
							$html .= '<div class="redux-color-scheme-accordion">';
						}
						$icon_class = '';

						// apply group title, if any.
						if ( $title !== '' ) {
							$html .= '<br><label class="redux-layout-group-label">' . esc_attr( $title ) . '</label>';

							if ( $is_accordion ) {
								$icon_class = ' titled';
							}
							$addHR = true;
						} else {
							if ( $is_accordion ) {
								$icon_class = ' not-titled';
							}
						}

						// apply group description, if any.
						if ( isset( $grp_desc[ $title ] ) && $grp_desc[ $title ] !== '' ) {
							$html  .= '<label class="redux-layout-group-desc-label' . $icon_class . '">' . esc_attr( $grp_desc[ $title ] ) . '</label>';
							$addHR = true;

							if ( $is_accordion ) {
								$icon_class .= ' subtitled';
							}
						}

						if ( $is_accordion ) {
							$html .= '<span class="' . esc_attr( $open_icon ) . $icon_class . '"></span>';
						}

						// Add HR, if needed
						if ( $addHR == true ) {
							if ( ! $is_accordion ) {
								$html .= '<hr>';
							}
						}

						if ( $is_accordion ) {
							$html .= '</div>';
							$html .= '<div class="redux-color-scheme-accordion-section" data-state="' . esc_attr( $is_open ) . '">';
							if ( $grp_grpdesc != false ) {
								$html .= '<div class="redux-color-scheme-group-desc">';
								$html .= esc_attr( $grp_grpdesc[ $title ] );
								$html .= '</div>';
							}
						}

						// Select box render
						if ( array_key_exists( $title, $sel_grps ) ) {
							$html .= self::renderSelects( $sel_grps[ $title ], $scheme );
						}
					} else {
						if ( $is_accordion ) {
							$html .= '<div class="redux-color-scheme-accordion-section">';
						}
					}

					// Enum through each element/id
					foreach ( $schemeArr as $k => $v ) {
						if ( in_array( $v['id'], $idArr ) ) {

							// If no title, use ID.
							$v['title'] = isset( $v['title'] ) ? $v['title'] : $v['id'];

							// If no alpha, use 1 (solid)
							$v['alpha'] = isset( $v['alpha'] ) ? $v['alpha'] : 1;

							// Fuck forbid no colour, set to white
							$v['color'] = isset( $v['color'] ) ? $v['color'] : '';

							// RGBA
							$trans     = $field['output_transparent'];
							$res       = ( $v['color'] == '' || $v['color'] == 'transparent' ) ? $trans : Redux_Helpers::hex2rgba( $v['color'], $v['alpha'] );// . ',' . $v['alpha'];
							$v['rgba'] = isset( $v['rgba'] ) ? $v['rgba'] : $res;

							// group name
							$v['group'] = isset( $v['group'] ) ? $v['group'] : '';

							$v['class'] = self::get_color_block_class( $field, $v['id'] );

							$block_hide = self::getBlockHidden( $field, $v['id'] ) ? 'hidden' : '';

							// tooltips
							$tip_title = '';
							$tip_text  = '';

							$tooltip_data = self::getTooltipData( $field, $v['id'] );
							if ( $tooltip_data != false ) {
								$tip_title = isset( $tooltip_data['title'] ) ? $tooltip_data['title'] : '';
								$tip_text  = isset( $tooltip_data['text'] ) ? $tooltip_data['text'] : '';
							}

							// Begin the layout
							$html .= '<li class="redux-scheme-layout ' . $class_hide . ' redux-cs-qtip ' . $block_hide . '" qtip-title="' . esc_attr( $tip_title ) . '" qtip-content="' . esc_attr( $tip_text ) . '">';
							$html .= '<div class="redux-scheme-layout-container" data-id="' . $field_id . '-' . $v['id'] . '">';

							if ( '' == $v['color'] || 'transparent' == $v['color'] ) {
								$color = '';
							} else {
								$color = 'rgba(' . $v['rgba'] . ')';
							}

							// colour picker dropdown
							$html .= '<input
                                        id="' . $field_id . '-' . esc_attr( $v['id'] ) . '-color"
                                        class="' . $field_class . ' ' . esc_attr( $v['class'] ) . '"
                                        type="text"
                                        data-color="' . esc_attr( $color ) . '"
                                        data-hex-color="' . esc_attr( $v['color'] ) . '"
                                        data-alpha="' . esc_attr( $v['alpha'] ) . '"
                                        data-rgba="' . esc_attr( $v['rgba'] ) . '"
                                        data-title="' . esc_attr( $v['title'] ) . '"
                                        data-id="' . esc_attr( $v['id'] ) . '"
                                        data-group="' . esc_attr( $v['group'] ) . '"
                                        data-current-color="' . esc_attr( $v['color'] ) . '"
                                        data-block-id="' . $field_id . '-' . esc_attr( $v['id'] ) . '"
                                        data-output-transparent="' . esc_attr( $field['output_transparent'] ) . '"
                                      />';

							$scheme_data = self::getSchemeData( $scheme_id );
							$picker_data = $scheme_data[ $v['id'] ];

							// Hidden input for data string
							$html .= '<input
                                        type="hidden"
                                        class="redux-hidden-data"
                                        name="' . $opt_name . '[' . $field_id . ']' . '[' . esc_attr( $v['id'] ) . '][data]"
                                        id="' . $field_id . '-' . esc_attr( $v['id'] ) . '-data"
                                        value="' . rawurlencode( json_encode( $picker_data ) ) . '"
                                      />';

							// closing html tags
							$html .= '</div>';
							$html .= '<label class="redux-layout-label">' . esc_attr( $v['title'] ) . '</label>';
							$html .= '</li>';
						}
					}

					$html .= '<hr class="redux-color-scheme-blank-hr">';

					if ( $is_accordion ) {
						$html .= '</div>';
					}

					$html .= '</div>';
				}

				// Close list
				$html .= "</ul>";
			}

			// html var not empty, return it.
			if ( ! empty( $html ) ) {
				return $html;
			}
		}

		static private function get_color_block_class( $field, $id ) {
			$def = $field['default'];

			if ( ! empty( $def ) ) {
				foreach ( $def as $idx => $arr ) {
					if ( $arr['id'] == $id ) {
						if ( isset( $arr['class'] ) ) {
							return $arr['class'];
						}

						return '';
					}
				}
			}
		}

		static private function getTooltipData( $field, $id ) {
			$def = $field['default'];

			if ( ! empty( $def ) ) {
				foreach ( $def as $idx => $arr ) {
					if ( $arr['id'] == $id ) {
						if ( isset( $arr['tooltip'] ) ) {
							return $arr['tooltip'];
						}

						return false;
					}
				}
			}
		}

		static private function getBlockHidden( $field, $id ) {
			$def = $field['default'];

			if ( ! empty( $def ) ) {
				foreach ( $def as $idx => $arr ) {
					if ( $arr['id'] == $id ) {
						if ( isset( $arr['hidden'] ) ) {
							return $arr['hidden'];
						}

						return false;
					}
				}
			}
		}

		/**
		 * readSchemeFile Function.
		 *
		 * Returns scheme file contents.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $file Optional file name
		 * @param       bool $decode Flag to return JSON decoded data.
		 *
		 * @return      array Array of scheme data.
		 */
		static public function readSchemeFile() {
			$key  = self::getSchemeKey();
			$data = get_option( $key );

			if ( empty( $data ) ) {
				$arrData = false;
			} else {
				$arrData = $data;
			}

			return $arrData;
		}

		/**
		 * writeSchemeFile Function.
		 *
		 * Sets scheme file contents.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       array $arrData PHP array of data to encode.
		 * @param       string $file Optional file name to override default.
		 *
		 * @return      bool Result of write function.
		 */
		static public function writeSchemeFile( $arrData ) {
			$key     = self::getSchemeKey();
			$ret_val = update_option( $key, $arrData );

			return $ret_val;
		}

		/**
		 * getSchemeData Function.
		 *
		 * Gets individual scheme data from scheme JSON file.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $scheme_name Name of scheme.
		 *
		 * @return      array PHP array of scheme data.
		 */
		static public function getSchemeData( $scheme_name ) {
			$data = self::readSchemeFile();

			if ( false == $data ) {
				return false;
			}

			$data = $data[ $scheme_name ];

			return $data;
		}

		/**
		 * setSchemeData Function.
		 *
		 * Sets individual scheme data to scheme JSON file.
		 *
		 * @since       1.0.0
		 * @access      static public
		 *
		 * @param       string $name Name of scheme to save.
		 * @param       array $array Scheme data to encode
		 *
		 * @return      bool Result of file write.
		 */
		static public function setSchemeData( $name, $array ) {

			// Create blank array
			$new_scheme = array();

			// If name is present
			if ( $name ) {

				// then add the name at the new array's key
				$new_scheme['color_scheme_name'] = $name;

				// Enum through values and assign them to new array
				foreach ( $array as $item => $val ) {
					if ( isset( $val['id'] ) ) {
						$new_scheme[ $val['id'] ] = $val;
					}
				}

				// read the contents of the current scheme file
				$schemes = self::readSchemeFile();

				// If returned false (not there) then create a new array
				if ( false == $schemes ) {
					$schemes = array();
				}

				$scheme_data = isset( $schemes[ $name ] ) ? $schemes[ $name ] : '';

				if ( $scheme_data != $new_scheme ) {

					// Add new scheme to array that will be saved.
					$schemes[ $name ] = $new_scheme;

					// Write the data to the JSON file.
					return self::writeSchemeFile( $schemes );
				}
			}

			// !success
			return false;
		}

		/**
		 * getSchemeNames Function.
		 *
		 * Enumerate the scheme names from the JSON store file.
		 *
		 * @since       1.0.0
		 * @access      static public
		 * @return      array Array of stored scheme names..
		 */
		static public function getSchemeNames() {

			// Read the JSON file, which returns a PHP array
			$schemes = self::readSchemeFile();

			// Create a new array
			$output = array();

			if ( false != $schemes ) {

				// If the schemes array IS an array (versus false), then...
				if ( is_array( $schemes ) ) {

					// Enum them
					foreach ( $schemes as $scheme ) {

						// If the color_scheme_name key is set...
						if ( isset( $scheme['color_scheme_name'] ) ) {

							// Push it onto the array stack.
							$output[] = $scheme['color_scheme_name'];
						}
					}
				}
			}

			// Kick the full array out the door.
			return $output;
		}

		public static function data_array_from_scheme( $scheme ) {
			// Get scheme data from JSON file
			$data = self::getSchemeData( $scheme );
			// Don't need to save select arrays to database,
			// just the id => value.
			if ( ! empty( $data ) ) {
				foreach ( $data as $k => $v ) {
					if ( isset( $v['type'] ) ) {
						$val = $v['value'];

						unset( $data[ $k ] );

						$data[ $k ] = $val;
					}
				}
			}

			return $data;
		}

		/**
		 * setDatabaseData Function.
		 *
		 * Sets current scheme to database.
		 *
		 * @since       1.0.0
		 * @access      private
		 *
		 * @param       string $scheme Current scheme name
		 *
		 * @return      void
		 */
		public static function setDatabaseData( $scheme = 'Default' ) {

			$data = self::data_array_from_scheme( $scheme );

			// Get opt name, for database
			$opt_name = self::$_parent->args['opt_name'];

			// Get all options from database
			$redux_options = get_option( $opt_name );

			if ( empty( self::$_field_id ) ) {
				self::$_field    = self::getField();
				self::$_field_id = self::$_field['id'];
			}

			// Append ID to variable that holds the current scheme ID data
			$redux_options[ self::$_field_id ] = $data;

			// Save the modified settings
			update_option( $opt_name, $redux_options );
		}
	}
}
