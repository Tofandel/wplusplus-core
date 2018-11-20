<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 15/11/2018
 * Time: 11:25
 */

class Redux_Builder_Api extends Redux_Class {

	const ENDPOINT = 'redux_framework';
	const VER = 'v1';

	public function getNamespace() {
		return self::ENDPOINT . '/' . self::VER;
	}

	public function getUrl( $route ) {
		return rest_url( trailingslashit( $this->getNamespace() ) . ltrim( '/', $route ) );
	}

	public function __construct( $parent = null ) {
		parent::__construct( $parent );
		add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
	}

	public function rest_api_init() {
		register_rest_route( $this->getNamespace(), '/fields', array(
			'methods'  => 'GET',
			'callback' => [ $this, 'list_fields' ],
		) );
		register_rest_route( $this->getNamespace(), '/field/(?P<name>[a-z0-9]+)', array(
			'methods'  => 'GET',
			'callback' => [ $this, 'get_field' ],
		) );
		register_rest_route( $this->getNamespace(), '/field/(?P<name>[a-z0-9]+)/render', array(
			'methods'  => 'POST',
			'callback' => [ $this, 'render_field' ],
		) );
	}

	private function getSubclassesOf( $parent ) {
		$result = array();
		foreach ( get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, $parent ) ) {
				$result[] = $class;
			}
		}

		return $result;
	}

	public function list_fields( $data ) {
		$classes = $this->getSubclassesOf( 'Redux_Field' );
		$fields  = array();
		foreach ( $classes as $field ) {
			/**
			 * @var Redux_Field_Descriptor $descriptor
			 */
			$descriptor                       = call_user_func( [
				'ReduxFramework_' . $data[ 'name' ],
				'getDescriptor'
			] );
			$fields[ $descriptor->getName() ] = $descriptor->toArray();
		}

		return $fields;
	}

	public function get_field( $data ) {
		if ( ! empty( $data[ 'name' ] ) && is_subclass_of( 'ReduxFramework_' . $data[ 'name' ], 'Redux_Field' ) ) {
			/**
			 * @var Redux_Field_Descriptor $descriptor
			 */
			$descriptor = call_user_func( [ 'ReduxFramework_' . $data[ 'name' ], 'getDescriptor' ] );

			return $descriptor->toArray();
		}

		return array( 'success' => false );
	}

	public function render_field( $data ) {
		if ( ! empty( $data[ 'name' ] ) && is_subclass_of( 'ReduxFramework_' . $data[ 'name' ], 'Redux_Field' ) ) {
			try {
				$class = new ReflectionClass( 'ReduxFramework_' . $data[ 'name' ] );
			} catch ( ReflectionException $e ) {
				return array( 'success' => false );
			}
			/**
			 * @var Redux_Field_Descriptor $descriptor
			 */
			$descriptor = call_user_func( [ 'ReduxFramework_' . $data[ 'name' ], 'getDescriptor' ] );
			$req        = $descriptor->parseRequest( $_POST );
			$class->newInstance( $req, $_POST[ 'example_values' ], $this->parent );
		}

		return array( 'success' => false );
	}
}