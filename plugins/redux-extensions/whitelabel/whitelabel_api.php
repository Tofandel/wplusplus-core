<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Whitelabel', false ) ) {

	/**
	 * ReduxWhiteLabel Class
	 *
	 * @since       3.0.0
	 */
	class Redux_Whitelabel {

		const VERSION = '1.0.0';

		/**
		 * @access      private
		 * @var         Redux_Whitelabel
		 * @since       3.0.0
		 */
		private static $instance;

		private static $whitelabels = array();

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       3.1.3
		 * @return      self::$instance The one true ReduxFrameworkPlugin
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
				//self::$instance->init();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		private function hooks() {
			// Customize Details Popup
			add_filter( 'plugin_row_meta', array( $this, 'details_popup' ), 4, 4 );
			// Rename the plugin

			add_filter( 'all_plugins', array( $this, 'rename' ) );
			add_filter( 'is_plugin_active_for_network', array( $this, 'rename' ) );

			add_filter( 'plugin_action_links', array( $this, 'actions' ), 4, 4 );
			add_action( 'in_admin_footer', array( $this, 'enqueue' ), 100 );

			if ( class_exists( 'ReduxFramework' ) ) {
				//  remove_action( 'admin_menu', array( 'ReduxFrameworkPlugin', 'admin_menus' ), 12 );
				remove_action( 'init', array( 'Redux_Welcome', 'do_redirect' ), 10 );
				//add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
			}

			$GLOBALS['redux_notice_check'] = 0;


			global $pagenow;
			if ( $pagenow == "plugins.php" && isset( $_GET['action'] ) && isset( $_GET['plugin'] ) ) {
				if ( strpos( $_GET['plugin'], 'redux-framework' ) !== false ) {
					$id             = str_replace( array(
						'redux-framework/redux-framework',
						urlencode( 'redux-framework/redux-framework' ),
						'.php'
					), '', $_GET['plugin'] );
					$_GET['plugin'] = str_replace( $id, '', $_GET['plugin'] );
				}
			}

		}

		public function remove_redux_menu() {
			remove_submenu_page( 'tools.php', 'redux-about' );
		}

		function remove_demo() {
			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
				remove_filter( 'plugin_row_meta', array(
					ReduxFrameworkPlugin::instance(),
					'plugin_metalinks'
				), null );

				// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
				remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
			}
		}

		public function actions( $actions, $plugin_file, $plugin_data, $context ) {
			//print_r($actions);
			//exit();
			return $actions;
		}


		public function rename( $plugins ) {
			if ( isset( $plugins['redux-framework/redux-framework.php'] ) ) {
				$whitelabels = self::$whitelabels;
				if ( count( $whitelabels ) == 1 ) {
					$plugins['redux-framework/redux-framework.php'] = wp_parse_args( $whitelabels[0], $plugins['redux-framework/redux-framework.php'] );
				} else if ( count( $whitelabels ) > 1 ) {
					$notset = true;
					$count  = 1;
					foreach ( $whitelabels as $label ) {
						if ( $notset ) {
							$plugins['redux-framework/redux-framework.php'] = wp_parse_args( $label, $plugins['redux-framework/redux-framework.php'] );
							$notset                                         = false;
						} else {
							array_push( $plugins, wp_parse_args( $label, $plugins['redux-framework/redux-framework.php'] ) );
							//$plugins['redux-framework/redux-framework'.$count.'.php'] = wp_parse_args( $label, $plugins['redux-framework/redux-framework.php'] );
							$count ++;
						}
					}
				}
			}

			return $plugins;
		}

		public function enqueue() {

			if ( ! wp_script_is( 'redux-fix-deactivates', 'done' ) ) {
				?>
				<script>
					jQuery(document).ready(function ($) {
						var $href = $('.real-redux-plugin').attr('href');
							//console.log( $href );
						$(".fake-redux-plugin").each(
								function (index) {
									jQuery(this).attr('href', $href);
									console.log('Done');
								}
							);
						}
					);
				</script>
				<?php
				global $wp_scripts;
				$wp_scripts->done[] = 'redux-fix-deactivates';
			}


		}

		public function details_popup( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( strpos( $plugin_file, 'redux-framework/redux-framework' ) !== false ) {
				$id = str_replace( array( 'redux-framework/redux-framework', '.php' ), '', $plugin_file );
				if ( empty( $id ) ) {
					$id = 0;
				}
				foreach ( $plugin_meta as $key => $meta ) {
					if ( strpos( $meta, 'tab=plugin-information' ) !== false ) {
						$url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_data['slug'] );

						$new = isset( self::$whitelabels[ $id ] ) ? self::$whitelabels : array();

						if ( isset( $new['DetailsURI'] ) ) {
							if ( $new['DetailsURI'] == "remove" ) {
								unset( $plugin_meta[ $key ] );
								continue;
							}
							$url = $new['DetailsURI'];
							$url .= "?";
						} else {
							$url .= "&";
						}
						$url     .= 'TB_iframe=true&width=600&height=550';
						$classes = "thickbox";
						if ( $id == 0 ) {
							$classes .= " real-redux-plugin";
						}
						$plugin_meta[ $key ] = sprintf( '<a href="%s" class="%s" aria-label="%s" data-title="%s">%s</a>',
							esc_url( $url ),
							$classes,
							esc_attr( sprintf( __( 'More information about %s' ), $plugin_data['Title'] ) ),
							esc_attr( $plugin_data['Title'] ),
							__( 'View details' )
						);
					}
				}
			}

			return $plugin_meta;
		}

		public static function set( $args ) {
			self::$whitelabels[] = $args;
		}

	}

	Redux_Whitelabel::instance();
}