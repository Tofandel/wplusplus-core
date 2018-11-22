<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Objects;


class ShortcodeParameter {
	static $autoOrder = 0;

	protected $name;
	protected $label;
	protected $description;
	protected $type;
	protected $default;
	protected $category;
	protected $choices;
	protected $order;

	protected $mapperOptions = array();
	protected $moreOptions = array();

	private static $default_atts = array();

	public static function setDefaultAttributes( $atts ) {
		static::$default_atts = $atts;
	}

	/**
	 * ShortcodeParameter constructor.
	 *
	 * @param        $name
	 * @param        $label
	 * @param        $type
	 * @param string $description
	 * @param string $category
	 *
	 * @throws \Exception
	 */
	public function __construct( $name, $label, $type, $description = '', $category = '' ) {
		if ( ! ShortcodeParametersTypes::isValidType( $type ) ) {
			throw new \Exception( 'Unknown type ' . $type . ' for option ' . $name );
		}
		$this->name = $name;

		if ( ! is_string( $label ) || empty( $label ) ) {
			$this->label = ucfirst( $name );
		} else {
			$this->label = $label;
		}
		$this->order = static::$autoOrder++;
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

	public function getChoices() {
		return $this->choices;
	}

	public function setChoices( $choices ) {
		$this->choices = $choices;

		return $this;
	}

	public function getOrder() {
		return $this->order;
	}

	public function setOrder($order) {
		static::$autoOrder = $order;
		$this->order = (float) $order;

		return $this;
	}

	public function setLabel( $label ) {
		$this->label = $label;

		return $this;
	}

	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}

	public function setMapperOption( $mapper, $option, $value ) {
		$this->mapperOptions[ $mapper ][$option] = $value;

		return $this;
	}

	public function setMapperOptions( $mapper, $options ) {
		$this->mapperOptions[ $mapper ] = $options;

		return $this;
	}

	public function getMapperOptions( $mapper ) {
		return $this->mapperOptions[ $mapper ];
	}

	public function setCategory( $category ) {
		$this->category = $category;

		return $this;
	}

	public function setDefault( $default ) {
		$this->default = $default;

		return $this;
	}

	public function __get( $name ) {
		return isset( $this->moreOptions[ $name ] ) ? $this->moreOptions[ $name ] : null;
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 * @return self
	 */
	public function set($name, $value) {
		$this->__set($name, $value);

		return $this;
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get($name) {
		return $this->__get($name);
	}

	public function __set( $name, $value ) {
		$this->moreOptions[ $name ] = $value;
	}

	public function __isset( $name ) {
		return isset( $this->moreOptions[ $name ] );
	}
}