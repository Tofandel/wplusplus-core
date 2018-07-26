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
 * @subpackage  Social Profiles
 * @author      Kevin Provance (kprovance)
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_social_profiles' ) ) {

	/**
	 * Main ReduxFramework_spectrum class
	 *
	 * @since       1.0.0
	 */
	class ReduxFramework_social_profiles {

		public $field_id = '';
		public $opt_name = '';

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $field Field sections.
		 * @param       array $value Values.
		 * @param       array $parent Parent object.
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

			$this->opt_name = $parent->args['opt_name'];
			$this->field_id = $field['id'];

			$this->defaults = reduxSocialProfilesFunctions::get_default_data();
		}


		private function rebuild_setttings( $defaults, $settings ) {
			$fixed_arr = array();
			$stock     = '';

			foreach ( $this->defaults as $key => $arr ) {
				$search_default = true;

				$default_id = $arr['id'];

				foreach ( $settings as $i => $a ) {
					if ( $a['id'] == $default_id ) {
						$search_default    = false;
						$fixed_arr[ $key ] = $a;
						break;
					}
				}

				if ( $search_default ) {
					if ( $stock == '' ) {
						$stock = reduxSocialProfilesDefaults::get_social_media_defaults();
						$stock = reduxSocialProfilesFunctions::add_extra_icons( $stock );
					}

					foreach ( $stock as $i => $def_arr ) {
						if ( $default_id == $def_arr['id'] ) {
							$fixed_arr[ $key ] = $def_arr;
							break;
						}
					}
				}
			}

			return $fixed_arr;
		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			global $pagenow, $post;
			$settings = '';

			$redux_settings = get_option( $this->opt_name );
			$settings       = isset( $redux_settings[ $this->field_id ] ) ? $redux_settings[ $this->field_id ] : array();

			if ( is_admin() && ( $pagenow == "post-new.php" || $pagenow == "post.php" ) ) {
				$post_settings = get_post_meta( $post->ID, $this->field_id, true );

				if ( ! empty( $post_settings ) ) {
					$settings = $post_settings;
				}
			}

			$color_pickers = isset( $this->field['color_pickers'] ) ? $this->field['color_pickers'] : true;

			$dev_mode = $this->parent->args['dev_mode'];
			$dev_tag  = '';

			if ( true == $dev_mode ) {

				$dev_tag = ' data-dev-mode="' . $this->parent->args['dev_mode'] . '"
                            data-version="' . ReduxFramework_extension_social_profiles::$version . '"';
			}

			// Icon container
			echo '<div
                      class="redux-social-profiles-container ' . $this->field['class'] . '" 
                      data-opt-name="' . $this->opt_name . '"
                      data-id="' . $this->field_id . '"' .
			     $dev_tag . '
                  >';

			$show_msg = isset( $this->field['hide_widget_msg'] ) ? $this->field['hide_widget_msg'] : true;
			$msg      = isset( $this->field['widget_msg'] ) ? $this->field['widget_msg'] : __( 'Go to the <a href="%s">Widgets</a> page to add the Redux Social Widget to any active widget area.', 'redux-framework' );

			if ( ! $show_msg ) {
				echo '<div class="redux-social-profiles-header">';
				printf( $msg, admin_url( 'widgets.php' ) );
				echo '</div>';
			}

			echo '<div class="redux-social-profiles-selector-container">';
			echo '<ul id="redux-social-profiles-selector-list">';

			$settings = $this->rebuild_setttings( $this->defaults, $settings );

			foreach ( $this->defaults as $key => $social_provider_default ) {
				$social_provider_option = ( $settings && is_array( $settings ) && array_key_exists( $key, $settings ) ) ? $settings[ $key ] : null;

				$icon    = ( $social_provider_option && array_key_exists( 'icon', $social_provider_option ) && $social_provider_option['icon'] ) ? $social_provider_option['icon'] : $social_provider_default['icon']; //$social_provider_default[ 'icon' ];
				$name    = ( $social_provider_option && array_key_exists( 'name', $social_provider_option ) && $social_provider_option['name'] ) ? $social_provider_option['name'] : $social_provider_default['name']; //$social_provider_default[ 'name' ];
				$order   = ( $social_provider_option && array_key_exists( 'order', $social_provider_option ) ) ? $social_provider_option['order'] : $key;
				$order   = intval( $order );
				$enabled = ( $social_provider_option && array_key_exists( 'enabled', $social_provider_option ) && $social_provider_option['enabled'] ) ? $social_provider_option['enabled'] : $social_provider_default['enabled'];
				$display = ( $enabled ) ? 'enabled' : '';

				echo '<li class="redux-social-profiles-item-enable ' . $display . '" id="redux-social-profiles-item-enable-' . $key . '" data-key="' . $key . '" data-order="' . $order . '">';
				reduxSocialProfilesFunctions::render_icon( $icon, '', '', $name );
				echo '</li>';
			}

			echo '</ul>';
			echo '</div>';

			echo '<ul id="redux-social-profiles-list">';

			foreach ( $this->defaults as $key => $social_provider_default ) {
				echo '<li id="redux-social-item-' . $key . '" data-key="' . $key . '" style="display: none;">';
				echo '<div class="redux-social-item-container">';


				$social_provider_option = ( $settings && is_array( $settings ) && array_key_exists( $key, $settings ) ) ? $settings[ $key ] : null;
				$icon                   = ( $social_provider_option && array_key_exists( 'icon', $social_provider_option ) && $social_provider_option['icon'] ) ? $social_provider_option['icon'] : $social_provider_default['icon']; //$social_provider_default[ 'icon' ];
				$id                     = ( $social_provider_option && array_key_exists( 'id', $social_provider_option ) && $social_provider_option['id'] ) ? $social_provider_option['id'] : $social_provider_default['id']; //$social_provider_default[ 'id' ];
				$enabled                = ( $social_provider_option && array_key_exists( 'enabled', $social_provider_option ) && $social_provider_option['enabled'] ) ? $social_provider_option['enabled'] : $social_provider_default['enabled'];
				$name                   = ( $social_provider_option && array_key_exists( 'name', $social_provider_option ) && $social_provider_option['name'] ) ? $social_provider_option['name'] : $social_provider_default['name']; //$social_provider_default[ 'name' ];

				$label = ( $social_provider_option && array_key_exists( 'label', $social_provider_option ) && $social_provider_option['label'] ) ? $social_provider_option['label'] : __( 'Link URL', 'redux-framework' ); //$social_provider_default[ 'name' ];

				$color = ( $social_provider_option && array_key_exists( 'color', $social_provider_option ) ) ? $social_provider_option['color'] : $social_provider_default['color'];
				$color = esc_attr( $color );

				$background = ( $social_provider_option && array_key_exists( 'background', $social_provider_option ) ) ? $social_provider_option['background'] : $social_provider_default['background'];
				$background = esc_attr( $background );

				$order = ( $social_provider_option && array_key_exists( 'order', $social_provider_option ) ) ? $social_provider_option['order'] : $key;
				$order = intval( $order );

				$url = ( $social_provider_option && array_key_exists( 'url', $social_provider_option ) ) ? $social_provider_option['url'] : $social_provider_default['url'];
				$url = esc_attr( $url );

				$profile_data = array(
					'id'         => $id,
					'icon'       => $icon,
					'enabled'    => $enabled,
					'url'        => $url,
					'color'      => $color,
					'background' => $background,
					'order'      => $order,
					'name'       => $name,
					'label'      => $label
				);

				$profile_data = rawurlencode( json_encode( $profile_data ) );

				echo
					'<input
                    type="hidden"
                    class="redux-social-profiles-hidden-data-' . $key . '"
                    id="' . $this->field_id . '-' . $id . '-data"
                    name="' . $this->opt_name . '[' . $this->field_id . '][' . $key . '][data]" 
                    value="' . $profile_data . '" 
                />';

				echo '<div class="redux-icon-preview">';
				reduxSocialProfilesFunctions::render_icon( $icon, $color, $background, $name );
				echo '&nbsp;</div>';

				echo '<div class="redux-social-profiles-item-name">';
				echo $name;
				echo '</div>';

				echo '<div class="redux-social-profiles-item-enabled">';
				$checked = ( $enabled ) ? 'checked' : '';
				echo '<input type="checkbox" class="checkbox-' . $key . '" data-key="' . $key . '" value="1" ' . $checked . '/>';
				_e( 'Enabled', 'redux-framework' );
				echo '</div>';

				$color_class = $color_pickers ? '' : ' no-color-pickers';

				echo '<div class="redux-social-profiles-link-url input_wrapper' . $color_class . '">';
				echo '<label class="redux-text-url-label">' . $label . '</label>';
				echo '<input class="redux-social-profiles-url-text" data-key="' . $key . '" type="text" value="' . $url . '" />';
				echo '</div>';

				$reset_text = __( 'Reset', 'redux-framework' );
				echo '<div class="redux-social-profiles-item-reset">';
				echo '<a class="button" data-value="' . $key . '" value="' . $reset_text . '" />' . $reset_text . '</a>';
				echo '</div>';

				if ( $color_pickers ) {
					$label = apply_filters( 'redux/extensions/social_profiles/' . $this->opt_name . '/color_picker/text', __( 'Text', 'redux-framework' ) );

					echo '<div class="redux-social-profiles-text-color picker_wrapper" >';
					echo '<label class="redux-text-color-label">' . $label . '</label>';
					echo
						'<input
                            class="redux-social-profiles-color-picker-' . $key . ' text" 
                            type="text" 
                            value="' . $color . '" 
                            data-key="' . $key . '" 
                        />';
					echo "</div>";

					$label = apply_filters( 'redux/extensions/social_profiles/' . $this->opt_name . '/color_picker/background', __( 'Background', 'redux-framework' ) );

					echo '<div class="redux-social-profiles-background-color picker_wrapper">';
					echo '<label class="redux-background-color-label">' . $label . '</label>';
					echo
						'<input
                            class="redux-social-profiles-color-picker-' . $key . ' background" 
                            type="text" 
                            value="' . $background . '" 
                            data-key="' . $key . '" 
                        />';
					echo '</div>';
				}

				echo '<div class="redux-social-profiles-item-order">';
				echo
					'<input
                        type="hidden" 
                        value="' . $order . '" 
                    />';
				echo "</div>";

				echo '</div>';
				echo "</li>";
			}

			echo '</ul>';
			echo '</div>';
		}

		/**
		 * Output Function.
		 * Used to enqueue to the front-end
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function output() {
			if ( ! empty( $this->value ) ) {
				foreach ( $this->value as $idx => $arr ) {
					if ( $arr['enabled'] ) {

					}
				}
			}
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {

			$extension = ReduxFramework_extension_social_profiles::getInstance();

			// Set up min files for dev_mode = false.
			$min = Redux_Functions::isMin();

			// font-awesome
			wp_enqueue_style(
				'font-awesome',
				$this->extension_url . 'vendor/font-awesome' . $min . '.css',
				array(),
				time(),
				'all'
			);

			// Field dependent JS
			wp_enqueue_script(
				'redux-field-social-profiles-js',
				$this->extension_url . 'js/field_social_profiles' . $min . '.js',
				array( 'jquery', 'jquery-ui-sortable', 'redux-spectrum-js' ),
				time(),
				true
			);

			wp_localize_script(
				'redux-field-social-profiles-js',
				'reduxSocialDefaults',
				$this->defaults
			);

			// Field CSS
			wp_enqueue_style(
				'redux-field-social-profiles-css',
				$this->extension_url . 'css/field_social_profiles.css',
				array( 'redux-spectrum-css' ),
				time(),
				'all'
			);
		}
	}
}