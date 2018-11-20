<?php

/**
 * Register Extensions for use
 *
 * @since       3.0.0
 * @access      public
 * @return      void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Redux_Extensions', false ) ) {

	class Redux_Extensions extends Redux_Class {

		public function __construct( $parent ) {
			parent::__construct( $parent );

			$this->load();
		}

		private function load() {
			$core = $this->core();

			$path    = ReduxCore::$_dir . '/inc/extensions/';
			$folders = scandir( $path, 1 );

			/**
			 * Action 'redux/extensions/before'
			 *
			 * @param object $this ReduxFramework
			 */
			do_action( "redux/extensions/before", $core );

			/**
			 * Action 'redux/extensions/{opt_name}/before'
			 *
			 * @param object $this ReduxFramework
			 */
			do_action( "redux/extensions/{$core->args['opt_name']}/before", $core );

			if ( isset( $core->old_opt_name ) && $core->old_opt_name !== null ) {
				do_action( "redux/extensions/" . $core->old_opt_name . "/before", $core );
			}
      require_once 'class.redux_abstract_extension.php';

			foreach ( $folders as $folder ) {
				if ( '.' === $folder || '..' === $folder || ! is_dir( $path . $folder ) || substr( $folder, 0, 1 ) === '.' || substr( $folder, 0, 1 ) === '@' || substr( $folder, 0, 4 ) === '_vti' ) {
					continue;
				}

				$extension_class = 'ReduxFramework_Extension_' . $folder;

				/**
				 * Filter 'redux/extension/{opt_name}/{folder}'.
				 *
				 * @param        string                    extension class file path
				 * @param string $extension_class extension class name
				 */
				$class_file = apply_filters( "redux/extension/{$core->args['opt_name']}/$folder", "$path/$folder/extension_{$folder}.php", $extension_class );

				if ( $class_file ) {
					if ( file_exists( $class_file ) ) {
						require_once $class_file;
						$obj = new $extension_class( $core );

						try {
							$name             = str_replace( '_', '-', sanitize_title( $folder ) );
							$this->upload_dir = ReduxFramework::$_upload_dir . $name;
							$this->upload_url = ReduxFramework::$_upload_url . $name;
							$path_info        = Redux_Helpers::path_info( dirname( $class_file ) );

							$obj->_extension_dir = trailingslashit( $path_info['realpath'] );
							$obj->_extension_url = trailingslashit( $path_info['url'] );
							$obj->extension_url  = $obj->_extension_url;
							$obj->extension_dir  = $obj->_extension_dir;
						} catch ( Exception $e ) {
							print( esc_html( $e ) );
						}

						$core->extensions[ $folder ] = $obj;
					}
				}
			}

			/**
			 * Action 'redux/extensions/{opt_name}'
			 *
			 * @param object $this ReduxFramework
			 */
			do_action( "redux/extensions/{$core->args['opt_name']}", $core );

			if ( isset( $core->old_opt_name ) && $core->old_opt_name !== null ) {
				do_action( "redux/extensions/" . $core->old_opt_name, $core );
			}
		}

	}

}
