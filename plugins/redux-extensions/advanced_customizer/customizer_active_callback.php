<?php

if ( ! class_exists( 'Redux_Customizer_Active_Callback' ) ) {
	/**
	 * Callback class for use with the "required" argument
	 */
	class Redux_Customizer_Active_Callback {
		/**
		 * Figure out whether the current object should be displayed or not.
		 *
		 * @param  WP_Customize_Setting $object The current field.
		 *
		 * @return boolean
		 */
		public static function evaluate( $object ) {
			// Get all fields.
			$opt_name = explode( '[', $object->setting->id );
			$opt_name = $opt_name[0];

			$id = str_replace( $opt_name . '[', '', str_replace( ']', '', $object->setting->id ) );

			$field = Redux::getField( $opt_name, $id );

			// Make sure the current object matches a registered field.
			if ( ! isset( $object->setting->id ) || ! isset( $field ) ) {
				return true;
			}
			$show = true;
			if ( isset( $field['required'] ) ) {
				$show = self::evaluate_requirement( $object, $field, $field['required'], $opt_name );
				if ( ! $show ) {
					return false;
				}
			}

			return $show;
		}

		/**
		 *
		 * @param  WP_Customize_Setting $object The current field.
		 * @param  object $field The current object.
		 * @param  array $requirement
		 * @param  string $opt_name
		 *
		 * @return boolean
		 */
		private static function evaluate_requirement( $object, $field, $requirement, $opt_name ) {
			$show = true;
			if ( ! is_array( $requirement[0] ) && count( $requirement ) == 3 ) {

				$requirement_id = $opt_name . '[' . $requirement[0] . ']';

				$current_setting_object = $object->manager->get_setting( $requirement_id );
				// If the object is hidden then there is no response in getting a setting.
				if ( $current_setting_object ) {
					$current_setting = $current_setting_object->value();

					$show = self::compare( $requirement['2'], $current_setting, $requirement['1'] );
				} else {
					$show = true;
				}

			} else if ( is_array( $requirement[0] ) ) {
				foreach ( $requirement as $required ) {
					if ( ! is_array( $required[0] ) && count( $required ) == 3 ) {
						$requirement_id         = $opt_name . '[' . $required[0] . ']';
						$current_setting_object = $object->manager->get_setting( $requirement_id );
						// If the object is hidden then there is no response in getting a setting.
						if ( $current_setting_object ) {
							$current_setting = $current_setting_object->value();
							$show            = self::compare( $required['2'], $current_setting, $required['1'] );
						} else {
							$show = true;
						}
						// if one returns false then hide item.
						if ( ! $show ) {
							return false;
						}
					}
				}
			}

			return $show;
		}

		/**
		 * Compares the 2 values given the condition
		 *
		 * @param mixed $value1 The 1st value in the comparison.
		 * @param mixed $value2 The 2nd value in the comparison.
		 * @param string $operator The operator we'll use for the comparison.
		 *
		 * @return boolean whether The comparison has succeded (true) or failed (false).
		 */
		public static function compare( $value1, $value2, $operator ) {
			switch ( $operator ) {
				case '===':
					$show = ( $value1 === $value2 ) ? true : false;
					break;
				case '==':
				case '=':
				case 'equals':
				case 'equal':
					$show = ( $value1 == $value2 ) ? true : false;
					break;
				case '!==':
					$show = ( $value1 !== $value2 ) ? true : false;
					break;
				case '!=':
				case 'not equal':
					$show = ( $value1 != $value2 ) ? true : false;
					break;
				case '>=':
				case 'greater or equal':
				case 'equal or greater':
					$show = ( $value1 >= $value2 ) ? true : false;
					break;
				case '<=':
				case 'smaller or equal':
				case 'equal or smaller':
					$show = ( $value1 <= $value2 ) ? true : false;
					break;
				case '>':
				case 'greater':
					$show = ( $value1 > $value2 ) ? true : false;
					break;
				case '<':
				case 'smaller':
					$show = ( $value1 < $value2 ) ? true : false;
					break;
				case 'contains':
				case 'in':
					if ( is_array( $value1 ) && ! is_array( $value2 ) ) {
						$array  = $value1;
						$string = $value2;
					} elseif ( is_array( $value2 ) && ! is_array( $value1 ) ) {
						$array  = $value2;
						$string = $value1;
					}
					if ( isset( $array ) && isset( $string ) ) {
						if ( ! in_array( $string, $array ) ) {
							$show = false;
						}
					} else {
						if ( false === strrpos( $value1, $value2 ) && false === strpos( $value2, $value1 ) ) {
							$show = false;
						}
					}
					break;
				default:
					$show = ( $value1 == $value2 ) ? true : false;
			}
			if ( isset( $show ) ) {
				return $show;
			}

			return true;
		}
	}
}