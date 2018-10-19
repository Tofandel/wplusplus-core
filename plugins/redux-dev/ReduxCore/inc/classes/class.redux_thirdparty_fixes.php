<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_ThirdParty_Fixes', false ) ) {

	class Redux_ThirdParty_Fixes extends Redux_Class {

		public function __construct( $parent ) {
			parent::__construct( $parent );

			$this->gt3_page_builder();
		}

		private function gt3_page_builder() {
			// Fix for the GT3 page builder: http://www.gt3themes.com/wordpress-gt3-page-builder-plugin/
			/** @global string $pagenow */
			if ( has_action( 'ecpt_field_options_' ) ) {
				global $pagenow;

				if ( $pagenow === 'admin.php' ) {
					remove_action( 'admin_init', 'pb_admin_init' );
				}
			}
		}
	}
}