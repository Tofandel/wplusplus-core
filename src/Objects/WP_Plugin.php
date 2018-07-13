<?php

namespace Tofandel\Core\Objects;

use Exception;
use ReflectionClass;
use Tofandel\Core\Traits\Singleton;


require_once __DIR__ . '/../../admin/tgmpa-config.php';

/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 */
abstract class WP_Plugin implements \Tofandel\Core\Interfaces\WP_Plugin {
	use Singleton;

	protected $text_domain;
	protected $slug;
	protected $name;
	protected $file;
	protected $version = false;
	protected $class;

	protected $is_muplugin = false;

	protected $download_url;

	public function getFile() {
		return $this->file;
	}

	public function getTextDomain() {
		return $this->text_domain;
	}

	public function getVersion() {
		return $this->version;
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

		//We define a global with the name of the class
		if ( ! isset( $GLOBALS[ $this->class->getShortName() ] ) ) {
			$GLOBALS[ $this->class->getShortName() ] = $this;
		}

		$this->setup();
	}

	protected function extractFromComment( $comment ) {

		//Read the version of the plugin from the comments
		if ( $comment && preg_match( '#version[: ]*([0-9\.]+)#i', $comment, $matches ) ) {
			$this->version = trim( $matches[1] );
		} else {
			$this->version = '1.0';
		}

		//Read the name of the plugin from the comments
		if ( $comment && preg_match( '#(?:plugin|theme)[- ]?name[: ]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->name = trim( $matches[1] );
		} else {
			$this->name = $this->slug;
		}

		//Read the text domain of the plugin from the comments
		if ( $comment && preg_match( '#text[- ]?domain[: ]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->text_domain = trim( $matches[1] );
			define( strtoupper( $this->class->getShortName() ) . '_TD', $this->text_domain );
		}

		if ( $comment && preg_match( '#download[- ]?url[: ]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->download_url = trim( $matches[1] );
			//require __DIR__ . '/../../vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
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

		if ( is_admin() && ! ( isset ( $_POST['action'] ) && $_POST['action'] == 'heartbeat' ) ) {
			$this->_reduxOptions();
			add_action( 'admin_init', [ $this, 'checkCompat' ] );
		} else {
			do_action( 'redux_not_loaded' );
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
	 * Magic method that returns the plugin text domain if trying to convert the plugin object to a string
	 * @return string
	 */
	public function __toString() {
		return $this->getTextDomain();
	}


	public function pluginName() {
		return esc_html__( str_replace( array( '-', '_' ), ' ', (string) $this ) );
	}

	public static function deleteTransients() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wpp_file_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wpp_file_' ) . '%'
		) );
	}

	/**
	 * @param string $opt_name
	 * @param string|array|null $option
	 * @param mixed $default
	 *
	 * @return array|string
	 */
	public static function getReduxOption( $opt_name, $option = null, $default = false ) {
		static $options = array();

		if ( ! isset( $options[ $opt_name ] ) ) {
			$options[ $opt_name ] = get_option( $opt_name, array() );
			if ( ! isset( $GLOBALS[ $opt_name ] ) ) {
				$GLOBALS[ $opt_name ] = $options[ $opt_name ];
			}
		}

		if ( is_array( $option ) ) {
			$option = array_reverse( $option );
			$v      = $options[ $opt_name ];
			while ( $k = array_pop( $option ) ) {
				if ( isset( $v[ $k ] ) ) {
					$v = $v[ $k ];
				} else {
					return $default;
				}
			}

			return $v;
		} elseif ( is_string( $option ) ) {
			return $options[ $opt_name ][ $opt_name ];
		} else {
			return $options[ $opt_name ];
		}
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
					//1 month file path cache to minimize I/O
					set_transient( 'wpp_file_' . $type . '_' . $name, $file, 2592000 );
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
		if ( strpos( $folder, ABSPATH ) === 0 ) {
			return trailingslashit( $folder );
		}
		return trailingslashit( trailingslashit( dirname( $this->file ) ) . "$folder" );
	}

	/**
	 * @param string $file
	 *
	 * @return string Path to the plugin's file
	 */
	public function file( $file = '' ) {
		if ( strpos( $file, ABSPATH ) === 0 ) {
			return $file;
		}
		return trailingslashit( dirname( $this->file ) ) . "$file";
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
		echo '<strong>' . sprintf( esc_html__( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', $this->text_domain ), $this->name, '5.6', PHP_VERSION ) . '</strong>';
	}

	/**
	 * Called function on plugin activation
	 */
	public function activate() {
		if ( ! self::checkCompatibility() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', $this->text_domain ), $this->name, '5.6', PHP_VERSION ) );
		}

		if ( ! add_option( $this->slug . '_version', $this->version ) ) {
			//An old version existed before
			$last_version = get_option( $this->slug . '_version' );
			//Fresh install

			//Check the version number
			if ( version_compare( $last_version, $this->version, '>' ) ) {
				$this->multisiteUpgrade( $last_version );
			} elseif ( version_compare( $last_version, $this->version, '<' ) ) {
				$this->multisiteDowngrade( $last_version );
			}
			if ( $last_version != $this->version ) {
				update_option( $this->slug . '_version', $this->version );
			}

		}

		//Setup default plugin folders
		//$this->mkdir( 'languages' );
		//$this->mkdir( 'css' );
		//$this->mkdir( 'js' );
		self::deleteTransients();
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

	static $reduxInstance;

	public static function getReduxInstance() {
		if ( empty( static::$reduxInstance ) ) {
			$reduxInstances = \ReduxFrameworkInstances::get_all_instances();
			if ( empty( $reduxInstances ) ) {
				return false;
			}
			static::$reduxInstance = array_pop( $reduxInstances );
		}

		return true;
	}

	public function mkdir( $folder ) {
		if ( class_exists( \ReduxFrameworkInstances::class, true ) && static::getReduxInstance() ) {
			if ( ! is_dir( $f = $this->folder( $folder ) ) ) {
				return static::$reduxInstance->filesystem->execute( 'mkdir', $f, array( 'recursive' => true ) );
			}

			return true;
		}

		return false;
	}

	public function copy( $file, $dest ) {
		if ( class_exists( \ReduxFrameworkInstances::class, true ) && static::getReduxInstance() ) {
			if ( $f = file_exists( $this->file( $file ) ) ) {
				static::$reduxInstance->filesystem->execute( 'copy', $f, array( 'destination' => $this->file( $dest ) ) );
			}
		}
	}

	public function put_contents( $file, $content ) {
		if ( class_exists( \ReduxFrameworkInstances::class, true ) && static::getReduxInstance() ) {
			static::$reduxInstance->filesystem->execute( 'put_contents', $this->file( $file ), array( 'content' => $content ) );
		}
	}

	public function get_contents( $file, $content ) {
		if ( class_exists( \ReduxFrameworkInstances::class, true ) && static::getReduxInstance() ) {
			return static::$reduxInstance->filesystem->execute( 'get_contents', $this->file( $file ), array( 'content' => $content ) );
		}

		return false;
	}

	public function delete( $file ) {
		if ( class_exists( \ReduxFrameworkInstances::class, true ) && static::getReduxInstance() ) {
			static::$reduxInstance->filesystem->execute( 'delete', $this->file( $file ), array( 'recursive' => false ) );
		}
	}

	public function deleteFolder( $folder ) {
		if ( class_exists( \ReduxFrameworkInstances::class, true ) && static::getReduxInstance() ) {
			static::$reduxInstance->filesystem->execute( 'delete', $this->folder( $folder ), array( 'recursive' => true ) );
		}
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	abstract public function reduxOptions();

	public function _reduxOptions() {
		$this->reduxOptions();
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