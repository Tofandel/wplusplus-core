<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 24/10/2018
 * Time: 15:36
 */

namespace Tofandel\Core\ShortcodeMappers;


use Tofandel\Core\Objects\ShortcodeMapper;
use Tofandel\Core\Objects\ShortcodeParameter;
use Tofandel\Core\Traits\Initializable;

class VC_Mapper extends ShortcodeMapper {
	use Initializable;

	/**
	 * Do the child initialisation in this
	 */
	static function __StaticInit() {
		self::$vc_mapping = apply_filters( 'wpp_shortcode_parameter_vc_mapping', self::$vc_mapping );
	}

	private static $vc_mapping = array(
		ShortcodeParameter::T_RAWHTML  => 'textarea_raw_html',
		ShortcodeParameter::T_LINK     => 'vc_link',
		ShortcodeParameter::T_IMAGE    => 'attach_image',
		ShortcodeParameter::T_IMAGES   => 'attach_images',
		ShortcodeParameter::T_BOOL     => 'checkbox',
		ShortcodeParameter::T_TEXT     => 'textfield',
		ShortcodeParameter::T_LONGTEXT => 'textarea',
		ShortcodeParameter::T_COLOR    => 'colorpicker',
		ShortcodeParameter::T_CHOICE   => 'dropdown',
	);

	public static function isVC() {
		return defined( 'WPB_VC_VERSION' );
	}

	/**
	 * Whether this mapper should be active or not (depending on the active plugins or the request...)
	 *
	 * @return bool
	 */
	public static function shouldMap() {
		global $pagenow;

		if ( self::isVC() && ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( ! empty( $_REQUEST[ 'action' ] ) && wp_doing_ajax() && strpos( $_REQUEST[ 'action' ], 'vc_' ) === 0 ) ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Handles the mapping logic
	 *
	 * @param array $info
	 *
	 */
	public static function map( array $info ) {
		add_action( 'vc_before_mapping', function () use ( $info ) {
			/**
			 * @var \stdClass $info
			 */
			$params = array();
			foreach ( $info->params as $param ) {
				/**
				 * @var ShortcodeParameter $param
				 */
				self::mapParameter( $param );
			}
			$map = array(
				'category'    => $info->category,
				'description' => $info->description,
				'name'        => $info->name,
				'icon'        => $info->icon,
				'params'      => $params
			);
			vc_map( $map );
		} );
	}

	/**
	 * Handles the parameter mapping logic
	 *
	 * @param ShortcodeParameter $p
	 *
	 * @return mixed
	 */
	public static function mapParameter( ShortcodeParameter $p ) {
		return array_merge( array(
			'type'        => isset( self::$vc_mapping[ $p->getType() ] ) ? self::$vc_mapping[ $p->getType() ] : $p->getType(),
			'heading'     => $p->getLabel(),
			'param_name'  => $p->getName(),
			'description' => $p->getDescription(),
			'std'         => $p->getDefault(),
			'group'       => $p->getCategory(),
			'value'       => $p->getChoices(),
		), $p->getMapperOptions( self::class ) );
	}
}