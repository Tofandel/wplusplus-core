<?php

namespace Tofandel\Classes;

use Exception;
use ReflectionClass;

/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
abstract class WP_Plugin {
	use Singleton;

	protected $text_domain;
	protected $slug;
	protected $name;
	protected $file;
	protected $version = false;
	protected $class;

	//private $settings = array();
	//private $option_groups = array();
	protected $option_group = 'general';
	protected $db_tables = array();

	protected $menu_pages;


	/**
	 * Plugin constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {

		$this->class = new ReflectionClass( $this );
		$this->slug  = $this->class->getShortName();
		$this->file  = $this->class->getFileName();

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
		if ( $comment && preg_match( '#plugin[- ]?name[: ]*(\S+)#i', $comment, $matches ) ) {
			$this->name = $matches[1];
		} else {
			$this->name = $this->slug;
		}

		//Read the text domain of the plugin from the comments
		if ( $comment && preg_match( '#text[- ]?domain[: ]*(\S+)#i', $comment, $matches ) ) {
			$this->text_domain = $matches[1];
			define( strtoupper( $this->class->getShortName() ) . '_DN', $this->text_domain );
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
		add_action( 'admin_init', [ $this, 'menusAndSettings' ] );
		add_action( 'admin_init', [ $this, 'checkCompat' ] );
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

	public function optionGroup( $option_group ) {
		$this->option_group = $option_group;
	}


	public function pluginName() {
		return esc_html__( str_replace( array( '-', '_' ), ' ', (string) $this ) );
	}

	/**
	 * @param string $page_title The page title
	 * @param callable $function The function to display the page
	 * @param string $capability The capability required to see the page
	 * @param string $icon_url Can be a Dashicon helper class, a Base64 encoded SVG or 'none' if style is added via CSS
	 * @param null $position The position the menu should appear.
	 * @param array $stylesheets An array of css filenames to be included on that page ('.css' or '.min.css' are not necessary) must be in plugin's css folder
	 * @param array $javascripts An array of js filenames to be included on that page ('.js' or '.min.js' are not necessary) must be in plugin's js folder
	 *
	 * @return string Menu's hook
	 */
	public function addMenuPage( $page_title, $function, $capability = 'manage_options', $icon_url = '', $position = null, array $stylesheets = array(), array $javascripts = array() ) {
		$slug = self::uniqueSlug( $page_title, $this->slug );
		$hook = add_menu_page( $page_title, $page_title, $capability, $slug, $function, $icon_url, $position );
		//global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;
		if ( $hook ) {
			//$this->menu_pages[ $slug ] = array( 'hook' => $hook, 'css' => $stylesheets, 'js' => $javascripts );
			if ( ! empty( $stylesheets ) ) {
				add_action( 'load-' . $hook, [ $this, 'enqueueMenuStyles' ] );
			}

			if ( ! empty( $javascripts ) ) {
				add_action( 'load-' . $hook, [ $this, 'enqueueMenuScripts' ] );
			}
		}

		return $hook;
	}

	/**
	 * Security to make sure the generated slug is unique, concatenating with plugin name and checking against other declared slugs
	 *
	 * @param string $string The string to slugifiy
	 * @param string|bool $plugin_name The plugin name to concatenate
	 *
	 * @return string
	 */
	protected static function uniqueSlug( $string, $plugin_name = false ) {
		static $slugs = array();

		$string = ( $plugin_name ? wpp_slugify( $plugin_name ) . '-' : '' ) . wpp_slugify( $string );
		if ( ! in_array( $string, $slugs ) ) {
			$slugs[] = $string;

			return $string;
		}
		/** @noinspection PhpStatementHasEmptyBodyInspection */
		for ( $i = 2; in_array( $string . $i, $slugs ); $i ++ ) {
			;
		}
		$slugs[] = $string . $i;

		return $string . $i;
	}

