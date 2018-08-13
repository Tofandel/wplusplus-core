<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Tofandel\Core\Objects;

use ReduxFrameworkPlugin;
use ReflectionClass;
use Tofandel\Core\Interfaces\SubModule;
use Tofandel\Core\Modules\LicenceManager;
use Tofandel\Core\Modules\ReduxFramework;
use Tofandel\Core\Traits\Singleton;


/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 *
 * TODO Plugin tracking via cron
 * TODO Licence manager premium updater
 * TODO Premium support
 */
abstract class WP_Plugin implements \Tofandel\Core\Interfaces\WP_Plugin {
	use Singleton;

	protected $required_php_version = '5.5';

	protected $text_domain;
	protected $slug;
	protected $name;
	protected $file;
	protected $version = false;
	protected $class;
	protected $redux_opt_name;

	protected $is_muplugin = false;

	protected $repo_url = '';
	protected $download_url = '';
	protected $buy_url = '';
	protected $is_licensed = false;
	protected $product_id = '';
	protected $no_redux = false;

	/**
	 * @var SubModule[]
	 */
	private $modules = array();

	/**
	 * @var string[]
	 */
	private $shortcodes = array();

	public function getModule( $name ) {
		return isset( $this->modules[ $name ] ) ? $this->modules[ $name ] : null;
	}

	public function getName() {
		return $this->name;
	}

	public function getProductID() {
		return ! empty( $this->product_id ) ? $this->product_id : $this->slug;
	}

	public function getBuyUrl() {
		return trailingslashit( $this->buy_url ?: $this->download_url );
	}

	public function getDownloadUrl() {
		return trailingslashit( $this->download_url );
	}

	public function getSlug() {
		return $this->slug;
	}

	/**
	 * @param string[] $shortcodes array of class names
	 */
	public function setShortcodes( array $shortcodes ) {
		$this->shortcodes = $shortcodes;
	}

	/**
	 * @param string $shortcode class name
	 */
	public function setShortcode( $shortcode ) {
		$this->shortcodes[ $shortcode ] = $shortcode;
	}

	public function initShortcodes() {
		foreach ( $this->shortcodes as $shortcode ) {
			call_user_func( [ $shortcode, '__StaticInit' ] );
		}
	}

	/**
	 * @param SubModule $submodule
	 */
	public function setSubModule( $submodule ) {
		if ( $submodule instanceof SubModule ) {
			try {
				$reflection                              = new ReflectionClass( $submodule );
				$this->modules[ $reflection->getName() ] = $submodule;
			} catch ( \ReflectionException $exception ) {
				error_log( $exception->getMessage() );
			}
		}
	}

	/**
	 * @param SubModule[] $submodules
	 */
	public function setSubModules( array $submodules ) {
		foreach ( $submodules as $module ) {
			$this->setSubModule( $module );
		}
	}

	public function getLicenceEmail() {
		return $this->getOption( 'licence_email', '' );
	}

	public function getLicenceKey() {
		return $this->getOption( 'licence_key', '' );
	}

	public function isLicensed() {
		/**
		 * @var LicenceManager $LicenceManager
		 */
		$LicenceManager = $this->getModule( LicenceManager::class );
		if ( $LicenceManager ) {
			return $LicenceManager->checkLicence();
		}

		return false;
	}

	public function getRepoUrl() {
		return trailingslashit( $this->repo_url );
	}

