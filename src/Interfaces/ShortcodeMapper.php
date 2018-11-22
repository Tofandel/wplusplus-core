<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 24/10/2018
 * Time: 15:30
 */

namespace Tofandel\Core\Interfaces;


use Tofandel\Core\Objects\ShortcodeDescriptor;
use Tofandel\Core\Objects\ShortcodeParameter;

interface ShortcodeMapper {
	/**
	 * Whether this mapper should be active or not (depending on the active plugins or the request...)
	 *
	 * @return bool
	 */
	public static function shouldMap();

	/**
	 * Handles the mapping logic
	 *
	 * @param ShortcodeDescriptor $info
	 *
	 */
	public static function map( ShortcodeDescriptor $info );

	/**
	 * Handles the parameter mapping logic
	 *
	 * @param ShortcodeParameter $param
	 *
	 * @return mixed
	 */
	//public static function mapParameter( ShortcodeParameter $param );
}