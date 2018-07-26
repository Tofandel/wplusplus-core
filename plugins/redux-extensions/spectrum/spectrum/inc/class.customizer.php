<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ReduxColorSchemeCustomizer' ) ) {

	class ReduxColorSchemeCustomizer {

		public function __construct( $parent, $ext_dir ) {
			$this->parent = $parent;

			if ( ! class_exists( 'ReduxColorSchemeFunctions' ) ) {
				include_once( $ext_dir . 'color_scheme/inc/class.color_scheme_functions.php' );
				ReduxColorSchemeFunctions::$_parent = $parent;
				ReduxColorSchemeFunctions::init();
			}

			$scheme_name = ReduxColorSchemeFunctions::getCurrentSchemeID();

			$this->localize_data['data']     = ReduxColorSchemeFunctions::getSchemeData( $scheme_name );
			$this->localize_data['opt_name'] = $this->getOptName();

			$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $ext_dir ) );

			add_action( 'customize_register', array( $this, 'cs_customize_register' ) );
			add_action( 'customize_preview_init', array( $this, 'cs_customizer_live_preview' ) );
		}

		private function getOptName() {
			$arr = $this->parent;

			foreach ( $arr as $part => $bla ) {
				if ( $part == 'args' ) {
					foreach ( $bla as $section => $field ) {
						return $bla['opt_name'];
						continue;
					}
				}
			}
		}

		public function cs_customize_register( $wp_customize ) {
			$opt_name = $this->localize_data['opt_name'];
			$data     = $this->localize_data['data'];

			$wp_customize->add_section( 'color_scheme', array(
					'title'       => __( 'Color Scheme', 'redux-framework' ),
					'priority'    => 202,
					'description' => __( 'These colours are used throughout the theme to give your site consistent styling, these would likely be colours from your identity colour scheme.', 'redux-framework' ),
				)
			);

			foreach ( $data as $k => $v ) {
				if ( $k !== 'color_scheme_name' ) {
					$id    = $v['id'];
					$title = $v['title'];
					$color = $v['color'];

					$wp_customize->add_setting( $opt_name . '[' . $id . ']', array(
							'default'    => $color,
							'type'       => 'option',
							'transport'  => 'postMessage',
							'capability' => 'edit_theme_options',
						)
					);

					//$wp_customize->add_control ( new WP_Customize_Color_Control ( $wp_customize, $id, array(
					$wp_customize->add_control( new Rdx_Customize_Spectrum_Control ( $wp_customize, $id, array(
								'label'    => __( $title, 'redux-framework' ),
								'section'  => 'color_scheme',
								'settings' => $opt_name . '[' . $id . ']',
							)
						)
					);
				}
			}
		}

		public function cs_customizer_live_preview() {

			$min = Redux_Functions::isMin();

			wp_register_script(
				'redux-cs-customizer',
				$this->extension_url . 'color_scheme/js/cs-customizer' . $min . '.js',
				array( 'jquery' ),
				time(),
				true
			);

			// Values used by the javascript
			wp_localize_script(
				'redux-cs-customizer',
				'color_scheme',
				$this->localize_data
			);

			wp_enqueue_script( 'redux-cs-customizer' ); // Enque the JS now    

		}
	}
}

if ( class_exists( 'WP_Customize_Control' ) ) {
	if ( ! class_exists( 'Rdx_Customize_Spectrum_Control' ) ) {
		class Rdx_Customize_Spectrum_Control extends WP_Customize_Control {
			public $type = 'spectrum';

			public function render_content() {
				$control_id = $this->id;

				?>
				<!--            <div class="redux-spectrum">      
                <label>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
                </label>
                <input id="redux-color-scheme" name="<?php echo $this->id; ?>" type="text" <?php $this->link(); ?> value="<?php echo $this->value(); ?>" data-color="<?php echo $this->value(); ?>" />
                <div class="spectrum"></div>

           </div>-->

				<label>
					<label>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<?php if ( $this->description ): ?>
							<p class="description"><?php echo esc_html( $this->description ); ?></p>
						<?php endif; ?>
					</label>
					<div class="customize-control-content">
						<input id="<?php echo $control_id; ?>" class="redux-spectrum-color-picker" type="text"
							   value="<?php echo esc_attr( $this->value() ); ?>"
							   name="<?php echo $this->id; ?>" <?php $this->link(); ?> />
					</div>
				</label>
				<script>
					jQuery(document).ready(function ($) {
						$('#<?php echo $control_id; ?>').spectrum({
							showAlpha: true,
							showInput: true,
							allowEmpty: true,
							showPalette: true,
							palette: [
								['black', 'white', 'red'],
								['rgb(255, 128, 0);', 'hsv 100 70 50', 'lightyellow']
							],
							preferredFormat: 'hex6',
							appendTo: 'parent',
							change: function (color) {
								console.log('change');
							}
						});
					});
				</script>


				<?php
			}

			public function enqueue() {
				$min = Redux_Functions::isMin();
				$url = ReduxFramework_extension_color_scheme::getExtURL();

				wp_enqueue_script(
					'redux-spectrum-js',
					$url . 'color_scheme/vendor/spectrum' . $min . '.js',
					array( 'jquery' ),
					time(),
					true
				);

				wp_enqueue_style(
					'redux-spectrum-css',
					$url . 'color_scheme/vendor/spectrum.css',
					time(),
					true
				);

			}
		}
	}
}