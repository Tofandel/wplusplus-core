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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Instances', false ) ) {

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
		 * @param  string|false $opt_name the defined opt_name.
		 *
		 * @return ReduxFramework class instance
		 */
		public static function get_instance( $opt_name = false ) {

			if ( $opt_name && ! empty( self::$instances[ $opt_name ] ) ) {
				return self::$instances[ $opt_name ];
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

		public function __construct( $redux_framework = false ) {
			if ( $redux_framework ) {
				$this->store( $redux_framework );
			} else {
				add_action( 'redux/construct', array( $this, 'store' ), 5, 1 );
			}
		}

		public function store( $redux_framework ) {
			if ( $redux_framework instanceof ReduxFramework ) {
				$key                     = $redux_framework->args[ 'opt_name' ];
				self::$instances[ $key ] = $redux_framework;
			}
		}
	}

	if ( ! class_exists( 'ReduxFrameworkInstances' ) ) {
		class_alias( 'Redux_Instances', 'ReduxFrameworkInstances' );
	} else {
		error_log('Warning: you may have issues with your redux extensions, 2 different versions of Redux have been loaded and the extensions using ReduxFrameworkInstances of the ulterior version will not work correctly');
	}
}