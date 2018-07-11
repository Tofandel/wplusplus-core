<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 22/05/2018
 * Time: 16:26
 */

namespace Tofandel\Core\Interfaces;


/**
 * Class WP_Plugin
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
interface WP_Shortcode {
	/**
	 * The initialisation function
	 */
	public static function __init__();

	/**
	 * The shortcode logic function
	 *
	 * @param array $atts
	 * @param string $content
	 * @param string $name
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $name );
}