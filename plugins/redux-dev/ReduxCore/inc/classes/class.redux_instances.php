<?php

/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */
/**
 * Redux Framework Instance Container Class
 * Automatically captures and stores all instances
 * of ReduxFramework at instantiation.
 *
 * @package     Redux_Framework
 * @subpackage  Core
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Redux_Instances', false)) {

    class Redux_Instances {

        /**
         * ReduxFramework instances
         *
         * @var ReduxFramework[]
         */
        private static $instances;

        /**
         * Get Instance
         * Get Redux_Instances instance
         * OR an instance of ReduxFramework by [opt_name]
         *
         * @param  string|false $opt_name the defined opt_name
         *
         * @return ReduxFramework class instance
         */
        public static function get_instance($opt_name = false) {

            if ($opt_name && !empty(self::$instances[$opt_name])) {
                return self::$instances[$opt_name];
            }

            return null;
        }

        /**
         * Get all instantiated ReduxFramework instances (so far)
         *
         * @return [type] [description]
         */
        public static function get_all_instances() {
            return self::$instances;
        }

        public function __construct($ReduxFramework = false) {
            if ($ReduxFramework) {
                $this->store($ReduxFramework);
            } else {
                add_action('redux/construct', array($this, 'store'), 5, 1);
            }
        }

        public function store($ReduxFramework) {
            if ($ReduxFramework instanceof ReduxFramework) {
                $key = $ReduxFramework->args['opt_name'];
                self::$instances[$key] = $ReduxFramework;
            }
        }

    }

}

class_alias('Redux_Instances', 'ReduxFrameworkInstances');
