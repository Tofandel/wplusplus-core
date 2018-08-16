<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

/**
 * Class WP_VC_Shortcode
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
trait WP_VC_Shortcode {
	use WP_Shortcode;

	/**
	 * @see https://kb.wpbakery.com/docs/inner-api/vc_map/
	 * @var array(
	 *          name                String          Name of your shortcode for human reading inside element list
	 *          base*               String          Shortcode tag. For [my_shortcode] shortcode base is my_shortcode
	 *          description         String          Short description of your element, it will be visible in “Add element” window
	 *          class               String          CSS class which will be added to the shortcode’s content element in the page edit screen in WPBakery Page Builder backend edit mode
	 *          weight              Integer         Content elements with greater weight will be rendered first in “Content Elements” grid (Available from WPBakery Page Builder 3.7 version)
	 *          category            String          Category which best suites to describe the functionality of this shortcode. Default categories: Content, Social, Structure. You can add your own category, simply enter new category title here
	 *          group               String          Group your params in groups, they will be divided in tabs in the edit element window (Available from WPBakery Page Builder 4.1)
	 *          icon                String          URL or CSS class with icon image.
	 *          admin_enqueue_js    String|Array    Absolute url to javascript file, this js will be loaded in the js_composer edit mode (it allows you to add more functionality to your shortcode in js_composer edit mode)
	 *          admin_enqueue_css   String|Array    Absolute url to css file if you need to add custom css for element block in js_composer constructor mode
	 *          front_enqueue_js    String|Array    Absolute url to javascript file (useful for storing your custom backbone.js views), this js will be loaded in the js_composer frontend edit mode (it allows you to add more functionality to your shortcode in js_composer frontend edit mode). (Available from WPBakery Page Builder 4.2.2)
	 *          front_enqueue_css   String|Array    Absolute url to css file if you need to load custom css file in the frontend editing mode. (Available from WPBakery Page Builder 4.2.2)
	 *          custom_markup       String          Custom html markup for representing shortcode in visual composer editor
	 *          js_view             String          Set custom backbone.js view controller for this content element
	 *          html_template       String          Path to shortcode template. This is useful if you want to reassign path of existing content elements through your plugin. Another way to change html markup
	 *          deprecated          String          Enter version number from which content element will be deprecated. It will be moved to the “Deprecated” tab in “Add element” window and notification message will be shown on elements edit page. To hide element from “Add element” all together use ‘content_element’=>false (Available from WPBakery Page Builder 4.5)
	 *          content_element     Boolean         If set to false, content element will be hidden from “Add element” window. It is handy to use this param in pair with ‘deprecated’ param (Available from WPBakery Page Builder 4.5)
	 *          params*             Array           List of shortcode attributes. Array which holds your shortcode params, these params will be editable in shortcode settings page
	 *      )
	 * Elements marked with * are already defined and thus not required but can be overridden
	 */
	protected static $vc_params = array(
		'name'                      => '',
		'description'               => '',
		'weight'                    => 1,
		'category'                  => '',
		'group'                     => '',
		'icon'                      => '',
		'admin_enqueue_js'          => '',
		'admin_enqueue_css'         => '',
		'front_enqueue_js'          => '',
		'front_enqueue_css'         => '',
		'custom_markup'             => '',
		'js_view'                   => '',
		'html_template'             => '',
		'controls'                  => 'full',
		'allowed_container_element' => true,
		'content_element'           => true,
		'is_container'              => true,
		'params'                    => array()
	);

	public static function initVCParams() {
	}

	private static function _initVCParams() {
		static $init = false;

		if ( $init ) {
			return;
		}
		$init = true;

		static::getName();
		static::initVCParams();
		foreach ( static::$vc_params['params'] as $key => $param ) {
			if ( isset( static::$atts[ $param['param_name'] ] ) ) {
				static::$vc_params['params'][ $key ]['std'] = static::$atts[ $param['param_name'] ];
			}
		}

		static::$vc_params = array_merge( array(
			'base' => static::$_name
		), static::$vc_params );
	}

	public static function __StaticInit() {
		if ( ! static::$reflectionClass->implementsInterface( \Tofandel\Core\Interfaces\WP_VC_Shortcode::class ) ) {
			return;
		}
		if ( empty( static::$atts ) ) {
			static::_initVCParams();
			foreach ( static::$vc_params['params'] as $param ) {
				if ( isset( $param['param_name'] ) ) {
					static::$atts[ $param['param_name'] ] = isset( $param['std'] ) ? $param['std'] : '';
				}
			}
		}
		global $pagenow;


		if ( function_exists( 'vc_map' ) && ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( ! empty( $_REQUEST['action'] ) && wp_doing_ajax() && strpos( $_REQUEST['action'], 'vc_' ) === 0 ) ) ) {
			add_action( 'vc_before_mapping', function () {
				static::_initVCParams();
				vc_map( static::$vc_params );
			} );
		}

		new \Tofandel\Core\Objects\WP_Shortcode( static::getName(), [ static::class, 'shortcode' ], static::$atts );
	}
}