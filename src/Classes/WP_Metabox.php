<?php

namespace Tofandel\Classes;


use Tofandel\Traits\Initializable;
use function Tofandel\wpp_create_meta_box_callback;

class WP_Metabox {
	use Initializable;

	protected $id;
	protected $title;
	protected $screen;


	/**
	 * WP_Metabox constructor.
	 *
	 * @param string $id
	 * @param string $title
	 * @param array $options List of options to use for wpp_create_meta_box_callback
	 * @param string|array|\WP_Screen $screen The screen or screens on which to show the box (such as a post type, 'link', or 'comment')
	 * @param string $context
	 * @param string $priority
	 */
	public function __construct($id, $title, $screen, $options, $context = 'advanced', $priority = 'default') {
		add_meta_box(
			$id,
			$title,
			'wpp_create_meta_box_callback',
			$screen,
			$context,
			$priority,
			$options
		);
	}

	protected function __init() {
		global $WPlusPlusCore;
		$WPlusPlusCore->addScript('metabox', array('jquery'));
		$WPlusPlusCore->addStyle('metabox');
	}
}