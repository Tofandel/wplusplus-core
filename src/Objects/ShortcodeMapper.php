<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 24/10/2018
 * Time: 15:31
 */

namespace Tofandel\Core\Objects;


abstract class ShortcodeMapper implements \Tofandel\Core\Interfaces\ShortcodeMapper {
	/**
	 * Whether this mapper should be active or not (depending on the active plugins or the request...)
	 *
	 * @return bool
	 */
	abstract public static function shouldMap();

	/**
	 * Handles the mapping logic
	 *
	 * @param ShortcodeDescriptor $info
	 *
	 */
	abstract public static function map( ShortcodeDescriptor $info );

	/**
	 * Handles the parameter mapping logic
	 *
	 * @param ShortcodeParameter $param
	 *
	 * @return mixed
	 */
	//abstract public static function mapParameter( ShortcodeParameter $param );
}