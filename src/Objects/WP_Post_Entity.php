<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 25/10/2018
 * Time: 14:11
 */

namespace Tofandel\Core\Objects;

abstract class WP_Post_Entity implements \Tofandel\Core\Interfaces\WP_Post_Entity, \Tofandel\Core\Interfaces\StaticSubModule {
	use \Tofandel\Core\Traits\WP_Post_Entity;
}