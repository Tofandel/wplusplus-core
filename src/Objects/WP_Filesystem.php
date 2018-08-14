<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Objects;

use Tofandel\Core\Traits\Singleton;
use WP_Filesystem_Direct;

class WP_Filesystem {
	use Singleton;

	/**
	 * @var \WP_Filesystem_Direct
	 */
	private $direct;
	/**
	 * @var \WP_Filesystem_Base
	 */
	private $filesystem;

	private $ftp_form;
	private $creds;

	static $chmod_file = 0644;
	static $chmod_dir = 0755;

	public function ftp_form() {
		if ( isset( $this->ftp_form ) && ! empty( $this->ftp_form ) ) {
			echo '<div class="wrap"><div class="error"><p>';
			echo '<strong>' . __( 'File Permission Issues', 'redux-framework' ) . '</strong><br/>' . sprintf( __( 'We were unable to modify required files. Please ensure that <code>%1s</code> has the proper read-write permissions, or modify your wp-config.php file to contain your FTP login credentials as <a href="%2s" target="_blank">outlined here</a>.', 'redux-framework' ), Redux_Helpers::cleanFilePath( trailingslashit( WP_CONTENT_DIR ) ) . '/uploads/', 'https://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants' );
			echo '</p></div><h2></h2>' . '</div>';
		}
	}

	protected function __init() {
		// Setup the filesystem with creds
		require_once ABSPATH . '/wp-admin/includes/template.php';
		require_once ABSPATH . '/wp-includes/pluggable.php';
		require_once ABSPATH . '/wp-admin/includes/file.php';

		if ( defined( 'FS_CHMOD_FILE' ) ) {
			self::$chmod_file = FS_CHMOD_FILE;
		}
		if ( defined( 'FS_CHMOD_DIR' ) ) {
			self::$chmod_dir = FS_CHMOD_DIR;
		}

		$url = false;
		global $wp;
		if ( isset( $wp ) ) {
			$base = home_url( $wp->request );

			$url = wp_nonce_url( $base, isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'filesystem' );
		}

		$this->filesystem_init( $url, 'direct' );
	}

	public function filesystem_init( $form_url, $method = '', $context = false ) {
		if ( ! empty( $this->creds ) ) {
			return true;
		}

		ob_start();

		/* first attempt to get credentials */
		if ( false === ( $this->creds = request_filesystem_credentials( $form_url, $method, false, $context ) ) ) {
			$this->creds    = array();
			$this->ftp_form = ob_get_contents();
			ob_end_clean();

			/**
			 * if we comes here - we don't have credentials
			 * so the request for them is displaying
			 * no need for further processing
			 **/

			return false;
		}

		/* now we got some credentials - try to use them*/
		if ( ! WP_Filesystem( $this->creds ) ) {
			$this->creds = array();
			/* incorrect connection data - ask for credentials again, now with error message */
			request_filesystem_credentials( $form_url, '', true, $context );
			$this->ftp_form = ob_get_contents();
			ob_end_clean();

			return false;
		}

		global $wp_filesystem;
		$this->filesystem = $wp_filesystem;

		return true;
	}

	public function load_direct() {
		if ( ! isset( $this->direct ) ) {
			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
			$this->direct = new WP_Filesystem_Direct( array() );
		}
	}

	public function copy( $file, $dest, $overwrite = false ) {
		return $this->filesystem->copy( $file, $dest, $overwrite, self::$chmod_file );
	}

	public function mkdir( $dir ) {
		$res = $this->filesystem->mkdir( $dir );
		if ( ! $res ) {
			wp_mkdir_p( $dir );

			$res = file_exists( $dir );
			if ( ! $res ) {
				mkdir( $dir, self::$chmod_dir, true );
				$res = file_exists( $dir );
			}
		}
		$index_path = trailingslashit( $dir ) . 'index.php';
		if ( ! file_exists( $index_path ) ) {
			$this->filesystem->put_contents(
				$index_path,
				'<?php' . PHP_EOL . '// Silence is golden.',
				self::$chmod_file // predefined mode settings for WP files
			);
		}

		return $res;
	}

	public function deleteDir( $dir, $recursive = false ) {
		return $this->filesystem->rmdir( $dir, $recursive );
	}

	public function deleteFile( $file ) {
		return $this->filesystem->delete( $file, false, 'f' );
	}

	public function putContents( $file, $content ) {
		// Write a string to a file
		if ( isset( $this->ftp_form ) && ! empty( $this->ftp_form ) ) {
			$this->load_direct();

			return $this->direct->put_contents( $file, $content, self::$chmod_file );
		} else {
			return $this->filesystem->put_contents( $file, $content, self::$chmod_file );
		}
	}

	public function chmod( $chmod, $file, $recursive = false ) {
		return $this->filesystem->chmod( $file, $chmod, $recursive );
	}

	public function getContents( $file ) {
		// Reads entire file into a string
		if ( isset( $this->ftp_form ) && ! empty( $this->ftp_form ) ) {
			$this->load_direct();

			return $this->direct->get_contents( $file );
		} else {
			return $this->filesystem->get_contents( $file );
		}
	}

	public function getContentsArray( $file ) {
		return $this->filesystem->get_contents_array( $file );
	}

	public function chown( $owner, $file, $recursive = false ) {
		return $this->filesystem->chown( $file, $owner, $recursive );
	}

	public function dirlist( $dir, $include_hidden = false, $recursive = false ) {
		return $this->filesystem->dirlist( $dir, $include_hidden, $recursive );
	}

}

try {
	WP_Filesystem::__StaticInit();
} catch ( \Exception $e ) {
	error_log( $e->getMessage() );
}