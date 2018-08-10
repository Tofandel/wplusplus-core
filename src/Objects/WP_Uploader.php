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
 * Created by PhpStorm.
 * User: Adrien
 * Date: 24/02/2018
 * Time: 16:18
 */

namespace Tofandel\Core\Objects;


use Tofandel\Core\Traits\Singleton;
use Tofandel\WPlusPlusCore;
use WP_Query;

class WP_Uploader {
	use Singleton;

	function __construct() {
		add_action( 'init', function () {
			add_rewrite_endpoint( 'get_private_file', EP_ROOT );
			if ( strpos( $_SERVER['REQUEST_URI'], '/get_private_file/' ) === 0 ) {
				self::get_private_file();
			}
		} );

		add_filter( 'query_vars', function ( $vars ) {
			$vars[] = "get_private_file";

			return $vars;
		} );
		add_action( 'template_redirect', function () {
			$url = get_query_var( 'get_private_file', false );
			if ( $url !== false ) {
				self::get_private_file();
			}
		}, 1 );

		add_filter( 'wp_get_attachment_metadata', [ self::class, 'get_private_attachment_metadata' ], 999, 2 );
		add_filter( 'wp_get_attachment_image_src', [ self::class, 'get_private_attachment_image_src' ], 999, 3 );
		add_filter( 'wp_get_attachment_url', [ self::class, 'get_private_attachment_url' ], 999, 2 );

		add_action( 'admin_init', [ $this, 'add_attachment_meta' ] );
		add_action( 'edit_attachment', [ $this, 'save_attachment_meta' ] );

		new WP_Ajax( 'wpp-file-upload', [ $this, 'ajax_upload' ] );
	}