	public function initUpdateChecker() {
		if ( ! empty( $this->getRepoUrl() ) && ( ( is_admin() && ! wp_doing_ajax() )
		                                         || ( wp_doing_ajax() && $_REQUEST['action'] == 'update-plugin' ) ) ) {
			\Puc_v4_Factory::buildUpdateChecker(
				$this->getRepoUrl(),
				$this->file, //Full path to the main plugin file or functions.php.
				$this->slug
			);
		} elseif ( ! empty( $this->getDownloadUrl() )
		           && ( ( is_admin() && ! wp_doing_ajax() ) ||
		                ( wp_doing_ajax() && $_REQUEST['action'] == 'update-plugin' ) ) && $this->is_licensed ) {
			/**
			 * @var LicenceManager $LicenceManager
			 */
			$LicenceManager = $this->getModule( LicenceManager::class );
			//if ( $LicenceManager && $data = $LicenceManager->updateRequest() ) {
				//TODO
			//}
		}
	}

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
	 * @throws \ReflectionException
	 */
	public function __construct() {
		$this->init();

		$this->initUpdateChecker();

		$this->setup();
	}

	/**
	 * @throws \ReflectionException
	 */
	private function init() {
		$this->class = new ReflectionClass( $this );
		$this->slug  = $this->class->getShortName();
		$this->file  = $this->class->getFileName();

		if ( strpos( $this->file, 'mu-plugin' ) ) {
			$this->is_muplugin = true;
		}

		$comment = $this->class->getDocComment();

		$this->extractFromComment( $comment );

		if ( ! isset( $this->redux_opt_name ) ) {
			$this->redux_opt_name = strtolower( $this->class->getShortName() ) . '_options';
		}

		//We define a global with the name of the class
		if ( ! isset( $GLOBALS[ $this->class->getShortName() ] ) ) {
			$GLOBALS[ $this->class->getShortName() ] = $this;
		}

		if ( $this->is_licensed ) {
			$this->setSubModule( new LicenceManager( $this ) );
		}
	}


	public function getReduxOptName() {
		return $this->redux_opt_name;
	}

