<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Objects;


use Tofandel\Core\Traits\Initializable;

class ShortcodeParameter {
	use Initializable;

	protected $name;
	protected $label;
	protected $description;
	protected $type;
	protected $default;
	protected $category;
	protected $choices;

	protected $options = array();

	const T_HIDDEN = 'hidden';
	const T_WARNING = 'warning';

	const T_CHOICE = 'choice';
	const T_BOOL = 'bool';
	const T_TEXT = 'text';
	const T_LONGTEXT = 'longtext';
	const T_NUMBER = 'number';
	const T_IMAGE = 'image';
	const T_IMAGES = 'images';
	const T_COLOR = 'color';
	const T_RAWHTML = 'rawhtml';
	const T_LINK = 'link';
	const T_CSS = 'css';

	const T_PAGE = 'page';
	const T_POST = 'post';

	private static $default_atts = array();

	public static function setDefaultAttributes( $atts ) {
		static::$default_atts = $atts;
	}

	public function __construct( $name, $label, $type, $description = '', $category = '' ) {
		$this->name        = $name;
		$this->label       = $label;
		$this->type        = $type;
		$this->category    = $category;
		$this->description = $description;
		$this->default     = isset( static::$default_atts[ $name ] ) ? static::$default_atts[ $name ] : null;
	}

	public function getName() {
		return $this->name;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getCategory() {
		return $this->category;
	}

	public function getDescription() {
		return $this->category;
	}

	public function getDefault() {
		return $this->default;
	}

	public function getType() {
		return $this->type;
	}

	public function setChoices( $choices ) {
		$this->choices = $choices;
	}

	public function getChoices() {
		return $this->choices;
	}

	public function setLabel( $label ) {
		$this->label = $label;
	}

	public function setDescription( $description ) {
		$this->description = $description;
	}

	public function setMapperOptions( $mapper, $options ) {
		$this->options[ $mapper ] = $options;
	}

	public function getMapperOptions( $mapper ) {
		return $this->options[ $mapper ];
	}

	public function setCategory( $category ) {
		$this->category = $category;
	}

	public function setDefault( $default ) {
		$this->default = $default;
	}


	public function mapToDoc() {
		//TODO
	}

}