<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Modules;


use Tofandel\Core\Interfaces\SubModule;

class VC_Integration implements SubModule {
	use \Tofandel\Core\Traits\SubModule;

	public function actionsAndFilters() {
		add_action( 'vc_mapper_init_after', array( $this, 'integratetoVC' ), 11 );
	}

	public function integrateToVC() {
		//global $vc_params_list;
		// Remove default dropdown to be able to override it
		//unset( $vc_params_list[ array_search( 'dropdown', $vc_params_list ) ] );

		vc_add_shortcode_param( 'number', array( $this, 'createVCNumber' ) );
		vc_add_shortcode_param( 'multidropdown', array( $this, 'createVCMultiDropdown' ) );
		//vc_add_shortcode_param( 'wpp_dropdown', array( $this, 'createVCDropdown' ) );
		vc_add_shortcode_param( 'dimensions', array( $this, 'createVCDimensions' ) );
		vc_add_shortcode_param( 'warning', array( $this, 'createVCWarning' ) );
		vc_add_shortcode_param( 'pro', array( $this, 'createVCPro' ) );
		vc_add_shortcode_param( 'hidden', '__return_false' );
	}


	public function createVCPro( $settings ) {
		return '<i class="dashicons dashicons-lock"></i><strong>' . sprintf( esc_html( __( 'This is a pro feature, %sgo pro now !%s', $this->getTextDomain() ) ), '<a href="' . $settings['buy_url'] . '" target="_blank" rel="noopener">', '</a>' ) . '</strong>';
	}

	/**
	 * Create info box for VC editor
	 */
	public function createVCWarning( $settings, $value ) {
		return '<strong>' . $settings['message'] . '</strong>';
	}


	/**
	 * Create custom select field for VC
	 *
	 * @param array $settings
	 * @param string $value
	 *
	 * @return string
	 */
	public function createVCDropdown( $settings, $value ) {
		$output     = '';
		$css_option = str_replace( '#', 'hash-', vc_get_dropdown_option( $settings, $value ) );
		$output     .= '<div class="wpp-custom-select"><select name="'
		               . $settings['param_name']
		               . '" class="wpb_vc_param_value wpb-input wpb-select '
		               . $settings['param_name']
		               . ' ' . $settings['type']
		               . ' ' . $css_option
		               . '" data-option="' . $css_option . '">';
		if ( is_array( $value ) ) {
			$value = isset( $value['value'] ) ? $value['value'] : array_shift( $value );
		}
		if ( ! empty( $settings['value'] ) ) {
			foreach ( $settings['value'] as $index => $data ) {
				if ( is_numeric( $index ) && ( is_string( $data ) || is_numeric( $data ) ) ) {
					$option_label = $data;
					$option_value = $data;
				} elseif ( is_numeric( $index ) && is_array( $data ) ) {
					$option_label = isset( $data['label'] ) ? $data['label'] : array_pop( $data );
					$option_value = isset( $data['value'] ) ? $data['value'] : array_pop( $data );
				} else {
					$option_value = $index;
					$option_label = $data;
				}
				$selected            = '';
				$option_value_string = (string) $option_value;
				$value_string        = (string) $value;
				if ( '' !== $value && $option_value_string === $value_string ) {
					$selected = ' selected="selected"';
				}
				$option_class = str_replace( '#', 'hash-', $option_value );
				$output       .= '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $option_value ) . '" ' . $selected . '>'
				                 . htmlspecialchars( $option_label ) . '</option>';
			}
		}
		$output .= '</select></div>';

		return $output;
	}

	public function createVCMultiDropdown( $settings, $value ) {
		$output     = '';
		$css_option = str_replace( '#', 'hash-', vc_get_dropdown_option( $settings, $value ) );
		$output     .= '<div class="wpp-custom-select"><select multiple name="'
		               . $settings['param_name']
		               . '" class="wpb_vc_param_value wpb-input wpb-select '
		               . $settings['param_name']
		               . ' ' . $settings['type']
		               . ' ' . $css_option
		               . '" data-option="' . $css_option . '">';
		if ( ! empty( $settings['value'] ) ) {
			if ( ! is_array( $value ) ) {
				$param_value_arr = explode( ',', $value );
			} else {
				$param_value_arr = $value;
			}
			foreach ( $settings['value'] as $index => $data ) {
				if ( is_numeric( $index ) && ( is_string( $data ) || is_numeric( $data ) ) ) {
					$option_label = $data;
					$option_value = $data;
				} elseif ( is_numeric( $index ) && is_array( $data ) ) {
					$option_label = isset( $data['label'] ) ? $data['label'] : array_pop( $data );
					$option_value = isset( $data['value'] ) ? $data['value'] : array_pop( $data );
				} else {
					$option_value = $data;
					$option_label = $index;
				}
				$selected = '';

				$option_value_string = (string) $option_value;
				if ( $value !== '' && in_array( $option_value_string, $param_value_arr ) ) {
					$selected = ' selected="selected"';
				}
				$option_class = str_replace( '#', 'hash-', $option_value );
				$output       .= '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $option_value ) . '" ' . $selected . '>'
				                 . htmlspecialchars( $option_label ) . '</option>';
			}
		}
		$output .= '</select></div>';

		return $output;
	}