	protected function extractFromComment( $comment ) {

		//Read the version of the plugin from the comments
		if ( empty( $this->version ) && $comment && preg_match( '#version[:\s]*([0-9\.]+)#i', $comment, $matches ) ) {
			$this->version = trim( $matches[1] );
		} elseif ( empty( $this->version ) ) {
			$this->version = '1.0';
		}

		//Read the name of the plugin from the comments
		if ( empty( $this->name ) && $comment && preg_match( '#(?:plugin|theme)[-\s]?name[:\s]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->name = trim( $matches[1] );
		} elseif ( empty( $this->name ) ) {
			$this->name = $this->slug;
		}

		//Read the text domain of the plugin from the comments
		if ( empty( $this->text_domain ) && $comment && preg_match( '#text[-\s]?domain[:\s]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->text_domain = trim( $matches[1] );
		} elseif ( empty( $this->text_domain ) ) {
			$this->text_domain = $this->slug;
		}

		/** @deprecated Overwrite the variable instead */
		if ( empty( $this->download_url ) && $comment && preg_match( '#download[-\s]?url[:\s]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->download_url = trim( $matches[1] );
		}

		if ( empty( $this->buy_url ) && $comment && preg_match( '#donate[-\s]?url[:\s]*([^\r\n]*)#i', $comment, $matches ) ) {
			$this->buy_url = trim( $matches[1] );
		} elseif ( empty( $this->buy_url ) ) {
			$this->buy_url = $this->download_url;
		}

		if ( $comment && preg_match( '#requires[-\s]?php[:\s]*([0-9\.]+)#i', $comment, $matches ) ) {
			$v                          = trim( $matches[1] );
			$this->required_php_version = version_compare( $this->required_php_version, $v, '<' ) ? $v : $this->required_php_version;
		}
	}

	/**
	 * Setup default plugin actions
	 */
	protected function setup() {
		add_action( 'plugins_loaded', array( $this, 'loadTextdomain' ) );
		register_activation_hook( $this->file, array( $this, 'activated' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivated' ) );
		register_uninstall_hook( $this->file, static::class . '::uninstallHook' );
		$this->definitions();
		$this->actionsAndFilters();
		foreach ( $this->modules as $module ) {
			$module->actionsAndFilters();
		}

		add_action( 'init', [ $this, 'initShortcodes' ], 1 );

		if ( ! $this->no_redux ) {
			if ( is_admin() && ! wp_doing_ajax() ||
			     ( wp_doing_ajax() && isset ( $_REQUEST['action'] ) &&
			       ( $_REQUEST['action'] == $this->redux_opt_name . '_ajax_save' || strpos( $_REQUEST['action'], 'redux' ) === 0 ) ) ) {
				$this->_reduxConfig();
				do_action( 'redux_loaded' );
				add_action( 'admin_init', [ $this, 'checkCompat' ] );
				add_action( 'init', array( $this, 'removeDemoModeLink' ) );
			} else {
				$GLOBALS[ $this->redux_opt_name ] = get_option( $this->redux_opt_name, array() );
				do_action( 'redux_not_loaded' );
				add_action( 'wp_enqueue_scripts', [ $this, 'enqueueReduxFonts' ], 999 );
			}
		}

		if ( ! add_option( $this->slug . '_version', $this->version ) ) {
			//An old version existed before
			$last_version = get_option( $this->slug . '_version' );
			//Fresh install

			//Check the version number
			if ( version_compare( $last_version, $this->version, '<' ) ) {
				$this->activated();
				$this->multisiteUpgrade( $last_version );
			} elseif ( version_compare( $last_version, $this->version, '>' ) ) {
				$this->activated();
				$this->multisiteDowngrade( $last_version );
			}
			if ( $last_version != $this->version ) {
				update_option( $this->slug . '_version', $this->version );
			}
		}
	}


	public function removeDemoModeLink() {
		if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
			remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_metalinks' ), null );
			remove_action( 'admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );
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
	 * @throws \ReflectionException
	 */
	public static function uninstallHook() {
		$ref = new ReflectionClass( static::class );
		delete_option( $ref->getShortName() . '_version' );
		/**
		 * @var self $plugin
		 */
		$plugin = $ref->newInstanceWithoutConstructor();
		$plugin->init();
		$plugin->uninstall();
		delete_option( $plugin->getReduxOptName() );
	}

	/**
	 * Called function if a plugin is uninstalled
	 * @throws \ReflectionException
	 */
	abstract protected function uninstall();

	/**
	 * Magic method that returns the plugin text domain if trying to convert the plugin object to a string
	 * @return string
	 */
	public function __toString() {
		return $this->getTextDomain();
	}


	//public function pluginName() {
	//	return esc_html__( str_replace( array( '-', '_' ), ' ', (string) $this ) );
	//}

	public static function deleteTransients() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wpp_file_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wpp_file_' ) . '%'
		) );
	}

	public function getOption( $option = null, $default = false ) {
		return self::getReduxOption( $this->redux_opt_name, $option, $default );
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
			if ( isset( $GLOBALS[ $opt_name ] ) ) {
				$options[ $opt_name ] = $GLOBALS[ $opt_name ];
			}
		}

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
			return isset( $options[ $opt_name ][ $option ] ) ? $options[ $opt_name ][ $option ] : $default;
		} else {
			return isset( $options[ $opt_name ] ) ? $options[ $opt_name ] : $default;
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

		if ( ! WP_DEBUG && $cache && $f = get_transient( 'wpp_file_' . $this->getSlug() . '_' . $type . '_' . $name ) ) {
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
		if ( @strrpos( $string, $ext, $ext_len ) === 0 ) {
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

		return trailingslashit( trailingslashit( dirname( $this->file ) ) . ltrim( "$folder", '/' ) );
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

		return trailingslashit( dirname( $this->file ) ) . ltrim( "$file", '/' );
	}

	/**
	 * @param string $js Filename (optional extension)
	 * @param array $require
	 * @param bool $localize
	 * @param bool $in_footer
	 * @param string $async_defer
	 *
	 * @return bool|string Handle name if registered false otherwise
	 */
	public function addScript( $js, $require = array(), $localize = false, $in_footer = false, $async_defer = 'async' ) {
		if ( ! did_action( 'init' ) ) {
			add_action( 'init', function () use ( $js, $require, $localize, $in_footer ) {
				$this->addScript( $js, $require, $localize, $in_footer );
			} );

			return false;
		}
		$name = $this->registerScript( $js, $require, $localize, $in_footer, $async_defer );

		if ( $name ) {
			wp_enqueue_script( $name );
		}

		return $name;
	}

	public function getHandleName( $script ) {
		return $this->getSlug() . basename( $script );
	}

	/**
	 * @param $js
	 * @param array $require
	 * @param bool $localize
	 * @param bool $in_footer
	 * @param string $async_defer
	 *
	 * @return bool|string Handle name if registered false otherwise
	 */
	public function registerScript( $js, $require = array(), $localize = false, $in_footer = false, $async_defer = 'async' ) {
		if ( ! did_action( 'init' ) ) {
			add_action( 'init', function () use ( $js, $require, $localize, $in_footer ) {
				$this->registerScript( $js, $require, $localize, $in_footer );
			} );

			return false;
		}
		$name = $this->getHandleName( $js );
		if ( ! wp_script_is( $name, 'registered' ) ) {
			if ( $file = $this->searchFile( $js, 'js', true, 'js' ) ) {
				wp_register_script( $name, $file, $require, $this->version, $in_footer );
			}
		}
		if ( wp_script_is( $name, 'registered' ) ) {
			if ( ! empty( $async_defer ) ) {
				$this->addAsyncDeferAttribute( $name, $async_defer );
			}
			if ( ! empty( $localize ) ) {
				//TODO make the localize work if called multiple times TOP PRIORITY
				wp_localize_script( $name, str_replace( '-', '_', $this->slug ), array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					$name     => $localize
				) );
				//wp_localize_script( $name, str_replace( '-', '_', $this->slug . '.' . str_replace( '.', '_', $name ) ), array_merge( array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ), $localize ) );
			}

			return $name;
		}

		return false;
	}


	public function addAsyncDeferAttribute( $handle, $async_defer = 'async' ) {
		add_filter( 'script_loader_tag', function ( $tag, $hndl ) use ( $handle, $async_defer ) {
			if ( $hndl !== $handle ) {
				return $tag;
			}

			return str_replace( ' src', " $async_defer src", $tag );
		}, 10, 2 );
	}

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return bool|string Handle name if registered false otherwise
	 */
	public function addStyle( $css, $media = 'all' ) {
		if ( ! did_action( 'init' ) ) {
			add_action( 'init', function () use ( $css, $media ) {
				$this->addStyle( $css, $media );
			} );

			return false;
		}
		$name = $this->registerStyle( $css, $media );

		if ( $name ) {
			wp_enqueue_style( $name );
		}

		return $name;
	}

	/**
	 * @param string $css Filename (extension is optional)
	 * @param string $media
	 *
	 * @return bool|string Handle name if registered false otherwise
	 */
	public function registerStyle( $css, $media = 'all' ) {
		if ( ! did_action( 'init' ) ) {
			add_action( 'init', function () use ( $css, $media ) {
				$this->registerStyle( $css, $media );
			} );

			return false;
		}
		$name = $this->getHandleName( $css);
		if ( ! wp_style_is( $name, 'registered' ) ) {
			if ( $file = $this->searchFile( $css, 'css', true, 'css' ) ) {
				wp_register_style( $name, $file, array(), $this->version, $media );
			}
		}

		if ( ! wp_style_is( $name, 'registered' ) ) {
			return false;
		}

		return $name;
	}

	public function dirUrl( $dir = '' ) {
		return plugin_dir_url( $this->file ) . "$dir";
	}

	public function fileUrl( $folder = '' ) {
		return plugin_dir_url( $this->file ) . "$folder";
	}

	/**
	 * Prepare plugin internationalisation
	 */
	public function loadTextdomain() {
		call_user_func( 'load_' . ( $this->is_muplugin ? 'mu' : '' ) . 'plugin_textdomain', $this->text_domain, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	public function checkCompat() {
		if ( ! $this->checkCompatibility() ) {
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
	public function checkCompatibility() {
		if ( version_compare( phpversion(), $this->required_php_version, '<' ) ) {
			return false;
		}

		return true;
	}

	public function disabled_notice() {
		echo '<strong>' . sprintf( esc_html__( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', $this->text_domain ), $this->name, $this->required_php_version, PHP_VERSION ) . '</strong>';
	}

	/**
	 * Called function on plugin activation
	 */
	public function activated() {
		if ( ! $this->checkCompatibility() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( '%1$s requires PHP %2$s or higher! (Current version is %3$s)', $this->text_domain ), $this->name, $this->required_php_version, PHP_VERSION ) );
		}

		foreach ( $this->modules as $module ) {
			$module->activated();
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
			$this->_upgrade( $last_version );
		} else {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->_upgrade( $last_version );
				restore_current_blog();
			}
		}
	}

	private function _upgrade( $last_version ) {
		foreach ( $this->modules as $module ) {
			$module->upgrade( $last_version );
		}
		$this->upgrade( $last_version );
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

	/**
	 * @param $folder
	 *
	 * @return bool
	 */
	public function mkdir( $folder ) {
		try {
			return WP_Filesystem::__StaticInit()->mkdir( $this->folder( $folder ) );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 * @param $dest
	 *
	 * @param bool $overwrite
	 *
	 * @return bool
	 */
	public function copy( $file, $dest, $overwrite = true ) {
		try {
			return WP_Filesystem::__StaticInit()->copy( $this->file( $file ), $this->file( $dest ), $overwrite );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 * @param $content
	 *
	 * @return bool
	 */
	public function put_contents( $file, $content ) {
		try {
			return WP_Filesystem::__StaticInit()->putContents( $this->file( $file ), $content );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 *
	 * @return bool|mixed|string
	 */
	public function get_contents( $file ) {
		try {
			return WP_Filesystem::__StaticInit()->getContents( $this->file( $file ) );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $file
	 *
	 * @return bool
	 */
	public function delete_file( $file ) {
		try {
			return WP_Filesystem::__StaticInit()->deleteFile( $this->file( $file ) );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @param $folder
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	public function delete_dir( $folder, $recursive = true ) {
		try {
			return WP_Filesystem::__StaticInit()->deleteDir( $this->folder( $folder ), $recursive );
		} catch ( \ReflectionException $e ) {
			return false;
		}
	}

	/**
	 * @var ReduxConfig
	 */
	public $redux_config;

	/**
	 * @param ReduxFramework $framework
	 *
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	public function reduxInit( ReduxFramework $framework ) {
	}

	protected function _reduxConfig() {
		$module = new ReduxFramework( $this );
		$this->setSubModule( $module );
		$this->reduxInit( $module );
		do_action( 'wpp_redux_' . $this->redux_opt_name . '_config', $module );
	}

	/**
	 * Called function on plugin deactivation
	 * Options and plugin data should only be removed in the uninstall function
	 */
	public function deactivated() {
		flush_rewrite_rules();

		foreach ( $this->modules as $module ) {
			$module->deactivated();
		}
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

	/** To enqueue redux fonts since we disable it on front for performance */

	/**
	 * makeGoogleWebfontLink Function.
	 * Creates the google fonts link.
	 *
	 * @since ReduxFramework 3.0.0
	 */
	private function makeGoogleWebfontLink( $fonts ) {
		$link    = "";
		$subsets = array();

		foreach ( $fonts as $family => $font ) {
			if ( ! empty( $link ) ) {
				$link .= "%7C"; // Append a new font to the string
			}
			$link .= $family;

			if ( ! empty( $font['font-style'] ) || ! empty( $font['all-styles'] ) ) {
				$link .= ':';
				if ( ! empty( $font['all-styles'] ) ) {
					$link .= implode( ',', $font['all-styles'] );
				} else if ( ! empty( $font['font-style'] ) ) {
					$link .= implode( ',', $font['font-style'] );
				}
			}

			if ( ! empty( $font['subset'] ) ) {
				foreach ( $font['subset'] as $subset ) {
					if ( ! in_array( $subset, $subsets ) ) {
						array_push( $subsets, $subset );
					}
				}
			}
		}

		if ( ! empty( $subsets ) ) {
			$link .= "&subset=" . implode( ',', $subsets );
		}


		return '//fonts.googleapis.com/css?family=' . str_replace( '|', '%7C', $link );
	}

	/**
	 * makeGoogleWebfontString Function.
	 * Creates the google fonts link.
	 *
	 * @since ReduxFramework 3.1.8
	 */
	private function makeGoogleWebfontString( $fonts ) {
		$link    = "";
		$subsets = array();

		foreach ( $fonts as $family => $font ) {
			if ( ! empty( $link ) ) {
				$link .= "', '"; // Append a new font to the string
			}
			$link .= $family;

			if ( ! empty( $font['font-style'] ) || ! empty( $font['all-styles'] ) ) {
				$link .= ':';
				if ( ! empty( $font['all-styles'] ) ) {
					$link .= implode( ',', $font['all-styles'] );
				} else if ( ! empty( $font['font-style'] ) ) {
					$link .= implode( ',', $font['font-style'] );
				}
			}

			if ( ! empty( $font['subset'] ) ) {
				foreach ( $font['subset'] as $subset ) {
					if ( ! in_array( $subset, $subsets ) ) {
						array_push( $subsets, $subset );
					}
				}
			}
		}

		if ( ! empty( $subsets ) ) {
			$link .= "&subset=" . implode( ',', $subsets );
		}

		return $link;
	}

	const async_typography = false;
	const disable_google_fonts_link = false;

	private function getFonts() {
		$fonts     = array();
		$opt_fonts = array_filter( $GLOBALS[ $this->redux_opt_name ], function ( $key ) {
			return strpos( $key, 'font-' ) === 0;
		}, ARRAY_FILTER_USE_KEY );
		foreach ( $opt_fonts as $key => $value ) {
			if ( (bool) $value['google'] == true ) {
				$value['font-family'] = str_replace( ' ', '+', $value['font-family'] );
				if ( ! isset( $fonts[ $value['font-family'] ] ) ) {
					$fonts[ $value['font-family'] ] = array(
						'font-style' => ! empty( $value['font-style'] ) ? array( $value['font-style'] ) : array(),
						'subset'     => ! empty( $value['subsets'] ) ? array( $value['subsets'] ) : array()
					);
				} else {
					if ( ! empty( $value['font-style'] ) ) {
						$fonts[ $value['font-family'] ]['font-style'][] = $value['font-style'];
					}
					if ( ! empty( $value['subsets'] ) ) {
						$fonts[ $value['font-family'] ]['subset'][] = $value['subsets'];
					}
				}
			}
		}

		return $fonts;
	}

	public function enqueueReduxFonts() {
		$fonts = $this->getFonts();
		if ( empty( $fonts ) ) {
			return;
		}
		if ( static::async_typography ) {
			$families = array();
			foreach ( $fonts as $key => $value ) {
				$families[] = $key;
			}
			$fonts = $this->makeGoogleWebfontString( $fonts );
			echo <<<HTML
<script>
if (typeof WebFontConfig === "undefined") {var WebFontConfig = {};}
WebFontConfig['google'] = {families: ['{$fonts}']};
(function () {
	var wf = document.createElement('script');
	wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.5.3/webfont.js';
	wf.type = 'text/javascript';
	wf.async = true;
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(wf, s);
})();
</script>
HTML;
		} elseif ( ! static::disable_google_fonts_link ) {
			wp_enqueue_style( 'redux-google-fonts-' . $this->redux_opt_name, $this->makeGoogleWebfontLink( $fonts ), '', $this->version );
		}
	}
}