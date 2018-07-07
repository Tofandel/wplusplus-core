<?php

namespace Tofandel\Core\Classes;


/**
 * Class WP_Metabox
 * @package Tofandel\Core\Classes
 * @doc https://docs.reduxframework.com/extensions/metaboxes/
 */
class WP_Metabox {
	protected $opt_name;
	protected $id;
	protected $title;
	protected $screen;
	protected $priority;
	protected $position;
	protected $last_section;


	/**
	 * WP_Metabox constructor.
	 *
	 * @param string $opt_name
	 * @param string $id
	 * @param string $title
	 * @param string $position Either 'normal', 'advanced' or 'side'
	 * @param string|array $post_types The posts on which to show the box (such as a post type, 'link', or 'comment')
	 * @param string $priority Either 'high', 'core', 'default', or low - Priorities of placement
	 */
	public function __construct( $opt_name, $id, $title, $post_types, $position = 'normal', $priority = 'default' ) {
		global $pagenow;
		if ( ! is_admin() || ! ( $pagenow == "post-new.php" || $pagenow == "post.php" || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'heartbeat' ) ) {
			return;
		}
		$this->opt_name = $opt_name;
		$this->id       = $id;
		$this->title    = $title;
		$this->screen   = is_array( $post_types ) ? $post_types : array( $post_types );
		$this->priority = $priority;
		$this->position = $position;

		$metabox = array(
			'id'         => $this->id,
			'title'      => $this->title,
			'post_types' => $this->screen,
			//'page_template' => array('page-test.php'), // Visibility of box based on page template selector
			//'post_format' => array('image'), // Visibility of box based on post format
			'position'   => $this->position, // normal, advanced, side
			'priority'   => $this->priority, // high, core, default, low - Priorities of placement
		);
		\Redux_Metaboxes::setBox( $opt_name, $metabox );
	}

	/**
	 * @param array $sections Array of box sections for redux
	 */
	public function setSections( $sections ) {
		if ( ! empty( $this->opt_name ) ) {
			\Redux_Metaboxes::processSectionsArray( $this->opt_name, $sections );
		}
	}

	/**
	 * @param string $id
	 * @param array $fields
	 * @param string $title
	 * @param string $icon
	 */
	public function setSection( $id, $fields, $title = '', $icon = '' ) {
		if ( ! empty( $this->opt_name ) ) {
			$this->last_section = $id;
			$section            = array(
				'box_id' => $this->id,
				'id'     => $id,
				'title'  => $title,
				'icon'   => $icon,
				'fields' => $fields
			);
			\Redux_Metaboxes::setSection( $this->opt_name, $section );
		}
	}

	/**
	 * @param array $field
	 */
	public function setField( $field ) {
		if ( ! empty( $this->opt_name ) && ! empty( $this->last_section ) ) {
			$field = array_merge( $field, array( 'section_id' => $this->last_section ) );
			\Redux_Metaboxes::setField( $this->opt_name, $field );
		}
	}

}