	/**
	 * Create number field for VC
	 *
	 * @param array $settings
	 * @param string $value
	 *
	 * @return string
	 */
	public function createVCNumber( $settings, $value ) {
		$value = isset( $settings['value'] ) && is_null( $value ) ? $settings['value'] : $value;

		if ( isset( $settings['extra']['responsive'] ) && $settings['extra']['responsive'] === true ) {
			$responsive_values = json_decode( str_replace( "'", '"', $value ), true );
			$html              = '<div class="responsive-number-set">' .
			                     '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-textinput responsive-number ' .
			                     esc_attr( $settings['param_name'] ) . ' ' .
			                     esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" ' . ( isset( $settings['extra']['min'] ) ? ' min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . '/>' .
			                     '<div class="responsive-field-icon"><i class="fa fa-desktop"></i></div><input type="number" value="' . esc_attr( $responsive_values['desktop'] ) . '" ' . ( isset( $settings['extra']['min'] ) ? 'min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . ' data-responsive="desktop">' .
			                     ( ! isset( $settings['extra']['bootstrap'] ) || $settings['extra']['bootstrap'] != true ? '<div class="responsive-field-icon"><i class="fa fa-laptop"></i></div><input type="number" value="' . esc_attr( $responsive_values['laptop'] ) . '" ' . ( isset( $settings['extra']['min'] ) ? 'min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . ' data-responsive="laptop">' : '' ) .
			                     '<div class="responsive-field-icon"><i class="fa fa-tablet fa-rotate-90"></i></div><input type="number" value="' . esc_attr( $responsive_values['tablet-landscape'] ) . '" ' . ( isset( $settings['extra']['min'] ) ? ' min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . ' data-responsive="tablet-landscape">' .
			                     '<div class="responsive-field-icon"><i class="fa fa-tablet"></i></div><input type="number" value="' . esc_attr( $responsive_values['tablet-portrait'] ) . '" ' . ( isset( $settings['extra']['min'] ) ? ' min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . ' data-responsive="tablet-portrait">' .
			                     '<div class="responsive-field-icon"><i class="fa fa-mobile"></i></div><input type="number" value="' . esc_attr( $responsive_values['mobile'] ) . '" ' . ( isset( $settings['extra']['min'] ) ? ' min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . ' data-responsive="mobile">' .
			                     '</div>';
		} else {
			$html = '<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-textinput ' .
			        esc_attr( $settings['param_name'] ) . ' ' .
			        esc_attr( $settings['type'] ) . '_field" type="number" value="' . esc_attr( $value ) . '"' . ( isset( $settings['extra']['min'] ) ? ' min="' . $settings['extra']['min'] . '"' : '' ) . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . '/>';
		}

		return $html;
	}


	/**
	 * Create dimensions field for VC
	 *
	 * @param array $settings
	 * @param string $value
	 *
	 * @return string
	 */
	public function createVCDimensions( $settings, $value ) {
		$value = isset( $settings['value'] ) && is_null( $value ) ? $settings['value'] : $value;

		$html = '<input style="width:100px" name="' . esc_attr( $settings['param_name'] ) . '[width]" class="wpb_vc_param_value wpb-textinput ' .
		        esc_attr( $settings['param_name'] ) . '-width ' .
		        esc_attr( $settings['type'] ) . '_field" type="number" value="' . esc_attr( $value['width'] ) . '" min="0"' . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . '/>';
		$html .= '<span>' . ( isset( $settings['extra']['w_unit'] ) ? ' ' . $settings['extra']['w_unit'] : '' ) . ' x </span>';
		$html .= '<input style="width:100px" name="' . esc_attr( $settings['param_name'] ) . '[width]" class="wpb_vc_param_value wpb-textinput ' .
		         esc_attr( $settings['param_name'] ) . '-height ' .
		         esc_attr( $settings['type'] ) . '_field" type="number" value="' . esc_attr( $value['height'] ) . '" min="0"' . ( isset( $settings['extra']['max'] ) ? ' max="' . $settings['extra']['max'] . '"' : '' ) . '/>';
		$html .= '<span>' . ( isset( $settings['extra']['h_unit'] ) ? ' ' . $settings['extra']['h_unit'] : '' ) . '</span>';

		return $html;
	}

	/**
	 * Called function on plugin activation
	 */
	public function activated() {
	}

	/**
	 * Called when the plugin is updated
	 *
	 * @param $version
	 */
	public function upgrade( $version ) {
	}
}