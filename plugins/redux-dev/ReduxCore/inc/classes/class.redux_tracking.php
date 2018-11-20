<?php
/**
 * @package Redux_Tracking
 */

if ( ! class_exists( 'ReduxFramework', false ) ) {
	return;
}

/**
 * Class that creates the tracking functionality for Redux, as the core class might be used in more plugins,
 * it's checked for existence first.
 * NOTE: this functionality is opt-in. Disabling the tracking in the settings or saying no when asked will cause
 * this file to not even be loaded.
 */

if ( ! class_exists( 'Redux_Tracking', false ) ) {

	/**
	 * Class Redux_Tracking
	 */
	class Redux_Tracking {

		/**
		 * @var array
		 */
		public $options = array();

		/**
		 * @var
		 */
		public $parent;

		/** Refers to a single instance of this class. */
		private static $instance = null;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return Redux_Tracking A single instance of this class.
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * @param ReduxFramework $parent
		 */
		public function load( $parent ) {
			$this->parent = $parent;

			$this->options             = get_option( 'redux-framework-tracking' );
			$this->options['dev_mode'] = $parent->args['dev_mode'];

			if ( ! isset( $this->options['hash'] ) || ! $this->options['hash'] || empty( $this->options['hash'] ) ) {
				$this->options['hash'] = Redux_Helpers::get_hash();
				update_option( 'redux-framework-tracking', $this->options );
			}

			if ( isset( $_GET['redux_framework_disable_tracking'] ) && ! empty( $_GET['redux_framework_disable_tracking'] ) ) { // WPCS: CSRF ok.
				$this->options['allow_tracking'] = 'no';
				update_option( 'redux-framework-tracking', $this->options );
			}

			if ( isset( $_GET['redux_framework_enable_tracking'] ) && ! empty( $_GET['redux_framework_enable_tracking'] ) ) { // WPCS: CSRF ok.
				$this->options['allow_tracking'] = 'yes';
				update_option( 'redux-framework-tracking', $this->options );
			}

			if ( isset( $_GET['page'] ) && $this->parent->args['page_slug'] === $_GET['page'] ) { // WPCS: CSRF ok.
				if ( empty( $this->options['allow_tracking'] ) ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_tracking' ) );
				} elseif ( empty( $this->options['tour'] ) && ( true === $this->parent->args['dev_mode'] || 'redux_demo' === $this->parent->args['page_slug'] ) ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_newsletter' ) );
				}
			}

			$hash = md5( trailingslashit( network_site_url() ) . '-redux' );
			add_action( 'wp_ajax_nopriv_' . $hash, array( $this, 'tracking_arg' ) );
			add_action( 'wp_ajax_' . $hash, array( $this, 'tracking_arg' ) );

			$hash = md5( md5( AUTH_KEY . SECURE_AUTH_KEY . '-redux' ) . '-support' );
			add_action( 'wp_ajax_nopriv_' . $hash, array( $this, 'support_args' ) );
			add_action( 'wp_ajax_' . $hash, array( $this, 'support_args' ) );

			if ( isset( $this->options['allow_tracking'] ) && 'yes' === $this->options['allow_tracking'] ) {
				// The tracking checks daily, but only sends new data every 7 days.
				if ( ! wp_next_scheduled( 'redux_tracking' ) ) {
					wp_schedule_event( time(), 'daily', 'redux_tracking' );
				}
				add_action( 'redux_tracking', array( $this, 'tracking' ) );
			}
		}

		public function enqueue_tracking() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
			add_action( 'admin_print_footer_scripts', array( $this, 'tracking_request' ) );
		}

		public function enqueue_newsletter() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
			add_action( 'admin_print_footer_scripts', array( $this, 'newsletter_request' ) );
		}

		/**
		 * Shows a popup that asks for permission to allow tracking.
		 */
		public function tracking_request() {
			$id    = '#wpadminbar';
			$nonce = wp_create_nonce( 'redux_activate_tracking' );

			$content = '<h3>' . esc_html__( 'Help improve Our Panel', 'redux-framework' ) . '</h3>';
			$content .= '<p>' . esc_html__( 'Please helps us improve our panel by allowing us to gather anonymous usage stats so we know which configurations, plugins and themes to test to ensure compatibility.', 'redux-framework' ) . '</p>';
			$opt_arr = array(
				'content'  => $content,
				'position' => array(
					'edge'  => 'top',
					'align' => 'center',
				),
			);

			$button2 = esc_html__( 'Allow tracking', 'redux-framework' );

			$function2 = 'redux_store_answer("yes","' . $nonce . '")';
			$function1 = 'redux_store_answer("no","' . $nonce . '")';

			$this->print_scripts( $id, $opt_arr, esc_html__( 'Do not allow tracking', 'redux-framework' ), $button2, $function2, $function1 );
		}

		/**
		 * Shows a popup that asks for permission to allow tracking.
		 */
		public function newsletter_request() {
			$id    = '#wpadminbar';
			$nonce = wp_create_nonce( 'redux_activate_tracking' );

			// TODO: This escapeing and sprintf stuff if fucked.
			$content = '<h3>' . esc_html__( 'Welcome to the Redux Demo Panel', 'redux-framework' ) . '</h3>';
			$content .= '<p><strong>' . esc_html__( 'Getting Started', 'redux-framework' ) . '</strong><br>' . sprintf( esc_html__( 'This panel demonstrates the many features of Redux.  Before digging in, we suggest you get up to speed by reviewing', 'redux-framework' ) . ' %1$s.', '<a href="' . '//docs.reduxframework.com/redux-framework/getting-started/" target="_blank">' . esc_html__( 'our documentation', 'redux-framework' ) . '</a>' );
			$content .= '<p><strong>' . esc_html__( 'Redux Generator', 'redux-framework' ) . '</strong><br>' . sprintf( esc_html__( 'Want to get a head start? Use the', 'redux-framework' ) . ' %1$s. ' . esc_html__( 'It will create a customized boilerplate theme or a standalone admin folder complete with all things Redux (with the help of Underscores and TGM). Save yourself a headache and try it today.', 'redux-framework' ), '<a href="' . '//generate.reduxframework.com/" target="_blank">' . esc_html__( 'Redux Generator', 'redux-framework' ) . '</a>' );
			$content .= '<p><strong>' . esc_html__( 'Redux Extensions', 'redux-framework' ) . '</strong><br>' . sprintf( esc_html__( 'Did you know we have extensions, which greatly enhance the features of Redux?  Visit our', 'redux-framework' ) . ' %1$s ', esc_html__( 'to learn more!', 'redux-framework' ), '<a href="' . '//reduxframework.com/extensions/" target="_blank">' . esc_html__( 'extensions directory', 'redux-framework' ) . '</a>' );
			$content .= '<p><strong>' . esc_html__( 'Like Redux?', 'redux-framework' ) . '</strong><br>' . sprintf( esc_html__( 'If so, please', 'redux-framework' ) . ' %1$s ' . esc_html__( 'and consider making a', 'redux-framework' ) . ' %2$s ' . esc_html__( 'to keep development of Redux moving forward.', 'redux-framework' ), '<a target="_blank" href="' . '//wordpress.org/support/view/plugin-reviews/redux-framework">' . esc_html__( 'leave us a favorable review on WordPress.org', 'redux-framework' ) . '</a>', '<a href="' . 'https://' . 'www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=N5AD7TSH8YA5U" target="_blank">' . esc_html__( 'donation', 'redux-framework' ) . '</a>' );
			$content .= '<p><strong>' . esc_html__( 'Newsletter', 'redux-framework' ) . '</strong><br>' . esc_html__( 'If you\'d like to keep up to with all things Redux, please subscribe to our newsletter', 'redux-framework' ) . ':</p>';
			$content .= '<form action="http://news.redux.io/subscribe" method="POST" target="_blank" accept-charset="utf-8" class="validate">
                                <p style="text-align: center;">
                                    <label for="email">' . esc_html__( 'Email Address', 'redux-framework' ) . '</label>
                                    <input type="email" name="email" class="required email" id="email"/>
                                    <input type="hidden" name="list" value="9K1qDRvB8Ux0DqpEoQSEPA"/>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="submit" class="button button-primary" name="submit" value="' . esc_html__( 'Subscribe', 'redux-framework' ) . '" id="submit"/>
                                    </p>
                            </form>';
			$opt_arr = array(
				'content'      => $content,
				'position'     => array(
					'edge'  => 'top',
					'align' => 'center',
				),
				'pointerWidth' => 450,
			);

			$function1 = 'redux_store_answer("tour","' . $nonce . '")';

			$this->print_scripts( $id, $opt_arr, esc_html__( 'Close', 'redux-framework' ), false, '', $function1 );
		}

		/**
		 * Prints the pointer script
		 *
		 * @param string      $selector         The CSS selector the pointer is attached to.
		 * @param array       $options          The options for the pointer.
		 * @param string      $button1          Text for button 1
		 * @param string|bool $button2          Text for button 2 (or false to not show it, defaults to false)
		 * @param string      $button2_function The JavaScript function to attach to button 2
		 * @param string      $button1_function The JavaScript function to attach to button 1
		 */
		private function print_scripts( $selector, $options, $button1, $button2 = false, $button2_function = '', $button1_function = '' ) {
?>
			<script type="text/javascript">
				(function( $ ) {
					var redux_pointer_options = <?php echo wp_json_encode( $options ); ?>, setup;

					function redux_store_answer( input, nonce ) {
						var redux_tracking_data = {
							action: 'redux_allow_tracking',
							allow_tracking: input,
							nonce: nonce
						};
						$.post(
							ajaxurl, redux_tracking_data, function() {
								$( '#wp-pointer-0' ).remove();
							}
						);
					}

					redux_pointer_options = $.extend(
						redux_pointer_options, {
							buttons: function( event, t ) {
								button = $(
									'<a id="pointer-close" style="margin-left:5px" class="button-secondary">' + '<?php echo esc_html( $button1 ); ?>' + '</a>' );
								button.bind(
									'click.pointer', function() {
										t.element.pointer( 'close' );
										//console.log( 'close button' );
									}
								);
								return button;
							},
							close: function() {
							}
						}
					);

					setup = function() {
						$( '<?php echo( esc_html( $selector ) ); ?>' ).pointer( redux_pointer_options ).pointer( 'open' );
						var ptc = $( '#pointer-close' );
						<?php if ( $button2 ) { ?>
						ptc.after(
							'<a id="pointer-primary" class="button-primary">' + '<?php echo esc_html( $button2 ); ?>' + '</a>' );
						$( '#pointer-primary' ).click(
							function() {
								<?php echo( esc_html( $button2_function ) ); ?>
							}
						);
						ptc.click(
							function() {
								<?php if ( '' === $button1_function ) { ?>
								redux_store_answer( input, nonce );
								<?php } else { ?>
								<?php echo( esc_html( $button1_function ) ); ?>
								<?php } ?>
							}
						);
						<?php } elseif ( $button1 && ! $button2 ) { ?>
						ptc.click(
							function() {
								<?php if ( '' !== $button1_function ) { ?>
									<?php echo( esc_html( $button1_function ) ); ?>
								<?php } ?>
							}
						);
						<?php } ?>
					};

					if ( redux_pointer_options.position && redux_pointer_options.position.defer_loading ) {
						$( window ).bind( 'load.wp-pointers', setup );
						console.log( 'load' );
					} else {
						$( document ).ready( setup );
					}
				})( jQuery );
			</script>
			<?php
		}

		private function tracking_object() {
			global $blog_id, $wpdb;
			$pts = array();

			foreach ( get_post_types( array( 'public' => true ) ) as $pt ) {
				$count      = wp_count_posts( $pt );
				$pts[ $pt ] = $count->publish;
			}

			$comments_count = wp_count_comments();
			$theme_data     = wp_get_theme();
			$theme          = array(
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName
				'version'  => $theme_data->Version,
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName
				'name'     => $theme_data->Name,
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName
				'author'   => $theme_data->Author,
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName
				'template' => $theme_data->Template,
			);

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/admin.php';
			}

			$plugins = array();
			foreach ( get_option( 'active_plugins', array() ) as $plugin_path ) {
				$plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );

				$slug             = str_replace( '/' . basename( $plugin_path ), '', $plugin_path );
				$plugins[ $slug ] = array(
					'version'    => $plugin_info['Version'],
					'name'       => $plugin_info['Name'],
					'plugin_uri' => $plugin_info['PluginURI'],
					'author'     => $plugin_info['AuthorName'],
					'author_uri' => $plugin_info['AuthorURI'],
				);
			}
			if ( is_multisite() ) {
				foreach ( get_option( 'active_sitewide_plugins', array() ) as $plugin_path ) {
					$plugin_info      = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
					$slug             = str_replace( '/' . basename( $plugin_path ), '', $plugin_path );
					$plugins[ $slug ] = array(
						'version'    => $plugin_info['Version'],
						'name'       => $plugin_info['Name'],
						'plugin_uri' => $plugin_info['PluginURI'],
						'author'     => $plugin_info['AuthorName'],
						'author_uri' => $plugin_info['AuthorURI'],
					);
				}
			}


			$version = explode( '.', PHP_VERSION );
			$version = array(
				'major'   => $version[0],
				'minor'   => $version[0] . '.' . $version[1],
				'release' => PHP_VERSION,
			);

			$user_query = new WP_User_Query(
				array(
					'blog_id'     => $blog_id,
					'count_total' => true,
				)
			);

			$comments_query = new WP_Comment_Query();
			$data           = array(
				'_id'       => $this->options['hash'],
				'localhost' => ( '127.0.0.1' === ReduxCore::$_server['REMOTE_ADDR'] ) ? 1 : 0,
				'php'       => $version,
				'site'      => array(
					'hash'      => $this->options['hash'],
					'version'   => get_bloginfo( 'version' ),
					'multisite' => is_multisite(),
					'users'     => $user_query->get_total(),
					'lang'      => get_locale(),
					'wp_debug'  => ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? true : false : false ),
					'memory'    => WP_MEMORY_LIMIT,
				),
				'pts'       => $pts,
				'comments'  => array(
					'total'    => $comments_count->total_comments,
					'approved' => $comments_count->approved,
					'spam'     => $comments_count->spam,
					'pings'    => $comments_query->query(
						array(
							'count' => true,
							'type'  => 'pingback',
						)
					),
				),
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				'options'   => apply_filters( 'redux/tracking/options', array() ),
				'theme'     => $theme,
				'redux'     => array(
					'mode'      => ReduxCore::$_is_plugin ? 'plugin' : 'theme',
					'version'   => ReduxCore::$_version,
					'demo_mode' => get_option( 'ReduxFrameworkPlugin' ),
				),
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				'developer' => apply_filters( 'redux/tracking/developer', array() ),
				'plugins'   => $plugins,
			);

			$parts    = explode( ' ', ReduxCore::$_server['SERVER_SOFTWARE'] );
			$software = array();
			foreach ( $parts as $part ) {
				if ( '(' === $part[0] ) {
					continue;
				}
				if ( strpos( $part, '/' ) !== false ) {
					$chunk                               = explode( '/', $part );
					$software[ strtolower( $chunk[0] ) ] = $chunk[1];
				}
			}
			$software['full']             = ReduxCore::$_server['SERVER_SOFTWARE'];
			$data['environment']          = $software;
			$data['environment']['mysql'] = $wpdb->db_version();

			if ( empty( $data['developer'] ) ) {
				unset( $data['developer'] );
			}

			return $data;
		}

		/**
		 * Main tracking function.
		 */
		public function tracking() {
			// Start of Metrics.
			$data = get_transient( 'redux_tracking_cache' );
			if ( ! $data ) {
				$args = array( 'body' => $this->tracking_object() );

				// $response = wp_remote_post( 'https://redux-tracking.herokuapp.com', $args );
				// Store for a week, then push data again.
				set_transient( 'redux_tracking_cache', true, WEEK_IN_SECONDS );
			}
		}

		public function tracking_arg() {
			echo esc_html( md5( AUTH_KEY . SECURE_AUTH_KEY . '-redux' ) );
			die();
		}

		function support_args() {
			header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . 'GMT' );
			header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );

			$instances = Redux::all_instances();

			if ( isset( $_REQUEST['i'] ) && ! empty( $_REQUEST['i'] ) ) { // WPCS: CSRF ok.
				if ( is_array( $instances ) && ! empty( $instances ) ) {
					foreach ( $instances as $opt_name => $data ) {
						if ( md5( $opt_name . '-debug' ) === $_REQUEST['i'] ) { // WPCS: CSRF ok.
							$array = $instances[ $opt_name ];
						}
					}
				}
				if ( isset( $array ) ) {
					if ( isset( $array->extensions ) && is_array( $array->extensions ) && ! empty( $array->extensions ) ) {
						foreach ( $array->extensions as $key => $extension ) {
							if ( isset( $extension->version ) ) {
								$array->extensions[ $key ] = $extension->version;
							} else {
								$array->extensions[ $key ] = true;
							}
						}
					}
					if ( isset( $array->debug ) ) {
						unset( $array->debug );
					}
				} else {
					die();
				}
			} else {
				$array = $this->tracking_object();
				if ( is_array( $instances ) && ! empty( $instances ) ) {
					$array['instances'] = array();
					foreach ( $instances as $opt_name => $data ) {
						$array['instances'][] = $opt_name;
					}
				}
				$array['key'] = md5( AUTH_KEY . SECURE_AUTH_KEY );
			}

			// phpcs:ignored WordPress.PHP.NoSilencedErrors
			echo @wp_json_encode( $array, true ); // WPCS: XSS ok.
			die();
		}
	}

	Redux_Tracking::get_instance();

	/**
	 * Adds tracking parameters for Redux settings. Outside of the main class as the class could also be in use in other ways.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	function redux_tracking_additions( $options ) {
		$options['redux'] = array(
			'demo_mode' => get_option( 'ReduxFrameworkPlugin' ),
		);

		return $options;
	}

	add_filter( 'redux/tracking/options', 'redux_tracking_additions' );

	function redux_allow_tracking_callback() {
		// Verify that the incoming request is coming with the security nonce.
		if ( check_ajax_referer( 'redux_activate_tracking', 'nonce' ) ) {
			$options = get_option( 'redux-framework-tracking' );

			if ( isset( $_REQUEST['allow_tracking'] ) && 'tour' === $_REQUEST['allow_tracking'] ) {
				$options['tour'] = 1;
			} else {
				$options['allow_tracking'] = sanitize_text_field( wp_unslash( $_REQUEST['allow_tracking'] ) );
			}

			if ( update_option( 'redux-framework-tracking', $options ) ) {
				die( '1' );
			} else {
				die( '0' );
			}
		} else {
			// Send -1 if the attempt to save via Ajax was completed invalid.
			die( '-1' );
		} // end if
	}

	add_action( 'wp_ajax_redux_allow_tracking', 'redux_allow_tracking_callback' );
}
