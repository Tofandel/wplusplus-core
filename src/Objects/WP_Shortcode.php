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

namespace Tofandel\Core\Objects;

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
		$this->name         = $name;
		$this->function     = $function;
		$this->default_atts = $default_atts;

		add_shortcode( wpp_slugify( $name ), [ $this, 'call' ] );
		add_shortcode( $this->name, [ $this, 'call' ] );

		self::$shortcodes[ $this->name ] = &$this;
	}

	public function call( $attr, $content, $shortcode ) {
		$attr = shortcode_atts( $this->default_atts, $attr, $shortcode );
		//$content = do_shortcode($content);
		//This makes tree execution ascendant (the default behavior being descendant)
		//This might not be a desired effect if for example you need the shortcodes tag during the execution of a parent shortcode
		//Unfortunately that is the case for me so not including this functionality :D
		//As such you'll have to keep calling do_shortcode() on the shortcode's content
		if ( has_filter( 'override_shortcode_' . $this->name ) ) {
			return apply_filters( 'override_shortcode_' . $this->name, $attr, $content, $shortcode );
		} elseif ( has_filter( 'shortcode_' . $this->name ) ) {
			return apply_filters( 'shortcode_' . $this->name, call_user_func( $this->function, $attr, $content, $shortcode ), $attr, $content, $shortcode );
		} else {
			return call_user_func( $this->function, $attr, $content, $shortcode );
		}
	}
}