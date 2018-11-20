<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Transients', false ) ) {

	class Redux_Transients extends Redux_Class {

		public function get() {
			$core = $this->core();

			if ( ! isset( $core->transients ) ) {
				$core->transients       = get_option( $core->args['opt_name'] . '-transients', array() );
			}
		}

		public function set() {
			$core = $this->core();

			if ( ! isset( $core->transients ) || ! isset( $core->transients_check ) || $core->transients_check !== $core->transients ) {
				update_option( $core->args['opt_name'] . '-transients', $core->transients );
			}
		}
	}
}
