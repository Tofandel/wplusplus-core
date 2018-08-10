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
 * Date: 28/07/2018
 * Time: 12:56
 */

namespace Tofandel\Core\Modules;


use Tofandel\Core\Interfaces\SubModule;
use Tofandel\Core\Interfaces\WP_Plugin;

final class LicenceManager implements SubModule, \Tofandel\Core\Interfaces\LicenceManager {
	use \Tofandel\Core\Traits\SubModule {
		__construct as __parentConstruct;
	}

	private $product_id;
	private $version;
	private $api_url;
	private $instance;
	private $message = '';
	private $email = '';
	private $api_key = '';
	private $buy_url;

	public function __construct( WP_Plugin &$parent = null ) {
		self::__parentConstruct( $parent );
		$this->version    = $parent->getVersion();
		$this->product_id = $parent->getProductID();
		$this->api_url    = $parent->getDownloadUrl();
		$this->buy_url    = $parent->getBuyUrl();
		$this->instance   = str_replace( array( '/', 'https', 'http', ':' ), array( '' ), network_site_url() );

		$this->email   = $this->parent->getLicenceEmail();
		$this->api_key = $this->parent->getLicenceKey();
	}

	private function setMessage( $message ) {
		$this->message = $message;
	}

	public function getMessage() {
		return $this->message;
	}

	public function updateRequest() {
		if ( empty( $this->api_url ) ) {
			return false;
		}

		if ( ! $this->isActivated() ) {
			$this->activateLicence();
		}
		$request = array(
			'slug'             => $this->parent->getSlug(),
			'plugin_name'      => $this->parent->getName(),
			'version'          => $this->version,
			'product_id'       => $this->product_id,
			'api_key'          => $this->api_key,
			'activation_email' => $this->email,
			'instance'         => $this->instance,
			'domain'           => get_site_url(),
			'software_version' => $this->version
		);

		$data = wp_remote_retrieve_body( wp_remote_post( $this->api_url . 'woocommerce/?wc-api=upgrade-api&request=pluginupdatecheck', array(
			'method'   => 'POST',
			'timeout'  => 30,
			'blocking' => true,
			'body'     => $request,
			'cookies'  => array()
		) ) );

		$data = unserialize( $data );
		if ( empty( $data ) ) {
			return false;
		}
		$data = (array) $data;
		if ( ! empty( $data['errors'] ) ) {
			if ( in_array( 'no_activation', $data['errors'] ) ) {
				$this->activateLicence();
			}

			return false;
		}


		return $data;
	}

