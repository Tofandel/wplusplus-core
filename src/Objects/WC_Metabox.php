<?php

namespace Tofandel\Core\Objects;


class WC_Metabox extends WP_Metabox {
	private $product_types = array();

	/** @noinspection PhpMissingParentConstructorInspection */

	/**
	 * WC_Metabox constructor.
	 *
	 * @param string $opt_name
	 * @param string $id
	 * @param string $title
	 * @param array $product_types
	 * @param int $priority
	 */
	public function __construct( string $opt_name, string $id, string $title, $product_types = array(), $priority = 90 ) {
		global $pagenow, $post;
		if ( ! empty( $product_types ) && is_string( $product_types ) ) {
			$product_types = array( $product_types );
		} elseif ( empty( $product_types ) ) {
			$product_types = array();
		}

		if ( ! is_object( $post ) && isset( $_REQUEST['post'] ) ) {
			$post = get_post( intval( $_REQUEST['post'] ) );
		}

		if ( ! is_admin()
		     || ! ( ( ( ( $pagenow == "post-new.php" || $pagenow == "post.php" ) && isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'product' )
		              || ( $pagenow == "post.php" && ( ! empty( $post ) && $post->post_type == 'product' ) ) ) || wp_doing_ajax() )
		     || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'heartbeat' ) ) {
			return;
		}

		if ( ! class_exists( 'Redux_Metaboxes' ) ) {
			global $WPlusPlusCore;
			require_once $WPlusPlusCore->file( 'plugins/redux-extensions/metaboxes/metaboxes_api.php' );
		}

		$this->opt_name = $opt_name;
		$this->id       = $id;
		$this->title    = $title;
		$this->screen   = array( 'product' );
		$this->priority = $priority;

		$metabox = array(
			'id'            => $this->id,
			'title'         => $this->title,
			'post_types'    => $this->screen,

			//'page_template' => array('page-test.php'), // Visibility of box based on page template selector
			//'post_format' => array('image'), // Visibility of box based on post format
			'priority'      => $this->priority,
			'position'      => 'woocommerce', // normal, advanced, side
			'product_types' => $this->product_types
		);
		\Redux_Metaboxes::setBox( $opt_name, $metabox );
		$this->product_types = $product_types;
	}
}