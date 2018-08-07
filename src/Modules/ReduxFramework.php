<?php

namespace Tofandel\Core\Modules;


use Tofandel\Core\Interfaces\SubModule;
use Tofandel\Core\Interfaces\WP_Plugin;
use Tofandel\Core\Objects\ReduxConfig;
use Tofandel\Core\Objects\WP_Metabox;

class ReduxFramework implements SubModule {
	use \Tofandel\Core\Traits\SubModule {
		__construct as ParentConstruct;
	}

	private $args = null;
	private $opt_name;
	/**
	 * @var ReduxConfig
	 */
	private $redux_config;
	/**
	 * @var WP_Metabox
	 */
	private $metabox;

	public function __construct( WP_Plugin &$parent = null ) {
		$this->ParentConstruct( $parent );
		if ( $parent ) {
			$this->opt_name = $parent->getReduxOptName();
		}
	}

	private function assertReduxLoaded() {
		if ( ! isset( $this->args ) ) {
			$this->setArgs();
		}
	}

	/**
	 * @throws \Exception
	 */
	private function assertMetaboxLoaded() {
		if ( ! isset( $this->metabox ) ) {
			throw new \Exception( 'You must define a metabox with "setMetabox" before doing metabox manipulations' );
		}
	}

	public function setArgs( $args = array() ) {
		$this->args         = $args;
		$this->redux_config = new ReduxConfig( $this->opt_name, $args );
	}

	/**
	 * WP_Metabox constructor.
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $position Either 'normal', 'advanced' or 'side'
	 * @param string|array $post_types The posts on which to show the box (such as a post type, 'link', or 'comment')
	 * @param string $priority Either 'high', 'core', 'default', or low - Priorities of placement
	 *
	 * @return WP_Metabox
	 */
	public function setMetabox( $id, $title, $post_types, $position = 'normal', $priority = 'default' ) {
		$this->assertReduxLoaded();

		$this->metabox = new WP_Metabox( $this->opt_name, $id, $title, $post_types, $position, $priority );

		return $this->metabox;
	}

	/**
	 * @param array $sections Array of box sections for redux
	 *
	 * @throws \Exception
	 */
	public function setMetaboxSections( $sections ) {
		$this->assertMetaboxLoaded();
		$this->metabox->setSections( $sections );
	}

	/**
	 * @param string $id
	 * @param array $fields
	 * @param string $title
	 * @param string $icon
	 *
	 * @throws \Exception
	 */
	public function setMetaboxSection( $id, $fields, $title = '', $icon = '' ) {
		$this->assertMetaboxLoaded();
		$this->metabox->setSection( $id, $fields, $title, $icon );
	}


	public function setField( $field = array() ) {
		$this->assertReduxLoaded();
		$this->redux_config->setField( $field );
	}

	public function setHelpTab( $tab = array() ) {
		$this->assertReduxLoaded();
		$this->redux_config->setHelpTab( $tab );
	}

	public function setHelpSidebar( $content = "" ) {
		$this->assertReduxLoaded();
		$this->redux_config->setHelpSidebar( $content );
	}

	public function setOption( $key = "", $option = "" ) {
		$this->assertReduxLoaded();
		$this->redux_config->setOption( $key, $option );
	}

	public function setSections( $sections = array() ) {
		$this->assertReduxLoaded();
		$this->redux_config->setSections( $sections );
	}

	public function setSection( $section = array() ) {
		$this->assertReduxLoaded();
		$this->redux_config->setSection( $section );
	}

}