	/**
	 * @param string $parent_slug
	 * @param string $page_title
	 * @param callable $function
	 * @param string $capability
	 * @param array $stylesheets
	 * @param array $javascripts
	 *
	 * @return string Menu's hook
	 */
	public function addSubmenuPage( $parent_slug, $page_title, $function, $capability = 'manage_options', array $stylesheets = array(), array $javascripts = array() ) {
		$slug = self::uniqueSlug( $page_title, $this->slug );
		$hook = add_submenu_page( $parent_slug, $page_title, $page_title, $capability, $slug, $function );

		if ( $hook ) {
			$this->menu_pages[ $slug ] = array( 'hook' => $hook, 'css' => $stylesheets, 'js' => $javascripts );

			if ( ! empty( $stylesheets ) ) {
				add_action( 'load-' . $hook, [ $this, 'enqueueMenuStyles' ] );
			}

			if ( ! empty( $javascripts ) ) {
				add_action( 'load-' . $hook, [ $this, 'enqueueMenuScripts' ] );
			}
		}

		return $hook;
	}

	public function enqueueMenuScripts() {
		global $plugin_page;
		foreach ( $this->menu_pages[ $plugin_page ]['js'] as $js ) {
			if ( ! wp_script_is( $js, 'registered' ) && $f = $this->searchFile( $js, 'js', true, 'js' ) ) {
				wp_register_script( $js, $f, array(
					'jquery',
					'jquery-ui-core',
				), $this->version );
			}
			wp_enqueue_script( $js );
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

	public function enqueueMenuStyles() {
		global $plugin_page;
		foreach ( $this->menu_pages[ $plugin_page ]['css'] as $css ) {
			if ( ! wp_style_is( $css, 'registered' ) && $f = $this->searchFile( $css, 'css', true, 'js' ) ) {
				wp_register_style( $css, $f, $this->version );
			}
			wp_enqueue_style( $css );
		}
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
		load_plugin_textdomain( $this->textDomain(), false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Returns the text domain for internationalisation
	 * @return string
	 */
	public function textDomain() {
		return $this->text_domain;
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

	public static function checkCompatibility() {
		if ( version_compare( phpversion(), '7.0', '<' ) ) {
			return false;
		}

		return true;
	}

	public function disabled_notice() {
		echo '<strong>' . sprintf( esc_html__( '%s requires PHP 7.0 or higher!', self::textDomain() ), $this->name ) . '</strong>';
	}

	/**
	 * Called function on plugin activation
	 */
	public function activate() {
		if ( ! self::checkCompatibility() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( '%s requires PHP 7.0 or higher!', self::textDomain() ), $this->name ) );
		}

		if ( ! add_option( $this->slug . '_version', $this->version ) ) {
			//An old version existed before
			$last_version = get_option( $this->slug . '_version' );
			//Fresh install
			foreach ( $this->db_tables as $table ) {
				/**
				 * @var WP_DB_Table $table
				 */
				$table->upgrade();
			}
			//Check the version number
			if ( version_compare( $last_version, $this->version, '>' ) ) {
				$this->multisiteUpgrade( $last_version );
			} elseif ( version_compare( $last_version, $this->version, '<' ) ) {
				$this->multisiteDowngrade( $last_version );
			}
			if ( $last_version != $this->version ) {
				update_option( $this->slug . '_version', $this->version );
			}
		} else {
			//Fresh install
			foreach ( $this->db_tables as $table ) {
				/**
				 * @var WP_DB_Table $table
				 */
				$table->register();
			}
		}

		//Setup default plugin folders
		$this->mkdir( 'languages' );
		$this->mkdir( 'css' );
		$this->mkdir( 'js' );
		add_action( 'init', 'flush_rewrite_rules' );
	}

	protected function multisiteUpgrade( $last_version ) {
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
	 * Rarely supported but still including this function
	 */
	protected function downgrade( $last_version ) {
	}

	public function mkdir( $folder ) {
		if ( ! is_dir( $this->folder( $folder ) ) ) {
			mkdir( $this->folder( $folder ), 0755 );
		}
	}

	/**
	 * Add menus, sub-menus and settings page in this function
	 */
	abstract public function menusAndSettings();

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