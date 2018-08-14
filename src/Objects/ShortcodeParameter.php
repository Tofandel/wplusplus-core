<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Objects;


class ShortcodeParameter {
	protected $name;
	protected $label;
	protected $description;
	protected $type;
	protected $default;
	protected $options;
	protected $category;

	private $vc_mapping = array(
		self::T_RAWHTML => 'textarea_raw_html',
		self::T_LINK    => 'vc_link',
		self::T_IMAGE   => 'attach_image',
		self::T_IMAGES  => 'attach_images'
	);

	const T_HIDDEN = 'hidden';
	const T_WARNING = 'warning';

	const T_CHOICE = 'dropdown';
	const T_BOOL = 'checkbox';
	const T_TEXT = 'textfield';
	const T_LONGTEXT = 'textarea';
	const T_NUMBER = 'number';
	const T_IMAGE = 'image';
	const T_IMAGES = 'images';
	const T_COLOR = 'colorpicker';
	const T_RAWHTML = 'rawhtml';
	const T_LINK = 'link';
	const T_CSS = 'css';

	const T_PAGE = 'page';
	const T_POST = 'post';

	private static $default_atts = array();

	public static function setDefaultAttributes( $atts ) {
		static::$default_atts = $atts;
	}

	public function __construct( $name, $label, $type, $category = '' ) {
		$this->name     = $name;
		$this->label    = $label;
		$this->type     = $type;
		$this->category = $category;
		$this->default  = isset( static::$default_atts[ $name ] ) ? static::$default_atts[ $name ] : '';
	}

	public function getName() {
		return $this->name;
	}

	public function setLabel( $label ) {
		$this->label = $label;
	}

	public function setDescription( $description ) {
		$this->description = $description;
	}

	public function setOptions( $options ) {
		$this->options = $options;
	}

	public function setCategory( $category ) {
		$this->category = $category;
	}


	public function mapToVC() {
		//TODO
	}

	public function mapToDoc() {

	}
}