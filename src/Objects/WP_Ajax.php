<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Objects;


use Tofandel\WPlusPlusCore;

class WP_Ajax {
	const T_SUCCESS = 'success';
	const T_ERROR = 'error';
	const T_WARNING = 'warning';
	const T_INFO = 'info';
	const T_QUESTION = 'question';
	const T_NO_TYPE = '';
	public $response = array();
	protected $action;
	protected $func;
	protected $private;
	protected $code;
	protected $referer;
	private $is_called = false;

	/**
	 * WP_Ajax constructor.
	 *
	 * @param string $action
	 * @param callable $func
	 * @param bool $private
	 * @param string $referer
	 */
	public function __construct( $action, $func, $private = false, $referer = null ) {
		add_action( 'wp_ajax_' . $action, [ $this, 'call' ], 10, 0 );
		if ( ! $private ) {
			add_action( 'wp_ajax_nopriv_' . $action, [ $this, 'call' ], 10, 0 );
		} else {
			if ( $referer == true ) {
				$referer = $action;
			}
			$this->referer = $referer;
		}

		$this->action = $action;

		$this->func = $func;

		$this->private = $private;

		/*
				add_action('wp_enqueue_scripts', function () {
					wp_add_inline_script( 'wp-ajax', 'var ajax_url=' . admin_url( 'admin-ajax.php' ) . ';' );
				});*/
	}

	public function call() {
		if ( isset( $this->referer ) ) {
			check_ajax_referer( $this->referer );
		}
		$this->is_called = true;
		call_user_func( $this->func, $this );
		$this->send();
	}

	/**
	 * @param $obj
	 *
	 * @die
	 */
	public function send( $obj = null ) {
		$this->is_called = false;
		wp_send_json( isset( $obj ) ? array_merge( $this->response, (array) $obj ) : $this->response );
		die();
	}

	public function __destruct() {
		if ( $this->is_called ) {
			$this->send();
		}
	}

	public function silentSuccess() {
		$this->addResponse( 'OK', true );
		$this->send();
	}

	/**
	 * @param $key
	 * @param $val
	 */
	public function addResponse( $key, $val ) {
		if ( func_num_args() < 2 ) {
			return;
		}
		$ref = &$this->response;
		for ( $i = 0; $i < func_num_args() - 2; ++ $i ) {
			$key = func_get_arg( $i );
			if ( isset( $ref->{$key} ) ) {
				$ref = &$ref->$key;
			} elseif ( isset( $ref[ $key ] ) ) {
				$ref = &$ref[ $key ];
			} else {
				$ref[ $key ] = array();
				$ref         = &$ref[ $key ];
			}
		}
		$key   = func_get_arg( func_num_args() - 2 );
		$value = func_get_arg( func_num_args() - 1 );

		if ( is_array( $ref ) ) {
			$ref[ $key ] = $value;
		} elseif ( is_object( $ref ) ) {
			$ref->$key = $value;
		}
	}

	public function silentError() {
		$this->addResponse( 'OK', false );
		$this->send();
	}

	/**
	 * Only for poedit
	 */
	public function typeStr() {
		__( 'Success', WPlusPlusCore::TextDomain() );
		__( 'Error', WPlusPlusCore::TextDomain() );
		__( 'Warning', WPlusPlusCore::TextDomain() );
		__( 'Info', WPlusPlusCore::TextDomain() );
		__( 'Question', WPlusPlusCore::TextDomain() );
	}

	/**
	 * @param $url
	 */
	public function redirect( $url ) {
		$this->addResponse( 'OK', true );
		$this->addResponse( 'redirect', $url );
	}

	/**
	 * @param $type
	 * @param string $msg
	 * @param string $msg2
	 * @param bool $redirect
	 * @param int|false $delay
	 */
	public function swalRedirect( $type, $redirect = false, $delay = false, $msg = "", $msg2 = "" ) {
		if ( empty( $msg ) ) {
			$msg = __( ucfirst( $type ), WPlusPlusCore::TextDomain() );
		}

		if ( $type == self::T_ERROR ) {
			$this->addResponse( 'OK', false );
		} else {
			$this->addResponse( 'OK', true );
		}

		$this->addResponse( 'swal', 'type', $type );
		$this->addResponse( 'swal', 'title', $msg );
		$this->addResponse( 'swal', 'text', $msg2 );

		$this->addResponse( 'redirect', $redirect );

		if ( $delay ) {
			$this->addResponse( 'swal', 'showConfirmButton', false );
			$this->addResponse( 'swal', 'timer', $delay );
		}
	}

	public function check_nonce( $nonce ) {
		if ( ! check_ajax_referer( $nonce, '_wpnonce', false ) ) {
			$this->swal( 'error', __( 'Invalid nonce', WPlusPlusCore::TextDomain() ), __( 'It has probably expired, you should try again!', WPlusPlusCore::TextDomain() ) );
			$this->send();
			die();
		}
	}

	/**
	 * @param $type
	 * @param string $msg
	 * @param string $msg2
	 * @param string|bool $confirm_button
	 * @param string|bool $cancel_button
	 */
	public function swal( $type, $msg = "", $msg2 = "", $confirm_button = null, $cancel_button = false ) {
		if ( empty( $msg ) ) {
			$msg = __( ucfirst( $type ), WPlusPlusCore::TextDomain() );
		}

		if ( $type == self::T_ERROR ) {
			$this->addResponse( 'OK', false );
		} else {
			$this->addResponse( 'OK', true );
		}

		$this->addResponse( 'swal', 'type', $type );
		$this->addResponse( 'swal', 'title', $msg );
		$this->addResponse( 'swal', 'text', $msg2 );

		if ( $confirm_button === false ) {
			$this->addResponse( 'swal', 'showConfirmButton', false );
		} else {
			$this->addResponse( 'swal', 'confirmButtonText', isset( $confirm_button ) ? $confirm_button : __( 'Ok', WPlusPlusCore::TextDomain() ) );
		}


		if ( $cancel_button === false ) {
			$this->addResponse( 'swal', 'showCancelButton', false );
		} else {
			$this->addResponse( 'swal', 'cancelButtonText', isset( $cancel_button ) ? $cancel_button : __( 'Cancel', WPlusPlusCore::TextDomain() ) );
		}
	}
}