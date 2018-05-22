<?php

namespace Tofandel\Objects;

use function Tofandel\wpp_slugify;

/**
 * Class WP_Shortcode
 * @package Abstracts
 *
 * @author Adrien Foulon <tofandel@tukan.hu>
 */
class WP_Shortcode {
	static $shortcodes = array();

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var callable
	 */
	public $function;

	public $default_atts;

	public function __construct( $name, $function, $default_atts = array() ) {
		$this->name         = wpp_slugify( $name );
		$this->function     = $function;
		$this->default_atts = $default_atts;

		add_shortcode( $name, [ $this, 'call' ] );

		self::$shortcodes[ $this->name ] = &$this;
	}

	public function call( $attr, $content, $shortcode ) {
		$attr = shortcode_atts( $this->default_atts, $attr, $shortcode );
		if ( has_filter( 'override_shortcode_' . $this->name ) ) {
			return apply_filters( 'override_shortcode_' . $this->name, $attr, $content, $shortcode );
		} elseif ( has_filter( 'shortcode_' . $this->name ) ) {
			return apply_filters( 'shortcode_' . $this->name, call_user_func( $this->function, $attr, $content, $shortcode ), $attr, $content, $shortcode );
		} else {
			return call_user_func( $this->function, $attr, $content, $shortcode );
		}
	}
}