<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Dev', false ) ) {

	class Redux_Dev extends Redux_Class {

		public function __construct( $parent = null ) {
			parent::__construct( $parent );

			$this->load( $parent );
		}

		public function load( $core ) {
			if ( true === $core->args['dev_mode'] || true === Redux_Helpers::isLocalHost() ) {
				new Redux_Dashboard( $core );

				if ( ! isset( $GLOBALS['redux_notice_check'] ) || 0 === $GLOBALS['redux_notice_check'] ) {
					$params = array(
						'dir_name'    => 'notice',
						'server_file' => 'http://reduxframework.com/wp-content/uploads/redux/redux_notice.json',
						'interval'    => 3,
						'cookie_id'   => 'redux_blast',
					);

					new Redux_Newsflash( $core, $params );

					$GLOBALS['redux_notice_check'] = 1;
				}
			}
		}
	}
}