	private function doRequest( $request, $args = array() ) {
		$data = array(
			'email'       => $this->email,
			'licence_key' => $this->api_key,
			'product_id'  => $this->product_id,
			'platform'    => site_url(),
			'instance'    => $this->instance
		);
		$data = array_merge( $args, $data );

		try {
			$body = wp_remote_retrieve_body( wp_remote_post( $this->api_url . 'woocommerce/?wc-api=am-software-api&request=' . $request, array(
				'method'   => 'POST',
				'timeout'  => 30,
				'blocking' => true,
				'body'     => $data,
				'cookies'  => array()

			) ) );

			return json_decode( $body, true );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	private function setActivated() {
		// Check every day
		set_transient( $this->product_id . '_' . $this->instance . '_activated', $this->api_key );
	}

	private function setDeactivated() {
		// Check every day
		delete_transient( $this->product_id . '_' . $this->instance . '_activated' );
	}


	private function isActivated() {
		// Check every day
		return get_transient( $this->product_id . '_' . $this->instance . '_activated' ) === $this->api_key;
	}

	private function getActivatedApiKey() {
		return get_transient( $this->product_id . '_' . $this->instance . '_activated' );
	}


	private function setLicenceTransient() {
		// Check every day
		set_transient( $this->product_id . '_' . $this->instance . '_licence_valid', '1', 86400 );
	}

	private function unsetLicenceTransient() {
		delete_transient( $this->product_id . '_' . $this->instance . '_licence_valid' );
	}

	private function checkLicenceTransient() {
		if ( get_transient( $this->product_id . '_' . $this->instance . '_licence_valid' ) == '1' ) {
			return true;
		}

		return false;
	}

	public function hasCredentials() {
		return ! empty( $this->api_key ) && ! empty( $this->email );
	}

	public function checkLicence() {
		if ( ! $this->hasCredentials() ) {
			if ( $this->isActivated() ) {
				$this->setDeactivated();
			}

			return false;
		} elseif ( $this->activateLicence() ) {
			return true;
		}

		return false;
	}

	private function _checkLicence() {
		if ( $this->checkLicenceTransient() ) {
			return true;
		}

		$data = $this->doRequest( 'status' );
		if ( ! empty( $data['status_check'] ) && $data['status_check'] == 'active' ) {
			$this->setMessage( $data['activations_remaining'] );
			$this->setActivated();
			$this->setLicenceTransient();

			return true;
		} else {
			$this->setDeactivated();
			$this->setMessage( $data['error'] );
		}

		return false;
	}

	public function activateLicence() {
		if ( $this->isActivated() ) {
			return $this->_checkLicence();
		}
		$data = $this->doRequest( 'activation', array(
			'software_version' => $this->version
		) );
		if ( $data['activated'] == 'active' || $data['activated'] === true ) {
			$this->setMessage( $data['message'] );
			$this->setActivated();
			$this->setLicenceTransient();

			return true;
		} elseif ( $data['code'] == '104' ) {
			//Already activated
			return $this->_checkLicence();
		} else {
			$this->setDeactivated();
			$this->setMessage( $data['error'] );
		}

		return false;
	}

	public function deactivateLicence() {
		static $instance = array();

		if ( ! $this->isActivated() ) {
			return false;
		}
		if ( empty( $instance[ $this->instance ] ) ) {
			$data = $this->doRequest( 'deactivation', array( 'licence_key' => $this->getActivatedApiKey() ) );
			if ( $data['deactivated'] === true ) {
				$this->setMessage( $data['activations_remaining'] );
				$this->unsetLicenceTransient();
				$this->setDeactivated();
				$instance[ $this->instance ] = true;

				return true;
			} else {
				$this->setMessage( $data['error'] );
			}

			return false;
		} else {
			return true;
		}
	}

	/**
	 * Called function on plugin activation
	 */
	public function activated() {
		$this->activateLicence();
	}

	/**
	 * Called function on plugin deactivation
	 */
	public function deactivated() {
		$this->deactivateLicence();
	}

	/**
	 * The hooks of the submodule
	 */
	public function actionsAndFilters() {
		add_action( 'wpp_redux_' . $this->parent->getReduxOptName() . '_config', [ $this, 'addSection' ], 50, 1 );
	}

	public function addSection( ReduxFramework $framework ) {
		$url = parse_url( $this->buy_url );
		global $WPlusPlusCore;
		$framework->setSection( array(
			'title'  => __( "Licence key", $this->parent->getTextDomain() ),
			'desc'   => $this->parent->isLicensed() ? __( 'Your licence is active', $this->getTextDomain() ) : __( 'Your licence is inactive', $this->getTextDomain() ),
			'id'     => 'licence',
			'icon'   => 'el el-shopping-cart', //'el el-key'
			'fields' => array(
				array(
					'title'             => __( 'Licence Email Address', $this->parent->getTextDomain() ),
					'description'       => sprintf( esc_html__( 'If you have not yet purchased the product you can do it %shere%s', $WPlusPlusCore->getTextDomain() ),
						'<a href="' . $this->buy_url . '" target="_blank" rel="noopener">', '</a>' ),
					'id'                => 'licence_email',
					'type'              => 'text',
					'validate_callback' => function ( $field, $value, $existing_value ) use ( $WPlusPlusCore ) {
						$error = false;
						if ( ! is_email( $value ) ) {
							$error = true;
							$value = '';
						} else {
							$this->email = $value;
						}
						if ( $existing_value != $value && $this->isActivated() ) {
							$this->email = $existing_value;
							$this->deactivateLicence();
						}
						$return['value'] = $value;
						if ( $error == true ) {
							$return['msg']   = __( 'Invalid email', $WPlusPlusCore->getTextDomain() );
							$return['error'] = $field;
						}

						return $return;
					}
				),
				array(
					'title'             => __( 'Licence Key', $WPlusPlusCore->getTextDomain() ),
					'description'       => sprintf( esc_html__( 'If you have purchased the product you will find your api key %shere%s', $WPlusPlusCore->getTextDomain() ),
						'<a href="' . $url['scheme'] . '://' . $url['host'] . '/my-account/my-api-keys/" target="_blank" rel="noopener">', '</a>' ),
					'id'                => 'licence_key',
					'type'              => 'password',
					'class'             => 'regular-text',
					'username'          => false,
					'required'          => array(
						'licence_email',
						'contains',
						'@'
					),
					'validate_callback' => function ( $field, $value, $existing_value ) use ( $WPlusPlusCore ) {
						$return['value'] = $value;
						if ( empty( $value ) ) {
							$this->deactivateLicence();
							$this->setDeactivated();
							if ( ! empty( $existing_value ) ) {
								$field['msg'] = __( 'You must enter an Api key', $WPlusPlusCore->getTextDomain() );
							}
						} else {
							if ( $existing_value != $value && $this->isActivated() ) {
								$this->api_key = $existing_value;
								$this->deactivateLicence();
							}
							if ( $existing_value != $value || ! $this->isActivated() ) {
								$this->api_key = $value;

								$error = ! $this->activateLicence();

								$msg = $this->getMessage();
								if ( ! empty( $msg ) ) {
									$field['msg'] = $msg;
								}

								if ( $error == true ) {
									$return['error'] = $field;
								}
							}
						}

						return $return;
					}
				),
			)
		) );
	}

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public function upgrade( $last_version ) {
	}
}