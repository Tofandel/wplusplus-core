<?php

namespace Tofandel\Traits;

/**
 * Class WP_Shortcode
 * @package Abstracts
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
	static $vc_params = array(
		'name'              => '',
		'description'       => '',
		'weight'            => 1,
		'category'          => '',
		'group'             => '',
		'icon'              => '',
		'admin_enqueue_js'  => '',
		'admin_enqueue_css' => '',
		'front_enqueue_js'  => '',
		'front_enqueue_css' => '',
		'custom_markup'     => '',
		'js_view'           => '',
		'html_template'     => ''
	);

	/**
	 * @throws \ReflectionException
	 */
	public static function init() {
		WP_Shortcode::init();
		self::$vc_params = array_merge( array(
			'base'   => self::$_name,
			'params' => self::$atts
		), self::$vc_params );
		if ( function_exists( 'vc_map' ) ) {
			vc_map( self::$vc_params );
		}
	}

}