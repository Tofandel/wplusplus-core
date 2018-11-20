<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'ReduxFramework_Extension_number', false ) ) {

	/**
	 * Class ReduxFramework_Extension_my_extension
	 *
	 * A sample extension, replace 'my_extension' and 'my_field' to the name of your extension and your field respectively
	 */
	class ReduxFramework_Extension_number extends Redux_Abstract_Extension {
		public static $version = "1.0.1";

		public function __construct( $parent ) {
			parent::__construct( $parent, __FILE__ );

			$this->add_field( 'number' );
		}
	}
}
