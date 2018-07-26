<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @author      Dovy Paukstys (dovy)
 * @version     3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'Redux_Widget_Areas' ) ) {

	/**
	 * Main ReduxFramework customizer extension class
	 *
	 * @since       1.0.0
	 */
	class Redux_Widget_Areas {

		// Protected vars
		private $extension_url;
		private $extension_dir;
		/**
		 * Array of enabled widget_areas
		 *
		 * @since    1.0.0
		 * @var      array
		 */
		protected $widget_areas = array();
		protected $orig = array();

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $sections Panel sections.
		 * @param       array $args Class constructor arguments.
		 * @param       array $extra_tabs Extra panel tabs.
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {

			$this->parent = $parent;

			if ( empty( $this->extension_dir ) ) {
				$this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
				$this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
			}
			add_action( 'init', array( &$this, 'register_custom_widget_areas' ), 1000 );
			add_action( 'admin_print_scripts', array( $this, 'add_new_widget_area_box' ) );
			add_action( 'load-widgets.php', array( $this, 'add_widget_area_area' ), 100 );
			add_action( 'load-widgets.php', array( $this, '_enqueue' ), 100 );
			add_action( 'wp_ajax_redux_delete_widget_area', array( $this, 'redux_delete_widget_area_area' ) );

		}

		/**
		 * Function to create the HTML used to create widget_areas.
		 *
		 * @since     1.0.0
		 */
		public function add_new_widget_area_box() {
			$nonce = wp_create_nonce( 'delete-redux-widget_area-nonce' );
			?>
			<script type="text/html" id="redux-add-widget-template">
				<div id="redux-add-widget" class="widgets-holder-wrap">
					<div class="">
						<input type="hidden" name="redux-nonce" value="<?php echo $nonce ?>"/>

						<div class="sidebar-name">
							<h3><?php echo __( 'Create Widget Area', 'redux-framework' ); ?> <span
										class="spinner"></span></h3>
						</div>
						<div class="sidebar-description">
							<form id="addWidgetAreaForm" action="" method="post">
								<div class="widget-content">
									<input id="redux-add-widget-input" name="redux-add-widget-input" type="text"
										   class="regular-text"
										   title="<?php echo __( 'Name', 'redux-framework' ); ?>"
										   placeholder="<?php echo __( 'Name', 'redux-framework' ); ?>"/>
								</div>
								<div class="widget-control-actions">
									<div class="aligncenter">
										<input class="addWidgetArea-button button-primary" type="submit"
											   value="<?php echo __( 'Create Widget Area', 'redux-framework' ); ?>"/>
									</div>
									<br class="clear">
								</div>
							</form>
						</div>
					</div>
				</div>
			</script>
			<?php
		}

		/**
		 * Function to create a new widget_area
		 *
		 * @since     1.0.0
		 *
		 * @param    string    Name of the widget_area to be deleted.
		 *
		 * @return    string     'widget_area-deleted' if successful.
		 */
		function add_widget_area_area() {
			if ( ! empty( $_POST['redux-add-widget-input'] ) ) {
				//set_theme_mod('redux-widget_areas', '');
				$this->widget_areas = $this->get_widget_areas();
				array_push( $this->widget_areas, $this->check_widget_area_name( $_POST['redux-add-widget-input'] ) );
				$this->save_widget_areas();
				wp_redirect( admin_url( 'widgets.php' ) );

				die();
			}
		}


		/**
		 * Before we create a new widget_area, verify it doesn't already exist. If it does, append a number to the name.
		 *
		 * @since     1.0.0
		 *
		 * @param    string $name Name of the widget_area to be created.
		 *
		 * @return    name     $name      Name of the new widget_area just created.
		 */
		function check_widget_area_name( $name ) {
			if ( empty( $GLOBALS['wp_registered_widget_areas'] ) ) {
				return $name;
			}

			$taken = array();
			foreach ( $GLOBALS['wp_registered_widget_areas'] as $widget_area ) {
				$taken[] = $widget_area['name'];
			}

			$taken = array_merge( $taken, $this->widget_areas );

			if ( in_array( $name, $taken ) ) {
				$counter  = substr( $name, - 1 );
				$new_name = "";

				if ( ! is_numeric( $counter ) ) {
					$new_name = $name . " 1";
				} else {
					$new_name = substr( $name, 0, - 1 ) . ( (int) $counter + 1 );
				}

				$name = $this->check_widget_area_name( $new_name );
			}
			echo $name;
			exit();

			return $name;
		}

		function save_widget_areas() {
			set_theme_mod( 'redux-widget-areas', array_unique( $this->widget_areas ) );
		}

		/**
		 * Register and display the custom widget_area areas we have set.
		 *
		 * @since     1.0.0
		 */
		function register_custom_widget_areas() {
			// If the single instance hasn't been set, set it now.
			if ( empty( $this->widget_areas ) ) {
				$this->widget_areas = $this->get_widget_areas();
			}
			$this->orig = array_unique( apply_filters( 'redux/' . $this->parent->args['opt_name'] . '/widget_areas', array() ) );
			/* deprecated */
			$this->orig = array_unique( apply_filters( 'redux/widget_areas', $this->orig ) );

			if ( ! empty( $this->orig ) && $this->orig != $this->widget_areas ) {
				$this->widget_areas = array_unique( array_merge( $this->widget_areas, $this->orig ) );
				$this->save_widget_areas();
			}

			$options = array(
				'before_title'  => '<h3 class="widgettitle">',
				'after_title'   => '</h3>',
				'before_widget' => '<div id="%1$s" class="widget clearfix %2$s">',
				'after_widget'  => '</div>'
			);

			$options = apply_filters( 'redux_custom_widget_args', $options );

			if ( is_array( $this->widget_areas ) ) {
				foreach ( array_unique( $this->widget_areas ) as $widget_area ) {
					$options['class'] = 'redux-custom';
					$options['name']  = $widget_area;
					$options['id']    = sanitize_key( $widget_area );
					register_sidebar( $options );
				}
			}
		}


		/**
		 * Return the widget_areas array.
		 *
		 * @since     1.0.0
		 * @return    array    If not empty, active redux widget_areas are returned.
		 */
		public function get_widget_areas() {

			// If the single instance hasn't been set, set it now.
			if ( ! empty( $this->widget_areas ) ) {
				return $this->widget_areas;
			}

			$db = get_theme_mod( 'redux-widget-areas' );

			if ( ! empty( $db ) ) {
				$this->widget_areas = array_unique( array_merge( $this->widget_areas, $db ) );
			}

			return $this->widget_areas;

		}

		/**
		 * Before we create a new widget_area, verify it doesn't already exist. If it does, append a number to the name.
		 *
		 * @since     1.0.0
		 *
		 * @param    string    Name of the widget_area to be deleted.
		 *
		 * @return    string     'widget_area-deleted' if successful.
		 */
		function redux_delete_widget_area_area() {
			//check_ajax_referer('delete-redux-widget_area-nonce');

			if ( ! empty( $_REQUEST['name'] ) ) {
				$name               = strip_tags( ( stripslashes( $_REQUEST['name'] ) ) );
				$this->widget_areas = $this->get_widget_areas();
				$key                = array_search( $name, $this->widget_areas );
				if ( $key >= 0 ) {
					unset( $this->widget_areas[ $key ] );
					$this->save_widget_areas();
				}
				echo "widget_area-deleted";
			}

			die();
		}


		/**
		 * Enqueue CSS/JS for the customizer controls
		 *
		 * @since       1.0.0
		 * @access      public
		 * @global      $wp_styles
		 * @return      void
		 */
		function _enqueue() {

			wp_enqueue_style( 'dashicons' );

			wp_enqueue_script(
				'redux-widget_areas-js',
				$this->extension_url . 'assets/js/widget_areas.js',
				array( 'jquery' ),
				time(),
				true
			);

			//if ( function_exists( 'redux_enqueue_style' ) ) {
			//    wp_enqueue_style(
			//        'redux-widget_areas-css',
			//        $this->extension_url . 'assets/css/widget_areas.css'
			//    );
			//} else {
			wp_enqueue_style(
				'redux-widget_areas-css',
				$this->extension_url . 'assets/css/widget_areas.css',
				time(),
				true
			);
			//}

			$widgets = array();
			if ( ! empty( $this->widget_areas ) ) {
				foreach ( $this->widget_areas as $widget ) {
					$widgets[ $widget ] = 1;
				}
			}

			// Localize script
			wp_localize_script(
				'redux-widget_areas-js',
				'reduxWidgetAreasLocalize',
				array(
					'count'   => count( $this->orig ),
					'delete'  => __( 'Delete', 'wpex' ),
					'confirm' => __( 'Confirm', 'wpex' ),
					'cancel'  => __( 'Cancel', 'wpex' ),
				)
			);

//            wp_enqueue_style(
//                'redux-widget_areas-css', 
//                $this->extension_url.'assets/css/widget_areas.css', 
//                time(),
//                true
//            );

			$widgets = array();
			if ( ! empty( $this->widget_areas ) ) {
				foreach ( $this->widget_areas as $widget ) {
					$widgets[ $widget ] = 1;
				}
			}

			wp_localize_script( 'redux-widget_areas-js', 'redux_widget_areas', array( ( count( $this->orig ) ) ) );

		}//function

	} // class

} // if