	public static function get_private_file() {
		if ( ! isset( $_REQUEST['id'] ) ) {
			self::set_404();
		}

		$attachment = get_post( $_REQUEST['id'] );
		if ( $attachment->post_type != 'attachment' ) {
			self::set_404();
		}

		//post author or editor role
		if ( ! ( wpp_can( 'view', $attachment ) || wpp_can( 'edit', $attachment ) ) ) {
			self::set_404();
		}
		remove_filter( 'wp_get_attachment_metadata', self::class . '::get_private_attachment_metadata', 999 );
		remove_filter( 'wp_get_attachment_url', self::class . '::get_private_attachment_url', 999 );
		remove_filter( 'wp_get_attachment_image_src', self::class . '::get_private_attachment_image_src', 999 );
		if ( isset( $_REQUEST['size'] ) ) {
			$file = wp_get_attachment_image_url( $attachment->ID, $_REQUEST['size'], false );
			if ( $file ) {
				$file = ABSPATH . wp_make_link_relative( $file );
			}
		}
		if ( empty( $file ) || ! file_exists( $file ) ) {
			$file = get_attached_file( $attachment->ID );
		}
		if ( file_exists( $file ) ) {
			$type = wp_check_filetype( $file );
			header( 'Content-Type: ' . $type['type'] );
			header( "Content-Transfer-Encoding: Binary" );
			header( "Content-disposition: inline; filename=\"" . $attachment->post_title . '.' . pathinfo( $file, PATHINFO_EXTENSION ) . "\"" );
			header( 'Cache-Control: public, max-age=31536000' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + ( 60 * 60 * 24 * 365 ) ) );
			readfile( $file );
			die();
		} else {
			self::set_404();
		}
	}

	public static function set_404() {
		global $wp_query;
		/**
		 * @var WP_Query $wp_query
		 */
		$wp_query->set_404();
		status_header( 404 );
		die();
	}

	public static function get_private_attachment_metadata( $data, $attachment_id ) {
		if ( get_post_meta( $attachment_id, 'is_private', true ) == '1' ) {
			if ( ! empty( $data['sizes'] ) ) {
				foreach ( $data['sizes'] as $size => $vals ) {
					$data['sizes'][ $size ]['file'] = '?id=' . $attachment_id . '&size=' . $size;
				}
			}
		}

		return $data;
	}

	public static function get_private_attachment_url( $url, $attachment_id ) {
		if ( get_post_meta( $attachment_id, 'is_private', true ) == '1' ) {
			$p   = get_post( $attachment_id );
			$ext = pathinfo( $url, PATHINFO_EXTENSION );

			return add_query_arg( array( 'id' => $attachment_id ), site_url( 'get_private_file/' . $p->post_title . '.' . $ext ) );
		}

		return $url;
	}

	public static function get_private_attachment_image_src( $image, $attachment_id, $size ) {
		if ( get_post_meta( $attachment_id, 'is_private', true ) == '1' ) {
			return array(
				add_query_arg( array(
					'id'   => $attachment_id,
					'size' => $size
				), site_url( 'get_private_file/' . $image ) )
			);
		}

		return $image;
	}

	public static function file_upload_max_size( $size = false ) {
		static $max_size = - 1;

		if ( $size !== false ) {
			$size = self::parse_size( $size );
		}

		if ( $max_size < 0 ) {

			// Start with post_max_size.
			if ( $size ) {
				$max_size = self::parse_size( $size );
			}
			// Start with post_max_size.
			$post_max_size = self::parse_size( ini_get( 'post_max_size' ) );
			if ( $max_size < 0 || $post_max_size > 0 && $post_max_size < $max_size ) {
				$max_size = $post_max_size;
			}

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = self::parse_size( ini_get( 'upload_max_filesize' ) );
			if ( $upload_max > 0 && $upload_max < $max_size ) {
				$max_size = $upload_max;
			}
		}

		return $max_size;
	}

	public static function parse_size( $size ) {
		$unit = preg_replace( '/[^bkmgtpezy]/i', '', $size ); // Remove the non-unit characters from the size.
		$size = preg_replace( '/[^0-9\.]/', '', $size ); // Remove the non-numeric characters from the size.
		if ( $unit ) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round( $size * pow( 1024, stripos( 'bkmgtpezy', $unit[0] ) ) );
		} else {
			return round( $size );
		}
	}

	public static function format_size( $bytes, $precision = 2 ) {
		$units = array( 'o', 'Ko', 'Mo', 'Go', 'To' );

		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		// Uncomment one of the following alternatives
		// $bytes /= pow(1024, $pow);
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	/**
	 * @param array $exts
	 *
	 * @return array
	 */
	public static function getMimesFromExtensions( array $exts ) {
		$types = array();
		foreach ( $exts as $ext ) {
			foreach ( self::getMimes() as $type => $val ) {
				if ( isset( $types[ $type ] ) ) {
					continue;
				}
				if ( array_key_exists( $ext, $val['mimes'] ) ) {
					$types[ $type ] = $val;
				}
			}
		}

		return $types;
	}

	public static function getMimes() {
		static $def_types;
		if ( ! isset( $def_types ) ) {
			$def_types = array(
				'image'            => array(
					'title' => __( 'Image files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'jpg'  => 'image/jpeg',
						'jpeg' => 'image/jpeg',
						'jpe'  => 'image/jpeg',
						'gif'  => 'image/gif',
						'png'  => 'image/png',
						'bmp'  => 'image/bmp',
						'tiff' => 'image/tiff',
						'tif'  => 'image/tiff',
						'ico'  => 'image/x-icon',
					),
				),
				'video'            => array(
					'title' => __( 'Video files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'asf'  => 'video/x-ms-asf',
						'asx'  => 'video/x-ms-asf',
						'wmv'  => 'video/x-ms-wmv',
						'wmx'  => 'video/x-ms-wmx',
						'wm'   => 'video/x-ms-wm',
						'avi'  => 'video/avi',
						'divx' => 'video/divx',
						'flv'  => 'video/x-flv',
						'mov'  => 'video/quicktime',
						'qt'   => 'video/quicktime',
						'mpeg' => 'video/mpeg',
						'mpg'  => 'video/mpeg',
						'mpe'  => 'video/mpeg',
						'mp4'  => 'video/mp4',
						'm4v'  => 'video/mp4',
						'ogv'  => 'video/ogg',
						'webm' => 'video/webm',
						'mkv'  => 'video/x-matroska',
						'3gp'  => 'video/3gpp', // Can also be audio
						'3g2'  => 'video/3gpp2', // Can also be audio
						'3gpp' => 'video/3gpp', // Can also be audio
						'3gp2' => 'video/3gpp2', // Can also be audio
					)
				),
				'audio'            => array(
					'title' => __( 'Audio files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'3gp'  => 'video/3gpp', // Can also be audio
						'3g2'  => 'video/3gpp2', // Can also be audio
						'3gpp' => 'video/3gpp', // Can also be audio
						'3gp2' => 'video/3gpp2', // Can also be audio
						'mp3'  => 'audio/mpeg',
						'm4a'  => 'audio/mpeg',
						'm4b'  => 'audio/mpeg',
						'ra'   => 'audio/x-realaudio',
						'ram'  => 'audio/x-realaudio',
						'wav'  => 'audio/wav',
						'oga'  => 'audio/ogg',
						'ogg'  => 'audio/ogg',
						'mid'  => 'audio/midi',
						'midi' => 'audio/midi',
						'wma'  => 'audio/x-ms-wma',
						'wax'  => 'audio/x-ms-wax',
						'mka'  => 'audio/x-matroska',
					)
				),
				'text'             => array(
					'title' => __( 'Text files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'txt'  => 'text/plain',
						'asc'  => 'text/plain',
						'c'    => 'text/plain',
						'cc'   => 'text/plain',
						'h'    => 'text/plain',
						'srt'  => 'text/plain',
						'csv'  => 'text/csv',
						'tsv'  => 'text/tab-separated-values',
						'ics'  => 'text/calendar',
						'rtx'  => 'text/richtext',
						'css'  => 'text/css',
						'htm'  => 'text/html',
						'html' => 'text/html',
						'vtt'  => 'text/vtt',
						'dfxp' => 'application/ttaf+xml',
					)
				),
				'pdf'              => array(
					'title' => __( 'PDF files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'pdf' => 'application/pdf',
					)
				),
				'compressed'       => array(
					'title' => __( 'Compressed files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'tar'  => 'application/x-tar',
						'zip'  => 'application/zip',
						'gz'   => 'application/x-gzip',
						'gzip' => 'application/x-gzip',
						'rar'  => 'application/rar',
						'7z'   => 'application/x-7z-compressed',
					)
				),
				'exec'             => array(
					'title' => __( 'Executable files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'js'    => 'application/javascript',
						'swf'   => 'application/x-shockwave-flash',
						'class' => 'application/java',
						'exe'   => 'application/x-msdownload',
					)
				),
				'font'             => array(
					'title' => __( 'Font files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'svg'   => "image/svg+xml",
						'ttf'   => "application/x-font-ttf",
						'otf'   => "application/x-font-opentype",
						'woff'  => "application/font-woff",
						'woff2' => "application/font-woff2",
						'eot'   => "application/vnd.ms-fontobject",
						'sfnt'  => "application/font-sfnt",
					)
				),
				'image_editor'     => array(
					'title' => __( 'Image Editor files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'psd' => 'application/octet-stream',
						'xcf' => 'application/octet-stream',
						'svg' => "image/svg+xml",
					)
				),
				'microsoft_office' => array(
					'title' => __( 'MS Office files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'doc'     => 'application/msword',
						'pot'     => 'application/vnd.ms-powerpoint',
						'pps'     => 'application/vnd.ms-powerpoint',
						'ppt'     => 'application/vnd.ms-powerpoint',
						'wri'     => 'application/vnd.ms-write',
						'xla'     => 'application/vnd.ms-excel',
						'xls'     => 'application/vnd.ms-excel',
						'xlt'     => 'application/vnd.ms-excel',
						'xlw'     => 'application/vnd.ms-excel',
						'mdb'     => 'application/vnd.ms-access',
						'mpp'     => 'application/vnd.ms-project',
						'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'docm'    => 'application/vnd.ms-word.document.macroEnabled.12',
						'dotx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
						'dotm'    => 'application/vnd.ms-word.template.macroEnabled.12',
						'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
						'xlsm'    => 'application/vnd.ms-excel.sheet.macroEnabled.12',
						'xlsb'    => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
						'xltx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
						'xltm'    => 'application/vnd.ms-excel.template.macroEnabled.12',
						'xlam'    => 'application/vnd.ms-excel.addin.macroEnabled.12',
						'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
						'pptm'    => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
						'ppsx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
						'ppsm'    => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
						'potx'    => 'application/vnd.openxmlformats-officedocument.presentationml.template',
						'potm'    => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
						'ppam'    => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
						'sldx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
						'sldm'    => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
						'onetoc'  => 'application/onenote',
						'onetoc2' => 'application/onenote',
						'onetmp'  => 'application/onenote',
						'onepkg'  => 'application/onenote',
						'oxps'    => 'application/oxps',
						'xps'     => 'application/vnd.ms-xpsdocument',
						'rtf'     => 'application/rtf',
					)
				),
				'translation'      => array(
					'title' => __( 'Translation files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'pot' => 'text/x-gettext-translation',
						'po'  => 'text/x-gettext-translation',
						'mo'  => 'application/octet-stream',
					),
				),
				'open_office'      => array(
					'title' => __( 'Open Office files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'odt' => 'application/vnd.oasis.opendocument.text',
						'odp' => 'application/vnd.oasis.opendocument.presentation',
						'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
						'odg' => 'application/vnd.oasis.opendocument.graphics',
						'odc' => 'application/vnd.oasis.opendocument.chart',
						'odb' => 'application/vnd.oasis.opendocument.database',
						'odf' => 'application/vnd.oasis.opendocument.formula',
						'rtf' => 'application/rtf',
					)
				),
				'i_work'           => array(
					'title' => __( 'iWork files', WPlusPlusCore::TextDomain() ),
					'mimes' => array(
						'key'     => 'application/vnd.apple.keynote',
						'numbers' => 'application/vnd.apple.numbers',
						'pages'   => 'application/vnd.apple.pages',
					)
				),
			);
		}

		return $def_types;
	}

	/**
	 * @param $type
	 *
	 * @return array|false
	 */
	public static function getAllowedExt( $type ) {
		$m = self::getMimes();
		if ( array_key_exists( $type, $m ) ) {
			return $m[ $type ];
		}

		return false;
	}

	/**
	 * @param $dir
	 * @param $filename
	 * @param $ext
	 *
	 * @return string
	 */
	public static function unique_filename( $dir, $filename, $ext ) {
		$filename = md5( uniqid( microtime() . mt_rand() . $filename, true ) ) . $ext;

		return $filename;
	}

	/**
	 * Generic function to upload a file
	 *
	 * @param array $upload_data
	 * @param bool $private
	 * @param int $parent
	 * @param int $author
	 *
	 * @return int|\WP_Error attachment_id on success, wp error instead
	 * TODO ALLOWED MIMES
	 */
	public static function handle_upload( $upload_data, $private = true, $parent = 0, $author = 0 ) {

		$uploaded_file = self::wp_handle_upload( $upload_data, $private, array(
			'test_form'                => false,
			'unique_filename_callback' => $private ? [ self::class, 'unique_filename' ] : false
		) );

		// If the wp_handle_upload call returned a local path for the image
		if ( isset( $uploaded_file['file'] ) ) {
			$file_loc  = $uploaded_file['file'];
			$file_name = basename( $upload_data['name'] );
			$file_type = wp_check_filetype( $file_name );

			$attachment = array(
				'post_mime_type' => $file_type['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_name'      => '',
				'post_status'    => 'inherit',
				'post_parent'    => $parent,
				'post_author'    => $author ?: get_current_user_id(),
				'guid'           => $file_loc
			);

			$attach_id = wp_insert_attachment( $attachment, $file_loc );
			if ( $private ) {
				update_post_meta( $attach_id, 'is_private', '1' );
				wp_update_post( array(
					'ID'   => $attach_id,
					'guid' => site_url( 'get_private_file/?id=' . $attach_id )
				) );
			}

			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;
		}

		return new \WP_Error( '', $uploaded_file['error'] );
	}

	private static function wp_handle_upload( &$file, $private, $overrides = false ) {

		return self::_wp_handle_upload( $file, $private, $overrides, 'wp_handle_upload' );
	}

	private static function _wp_handle_upload( &$file, $private, $overrides, $action ) {
		// The default error handler.
		if ( ! function_exists( 'wp_handle_upload_error' ) ) {
			function wp_handle_upload_error( &$file, $message ) {
				$file = array( 'error' => $message );

				return $file;
			}
		}

		/**
		 * Filters the data for a file before it is uploaded to WordPress.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the post action.
		 *
		 * @since 2.9.0 as 'wp_handle_upload_prefilter'.
		 * @since 4.0.0 Converted to a dynamic hook with `$action`.
		 *
		 * @param array $file An array of data for a single file.
		 */
		$file = wpp_apply_filters( "{$action}_prefilter", $file );

		// You may define your own function and pass the name in $overrides['upload_error_handler']
		$upload_error_handler = 'wp_handle_upload_error';
		if ( isset( $overrides['upload_error_handler'] ) ) {
			$upload_error_handler = $overrides['upload_error_handler'];
		}

		// You may have had one or more 'wp_handle_upload_prefilter' functions error out the file. Handle that gracefully.
		if ( isset( $file['error'] ) && ! is_numeric( $file['error'] ) && $file['error'] ) {
			return call_user_func_array( $upload_error_handler, array( &$file, $file['error'] ) );
		}

		// Install user overrides. Did we mention that this voids your warranty?

		// You may define your own function and pass the name in $overrides['unique_filename_callback']
		$unique_filename_callback = null;
		if ( isset( $overrides['unique_filename_callback'] ) ) {
			$unique_filename_callback = $overrides['unique_filename_callback'];
		}

		/*
		 * This may not have orignially been intended to be overrideable,
		 * but historically has been.
		 */
		if ( isset( $overrides['upload_error_strings'] ) ) {
			$upload_error_strings = $overrides['upload_error_strings'];
		} else {
			// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
			$upload_error_strings = array(
				false,
				__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.' ),
				__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.' ),
				__( 'The uploaded file was only partially uploaded.' ),
				__( 'No file was uploaded.' ),
				'',
				__( 'Missing a temporary folder.' ),
				__( 'Failed to write file to disk.' ),
				__( 'File upload stopped by extension.' )
			);
		}

		// All tests are on by default. Most can be turned off by $overrides[{test_name}] = false;
		$test_form = isset( $overrides['test_form'] ) ? $overrides['test_form'] : true;
		$test_size = isset( $overrides['test_size'] ) ? $overrides['test_size'] : true;

		// If you override this, you must provide $ext and $type!!
		$test_type = isset( $overrides['test_type'] ) ? $overrides['test_type'] : true;
		$mimes     = isset( $overrides['mimes'] ) ? $overrides['mimes'] : false;

		// A correct form post will pass this test.
		if ( $test_form && ( ! isset( $_POST['action'] ) || ( $_POST['action'] != $action ) ) ) {
			return call_user_func_array( $upload_error_handler, array( &$file, __( 'Invalid form submission.' ) ) );
		}
		/**
		 * @var array $file
		 */
		// A successful upload will pass this test. It makes no sense to override this one.
		if ( isset( $file['error'] ) && $file['error'] > 0 ) {
			return call_user_func_array( $upload_error_handler, array(
				&$file,
				$upload_error_strings[ $file['error'] ]
			) );
		}

		$test_file_size = 'wp_handle_upload' === $action ? $file['size'] : filesize( $file['tmp_name'] );
		// A non-empty file will pass this test.
		if ( $test_size && ! ( $test_file_size > 0 ) ) {
			if ( is_multisite() ) {
				$error_msg = __( 'File is empty. Please upload something more substantial.' );
			} else {
				$error_msg = __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.' );
			}

			return call_user_func_array( $upload_error_handler, array( &$file, $error_msg ) );
		}

		// A properly uploaded file will pass this test. There should be no reason to override this one.
		$test_uploaded_file = 'wp_handle_upload' === $action ? @ is_uploaded_file( $file['tmp_name'] ) : @ is_file( $file['tmp_name'] );
		if ( ! $test_uploaded_file ) {
			return call_user_func_array( $upload_error_handler, array(
				&$file,
				__( 'Specified file failed upload test.' )
			) );
		}

		// A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
		if ( $test_type ) {
			$wp_filetype     = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );
			$ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
			$type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
			$proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];

			// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
			if ( $proper_filename ) {
				$file['name'] = $proper_filename;
			}
			if ( ( ! $type || ! $ext ) && ! current_user_can( 'unfiltered_upload' ) ) {
				return call_user_func_array( $upload_error_handler, array(
					&$file,
					__( 'Sorry, this file type is not permitted for security reasons.' )
				) );
			}
			if ( ! $type ) {
				$type = $file['type'];
			}
		} else {
			$type = '';
		}

		/*
		 * A writable uploads dir will pass this test. Again, there's no point
		 * overriding this one.
		 */
		if ( ! ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) ) {
			return call_user_func_array( $upload_error_handler, array( &$file, $uploads['error'] ) );
		}

		$uploads['basedir'] .= '/wpp-' . ( $private ? 'private' : 'public' ) . '-uploads';

		if ( ! is_dir( $uploads['basedir'] ) ) {
			if ( ! wp_mkdir_p( $uploads['basedir'] ) ) {
				return false;
			}

			if ( $private && ! file_exists( $uploads['basedir'] . '/.htaccess' ) ) {
				if ( ! file_put_contents( $uploads['basedir'] . '/.htaccess', "deny from all" ) ) {
					return false;
				}
				chmod( $uploads['basedir'] . '/.htaccess', 0644 );
			}
		}
		if ( $uid = get_current_user_id() ) {
			$uploads['basedir'] .= '/' . $uid;
			if ( ! is_dir( $uploads['basedir'] ) ) {
				if ( ! wp_mkdir_p( $uploads['basedir'] ) ) {
					return false;
				}
			}
			if ( $private ) {
				chmod( $uploads['basedir'], 0700 );
			}
		}

		$filename = wp_unique_filename( $uploads['basedir'], $file['name'], $unique_filename_callback );

		// Move the file to the uploads dir.
		$new_file = $uploads['basedir'] . "/$filename";
		if ( 'wp_handle_upload' === $action ) {
			$move_new_file = @ move_uploaded_file( $file['tmp_name'], $new_file );
		} else {
			// use copy and unlink because rename breaks streams.
			$move_new_file = @ copy( $file['tmp_name'], $new_file );
			unlink( $file['tmp_name'] );
		}

		if ( false === $move_new_file ) {
			if ( 0 === strpos( $uploads['basedir'], ABSPATH ) ) {
				$error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
			} else {
				$error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];
			}

			return $upload_error_handler( $file, sprintf( __( 'The uploaded file could not be moved to %s.' ), $error_path ) );
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		// Compute the URL.
		$url = $uploads['url'] . "/$filename";

		if ( is_multisite() ) {
			delete_transient( 'dirsize_cache' );
		}

		/**
		 * Filters the data array for the uploaded file.
		 *
		 * @since 2.1.0
		 *
		 * @param array $upload {
		 *     Array of upload data.
		 *
		 * @type string $file Filename of the newly-uploaded file.
		 * @type string $url URL of the uploaded file.
		 * @type string $type File type.
		 * }
		 *
		 * @param string $context The type of upload action. Values include 'upload' or 'sideload'.
		 */
		return wpp_apply_filters( 'wp_handle_upload', array(
			'file' => $new_file,
			'url'  => $url,
			'type' => $type
		), 'wp_handle_sideload' === $action ? 'sideload' : 'upload' );
	}

	public function save_attachment_meta() {
		global $post;
		if ( isset( $_POST['is_private'] ) ) {
			update_post_meta( $post->ID, 'is_private', absint( $_POST['is_private'] ) );
		}
	}

	public function add_attachment_meta() {
		add_meta_box( 'private-meta-box',
			'Confidentiality',
			[ $this, 'private_meta_box_callback' ],
			'attachment',
			'normal',
			'low' );
	}

	public function private_meta_box_callback() {
		global $post;
		$value = get_post_meta( $post->ID, 'is_private', true );
		?>
		<p>Confidentiality</p>
		<select name="is_private" required="required">
			<option value="0" <?php selected( 0, $value ); ?> >Public</option>
			<option value="1" <?php selected( 1, $value ); ?> >Private</option>
		</select>
		<?php
	}

	/**
	 * @param $attach_id
	 *
	 * @return bool
	 */
	function delete_file( $attach_id ) {
		$user_id = get_current_user_id();

		$attachment = get_post( $attach_id );
		if ( $user_id == $attachment->post_author || wpp_can( 'delete_private_uploads' ) ) {
			wp_delete_attachment( $attach_id, true );

			return true;
		}

		return false;
	}

	function associate_file( $attach_id, $post_id ) {
		wp_update_post( array(
			'ID'          => $attach_id,
			'post_parent' => $post_id
		) );
	}
}