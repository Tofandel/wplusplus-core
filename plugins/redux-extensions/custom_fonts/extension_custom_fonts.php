<?php
/**
 * @package     ReduxFramework
 * @author      Dovy Paukstys (dovy)
 * @author      Kevin Provance (kprovance), who hacked at it a bit (and took over the project).
 * @version     1.1.5
 *
 * Listen up, bitches.  This Redux extension is unavailable for sale and is for
 * PRIVATE use only.  That means, if you are another developer nosing around
 * my code, looking for shit you didn't write, and you attempt to steal this
 * extension, I will know, and I will hunt you down and make your life a steaming
 * pile of miserable hell.  Also, since it uses a Redux server for conversion
 * purposes, we will know and blacklist you and your users, which makes
 * stealing this extension worthless to you.  So, go ahead, try it, I dare you.
 *
 * Or, make me a fair licensing offer.  Otherwise, piss off!
 *
 * And don't think I won't follow through on my promise to hunt you down
 * and dDoS your shit into 1992 if you take this code and leech our server.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_extension_custom_fonts', false ) ) {
	/**
	 * Main ReduxFramework custom_fonts extension class
	 *
	 * @since       3.1.6
	 */
	class ReduxFramework_extension_custom_fonts {

		/**
		 * @var string
		 */
		static $version = "1.1.5";
		// Protected vars
		/**
		 * @var
		 */
		protected $parent;

		/**
		 * @var
		 */
		public $extension_url;

		/**
		 * @var string
		 */
		public $extension_dir;

		/**
		 * @var ReduxFramework_extension_custom_fonts
		 */
		public static $theInstance;

		/**
		 * @var array
		 */
		public $custom_fonts = array();

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $sections Panel sections.
		 * @param       array $args Class constructor arguments.
		 * @param       array $extra_tabs Extra panel tabs.
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {
			$this->parent = $parent;

			$this->upload_dir = ReduxFramework::$_upload_dir . 'custom-fonts/';
			$this->upload_url = ReduxFramework::$_upload_url . 'custom-fonts/';

			if ( ! is_dir( $this->upload_dir ) ) {
				$this->parent->filesystem->execute( 'mkdir', $this->upload_dir );
			}

			if ( ! is_dir( $this->upload_dir . '/custom' ) ) {
				$this->parent->filesystem->execute( 'mkdir', $this->upload_dir . '/custom' );
			}


			$this->getFonts();

			if ( file_exists( $this->upload_dir . 'fonts.css' ) ) {
				if ( filemtime( $this->upload_dir . 'custom' ) > ( filemtime( $this->upload_dir . 'fonts.css' ) + 10 ) ) {
					$this->generateCSS();
				}
			} else {
				$this->generateCSS();
			}

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
			}

			$this->field_name = 'custom_fonts';

			self::$theInstance = $this;

			// Adds the local field
			add_filter( 'redux/' . $this->parent->args['opt_name'] . '/field/class/' . $this->field_name, array(
				&$this,
				'overload_field_path'
			) );

			add_action( 'wp_ajax_redux_custom_fonts', array( $this, 'ajax' ) );
			add_action( 'wp_ajax_redux_custom_font_timer', array( $this, 'timer' ) );

			add_filter( "redux/{$this->parent->args['opt_name']}/field/typography/custom_fonts", array(
				$this,
				'addCustomFonts'
			) );

			$this->is_field = Redux_Helpers::isFieldInUse( $parent, 'custom_fonts' );

			if ( ! $this->is_field && $this->parent->args['dev_mode'] && isset( $this->parent->args['show_custom_fonts'] ) && $this->parent->args['show_custom_fonts'] ) {
				$this->add_section();
			}

			//$this->dynamic_section();

			add_filter( "redux/options/{$this->parent->args['opt_name']}/section/redux_dynamic_font_control", array(
				$this,
				'remove_dynamic_section'
			) );
			add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) );
			add_action( 'wp_head', array( $this, '_enqueue_output' ), 150 );
			add_filter( 'tiny_mce_before_init', array( $this, 'extend_tinymce_dropdown' ) );
		}

		public function timer() {
			$name = get_option( 'redux_custom_font_current' );

			if ( ! empty( $name ) ) {
				echo $name;
			}

			die();
		}

		/**
		 * Remove the dynamically added section if the field was used elsewhere
		 *
		 * @param $section
		 *
		 * @return array
		 * @since  Redux_Framework 3.1.1
		 */
		function remove_dynamic_section( $section ) {
			if ( isset( $this->parent->field_types[ $this->field_name ] ) ) {
				$section = array();
			}

			return $section;
		}

		/**
		 * Adds FontMeister fonts to the TinyMCE drop-down. Typekit fonts don't render properly in the drop-down and in the editor,
		 * because Typekit needs JS and TinyMCE doesn't support that.
		 *
		 * @param $opt
		 *
		 * @return array
		 */
		function extend_tinymce_dropdown( $opt ) {
			if ( ! is_admin() ) {
				return $opt;
			}

			if ( file_exists( $this->upload_dir . 'fonts.css' ) ) {
				$theme_advanced_fonts = isset( $opt['font_formats'] ) ? isset( $opt['font_formats'] ) : "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats";
				$custom_fonts         = '';

				$stylesheet = $this->upload_url . 'fonts.css';

				if ( empty( $opt['content_css'] ) ) {
					$opt['content_css'] = $stylesheet;
				} else {
					$opt['content_css'] = $opt['content_css'] . ',' . $stylesheet;
				}

				foreach ( $this->custom_fonts as $title => $arr ) {
					foreach ( $arr as $font => $pieces ) {
						$custom_fonts .= ';' . $font . '=' . $font;
					}

					continue;
				}

				$opt['font_formats'] = $theme_advanced_fonts . $custom_fonts;
			}

			return $opt;
		}


		/**
		 * Function to enqueue the custom fonts css
		 */
		function _enqueue_output() {
			if ( file_exists( $this->upload_dir . 'fonts.css' ) ) {
				wp_register_style(
					'redux-custom-fonts-css',
					$this->upload_url . 'fonts.css',
					array(),
					ReduxFramework_extension_custom_fonts::$version,
					'all'
				);

				wp_enqueue_style( 'redux-custom-fonts-css' );
			}
		}

		/**
		 * Adds the appropriate mime types to WordPress
		 *
		 * @param array $existing_mimes
		 *
		 * @return array
		 */
		function custom_upload_mimes( $existing_mimes = array() ) {
			$existing_mimes['ttf']  = 'font/ttf';
			$existing_mimes['otf']  = 'font/otf';
			$existing_mimes['woff'] = 'application/font-woff';

			return $existing_mimes;
		}

		/**
		 * Gets all the fonts in the custom_fonts directory
		 */
		public function getFonts() {
			if ( ! empty( $this->custom_fonts ) ) {
				return $this->custom_fonts;
			}

			$params = array(
				'include_hidden' => false,
				'recursive'      => true
			);

			$fonts = $this->parent->filesystem->execute( 'dirlist', $this->upload_dir, $params );

			if ( ! empty( $fonts ) ) {
				foreach ( $fonts as $section ) {
					if ( $section['type'] == "d" && ! empty( $section['name'] ) ) {
						if ( $section['name'] == "custom" ) {
							$section['name'] = __( 'Custom Fonts', 'redux-framework' );
						} else if ( $section['name'] == "fontsquirrel" ) {
							$section['name'] = __( 'Fonts Squirrel', 'redux-framework' );
						}

						if ( ! isset( $section['files'] ) || empty( $section['files'] ) ) {
							continue;
						}

						$this->custom_fonts[ $section['name'] ] = isset( $this->custom_fonts[ $section['name'] ] ) ? $this->custom_fonts[ $section['name'] ] : array();

						$kinds = array();
						foreach ( $section['files'] as $font ) {
							if ( ! empty( $font['name'] ) ) {
								if ( ! isset( $font['files'] ) || empty( $font['files'] ) ) {
									continue;
								}

								$kinds = array();
								foreach ( $font['files'] as $f ) {
									$valid = $this->checkFontFileName( $f );
									if ( $valid ) {
										array_push( $kinds, $valid );
									}
								}

								$this->custom_fonts[ $section['name'] ][ $font['name'] ] = $kinds;
							}
						}
					}
				}
			}
		}

		/**
		 * @param $custom_fonts
		 *
		 * @return array
		 */
		public function addCustomFonts( $custom_fonts ) {
			if ( ! is_array( $custom_fonts ) || empty( $custom_fonts ) ) {
				$custom_fonts = array();
			}

			$custom_fonts = wp_parse_args( $custom_fonts, $this->custom_fonts );

			return $custom_fonts;
		}

		/**
		 * Ajax used within the panel to add and process the fonts
		 */
		public function ajax() {
			if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], "redux_{$this->parent->args['opt_name']}_custom_fonts" ) ) {
				die( 0 );
			}

			if ( isset( $_REQUEST['type'] ) && $_REQUEST['type'] == "delete" ) {
				if ( $_REQUEST['section'] == __( 'Custom Fonts', 'redux-framework' ) ) {
					$_REQUEST['section'] = "custom";
				}

				if ( $_REQUEST['section'] == __( 'Fonts Squirrel', 'redux-framework' ) ) {
					$_REQUEST['section'] = "fontssquirrel";
				}

				try {
					$this->parent->filesystem->execute( 'rmdir', $this->upload_dir . $_REQUEST['section'] . '/' . $_REQUEST['name'] . '/', array( 'recursive' => true ) );

					$result = array(
						'type' => "success"
					);

					echo json_encode( $result );

				} catch ( Exception $e ) {
					echo json_encode( array(
						'type' => 'error',
						'msg'  => __( 'Unable to delete font file(s).', 'redux-framework' )
					) );
				}

				die();
			}

			if ( ! isset( $_REQUEST['title'] ) ) {
				$_REQUEST['title'] = "";
			}

			if ( isset( $_REQUEST['attachment_id'] ) && ! empty( $_REQUEST['attachment_id'] ) ) {
				$msg = $this->processWebfont( $_REQUEST['attachment_id'], $_REQUEST['title'], $_REQUEST['mime'] );

				if ( empty( $msg ) ) {
					$msg = '';
				}

				$result = array(
					'type' => "success",
					'msg'  => $msg
				);

				echo json_encode( $result );
			}

			die();
		}

		/**
		 * Get only valid files. Ensure everything is proper for processing.
		 *
		 * @param $path
		 *
		 * @return array
		 */
		function getValidFiles( $path ) {
			$output = array();
			$path   = trailingslashit( $path );

			$params = array(
				'include_hidden' => false,
				'recursive'      => true
			);

			$files = $this->parent->filesystem->execute( 'dirlist', $path, $params );

			foreach ( $files as $file ) {
				if ( $file['type'] == "d" ) {
					$output = array_merge( $output, $this->getValidFiles( $path . $file['name'] ) );
				} elseif ( $file['type'] == "f" ) {
					$valid = $this->checkFontFileName( $file );
					if ( $valid ) {
						$output[ $valid ] = trailingslashit( $path ) . $file['name'];
					}
				}
			}

			return $output;
		}

		/**
		 * Take a valid web font and process the missing pieces.
		 *
		 * @param        $attachment_id
		 * @param        $name
		 * @param        $mime_type
		 * @param string $subfolder
		 */
		function processWebfont( $attachment_id, $name, $mime_type, $subfolder = 'custom/' ) {
			$missing = array();

			$complete = array(
				'ttf',
				'woff',
				'eot',
				'svg'
			);

			$subtype = explode( '/', $mime_type );
			$subtype = trim( max( $subtype ) );

			if ( ! is_dir( $this->upload_dir ) ) {
				$this->parent->filesystem->execute( 'mkdir', $this->upload_dir );
			}

			if ( ! is_dir( $this->upload_dir . $subfolder ) ) {
				$this->parent->filesystem->execute( 'mkdir', $this->upload_dir . $subfolder );
			}

			$temp = $this->upload_dir . 'temp';
			$path = get_attached_file( $attachment_id, false );

			if ( empty( $path ) ) {
				echo json_encode( array(
					'type' => 'error',
					'msg'  => __( 'Attachment does not exist.', 'redux-framework' )
				) );

				die();
			}

			$filename = explode( '/', $path );

			$filename = $filename[ ( count( $filename ) - 1 ) ];

			$fontname = ucfirst( str_replace( array(
				'.zip',
				'.ttf',
				'.woff',
				'.eot',
				'.svg',
				'.otf'
			), '', strtolower( $filename ) ) );

			if ( ! isset( $name ) || empty( $name ) ) {
				$name = $fontname;
			}

			$msg = '';
			$ret = array();

			if ( $subtype == "zip" ) {
				if ( ! is_dir( $temp ) ) {
					$this->parent->filesystem->execute( 'mkdir', $temp );
				}

				$unzipfile = unzip_file( $path, $temp );
				$output    = $this->getValidFiles( $temp );

				if ( ! empty( $output ) ) {
					foreach ( $complete as $idx => $test ) {
						if ( ! isset( $output[ $test ] ) ) {
							$missing[] = $test;
						}
					}

					if ( ! is_dir( $this->upload_dir . $subfolder . $name . '/' ) ) {
						$this->parent->filesystem->execute( 'mkdir', $this->upload_dir . $subfolder . $name . '/' );
					}

					foreach ( $output as $key => $value ) {
						$param_array = array(
							'destination' => $this->upload_dir . $subfolder . $name . '/' . $fontname . '.' . $key,
							'overwrite'   => true,
							'chmod'       => 755
						);

						$this->parent->filesystem->execute( 'copy', $value, $param_array );
					}

					$ret = $this->getMissingFiles( $name, $fontname, $missing, $output, $subfolder );
				}

				$this->parent->filesystem->execute( 'rmdir', $temp, array( 'recursive' => true ) );

				$this->generateCSS();
				wp_delete_attachment( $attachment_id, true );
			} else if ( $subtype == "ttf" || $subtype == "otf" || $subtype == "font-woff" ) {
				foreach ( $complete as $idx => $test ) {
					if ( $test != $subtype ) {
						if ( ! isset( $output[ $test ] ) ) {
							$missing[] = $test;
						}
					}
				}

				if ( ! is_dir( $this->upload_dir . $subfolder . $name . '/' ) ) {
					$this->parent->filesystem->execute( 'mkdir', $this->upload_dir . $subfolder . $name . '/' );
				}

				$param_array = array(
					'destination' => $this->upload_dir . $subfolder . '/' . $name . '/' . $fontname . '.' . $subtype,
					'overwrite'   => true,
					'chmod'       => 755
				);

				$this->parent->filesystem->execute( 'copy', $path, $param_array );

				$output = array(
					$subtype => $path
				);

				$ret = $this->getMissingFiles( $name, $fontname, $missing, $output, $subfolder );

				$this->generateCSS();
				wp_delete_attachment( $attachment_id, true );
			} else {
				echo json_encode( array(
					'type' => 'error',
					'msg'  => __( 'File type not recognized. ' . $subtype, 'redux-framework' )
				) );

				die();
			}

			if ( is_array( $ret ) && ! empty( $ret ) ) {
				$msg = __( "Unidentified error.", 'redux-framework' );

				if ( isset( $ret['msg'] ) ) {
					$msg = $ret['msg'];
				}

				return $msg;
			}
		}

		/**
		 * Ping the mashape (fontsquirrel) API to get the missing files.
		 *
		 * @param $name
		 * @param $fontname
		 * @param $missing
		 * @param $output
		 * @param $subfolder
		 */
		private function getMissingFiles( $name, $fontname, $missing, $output, $subfolder ) {
			if ( ! isset( $name ) || empty( $name ) || ! isset( $missing ) || empty( $missing ) || ! is_array( $missing ) ) {
				return;
			}

			$temp = $this->upload_dir . 'temp';

			if ( count( $output ) == 1 && isset( $output['eot'] ) ) {
				echo json_encode( array(
					'type' => 'error',
					'msg'  => __( 'The font format .eot is not supported.', 'redux-framework' )
				) );

				$this->parent->filesystem->execute( 'rmdir', $this->upload_dir . $subfolder . $name . '/', array( 'recursive' => true ) );
				$this->parent->filesystem->execute( 'rmdir', $temp, array( 'recursive' => true ) );

				die();
			}

			// Find a file to convert from
			foreach ( $output as $key => $value ) {
				if ( $key == "eot" ) {
					continue;
				} else {
					$main = $key;
					break;
				}
			}

			if ( ! isset( $main ) ) {
				echo json_encode( array(
					'type' => 'error',
					'msg'  => __( 'No valid font file was found.', 'redux-framework' )
				) );

				$this->parent->filesystem->execute( 'rmdir', $temp, array( 'recursive' => true ) );
				$this->parent->filesystem->execute( 'rmdir', $this->upload_dir . $subfolder . $name . '/', array( 'recursive' => true ) );

				die();
			}

			foreach ( $missing as $idx => $item ) {
				update_option( 'redux_custom_font_current', $name . '.' . $item );

				$boundary = wp_generate_password( 24 );

				$headers = array(
					'content-type' => 'multipart/form-data; boundary=' . $boundary,
					'user-agent'   => 'redux-custom-fonts-' . self::$version . ' using ' . wp_get_theme(),
				);

				$payload = '';
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="format"' . "\r\n\r\n";
				$payload .= $item;
				$payload .= "\r\n";

				if ( $output[ $main ] ) {
					$payload .= '--' . $boundary;
					$payload .= "\r\n";
					$payload .= 'Content-Disposition: form-data; name="convert"; filename="' . basename( $output[ $main ] ) . '"' . "\r\n";
					$payload .= "\r\n";
					$payload .= file_get_contents( $output[ $main ] );
					$payload .= "\r\n";
				}

				$payload .= '--' . $boundary . '--';

				$args = array(
					'headers'    => $headers,
					'body'       => $payload,
					'user-agent' => $headers['user-agent'],
					'timeout'    => 60
				);

				$response = wp_remote_post( "http://fonts.redux.io/", $args );

				if ( is_wp_error( $response ) ) {
					return array(
						'type' => 'error',
						'msg'  => __( $response->get_error_message() . '<br><br>Your font could not be converted at this time. Please try again later.', 'redux-framework' )
					);
				} elseif ( json_decode( $response['body'] ) != null ) {
					return json_decode( $response['body'], true );
				}

				$param_array = array(
					'content'   => $response['body'],
					'overwrite' => true,
					'chmod'     => FS_CHMOD_FILE
				);

				$this->parent->filesystem->execute( 'put_contents', $this->upload_dir . $subfolder . $name . '/' . $fontname . '.' . $item, $param_array );
			}

			delete_option( 'redux_custom_font_current' );
		}

		/**
		 * Check if the file name is a valid font file.
		 *
		 * @param $file
		 *
		 * @return bool|string
		 */
		private function checkFontFileName( $file ) {
			if ( strtolower( substr( $file['name'], - 5 ) ) == ".woff" ) {
				return "woff";
			}

			$sub = strtolower( substr( $file['name'], - 4 ) );

			if ( $sub == ".ttf" ) {
				return "ttf";
			}

			if ( $sub == ".eot" ) {
				return "eot";
			}

			if ( $sub == ".svg" ) {
				return "svg";
			}

			if ( $sub == ".otf" ) {
				return "otf";
			}

			return false;
		}

		/**
		 * Generate a new custom CSS file for enqueing on the frontend and backend.
		 */
		private function generateCSS() {
			$params = array(
				'include_hidden' => false,
				'recursive'      => true
			);

			$fonts = $this->parent->filesystem->execute( 'dirlist', $this->upload_dir . 'custom/', $params );

			if ( empty( $fonts ) ) {
				if ( file_exists( $this->upload_dir . 'fonts.css' ) ) {
					$this->parent->filesystem->execute( 'delete', $this->upload_dir . 'fonts.css' );
				}

				return;
			}

			$css = "";

			foreach ( $fonts as $font ) {
				if ( $font['type'] == "d" ) {
					$css .= $this->generateFontCSS( $font['name'], $this->upload_dir . 'custom/' );
				}
			}

			$param_array = array(
				'content' => $css,
				'chmod'   => FS_CHMOD_FILE
			);

			$this->parent->filesystem->execute( 'put_contents', $this->upload_dir . 'fonts.css', $param_array ); // put_contents($this->upload_dir . 'fonts.css', $css, FS_CHMOD_FILE);
		}

		/**
		 * Process to actually construct the custom font css file.
		 *
		 * @param $name
		 * @param $dir
		 *
		 * @return string
		 */
		private function generateFontCSS( $name, $dir ) {
			$path = $dir . $name;

			$params = array(
				'include_hidden' => false,
				'recursive'      => true
			);

			$files = $this->parent->filesystem->execute( 'dirlist', $path, $params );

			if ( empty( $files ) ) {
				return;
			}

			$output = array();

			foreach ( $files as $file ) {
				$output[ $this->checkFontFileName( $file ) ] = $file['name'];
			}

			$css = "@font-face {";

			$css .= "font-family:'{$name}';";

			$src = array();

			if ( isset( $output['eot'] ) ) {
				$src[] = "url('{$this->upload_url}custom/{$name}/{$output['eot']}?#iefix') format('embedded-opentype')";
			}

			if ( isset( $output['woff'] ) ) {
				$src[] = "url('{$this->upload_url}custom/{$name}/{$output['woff']}') format('woff')";
			}

			if ( isset( $output['ttf'] ) ) {
				$src[] = "url('{$this->upload_url}custom/{$name}/{$output['ttf']}') format('truetype')";
			}

			if ( isset( $output['svg'] ) ) {
				$src[] = "url('{$this->upload_url}custom/{$name}/{$output['svg']}#svg{$name}') format('svg')";
			}

			if ( ! empty( $src ) ) {
				$css .= "src:" . implode( ", ", $src ) . ";";
			}

			// Replace font weight and style with sub-sets
			$css .= "font-weight: normal;";

			$css .= "font-style: normal;";

			$css .= "}";

			return $css;
		}

		/**
		 * @return ReduxFramework_extension_custom_fonts
		 */
		public static function get_instance() {
			return self::$theInstance;
		}

		/**
		 * Forces the use of the embeded field path vs what the core typically would use
		 *
		 * @param $field
		 *
		 * @return string
		 */
		public function overload_field_path( $field ) {
			return dirname( __FILE__ ) . '/' . $this->field_name . '/field_' . $this->field_name . '.php';
		}

		/**
		 * Custom function for filtering the sections array. Good for child themes to override or add to the sections.
		 * Simply include this function in the child themes functions.php file.
		 * NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
		 * so you must use get_template_directory_uri() if you want to use any of the built in icons
		 */
		function add_section() {
			if ( ! isset ( $this->parent->fontControl ) ) {
				$this->parent->sections[] = array(
					'title'  => __( 'Font Control', 'redux-framework' ),
					'desc'   => __( '<p class="description"></p>', 'redux-framework' ),
					'icon'   => 'el-icon-font',
					'id'     => 'redux_dynamic_font_control',
					// Leave this as a blank section, no options just some intro text set above.
					'fields' => array()
				);

				for ( $i = count( $this->parent->sections ); $i >= 1; $i -- ) {
					if ( isset ( $this->parent->sections[ $i ] ) && isset ( $this->parent->sections[ $i ]['title'] ) && $this->parent->sections[ $i ]['title'] == __( 'Font Control', 'redux-framework' ) ) {
						$this->parent->fontControl                                        = $i;
						$this->parent->sections[ $this->parent->fontControl ]['fields'][] = array(
							'id'   => 'redux_font_control',
							'type' => 'custom_fonts'
						);

						break;
					}
				}
			}
		}
	} // class
} // if