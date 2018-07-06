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
 * @package     Redux Framework
 * @subpackage  Serialized Repeater
 * @version     1.0.6
 * @author      Shannon Little <codingman@yahoo.com>
 * @author      Dovy Paukstys (dovy)
 * @author      Kevin Provance (kprovance)
 */
/*
This field will display a group of fields within a repeatable container
All the field data will be serialized and stored in a single entry


Field specific options:
  display_type                string      simple, card, accordion.  Sets the display type of the repeater.  Default is 'accordion'.
  accordion_style             string      single, collapsible, multiple.  Sets the default style, user can change.  Default is 'collapsible'.
  accordion_state             string      closed, first, all.  Which accordion pane(s) are open by default.  Closed and all are not available if style is 'single'. Default is 'first'.
  fields                      array       Array of fields to display in repeater.
  // NOT IMPLEMENTED  sortable                    boolean     Allow your users to drag/drop repeater blocks and by so doing reorder the results. Default is true.
  limit                       int         Limits the number of repeater blocks that can be created.  Set to 0 for no limit (default).
  static                      int         Fixed number of repeater rows to display. This will also disable the add/delete buttons next to each repeater block. Set to 0 to disable (default).
  item_name                   string      Added after Add/Delete to denote the name of the items you are adding or deleting.
  bind_title                  string      By default the first field will be used as the title for each repeater block. You may also pass in a string denoting an ID to use as the title for each repeater block.
	bind_to                   string      The ID of the field to bind the title to.
	separator                 string      Separator string for field that use options.
	limit                     string      Number of characters to limit in the title. Default is 60.  The prefix and postfix strings are included in the limit, but only the bound title text is clipped.
	limit_more                string      String to put at the end of the title if it was clipped. Default is ...
	prefix                    string      String to add before the title.
	postfix                   string      String to add after the title.

Display Types
  simple      For repeating a single field or row of fields, like a textbox.  No grouping background/border.  Simple X to delete row.
  card        For repeating a block of fields.  Similar to simple but with a gray box around the items to show they are grouped.  Simple X to delete row.
  accordion   For repeating a block of fields.  The block has a header at the top which can expand/collapse the repeater content.  Full Delete button to delete row.

Accordion Style
  single        A single accordion pane is open at all times.
  collapsible   A single accordion pane can opened or they can all be closed.
  multiple      Multiple accordion panes can be open or they can all be closed.

Accordion State
  closed        All the accordion panes are closed.           Not available if accordion_style is 'single'
  first         Only the first accordion pane will be open.
  all           All the accordion panes will be open.         Only available if accordion_style is 'multiple'
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_serialized_repeater' ) ) {

	/**
	 * Main ReduxFramework_css_layout class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_serialized_repeater {
		public $display_types = array( 'simple', 'card', 'accordion' );
		public $default_display_type = 2;

		public $accordion_styles = array( 'single', 'collapsible', 'multiple' );
		public $default_accordion_style = 1;

		public $accordion_states = array( 'closed', 'first', 'all' );
		public $default_accordion_state = 1;

		public static $recursion_level = 0;
		public static $render_all_repeaters = false;
		public static $root_id = '';


		/**
		 * Class Constructor. Defines the args for the extensions class
		 *
		 * @since       1.0.0
		 *
		 * @param       array $field Field sections.
		 * @param       array $value Values.
		 * @param       array $parent Parent object.
		 */
		public function __construct( $field = array(), $value = '', $parent ) {
			// e( __METHOD__ );

			// Set required variables
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;
			$this->args   = $parent->args;

			// Set extension dir & url
			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}
		}


		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 */
		public function render() {
			// e( __METHOD__ );
			// e("RENDER", static::$recursion_level, 10, static::$recursion_level * 3);

			$this->validate_settings();


			$title = '';

			// error_log("this->value");
			// error_log(print_r($this->value,true));

			// e($this->field['display_type']);


			// Outer container of entire repeater
			echo "\n" . '<div class="redux-serialized-repeater-row-container type-' . $this->field['display_type'] . '" data-fieldname="' . $this->field['name'] . '">';
			// echo '<input type="hidden" name="' . $this->parent->args['opt_name'] . '[' . $this->field['id'] . ']" value="' . esc_attr( json_encode( $this->value, JSON_HEX_QUOT ) ) . '" data-key="' . $x . '" />';

			// error_log("Data row count: " . count( $this->value ));

			if ( static::$render_all_repeaters ) {
				// Force all repeaters to render 1 row for the Javascript template
				$row_count = 1;
			} else {
				// Output 1 row for each row of data
				$row_count = count( $this->value );

				// Add extra rows until we're up to the 'static' number if 'static' is specified
				if ( $this->field['static'] > 0 && $row_count < $this->field['static'] ) {
					$row_count += $this->field['static'] - $row_count;
				}
			}

			// e("Total rows to output", $row_count, 10, static::$recursion_level * 3 + 1);

			// Display fields with data if existing data is present
			// $i represents which index in the $this->value array output_field will pull the data from the field
			for ( $i = 0; $i < $row_count; $i ++ ) {
				// e("ROW", '', 10, static::$recursion_level * 3 + 1);
				// e("Total field to output", count($this->field['fields']), 10, static::$recursion_level * 3 + 2);

				echo $this->get_repeater_before_html( $i );

				// e('Rendering fields');

				// Render each field in this repeater row
				foreach ( $this->field['fields'] as $field ) {

					if ( in_array( $field['type'], array( 'divide', 'info' ) ) ) {

						continue;
					}
					if ( isset( $field['required'] ) ) {
						unset( $field['required'] );
					}
					// e("FIELD", $field, 10, static::$recursion_level * 3 + 2);
					$this->output_field( $field, $i );
				}

				if ( $this->field['static'] == 0 ) {
					echo $this->get_delete_button_html();
				}

				echo $this->get_repeater_after_html();
			}

			echo '</div>' . "\n"; // div.redux-serialized-repeater-row-container

			// Render Add button
			$button_classes = array(
				'button',
				'button-primary',
				'redux-serialized-repeater-add',
			);

			// Fields rendered using static rows don't have an Add button (it's just hidden because sorting requires the data on the button)
			if ( $this->field['static'] != 0 ) {
				$button_classes[] = 'hidden';
			}

			// Disable the Add button if the number of rows is at or above the limit
			if ( $this->field['limit'] > 0 && $row_count >= $this->field['limit'] ) {
				$button_classes[] = 'button-disabled';
			}

			//e("Displaying ADD BUTTON");
			//e($this->field);

			// Display the Add button
			$button_text = __( 'Add', 'redux-framework' );

			if ( $this->field['item_name'] != '' ) {
				$button_text .= ' ' . $this->field['item_name'];
			}

			echo '<a href="#" class="' . implode( ' ', $button_classes ) . '" ' .
			     'data-rootid="' . esc_attr( static::$root_id ) . '" ' .
			     'data-settingsid="' . esc_attr( $this->parent->args['opt_name'] ) . '" ' .
			     'data-fieldname="' . esc_attr( $this->field['name'] ) . '" ' .
			     'data-limit="' . esc_attr( $this->field['limit'] ) . '" ' .
			     'data-level="' . static::$recursion_level . '" ' .
			     'data-count="' . ( static::$render_all_repeaters ? 0 : $row_count ) . '" ' . // The templates requires the values to be 0
			     'data-counter="' . ( static::$render_all_repeaters ? 0 : $row_count ) . '" ' . // as it regenerates all the counts when inserted into the page
			     //'data-sortable="'         . ( $this->field['sortable'] ? 'true' : 'false' )     . '" ' . // This option is not implemented and won't be unless a client specifically asks for it
			     'data-displaytype="' . esc_attr( $this->field['display_type'] ) . '" ' .
			     'data-accordionstyle="' . esc_attr( $this->field['accordion_style'] ) . '" ' .
			     'data-accordionstate="' . esc_attr( $this->field['accordion_state'] ) . '" ' .
			     'data-bindtitleseparator="' . esc_attr( $this->field['bind_title']['separator'] ) . '" ' .
			     'data-bindtitlelimit="' . esc_attr( $this->field['bind_title']['limit'] ) . '" ' .
			     'data-bindtitlemore="' . esc_attr( $this->field['bind_title']['more'] ) . '" ' .
			     'data-bindtitleprefix="' . esc_attr( $this->field['bind_title']['prefix'] ) . '" ' .
			     'data-bindtitlepostfix="' . esc_attr( $this->field['bind_title']['postfix'] ) . '" ' .
			     '>' . $button_text . '</a><br/>';

			// e("RENDER COMPLETE", static::$recursion_level, 10, static::$recursion_level * 3);
		}


		/**
		 * Renders the specified field to the page.
		 * Used by both render() which allows the content to go straight to the page
		 *   and by localize() which captures the output into a buffer and removes it, sending the
		 *    output to the Javascript as a template for making new rows
		 * Notes:
		 *   $this->parent always refers to \ReduxFramework
		 *   $field refers to the sub-field to render within the repeater
		 *   $this->field refers to the repeater field itself
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $field Field to render (its settings array)
		 * @param int $x The index of the field data in the repeater's data array
		 */
		public function output_field( $field, $x = 0 ) {
			// e( __METHOD__, $field['type']);
			// e($field);
			// e("output_field", $field['type'], 20, static::$recursion_level * 5 + 3);
			// e($field, '', 20, static::$recursion_level * 5 + 3);

			if ( ! isset( $field['type'] ) ) {
				return;
			}

			// Allow the field to enqueue any JS/CSS it needs
			$this->enqueue_dependencies( $field['type'] );

			if ( static::$render_all_repeaters ) {
				$x          = 99999;
				$field_id_x = 'index-' . static::$recursion_level . '';
			} else {
				$field_id_x = $x;
			}

			// Add class to allow easier targeting with CSS
			if ( ! isset( $field['class'] ) ) {
				$field['class'] = '';
				// $field['class'] .= ' serialized-repeater';
				// } else {
				// $field['class'] = 'serialized-repeater';
			}

			// Add bind_title class if its value is to be bound to the title (the Javascript looks for this class)
			// e('$field[\'id\']', $field['id']);
			// e('$field', $field);
			// e('$this->field[\'bind_title\'][\'bind_to\']', !empty($this->field['bind_title']['bind_to']) ? $this->field['bind_title']['bind_to'] : '');

			if ( ! empty( $this->field['bind_title']['bind_to'] ) && $field['id'] === $this->field['bind_title']['bind_to'] ) {
				// e('This title is bound to this field');

				if ( ! empty( $field['fieldset_class'] ) ) {
					$field['fieldset_class'] .= ' bind-title';
				} else {
					$field['fieldset_class'] = 'bind-title';
				}
			}

			// e("field['fieldset_class']", !empty($field['fieldset_class']) ? $field['fieldset_class'] : '');

			if ( ! empty( $field['title'] ) ) {
				// Titles for repeater fields are shown a big differently than for regular fields (limited room, maybe just css differences?)
				echo '<h4>' . $field['title'] . '</h4>';
			}

			if ( ! empty( $field['subtitle'] ) ) {
				echo '<span class="description">' . $field['subtitle'] . '</span>';
			}

			$original_field_id = $field['id'];

			// |           Level 0               ||            Level 1              |
			// [repeater_field_id][][sub_field_id][repeater_field_id][][sub_field_id]...

			if ( empty( $this->field['name'] ) ) {
				// When 'permissions' is set to a permission higher than what the current user has on a different section
				// than this on, the 'name' field isn't set at all.
				$field_name = $this->parent->args['opt_name'] . '[' . static::$root_id . ']';
			} else {
				$field_name = $this->field['name'];
			}

			// Append the field id/name to the previous id/name to create one for this level/row
			$field['id']   = $this->field['id'] . '-' . $field_id_x . '-' . $field['id'];
			$field['name'] = $field_name . '[' . $x . '][' . $original_field_id . ']';

			if ( static::$recursion_level == 0 ) {
				// The first level id needs the metabox's opt_name prepended to the front so all the following ids are unique
				// Fieldsets and labels refer to it
				$field['id'] = $this->parent->args['opt_name'] . '-' . $field['id'];
			}

			// e("this->field[id]"   , $this->field['id'] , 20 , static::$recursion_level * 5 + 3);
			// e("original_field_id" , $original_field_id , 20 , static::$recursion_level * 5 + 3);
			// e("field[id]"         , $field['id']       , 20 , static::$recursion_level * 5 + 3);
			// e("field[name]"       , $field['name']     , 20 , static::$recursion_level * 5 + 3);
			// e("field"             , $field             , 20 , static::$recursion_level * 5 + 3);

			// Get the field's data from the value array
			if ( isset( $this->field['__value'] ) ) {
				// 2nd or later level
				// Later levels only need to add their id and $x to the passed value array
				$value = $this->field['__value'];
			} else {
				// First level
				// The first level needs to get its value from the parent class
				if ( isset( $this->parent->options[ $this->field['id'] ] ) ) {
					$value = $this->parent->options[ $this->field['id'] ];
				}
			}

			// Overwrite the default value if a value is set
			if ( isset( $value[ $x ][ $original_field_id ] ) ) {
				$value = $value[ $x ][ $original_field_id ];
			} else {
				// No value set, use the default
				if ( isset( $field['default'] ) ) {
					$value = $field['default'];
				} elseif ( isset( $field['options'] ) && ( $field['type'] != "ace_editor" ) ) {
					// Sorter data filter
					if ( $field['type'] == "sorter" && isset( $field['data'] ) && ! empty( $field['data'] ) && is_array( $field['data'] ) ) {
						if ( ! isset( $field['args'] ) ) {
							$field['args'] = array();
						}

						foreach ( $field['data'] as $key => $data ) {
							if ( ! isset( $field['args'][ $key ] ) ) {
								$field['args'][ $key ] = array();
							}

							$field['options'][ $key ] = $this->get_wordpress_data( $data, $field['args'][ $key ] );
						}
					}

					$value = $field['options'];
				} else {
					$value = '';
				}
			}

			// e("this->parent->options", $this->parent->options, 20, static::$recursion_level * 5 + 3);
			// e("this->parent", $this->parent->options[ $this->field['id'] ], 20, static::$recursion_level * 5 + 3);
			// e("Field Value", $value, 10, static::$recursion_level * 5 + 3);

			if ( $field['type'] == 'serialized_repeater' ) {
				// e("RECURSION*******", '', 10, static::$recursion_level * 5 + 3);
				// e("Depth", static::$recursion_level, 10, static::$recursion_level * 5 + 3);
				//$field['recursion_depth'] = static::$recursion_level;

				// Pass a reference to our current position in value array
				$field['__value'] = &$value;
				//e("Set base", '', 10, static::$recursion_level * 5 + 3);

				//e("Setting base fields");
				static::$recursion_level ++;
			}

			// e("this>field", $this->field , 20 , static::$recursion_level * 5 + 3);
			// e("Field Data", $field, 10, (static::$recursion_level > 0 ? static::$recursion_level -1 : static::$recursion_level) * 5 + 3);

			ob_start();
			// e($field, '', 20, static::$recursion_level * 5 + 3);

			// Render the field itself
			// error_log('Calling _field_input');
			$this->parent->_field_input( $field, $value );

			$content = ob_get_contents();

			if ( $field['type'] == 'serialized_repeater' ) {
				static::$recursion_level --;
				// e("END RECURSION*******", '', 10, static::$recursion_level * 5 + 3);
				// e("Depth", static::$recursion_level, 10, static::$recursion_level * 5 + 3);
			}

			//
			//if ( ( $field['type'] === "text" ) && ( $field_is_title ) ) {
			//    $content        = str_replace( '>', 'data-title="true" />', $content );
			//    $field_is_title = false;
			//}

			$_field = apply_filters( 'redux-support-serialized-repeater', $content, $field, 0 );

			ob_end_clean();

			echo $_field;
		}


		/**
		 * Functions to pass data from the PHP to the JS at render time.
		 * localize() is called before render()
		 * localize() -> render() -> output_field() -> _field_input
		 *
		 * @since  1.0.0
		 * @return array Params to be saved as a Javascript object accessible to the UI.
		 */
		public function localize( $field, $value = "" ) {
			// e( __METHOD__ );
			// e( $this->parent );
			// error_log(print_r($field, true));

			$data = array();

			$this->validate_settings();

			if ( isset( $field['fields'] ) && ! empty( $field['fields'] ) ) {
				// Output each field and capture the complete HTML to use as a template for creating new rows
				ob_start();

				static::$render_all_repeaters = true;

				foreach ( $field['fields'] as $f ) {
					//e('FIELD', $f);

					// Add bind_title class if its value is to be bound to the title (the Javascript looks for this class)
					if ( isset( $this->field['bind_title']['bind_to'] ) && $f['id'] == $this->field['bind_title']['bind_to'] ) {
						if ( ! isset( $f['class'] ) || ( isset( $f['title'] ) && empty( $f['title'] ) ) ) {
							$f['class'] = "bind_title";
						} else {
							$f['class'] .= " bind_title";
						}
					}

					// Set its $x to {{index}} so it's easy to search and replace later when inserted into the form

					$this->output_field( $f );
				}

				static::$render_all_repeaters = false;

				$html = ob_get_contents();

				ob_end_clean();

				// Set variables to pass onto the Javascript
				$data = array();

				// This is copy of repeater structure rendered as HTML which is used as a template for when the Add button is clicked
				$data['html'] = '<fieldset>' .
				                '<div class="redux-serialized-repeater-row-container" data-fieldname="' . $this->parent->args['opt_name'] . '[' . static::$root_id . ']' . '">' .
				                $this->get_repeater_before_html( 0 ) .
				                $html .
				                $this->get_delete_button_html() .
				                $this->get_repeater_after_html() .
				                '</div>' .
				                '</fieldset>';

				$data['htmlDataNewReplacementDone'] = false;
			}

			//error_log(print_r($data, true));
			return $data;
		}


		public function get_delete_button_html() {
			if ( $this->field['display_type'] == 'accordion' ) {
				$classes     = ' button deletion';
				$button_text = __( 'Delete', 'redux-framework' );

				if ( $this->field['item_name'] ) {
					$button_text .= ' ' . $this->field['item_name'];
				}

			} else {
				$classes     = '';
				$button_text = '×';
			}

			return '<a href="#" class="redux-serialized-repeater-delete' . $classes . '">' . $button_text . '</a>';
		}


		public function get_repeater_before_html( $last_index ) {
			// e( __METHOD__ );

			// redux-serialized-repeater-row needs to be the first class name, the Javascript is checking for it
			$classes = 'redux-serialized-repeater-row';

			// Add bind_title class if its value is to be bound to the title (the Javascript looks for this class)
			if ( ! empty( $this->field['bind_title']['bind_to'] ) ) {
				$classes .= ' bind-title';
			}

			$bind_title = '&nbsp;'; //isset( $this->field['__bind_title'] ) && !empty( $this->field['__bind_title'] ) ? esc_html( $this->field['__bind_title'] ) : '&nbsp;';
			$html       = '<div class="' . $classes . '" data-index="' . $last_index . '" data-id="' . static::$root_id . '">';

			if ( $this->field['display_type'] == 'accordion' ) {
				//dashicons dashicons-menu
				$html .= '<h3><span class="sort-handle"></span><span class="title">' . $bind_title . '</span></h3>';    //$this->field['title']
			} else {
				$html .= '<div class="sort-handle"></div>';
			}

			$html .= '<fieldset class="redux-field">';

			return $html;
		}


		public function get_repeater_after_html() {
			// e( __METHOD__ );
			return '</fieldset>' .
			       '</div>';
		}


		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @return      void
		 */
		public function enqueue() {
			// e( __METHOD__ );
			$extension = ReduxFramework_extension_serialized_repeater::getInstance();

			// Set up min files for dev_mode = false.
			$min = Redux_Functions::isMin();

			wp_enqueue_script(
				'redux-field-serialized-repeater-js',
				apply_filters( "redux/serialized_repeater/{$this->parent->args['opt_name']}/enqueue/redux-field-serialized-repeater-js", $this->extension_url . 'field_serialized_repeater' . $min . '.js' ),
				array(
					'jquery',
					'jquery-ui-core',
					'jquery-ui-accordion',
					'jquery-ui-sortable',
					'wp-color-picker',
					'redux-js'
				),
				'1.0.0',
				true
			);

			wp_enqueue_style(
				'redux-field-serialized-repeater-css',
				apply_filters( "redux/serialized_repeater/{$this->parent->args['opt_name']}/enqueue/redux-field-serialized-repeater-css", $this->extension_url . 'field_serialized_repeater.css' ),
				array(),
				'1.0.0',
				'all'
			);
		}


		/**
		 * Loads the field type and allows it to enqueue any dependencies
		 *
		 * @param  string $field_type Field type to load and called its enqueue() method if it has one.
		 */
		private function enqueue_dependencies( $field_type ) {
			// e(__METHOD__, $field_type );

			$field_class = 'ReduxFramework_' . $field_type;

			if ( ! class_exists( $field_class ) ) {
				$class_file = apply_filters( 'redux-typeclass-load', ReduxFramework::$_dir . 'inc/fields/' . $field_type . '/field_' . $field_type . '.php', $field_class );

				// e('Class file to load', $class_file);

				if ( $class_file ) {
					/** @noinspection PhpIncludeInspection */
					require_once( $class_file );
				}
			}

			if ( class_exists( $field_class ) && method_exists( $field_class, 'enqueue' ) ) {
				$enqueue = new $field_class( '', '', $this );
				$enqueue->enqueue();
			}
		}


		/**
		 * Validates and sanitizes the field options.
		 */
		private function validate_settings() {
			// e( __METHOD__ );

			// Stores the name of the root field's id
			if ( static::$recursion_level == 0 ) {
				static::$root_id = $this->field['id'];
			}

			// Text used in the Add/Delete buttons
			if ( ! isset( $this->field['item_name'] ) ) {
				$this->field['item_name'] = '';
			}

			// Max number of rows
			if ( ! isset( $this->field['limit'] ) || ! is_numeric( $this->field['limit'] ) ) {
				// Default is unlimited
				$this->field['limit'] = 0;
			}

			// Number of fixed rows
			if ( ! isset( $this->field['static'] ) || ! is_numeric( $this->field['static'] ) ) {
				// Default is disabled
				$this->field['static'] = 0;
			}

			$this->field['sortable'] = isset( $this->field['sortable'] ) ? (bool) $this->field['sortable'] : true;

			if ( ! isset( $this->field['display_type'] ) || ! in_array( $this->field['display_type'], $this->display_types ) ) {
				$this->field['display_type'] = $this->display_types[ $this->default_display_type ];
			}

			if ( ! isset( $this->field['accordion_style'] ) || ! in_array( $this->field['accordion_style'], $this->accordion_styles ) ) {
				$this->field['accordion_style'] = $this->accordion_styles[ $this->default_accordion_style ];
			}

			if ( ! isset( $this->field['accordion_state'] ) || ! in_array( $this->field['accordion_state'], $this->accordion_states ) ) {
				$this->field['accordion_state'] = $this->accordion_states[ $this->default_accordion_state ];
			}

			// The 'all' default state can only be used by the 'multiple' type
			if ( $this->field['accordion_state'] == 'all' && $this->field['accordion_style'] != 'multiple' ) {
				$this->field['accordion_state'] = $this->accordion_states[ $this->default_accordion_state ];
			}

			// 'collapsible' can't use 'all'
			if ( $this->field['accordion_style'] == 'collapsible' && $this->field['accordion_state'] == 'all' ) {
				$this->field['accordion_state'] = $this->accordion_states[ $this->default_accordion_state ];
			}

			// 'single' can't use 'closed' or 'all'
			if ( $this->field['accordion_style'] == 'single' && ( in_array( $this->field['accordion_state'], array(
					'closed',
					'all'
				) ) )
			) {
				$this->field['accordion_state'] = $this->accordion_states[ $this->default_accordion_state ];
			}

			if ( ! isset( $this->field['bind_title'] ) ) {
				$this->field['bind_title'] = array();

				// If bind_title isn't set, use the first field by default
				if ( ! empty( $this->field['fields'] ) && isset( $this->field['fields'][0]['id'] ) ) {
					// e('Setting default bind title id', $this->field['fields'][0]['id']);

					$this->field['bind_title']['bind_to'] = $this->field['fields'][0]['id'];
				}
			} else if ( is_bool( $this->field['bind_title'] ) || $this->field['bind_title'] === 'false' ) {
				// false is the only boolean value allowed, which disables bind_title
				$this->field['bind_title'] = array();
			} else if ( is_string( $this->field['bind_title'] ) ) {
				// Move id to new location
				$this->field['bind_title'] = array(
					'bind_to' => $this->field['bind_title']
				);
			}

			if ( ! isset( $this->field['bind_title']['bind_title_separator'] ) ) {
				$this->field['bind_title']['separator'] = ', ';
			}

			if ( ! isset( $this->field['bind_title']['limit'] ) ) {
				$this->field['bind_title']['limit'] = 60;
			}

			if ( ! isset( $this->field['bind_title']['more'] ) ) {
				$this->field['bind_title']['more'] = '…';
			}

			if ( ! isset( $this->field['bind_title']['prefix'] ) ) {
				$this->field['bind_title']['prefix'] = '';
			}

			if ( ! isset( $this->field['bind_title']['postfix'] ) ) {
				$this->field['bind_title']['postfix'] = '';
			}

			if ( empty( $this->value ) || ! is_array( $this->value ) ) {
				$this->value = array();
			}
		}
	}

}