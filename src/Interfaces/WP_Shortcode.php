<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 22/05/2018
 * Time: 16:26
 */

namespace Tofandel\Core\Interfaces;

/**
 * Interface WP_Shortcode
 * @package Tofandel\Core\Interfaces
 */
interface WP_Shortcode {
	/**
	 * The shortcode initialisation function
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