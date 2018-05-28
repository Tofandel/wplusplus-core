<?php

namespace Tofandel\Classes;

use Exception;
use ReflectionClass;
use Tofandel\Traits\Singleton;


require_once __DIR__ . '/../../admin/tgmpa-config.php';

/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 */
abstract class WP_Plugin implements \Tofandel\Interfaces\WP_Plugin {
	use Singleton;

	protected $text_domain;
	protected $slug;
	protected $name;
	protected $file;
	protected $version = false;
	protected $class;

	protected $is_muplugin = false;

	protected $download_url;

	static $text_domains = array();

	public static function TextDomain() {
		static::__init__()->text_domain;
	}

	/**
	 * Plugin constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {

		$this->class = new ReflectionClass( $this );
		$this->slug  = $this->class->getShortName();
		$this->file  = $this->class->getFileName();

		if ( strpos( $this->file, 'mu-plugin' ) ) {
			$this->is_muplugin = true;
		}

		$comment = $this->class->getDocComment();

		$this->extractFromComment( $comment );

		$version = get_option( $this->slug . '_version' );
		if ( version_compare( $version, $this->version, '!=' ) ) {
			add_action( 'init', [ $this, 'activate' ], 1 );
		}
		$this->setup();
	}

	protected function extractFromComment( $comment ) {

		//Read the version of the plugin from the comments
		if ( $comment && preg_match( '#version[: ]*([0-9\.]+)#i', $comment, $matches ) ) {
			$this->version = $matches[1];
		} else {
			$this->version = '1.0';
		}

		//Read the name of the plugin from the comments
		if ( $comment && preg_match( '#(?:plugin|theme)[- ]?name[: ]*(\S+)#i', $comment, $matches ) ) {
			$this->name = $matches[1];
		} else {
			$this->name = $this->slug;
		}

		//Read the text domain of the plugin from the comments
		if ( $comment && preg_match( '#text[- ]?domain[: ]*(\S+)#i', $comment, $matches ) ) {
			$this->text_domain = $matches[1];
			define( strtoupper( $this->class->getShortName() ) . '_TD', $this->text_domain );
		}

		if ( $comment && preg_match( '#download[- ]?url[: ]*(\S+)#i', $comment, $matches ) ) {
			$this->download_url = $matches[1];
			require __DIR__ . '../../vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
			\Puc_v4_Factory::buildUpdateChecker(
				$this->download_url,
				$this->file, //Full path to the main plugin file or functions.php.
				$this->slug
			);
		}
	}

	/**
	 * Setup default plugin actions
	 */
	protected function setup() {
		add_action( 'plugins_loaded', array( $this, 'loadTextdomain' ) );
		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $this->file, get_called_class() . '::uninstall' );
		$this->definitions();
		$this->actionsAndFilters();

