<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 25/10/2018
 * Time: 14:11
 */

namespace Tofandel\Core\Objects;

abstract class WP_Mapped_Shortcode implements \Tofandel\Core\Interfaces\WP_Mapped_Shortcode, \Tofandel\Core\Interfaces\StaticSubModule {
	use \Tofandel\Core\Traits\WP_Mapped_Shortcode;
}