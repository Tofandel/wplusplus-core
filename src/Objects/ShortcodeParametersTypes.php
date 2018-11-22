<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 20/11/2018
 * Time: 17:11
 */

namespace Tofandel\Core\Objects;

abstract class ShortcodeParametersTypes {
	const HIDDEN = 'hidden';
	const WARNING = 'warning';

	const CHOICE = 'choice';
	const IDENTIFIER = 'identifier';
	const BOOL = 'bool';
	const TEXT = 'text';
	const LONGTEXT = 'longtext';
	const NUMBER = 'number';
	const IMAGE = 'image';
	const IMAGES = 'images';
	const COLOR = 'color';
	const RAWHTML = 'rawhtml';
	const LINK = 'link';
	const CSS = 'css';

	const PAGE = 'page';
	const POST = 'post';

	public static function getTypes() {
		static $constCache;

		if (!isset($constCache)) {
			$reflect    = new \ReflectionClass(__CLASS__);
			$constCache = $reflect->getConstants();
		}

		return $constCache;
	}

	public static function isValidType($value, $strict = true) {
		return in_array($value, self::getTypes(), $strict);
	}

}