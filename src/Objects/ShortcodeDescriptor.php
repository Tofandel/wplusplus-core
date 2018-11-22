<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 20/11/2018
 * Time: 16:37
 */

namespace Tofandel\Core\Objects;

use Tofandel\Core\ShortcodeMappers\VC_Mapper;

class ShortcodeDescriptor {
	protected $name;

	protected $title;
	protected $description;
	protected $category;
	protected $icon;

	/**
	 * @var ShortcodeParameter[]
	 */
	protected $parameters = array();

	protected $currentParam;

	protected $moreOptions;

	/**
	 * ShortcodeDescriptor constructor.
	 *
	 * @param $class
	 */
	public function __construct( $class ) {
		ShortcodeParameter::$autoOrder = 0;
		$this->name                    = call_user_func( array( $class, 'getName' ) );
	}

	public function getName() {
		return $this->name;
	}

	public function setInfo( $title, $description = '', $category = '', $icon = '' ) {
		$this->title       = $title;
		$this->description = $description;
		$this->category    = $category;
		$this->icon        = $icon;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getCategory() {
		return $this->category;
	}

	public function getIcon() {
		return $this->icon;
	}

	public function __get( $name ) {
		return isset( $this->moreOptions[ $name ] ) ? $this->moreOptions[ $name ] : null;
	}

	public function __set( $name, $value ) {
		$this->moreOptions[ $name ] = $value;
	}

	public function __isset( $name ) {
		return isset( $this->moreOptions[ $name ] );
	}

	public function addHTMLParameters() {
		global $WPlusPlusCore;
		$this->addParameter( 'html_id', __( 'ID', $WPlusPlusCore->getTextDomain() ), ShortcodeParametersTypes::TEXT, __( 'The HTML id of the element', $WPlusPlusCore->getTextDomain() ), __( 'HTML Attributes', $WPlusPlusCore->getTextDomain() ) );
		$this->addParameter( 'html_class', __( 'Class', $WPlusPlusCore->getTextDomain() ), ShortcodeParametersTypes::TEXT, __( 'The HTML class you want to add to the element', $WPlusPlusCore->getTextDomain() ), __( 'HTML Attributes', $WPlusPlusCore->getTextDomain() ) );
		return $this;
	}

	/**
	 * @param string|ShortcodeParameter $name
	 * @param string                    $title
	 * @param string                    $type ShortcodeParametersTypes::const
	 * @param string                    $description
	 * @param string                    $category
	 *
	 * @return ShortcodeParameter
	 */
	public function addParameter( $name, $title = '', $type = '', $description = '', $category = '' ) {
		if ( is_a( $name, ShortcodeParameter::class ) ) {
			$this->parameters[ $name->getName() ] = $name;
		} else {
			$this->parameters[ $name ] = new ShortcodeParameter( $name, $title, $type, $description, $category );
			$this->currentParam        = $name;
		}

		return $this->parameters[ $name ];
	}

	public function parseRequest( $req ) {
		$parsed_req = array();
		foreach ( $req as $k => $v ) {
			if ( isset( $this->parameters[ $k ] ) ) {
				$parsed_req[ $k ] = $v;
			}
		}

		return $parsed_req;
	}

	/**
	 * Selects and returns a field or the current field
	 *
	 * @param string $paramName
	 *
	 * @return mixed|null
	 */
	public function param( $paramName = '' ) {
		if ( ! empty( $paramName ) ) {
			$this->currentParam = $paramName;
		}

		if ( isset( $this->parameters[ $this->currentParam ] ) ) {
			return $this->parameters[ $this->currentParam ];
		} else {
			return null;
		}
	}

	public function removeParameter( $name ) {
		unset( $this->parameters[ $name ] );
	}

	protected function sortFields() {
		usort( $this->parameters, function ( ShortcodeParameter $item1, ShortcodeParameter $item2 ) {
			return $item1->getOrder() <=> $item2->getOrder();
		} );
	}

	/**
	 * @var ShortcodeMapper[]
	 */
	static $mappers;

	/**
	 * @return ShortcodeMapper[]
	 */
	public static function initMappers() {
		static $mappers;

		if ( ! isset( $mappers ) ) {
			self::$mappers = apply_filters( 'wpp_shortcode_mappers', array( VC_Mapper::class ) );
			foreach ( self::$mappers as $mapper ) {
				if ( $mapper::shouldMap() ) {
					$mappers[] = $mapper;
				}
			}
		}

		return $mappers;
	}
}