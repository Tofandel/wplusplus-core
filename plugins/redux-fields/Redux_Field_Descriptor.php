<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 12/11/2018
 * Time: 22:49
 */

class Redux_Field_Descriptor {
	protected $field;
	protected $name;
	protected $description;
	protected $icon;

	/**
	 * @var Redux_Field_Descriptor_Fields[]
	 */
	protected $fields = array();

	protected $currentField;

	public function __construct( $field ) {
		Redux_Field_Descriptor_Fields::$order = 0;
		$this->field                          = $field;
	}

	public function setInfo( $name, $description = '', $icon = '' ) {
		$this->name        = $name;
		$this->description = $description;
		$this->icon        = $icon;
	}

	public function getName() {
		return $this->name;
	}

	/**
	 * @param        $name
	 * @param        $title
	 * @param        $type
	 * @param string $description
	 * @param null   $default
	 *
	 * @return Redux_Field_Descriptor_Fields
	 */
	public function addField( $name, $title, $type, $description = '', $default = null ) {
		$this->fields[ $name ] = new Redux_Field_Descriptor_Fields( $name, $title, $type, $description, $default );
		$this->currentField    = $name;

		return $this->fields[ $name ];
	}

	public function parseRequest( $req ) {
		$parsed_req = array();
		foreach ( $req as $k => $v ) {
			if ( isset( $this->fields[ $k ] ) ) {
				$parsed_req[ $k ] = $v;
			}
		}

		return $parsed_req;
	}

	/**
	 * Selects and returns a field or the current field
	 *
	 * @param string $fieldName
	 *
	 * @return mixed|null
	 */
	public function field( $fieldName = '' ) {
		if ( ! empty( $fieldName ) ) {
			$this->currentField = $fieldName;
		}

		if ( isset( $this->fields[ $this->currentField ] ) ) {
			return $this->fields[ $this->currentField ];
		} else {
			return null;
		}
	}

	public function removeField( $name ) {
		unset( $this->fields[ $name ] );
	}

	public function toDoc() {
		$doc = $this->name . "\n" . $this->description . "\n";
		$doc .= 'Fields:';
		$this->sortFields();
		foreach ( $this->fields as $option ) {
			$doc .= $option->toDoc();
		}
	}

	protected function sortFields() {
		usort( $this->fields, function ( $item1, $item2 ) {
			if ( $item1[ 'order' ] == $item2[ 'order' ] ) {
				return 0;
			}

			return $item1[ 'order' ] < $item2[ 'order' ] ? - 1 : 1;
		} );
	}

	public function toArray() {
		$fields = array();

		$this->sortFields();
		foreach ( $this->fields as $option ) {
			if ( $option[ 'name' ] != 'order' ) {
				$fields[ $option[ 'name' ] ] = $option->toArray();
			}
		}

		return array(
			'type'        => $this->field_type,
			'name'        => $this->name,
			'description' => $this->description,
			'icon'        => $this->icon,
			'fields'      => $fields
		);
	}
}