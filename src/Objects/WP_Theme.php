<?php
/**
 * Adrien Foulon <tofandel@tukan.hu>
 * Copyright © 2018 - All Rights Reserved
 */

namespace Tofandel\Core\Objects;

use Exception;
use ReflectionClass;
use Tofandel\Core\Traits\Singleton;

/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
abstract class WP_Theme extends WP_Plugin implements \Tofandel\Core\Interfaces\WP_Theme {
	use Singleton;

	/**
	 * @var \WP_Theme
	 */
	protected $theme = null;
	/**
	 * @var \WP_Theme
	 */
	protected $parent = false;
	/**
	 * @var \WP_Theme
	 */
	protected $child = false;

	/** @noinspection PhpMissingParentConstructorInspection */

	/**
	 * Plugin constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		$this->class = new ReflectionClass( $this );
		$this->file  = $this->class->getFileName();
		$this->slug  = basename( dirname( $this->file ) );

		$this->theme = wp_get_theme( $this->slug );

		// Get parent theme info if this theme is a child theme
		if ( $this->theme->parent() ) {
			$this->parent = wp_get_theme( $this->theme->parent() );
			$this->file   = $this->theme->get_stylesheet();
		} else {
			$this->child = wp_get_theme( $this->theme->get_stylesheet() );
			$this->file  = $this->theme->get_template();
		}

		$this->version     = $this->theme->version;
		$this->name        = $this->theme->name;
		$this->text_domain = $this->theme->get( 'TextDomain' );

		$this->initUpdateChecker();
		$this->setup();
	}

	/**
	 * Setup default plugin actions
	 */
	protected function setup() {
		add_action( 'after_setup_theme', array( $this, 'loadTextdomain' ) );
		add_action( 'after_switch_theme', array( $this, 'activate' ) );
		add_action( 'switch_theme', array( $this, 'deactivate' ) );
		$this->definitions();
		$this->actionsAndFilters();
		add_action( 'admin_init', [ $this, 'menusAndSettings' ] );
	}

	public function webPath( $folder = '' ) {
		return ( $this->parent ? get_stylesheet_directory_uri() : get_template_directory_uri() ) . "$folder";
	}

	/**
	 * Prepare theme internationalisation
	 */
	public function loadTextdomain() {
		$this->theme->load_textdomain();
		$this->parent ? load_child_theme_textdomain( $this->getTextDomain(), $this->folder( '/languages/' ) ) :
			load_theme_textdomain( $this->getTextDomain(), $this->folder( '/languages/' ) );
	}

	/**
	 * @param string $folder
	 *
	 * @return string Path to the theme's folder
	 */
	public function folder( $folder = '' ) {
		return ( $this->parent ? get_stylesheet_directory() : get_template_directory() ) . "$folder";
	}
}