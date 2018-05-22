<?php
/**
 * Adrien Foulon <tofandel@tukan.hu>
 * Copyright © 2018 - All Rights Reserved
 */

namespace Tofandel\Classes;

use Exception;
use ReflectionClass;
use Tofandel\Traits\Singleton;

/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
abstract class WP_Theme extends WP_Plugin implements \Tofandel\Interfaces\WP_Theme {
	use Singleton;

	protected $parent = false;

	/** @noinspection PhpMissingParentConstructorInspection */

	/**
	 * Plugin constructor.
	 *
	 * @param $parent
	 *
	 * @throws Exception
	 */
	public function __construct( $parent = false ) {
		$this->parent = $parent;

		$this->class = new ReflectionClass( $this );
		$this->slug  = $this->class->getShortName();
		$this->file  = $parent ? get_stylesheet() : get_template();

		$comment = '';

		$fh = fopen( $this->file, 'rb' );

		for ( $i = 0; $i < 20; $i ++ ) {
			$comment .= fgets( $fh );
		}
		fclose( $fh );

		if ( preg_match( '#^/\*!?(.*?)\*/#', $comment, $match ) ) {
			$comment = $match[1];
		} else {
			$comment = false;
		}

		$this->extractFromComment( $comment );

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
		$this->parent ? load_child_theme_textdomain( $this->textDomain(), $this->folder( '/languages/' ) ) :
			load_theme_textdomain( $this->textDomain(), $this->folder( '/languages/' ) );
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