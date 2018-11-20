<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 14/11/2018
 * Time: 23:57
 */

abstract class Redux_Descriptor_Types {
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const BOOL = 'bool';
    const SLIDER = 'slider';
    const NUMBER = 'number';
    const RANGE = 'range';
    const OPTIONS = 'array';
    const WP_DATA = 'wp_data';
    const RADIO = 'radio';

    //Todo add more field types for the builder

    public static function getTypes() {
        static $constCache;

        if (!isset($constCache)) {
            $reflect    = new ReflectionClass(__CLASS__);
            $constCache = $reflect->getConstants();
        }

        return $constCache;
    }


    public static function isValidType($value, $strict = true) {
        return in_array($value, self::getTypes(), $strict);
    }

}