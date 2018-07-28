<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 28/07/2018
 * Time: 12:56
 */

namespace Tofandel\Core\Modules;


use Tofandel\Core\Interfaces\SubModule;
use Tofandel\Core\Interfaces\WP_Plugin;

final class LicenseManager implements SubModule, \Tofandel\Core\Interfaces\LicenseManager {
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

	public function __construct( WP_Plugin &$parent = null ) {
		self::__parentConstruct( $parent );
		$this->version    = $parent->getVersion();
		$this->product_id = $parent->getSlug();
		$this->api_url    = $parent->getDownloadUrl();
		$this->instance   = str_replace( '/', '', network_site_url( '', 'relative' ) );

		$this->email   = $this->parent->getLicenseEmail();
		$this->api_key = $this->parent->getLicenseKey();
	}

	private function setMessage( $message ) {
		$this->message = $message;
	}

	public function getMessage() {
		return $this->message;
	}

	public function activateLicense() {
		$data = $this->doRequest( 'activation', array( 'instance' => $this->instance ) );
		if ( $data['activated'] == true ) {
			$this->setMessage( $data['message'] );
			$this->setLicenseTransient();

			return true;
		} else {
			$this->setMessage( $data['error'] );
		}

		return false;
	}

	private function doRequest( $request, $args = array() ) {
		$data = array( 'email' => $this->email, 'license_key' => $this->api_key, 'product_id' => $this->product_id );
		$data = array_merge( $args, $data );

		return wp_remote_get( trailingslashit( $this->api_url ) . 'woocommerce/?wc-api=software-api&request=' . $request . '&' . http_build_query( $data ) );
	}

	private function setLicenseTransient() {
		// Check every day
		set_transient( $this->product_id . '_' . $this->instance . '_license_valid', '1', 86400 );
	}

	private function unsetLicenseTransient() {
		delete_transient( $this->product_id . '_' . $this->instance . '_license_valid' );
	}

	private function checkLicenseTransient() {
		if ( get_transient( $this->product_id . '_' . $this->instance . '_license_valid' ) == '1' ) {
			return true;
		}

		return false;
	}

	public function checkLicense() {
		if ( $this->checkLicenseTransient() ) {
			return true;
		}

		$data = $this->doRequest( 'status' );
		if ( $data['status_check'] == 'active' ) {
			$this->setMessage( $data['message'] );
			$this->setLicenseTransient();

			return true;
		} else {
			$this->setMessage( $data['error'] );
		}

		return false;
	}

	public function deactivateLicense() {
		$data = $this->doRequest( 'deactivation', array( 'instance' => $this->instance ) );
		if ( $data['deactivated'] == true ) {
			$this->setMessage( $data['activations_remaining'] );
			$this->unsetLicenseTransient();

			return true;
		} else {
			$this->setMessage( $data['error'] );
		}

		return false;
	}

	/**
	 * Called function on plugin activation
	 */
	public function activated() {
		$this->activateLicense();
	}

	public function deactivated() {
		$this->deactivateLicense();
	}

	/**
	 * The hooks of the submodule
	 */
	public function actionsAndFilters() {
		add_action( 'wpp_redux_' . $this->parent->getReduxOptName() . '_config', [ $this, 'addSection' ] );
	}

	public function addSection() {
		if ( isset( $this->parent->redux_config ) ) {
			$this->parent->redux_config->setSection( array(
				'desc'   => __( "Plugin's License", $this->parent->getTextDomain() ),
				'id'     => 'license',
				'icon'   => 'el el-shopping-cart', //'el el-key'
				'fields' => array(
					array(
						'title'             => __( 'License Email Address', $this->parent->getTextDomain() ),
						'id'                => 'license_email',
						'type'              => 'text',
						'validate_callback' => function ( $field, $value, $existing_value ) {
							$error = false;
							if ( ! is_email( $field ) ) {
								$error = true;
								$value = '';
							} else {
								$this->email = $value;
							}
							$return['value'] = $value;
							if ( $error == true ) {
								$return['error'] = $field;
							}

							return $return;
						}
					),
					array(
						'title'             => __( 'License Key', $this->parent->getTextDomain() ),
						'id'                => 'license_key',
						'type'              => 'text',
						'validate_callback' => function ( $field, $value, $existing_value ) {
							$this->api_key = $value;

							$error = ! $this->activateLicense();

							$field['msg'] = $this->getMessage();

							$return['value'] = $value;
							if ( $error == true ) {
								$return['error'] = $field;
							}

							return $return;
						}
					),
				)
			) );
		}
	}

	/**
	 * Called when the plugin is updated
	 *
	 * @param $last_version
	 */
	public function upgrade( $last_version ) {
	}
}