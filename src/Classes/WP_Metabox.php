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


	/**
	 * WP_Metabox constructor.
	 *
	 * @param string $opt_name
	 * @param string $id
	 * @param string $title
	 * @param string $position Either 'normal', 'advanced' or 'side'
	 * @param string|array|\WP_Screen $post_types The screen or screens on which to show the box (such as a post type, 'link', or 'comment')
	 * @param string $priority Either 'high', 'core', 'default', or low - Priorities of placement
	 */
	public function __construct( $opt_name, $id, $title, $post_types, $position = 'normal', $priority = 'default' ) {
		$this->opt_name = $opt_name;
		$this->id       = $id;
		$this->title    = $title;
		$this->screen   = $post_types;
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
		\Redux_Metaboxes::processSectionsArray( $this->opt_name, $sections );
	}

	/**
	 * @param array $fields
	 * @param string $title
	 * @param string $icon
	 */
	public function setSection( $fields, $title = '', $icon = '' ) {
		$section = array(
			'title'  => $title,
			'icon'   => $icon,
			'fields' => $fields
		);
		\Redux_Metaboxes::setSection( $this->opt_name, $section );
	}

	public function add_meta_box( $metaboxes ) {
		$metaboxes[] = array(
			'id'         => $this->id,
			'title'      => $this->title,
			'post_types' => $this->screen,
			//'page_template' => array('page-test.php'), // Visibility of box based on page template selector
			//'post_format' => array('image'), // Visibility of box based on post format
			'position'   => $this->position, // normal, advanced, side
			'priority'   => $this->priority, // high, core, default, low - Priorities of placement
			'sections'   => $this->sections,
		);

		return $metaboxes;
	}
}