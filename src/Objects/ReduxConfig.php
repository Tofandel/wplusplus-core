<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Objects;

use Redux;

/**
 * Class ReduxConfig
 * A Proxy Class to configure Redux more easily
 *
 * @package Tofandel\Core\Objects
 */
class ReduxConfig implements \Tofandel\Core\Interfaces\ReduxConfig {
	/**
	 * @var string
	 */
	protected $opt_name;
	/**
	 * @var WP_Plugin
	 */
	//protected $plugin;

	/**
	 * ReduxConfig constructor.
	 *
	 * param WP_Plugin $plugin
	 *
	 * @param array $args
	 */
	public function __construct( $opt_name, $args = array() ) {
		$this->opt_name = $opt_name;
		//$this->plugin         = $plugin;
		//$plugin->redux_config = &$this;

		self::loadRedux();

		if ( ! class_exists( Redux::class, true ) ) {
			return;
		}
		self::loadExtensions( $this->opt_name );

		$this->setArgs( $args );
	}

	public static function mu_hide_redux_plugin( $plugins ) {
		if ( in_array( self::$redux_loc, array_keys( $plugins ) ) ) {
			unset( $plugins[ self::$redux_loc ] );
		}

		return $plugins;
	}

	public static function hide_redux_plugin() {
		global $wp_list_table;
		if ( isset( $wp_list_table->items[ self::$redux_loc ] ) ) {
			unset( $wp_list_table->items[ self::$redux_loc ] );
		}
	}

	protected static $redux_loc;

	public static function loadRedux() {
		if ( ! class_exists( Redux::class, false ) ) {
			global $WPlusPlusCore;
			$folder = true ? 'redux-dev' : 'redux-framework';
			if ( file_exists( $f = $WPlusPlusCore->file( "plugins/$folder/redux-framework.php" ) ) ) {
				require_once $f;
			}
		}
	}

	public static function loadExtensions( $opt_name ) {
		global $WPlusPlusCore;
		// All extensions placed within the extensions directory will be auto-loaded for your Redux instance.
		Redux::setExtensions( $opt_name, $WPlusPlusCore->folder( 'plugins/redux-extensions' ) );
	}

	public function setArgs( $args = array() ) {
		if ( ! class_exists( Redux::class ) ) {
			return;
		}
		//Just some defaults, can override
		$def_args = array(
			'opt_name'            => $this->opt_name,
			'show_custom_fonts'   => false,
			'show_options_object' => false,
			'dev_mode'            => false,
			'use_cdn'             => true,
			'display_version'     => false,
			'update_notice'       => false,
			'menu_type'           => 'hidden',
			'menu_title'          => '',
			'allow_sub_menu'      => true,
			'page_priority'       => '39',
			'customizer'          => false,
			'default_mark'        => ' ¤',
			'hints'              => array(
				'icon'          => 'el el-question-sign',
				'icon_position' => 'right',
				'icon_color'    => '#071f49',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'light',
					'shadow'  => '1',
					'rounded' => '1',
					'style'   => 'bootstrap',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'fade',
						'duration' => '400',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'fade',
						'duration' => '400',
						'event'    => 'mouseleave unfocus',
					),
				),
			),
			'output'             => true,
			'output_tag'         => true,
			'settings_api'       => true,
			'cdn_check_time'     => '1440',
			'compiler'           => true,
			'page_permissions'   => 'manage_options',
			'save_defaults'      => true,
			'show_import_export' => true,
			'open_expanded'      => false,
			'database'           => 'options',
			'transient_time'     => '3600',
			'network_sites'      => true,
			'admin_bar'          => false,
		);

		$args = array_merge( $def_args, $args );

		global $WPlusPlusCore;
		$args['share_icons']['tofandel_github']   = array(
			'url'   => 'https://github.com/Tofandel/',
			'title' => __( 'Check me out on GitHub', $WPlusPlusCore->getTextDomain() ),
			'icon'  => 'el el-github'
			//'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
		);
		$args['share_icons']['tofandel_linkedin'] = array(
			'url'   => 'https://www.linkedin.com/in/adrien-foulon/',
			'title' => __( 'Find me on LinkedIn', $WPlusPlusCore->getTextDomain() ),
			'icon'  => 'el el-linkedin'
		);
		Redux::setArgs( $this->opt_name, $args );
	}


	public function setField( $field = array() ) {
		Redux::setField( $this->opt_name, $field );
	}

	public function setHelpTab( $tab = array() ) {
		Redux::setHelpTab( $this->opt_name, $tab );
	}

	public function setHelpSidebar( $content = "" ) {
		Redux::setHelpSidebar( $this->opt_name, $content );
	}

	public function setOption( $key = "", $option = "" ) {
		Redux::setOption( $this->opt_name, $key, $option );
	}

	public function setSections( $sections = array() ) {
		Redux::setSections( $this->opt_name, $sections );
	}

	public function setSection( $section = array() ) {
		Redux::setSection( $this->opt_name, $section );
	}
}