		add_action( 'admin_init', [ $this, '_reduxOptions' ] );
		add_action( 'admin_init', [ $this, 'checkCompat' ] );
	}

	protected function loadRedux() {
		$plugins = get_option( 'active_plugins' );

		$loaded = false;
		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'redux-framework' ) !== false ) {
				//We load redux's plugin
				if ( file_exists( ABSPATH . '/plugins/' . $plugin ) ) {
					require_once ABSPATH . '/plugins/' . $plugin;
					$loaded = true;
					break;
				}
			}
		}
		if ( ! $loaded ) {
			if ( file_exists( __DIR__ . '../../admin/redux-framework/framework.php' ) ) {
				require_once __DIR__ . '../../admin/redux-framework/framework.php';
			}
		}

		if ( file_exists( __DIR__ . '../../admin/redux-extensions/extensions-init.php' ) ) {
			require_once __DIR__ . '../../admin/redux-extensions/extensions-init.php';
		}
	}

	/**
	 * Add the tables and settings and any plugin variable specifics here
	 *
	 * @return void
	 */
	abstract public function definitions();

	/**
	 * Add actions and filters here
	 */
	abstract public function actionsAndFilters();

	/**
	 * Called function if a plugin is uninstalled
	 * @throws \ReflectionException
	 */
	public static function uninstall() {
		$ref = new ReflectionClass( static::class );
		delete_option( $ref->getShortName() . '_version' );
	}

	/**
	 * Magic method that returns the plugin name if trying to convert the plugin object to a string
	 * @return string
	 */
	public function __toString() {
		return $this->slug;
	}


	public function pluginName() {
		return esc_html__( str_replace( array( '-', '_' ), ' ', (string) $this ) );
	}

	/**
	 * Searchs if a file exists in the plugin folder (minified or not)
	 *
	 * @param string $name
	 * @param string $type
	 * @param bool $cache
	 * @param string|false $folder
	 *
	 * @return string
	 */
	public function searchFile( $name, $type = '', $cache = false, $folder = false ) {
		global $plugin_page;


		if ( strpos( $name, '//' ) === 0 || strpos( $name, 'http' ) === 0 ) {
			return $name;
		}

		$name = self::removeExtension( self::removeExtension( $name, $type ), 'min' );

		if ( ! WP_DEBUG && $cache && $f = get_transient( 'wpp_file_' . $type . '_' . $name ) ) {
			return $f;
		}

		$folder = trailingslashit( $this->folder( $folder ) );

		if ( WP_DEBUG ) {
			$files = array(
				$folder . $name . '.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.' . $type,
				$folder . $name . '.min.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.min.' . $type,
				$folder . $name
			);
		} else {
			$files = array(
				$folder . $name . '.min.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.min.' . $type,
				$folder . $name . '.' . $type,
				$folder . trailingslashit( $plugin_page ) . $name . '.' . $type,
				$folder . $name
			);
		}

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$file = str_replace( ABSPATH, '/', $file );
				if ( $cache ) {
					//1 day file cache to minimize I/O
					set_transient( 'wpp_file_' . $type . '_' . $name, $file, 86400 );
				}

				return $file;
			}
		}

		return false;
	}

	/**
	 * Removes the extension from a filename
	 *
	 * @param string $string
	 * @param string $ext
	 *
	 * @return string
	 */
	public static function removeExtension( $string, $ext ) {
		$ext     = '.' . $ext;
		$ext_len = strlen( $ext );
		if ( strrpos( $string, $ext, $ext_len ) === 0 ) {
			$string = substr( $string, 0, - $ext_len );
		}

		return $string;
	}

	/**
	 * @param string $folder
	 *
	 * @return string Path to the plugin's folder
	 */
	public function folder( $folder = '' ) {
		return trailingslashit( dirname( $this->file ) ) . "$folder";
	}

	/**
	 * @param string $js Filename (optional extension)
	 * @param array $require
	 * @param bool $localize
	 * @param bool $in_footer
	 *
	 * @return string
	 */
	public function addScript( $js, $require = array(), $localize = false, $in_footer = false ) {

		$name = basename( $js );
		$file = $this->registerScript( $js, $require, $localize, $in_footer );

		if ( $file && wp_script_is( $name, 'registered' ) ) {
			wp_enqueue_script( $name );
		} else {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	public function registerScript( $js, $require = array(), $localize = false, $in_footer = false ) {
		$name = basename( $js );
		if ( ! wp_script_is( $name, 'registered' ) ) {
			if ( $file = $this->searchFile( $js, 'js', true, 'js' ) ) {
				wp_register_script( $name, $file, $require, $this->version, $in_footer );
			}
		}
		if ( wp_script_is( $name, 'registered' ) ) {
			if ( ! empty( $localize ) ) {
				wp_localize_script( $name, str_replace( array(
					'-',
					'.'
				), '_', $name ), array_merge( array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ), $localize ) );
			}
		} else {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return string
	 */
	public function addStyle( $css, $media = 'all' ) {
		$name = basename( $css );
		$file = $this->registerStyle( $css, $media );

		if ( $file && wp_style_is( $name, 'registered' ) ) {
			wp_enqueue_style( $name );
		} else {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return string
	 */
	public function registerStyle( $css, $media = 'all' ) {
		$name = basename( $css );
		if ( ! wp_style_is( $name, 'registered' ) ) {
			if ( $file = $this->searchFile( $css, 'css', true, 'css' ) ) {
				wp_register_style( $name, $file, array(), $this->version, $media );
			}
		}

		if ( ! wp_style_is( $name, 'registered' ) ) {
			$file = false;
		}

		return isset( $file ) ? $file : $name;
	}

	public function webPath( $folder = '' ) {
		return plugin_dir_url( $this->file ) . "$folder";
	}

	/**
	 * Prepare plugin internationalisation
	 */
	public function loadTextdomain() {
		call_user_func( 'load_' . ( $this->is_muplugin ? 'mu' : '' ) . 'plugin_textdomain', $this->text_domain, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Returns the list of version information for the plugin
	 */
	public function getVersion() {
		return $this->version;
	}

	public function checkCompat() {
		if ( ! self::checkCompatibility() ) {
			if ( is_plugin_active( $this->file ) ) {
				deactivate_plugins( $this->file );
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}

	}

	/**
	 * Check that the required version of php is installed before activating the plugin
	 *
	 * @return bool
	 */
	public static function checkCompatibility() {
		if ( version_compare( phpversion(), '5.6', '<' ) ) {
			return false;
		}

		return true;
	}

	public function disabled_notice() {
		echo '<strong>' . sprintf( esc_html__( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', self::textDomain() ), $this->name, '5.6', PHP_VERSION ) . '</strong>';
	}

	/**
	 * Called function on plugin activation
	 */
	public function activate() {
		if ( ! self::checkCompatibility() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', self::textDomain() ), $this->name, '5.6', PHP_VERSION ) );
		}

		if ( ! add_option( $this->slug . '_version', $this->version ) ) {
			//An old version existed before
			$last_version = get_option( $this->slug . '_version' );
			//Fresh install
			//foreach ( $this->db_tables as $table ) {
			/**
			 * @var WP_DB_Table $table
			 */
			//	$table->upgrade();
			//}
			//Check the version number
			if ( version_compare( $last_version, $this->version, '>' ) ) {
				$this->multisiteUpgrade( $last_version );
			} elseif ( version_compare( $last_version, $this->version, '<' ) ) {
				$this->multisiteDowngrade( $last_version );
			}
			if ( $last_version != $this->version ) {
				update_option( $this->slug . '_version', $this->version );
			}
			//} else {
			//Fresh install
			//foreach ( $this->db_tables as $table ) {
			/**
			 * @var WP_DB_Table $table
			 */
			//	$table->register();
			//}
		}

		//Setup default plugin folders
		//$this->mkdir( 'languages' );
		//$this->mkdir( 'css' );
		//$this->mkdir( 'js' );
		add_action( 'init', 'flush_rewrite_rules' );
	}

	private function multisiteUpgrade( $last_version ) {
		if ( ! is_multisite() ) {
			$this->upgrade( $last_version );
		} else {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->upgrade( $last_version );
				restore_current_blog();
			}
		}
	}

	/**
	 * Called function after a plugin update
	 * Can be used if options needs to be added or if previous database entries need to be modified
	 */
	abstract protected function upgrade( $last_version );

	protected function multisiteDowngrade( $last_version ) {
		if ( ! is_multisite() ) {
			$this->downgrade( $last_version );
		} else {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->downgrade( $last_version );
				restore_current_blog();
			}
		}
	}

	/**
	 * Called function if a plugin is downgraded (incompatibility or something)
	 * Rarely supported/used but still including it just in case
	 *
	 * @param $last_version
	 */
	protected function downgrade( $last_version ) {
	}

	public function mkdir( $folder ) {
		if ( ! is_dir( $this->folder( $folder ) ) ) {
			mkdir( $this->folder( $folder ), 0755 );
		}
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	abstract public function reduxOptions();

	public function _reduxOptions() {
		if ( ! class_exists( 'Redux' ) ) {
			$this->loadRedux();
		}
		if ( class_exists( 'Redux' ) ) {
			$this->reduxOptions();
		}
	}

	/**
	 * Called function on plugin deactivation
	 * Options and plugin data should only be removed in the uninstall function
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Helper function to check if the new plugin version is a minor update
	 *
	 * @param $last_version
	 *
	 * @return bool
	 */
	protected function isMinorUpdate( $last_version ) {
		$new_versions  = explode( '.', $this->version, 2 );
		$last_versions = explode( '.', $last_version, 2 );

		if ( $new_versions[0] == $last_versions[0] && $new_versions[1] != $last_versions[1] ) {
			return true;
		}

		return false;
	}

	/**
	 * Helper function to check if the new plugin version is a major update
	 *
	 * @param $last_version
	 *
	 * @return bool
	 */
	protected function isMajorUpdate( $last_version ) {
		$new_versions  = explode( '.', $this->version, 2 );
		$last_versions = explode( '.', $last_version, 2 );

		if ( $new_versions[0] != $last_versions[0] ) {
			return true;
		}

		return false;
	}
}