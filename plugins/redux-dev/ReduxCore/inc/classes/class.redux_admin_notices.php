<?php

/**
 * Redux Framework Admin Notice Class
 * Makes instantiating a Redux object an absolute piece of cake.
 *
 * @package     Redux_Framework
 * @author      Kevin Provance
 * @author      Dovy Paukstys
 * @subpackage  Core
 */

/** TODO:  Add nonce verification to GET and POST, per WPCS rules. */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'Redux_Admin_Notices', false ) ) {

	/**
	 * Redux API Class
	 * Simple API for Redux Framework
	 *
	 * @since       1.0.0
	 */
	class Redux_Admin_Notices extends Redux_Class {

		/**
		 * WordPress admin notice array.
		 *
		 * @var array
		 * @access private
		 */
		private static $notices = array();

		public function __construct( $parent ) {
			parent::__construct( $parent );

			add_action( 'wp_ajax_redux_hide_admin_notice', array( $this, 'dismissAdminNoticeAJAX' ) );
			add_action( 'admin_notices', array( $this, 'notices' ), 99 );
			add_action( 'admin_init', array( $this, 'dismiss' ), 9 );
		}

		public function notices() {
			$this->adminNotices( self::$notices );
		}

		public function dismiss() {
			$this->dismissAdminNotice();
		}

		/**
		 * @param $data
		 */
		public static function set_notice( $data ) {
			$type    = null;
			$msg     = null;
			$id      = null;
			$dismiss = null;

			extract( $data );

			/**
			 * @var ReduxFramework $parent
			 */
			self::$notices[ $parent->args['page_slug'] ][] = array(
				'type'    => $type,
				'msg'     => $msg,
				'id'      => $id . '_' . $parent->args['opt_name'],
				'dismiss' => $dismiss,
				'color'   => isset( $color ) ? $color : '#00A2E3',
			);
		}

		/**
		 * adminNotices - Evaluates user dismiss option for displaying admin notices.
		 *
		 * @since       3.2.0
		 * @access      public
		 *
		 * @param array $notices
		 *
		 * @return      void
		 */
		public function adminNotices( $notices = array() ) {
			global $current_user, $pagenow;

			// Check for an active admin notice array.
			if ( ! empty( $notices ) ) {
				$core = $this->core();

				if ( isset( $_GET ) && isset( $_GET['page'] ) && $core->args['page_slug'] === $_GET['page'] ) {

					// Enum admin notices.
					foreach ( $notices[ $core->args['page_slug'] ] as $notice ) {

						$add_style = '';
						if ( strpos( $notice['type'], 'redux-message' ) !== false ) {
							$add_style = 'style="border-left: 4px solid ' . esc_attr( $notice['color'] ) . '!important;"';
						}

						if ( true === $notice['dismiss'] ) {

							// Get user ID.
							$userid = $current_user->ID;

							if ( ! get_user_meta( $userid, 'ignore_' . $notice['id'] ) ) {

								// Check if we are on admin.php.  If we are, we have
								// to get the current page slug and tab, so we can
								// feed it back to WordPress.  Why?  admin.php cannot
								// be accessed without the page parameter.  We add the
								// tab to return the user to the last panel they were
								// on.
								$page_name = '';
								$cur_tab   = '';

								if ( $pagenow == 'admin.php' || $pagenow == 'themes.php' ) {

									// Get the current page.  To avoid errors, we'll set
									// the redux page slug if the GET is empty.
									$page_name = empty( $_GET['page'] ) ? '&amp;page=' . $core->args['page_slug'] : '&amp;page=' . esc_attr( $_GET['page'] );

									// Ditto for the current tab.
									$cur_tab = empty( $_GET['tab'] ) ? '&amp;tab=0' : '&amp;tab=' . esc_attr( $_GET['tab'] );
								}

								global $wp_version;

								// Print the notice with the dismiss link.
								if ( version_compare( $wp_version, '4.2', '>' ) ) {
									$css_id    = esc_attr( $notice['id'] ) . $page_name . $cur_tab;
									$css_class = esc_attr( $notice['type'] ) . ' redux-notice notice is-dismissible redux-notice';

									$nonce = wp_create_nonce( $notice['id'] . $userid . 'nonce' );

									echo '<div ' . esc_html( $add_style ) . ' id=' . esc_attr( $css_id ) . ' class=' . esc_attr( $css_class ) . '>';
									echo '<input type="hidden" class="dismiss_data" id="' . esc_attr( $notice['id'] ) . esc_attr( $page_name ) . esc_attr( $cur_tab ) . '" value="' . esc_attr( $nonce ) . '">';
									echo '<p>' . wp_kses_post( $notice['msg'] ) . '</p>';
									echo '</div>';
								} else {
									echo '<div ' . esc_html( $add_style ) . ' class="' . esc_attr( $notice['type'] ) . ' notice is-dismissable"><p>' . wp_kses_post( $notice['msg'] ) . '&nbsp;&nbsp;<a href="?dismiss=true&amp;id=' . esc_attr( $notice['id'] ) . esc_attr( $page_name ) . esc_attr( $cur_tab ) . '">' . esc_html__( 'Dismiss', 'redux-framework' ) . '</a>.</p></div>';
								}
							}
						} else {
							// Standard notice.
							echo '<div ' . esc_html( $add_style ) . ' class="' . esc_attr( $notice['type'] ) . ' notice">;<p>' . wp_kses_post( $notice['msg'] ) . '</a>.</p></div>';
						}
						?>
						<script>
							jQuery( document ).ready( function( $ ) {
								$( document.body ).on(
									'click', '.redux-notice.is-dismissible .notice-dismiss', function( e ) {
										e.preventDefault();
										var $data = $( this ).parent().find( '.dismiss_data' );
										$.post(
											ajaxurl, {
												action: 'redux_hide_admin_notice',
												id: $data.attr( 'id' ),
												nonce: $data.val()
											}
										);
									} );
							} );
						</script>
						<?php

					}
				}

				// Clear the admin notice array.
				self::$notices[ $core->args['opt_name'] ] = array();
			}
		}

		/**
		 * dismissAdminNotice - Updates user meta to store dismiss notice preference.
		 *
		 * @since       3.2.0
		 * @access      public
		 * @return      void
		 */
		public function dismissAdminNotice() {
			global $current_user;

			// Verify the dismiss and id parameters are present.
			if ( isset( $_GET['dismiss'] ) && isset( $_GET['id'] ) ) {
				if ( 'true' === $_GET['dismiss'] || 'false' === $_GET['dismiss'] ) {

					// Get the user id.
					$userid = $current_user->ID;

					// Get the notice id.
					$id  = sanitize_text_field( wp_unslash( $_GET['id'] ) );
					$val = sanitize_text_field( wp_unslash( $_GET['dismiss'] ) );

					// Add the dismiss request to the user meta.
					update_user_meta( $userid, 'ignore_' . $id, $val );
				}
			}
		}

		/**
		 * dismissAdminNotice - Updates user meta to store dismiss notice preference
		 *
		 * @since       3.2.0
		 * @access      public
		 * @return      void
		 */
		public function dismissAdminNoticeAJAX() {
			global $current_user;

			if ( isset( $_POST['id'] ) ) {
				// Get the notice id.
				$id = explode( '&', sanitize_text_field( wp_unslash( $_POST['id'] ) ) );
				$id = $id[0];

				// Get the user id.
				$userid = $current_user->ID;

				if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $id . $userid . 'nonce' ) ) {
					die( 0 );
				} else {
					// Add the dismiss request to the user meta.
					update_user_meta( $userid, 'ignore_' . $id, true );
				}
			}
		}

	}

}