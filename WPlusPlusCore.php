<?php

namespace Tofandel;

use Tofandel\Core\Objects\WP_Plugin;

/**
 * Class WPlusPlusCore
 * @package Tofandel
 *
 * Plugin Name: W++ Core
 * Plugin URI: https://github.com/tofandel/wplusplus-core/
 * Description: A powerful wordpress plugin for developers to create forms and so much more!
 * Version: 1.2
 * Author: Adrien Foulon <tofandel@tukan.hu>
 * Author URI: https://tukan.fr/a-propos/#adrien-foulon
 * Text Domain: wpluspluscore
 * Domain Path: /languages/
 * WC tested up to: 4.9
 * Download Url: https://github.com/tofandel/wplusplus-core/
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once 'functions.php';

class WPlusPlusCore extends WP_Plugin {
	const MULOADER_DIR = WPMU_PLUGIN_DIR;

	public function actionsAndFilters() {
		add_action( 'vc_mapper_init_after', array( $this, 'integratetoVC' ), 11 );
	}

	public function definitions() {
	}

	public function integrateToVC() {
		vc_add_shortcode_params( 'number', array( $this, 'createVCNumber' ) );
		vc_add_shortcode_param( 'multidropdown', array( $this, 'createVCMultiDropdown' ) );
	}

	public function createVCMultiDropdown( $param, $value ) {
		$param_line = '';
		$param_line .= '<select multiple name="' . esc_attr( $param['param_name'] ) . '" class="wpb_vc_param_value wpb-input wpb-select ' . esc_attr( $param['param_name'] ) . ' ' . esc_attr( $param['type'] ) . '">';
		foreach ( $param['value'] as $text_val => $val ) {
			if ( is_numeric( $text_val ) && ( is_string( $val ) || is_numeric( $val ) ) ) {
				$text_val = $val;
			}
			$text_val = __( $text_val, "js_composer" );
			$selected = '';

			if ( ! is_array( $value ) ) {
				$param_value_arr = explode( ',', $value );
			} else {
				$param_value_arr = $value;
			}

			if ( $value !== '' && in_array( $val, $param_value_arr ) ) {
				$selected = ' selected="selected"';
			}
			$param_line .= '<option class="' . $val . '" value="' . $val . '"' . $selected . '>' . $text_val . '</option>';
		}
		$param_line .= '</select>';

		return $param_line;
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

	public function menusAndSettings() {
		// TODO Redux
	}

	public function upgrade( $last_version ) {
		// Nothing yet
	}

	public function activate() {
		parent::activate();
		//Create the muloader so the core get's loaded before any plugin, thus removing the need to require it in every child plugin
		$perms_dir  = (int) decoct( fileperms( WP_CONTENT_DIR . '/plugins' ) & 0777 ) ?: 0775;
		$perms_file = (int) decoct( fileperms( __FILE__ ) & 0777 ) ?: 0664;

		if ( mkdir( self::MULOADER_DIR, octdec( $perms_dir ), true ) ) {
			chmod( self::MULOADER_DIR, octdec( $perms_dir ) );
			copy( __DIR__ . '/wplusplus-muloader.php.bak', self::MULOADER_DIR . '/wplusplus-muloader.php' );
			chmod( self::MULOADER_DIR . '/wplusplus-muloader.php', octdec( $perms_file ) );
		}
	}

	public function deactivate() {
		//Clean the muloader
		unlink( self::MULOADER_DIR . '/wplusplus-muloader.php' );
	}

	/**
	 * Add redux framework menus, sub-menus and settings page in this function
	 */
	public function reduxOptions() {
	}
}


global $WPlusPlusCore;

try {
	$WPlusPlusCore = new WPlusPlusCore();
} catch ( \Exception $e ) {
	echo $e->getMessage();
}