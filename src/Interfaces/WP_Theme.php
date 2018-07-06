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
interface WP_Theme extends WP_Plugin {
	public function webPath( $folder = '' );

	/**
	 * Prepare theme internationalisation
	 */
	public function loadTextdomain();

	/**
	 * @param string $folder
	 *
	 * @return string Path to the theme's folder
	 */
	public function folder( $folder = '' );
}