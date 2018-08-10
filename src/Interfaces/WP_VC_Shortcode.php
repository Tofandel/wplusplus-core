<?php
/**
 * Copyright (c) 2018. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 10/08/2018
 * Time: 09:10
 */

namespace Tofandel\Core\Interfaces;


/**
 * Class WP_VC_Shortcode
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
interface WP_VC_Shortcode extends WP_Shortcode {
	/**
	 * @throws \ReflectionException
	 */
	public static function getName();

	/**
	 * Init the $vc_params static here
	 */
	public static function initVCParams();

	/**
	 * You can do other things on init here as well
	 */
	public static function __init__();
}