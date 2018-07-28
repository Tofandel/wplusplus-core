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
	public function dirUrl( $folder = '' );

	/**
	 * Prepare theme internationalisation
	 */
	public function loadTextdomain();

}