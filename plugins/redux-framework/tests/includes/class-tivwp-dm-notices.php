<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Class TIVWP_DM_Notices
 * @package TIVWP_DM
 * @author  tivnet
 */
class TIVWP_DM_Notices {

	const NOTICE_LABEL = 'TIVWP-DM';
	const WITH_TRIGGER_ERROR = true;
	const WITHOUT_TRIGGER_ERROR = false;

	/**
	 * @var array $_notices Collect here all notices we need to display
	 */
	private static $_notices = array();

	/**
	 * Add new notice
	 *
	 * @param string $notice Message to display
	 * @param bool $do_trigger_error [false] If true, issue trigger_error()
	 */
	public static function add( $notice, $do_trigger_error = self::WITHOUT_TRIGGER_ERROR ) {

		/**
		 * If we are in the admin area, and the current user is capable to understand what we are talking about...
		 */
		if ( is_admin() && current_user_can( TIVWP_DM::MIN_CAPABILITY ) ) {

			/**
			 * Add new notice to the stack
			 */
			self::$_notices[] = $notice;

			/**
			 * If that was the first notice, then hook our function
			 * @see display()
			 * to the admin_notices action
			 */
			if ( sizeof( self::$_notices ) === 1 ) {
				add_action( 'admin_notices', array(
					'TIVWP_DM_Notices',
					'display'
				) );
			}
		}

		/**
		 * ... and log the error immediately, whether we are in admin area or not, if requested
		 */
		if ( $do_trigger_error === self::WITH_TRIGGER_ERROR ) {

			$notice = self::NOTICE_LABEL . ':' . $notice;

			if ( WP_DEBUG && WP_DEBUG_DISPLAY ) {
				trigger_error( $notice ); // out loud
			} else {
				error_log( $notice ); // silently
			}
		}
	}

	/**
	 * Display all collected notices in admin area
	 * @wp-hook admin_notices
	 */
	public static function display() {

		echo '<div class="error"><p>';
		echo self::NOTICE_LABEL . ':';
		foreach ( self::$_notices as $notice ) {
			echo '<br/>' . esc_html( $notice );
		}
		echo '</p></div>';
	}

}

//class

# --- EOF
