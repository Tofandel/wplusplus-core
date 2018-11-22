<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

namespace Tofandel\Core\Traits;

use Tofandel\Core\Interfaces\WP_Plugin;

/**
 * Class WP_Post_Entity
 *
 * @property $post_author
 * @property $post_date
 * @property $post_title
 * @property $post_content
 * @property $post_name
 * @property $post_parent
 */
trait WP_Post_Entity {
	use WP_Entity {
		__construct as WP_Entity_construct;
	}
	use StaticSubModule {
		SubModuleInit as ParentSubModuleInit;
	}

	public function __construct( $read = 0 ) {
		$this->WP_Entity_construct( $read );
	}

	public static $capability = null;

	public function postType() {
		if ( ! isset( $this->_post_type ) ) {
			$this->_post_type = static::StaticPostType();
		}

		return $this->_post_type;
	}

	public static function StaticPostType() {
		return static::getClassSlug();
	}

	public static function getClassSlug( $class = false ) {
		try {
			$name = new \ReflectionClass( $class ?: static::class );
		} catch ( \ReflectionException $e ) {
			die( $e->getMessage() );
		}

		return strtolower( $name->getShortName() );
	}

	/**
	 * SubModule constructor.
	 *
	 * @param WP_Plugin|null $parent
	 */
	public static function SubModuleInit( WP_Plugin &$parent = null ) {
		static::ParentSubModuleInit( $parent );

		$post_type = static::StaticPostType();
		if ( ! isset( static::$capability ) ) {
			static::$capability = $post_type;
		}
		$class = static::class;
		add_action( 'init', function () use ( $post_type, $class ) {
			register_post_type( $post_type, call_user_func( array( static::class, 'post_type_options' ) ) );
		} );
	}

	abstract public static function post_type_options();


	public function set_transient( $transient, $val = null, $time = 3600 ) {
		if ( isset( $val ) ) {
			$this->add_meta_data( $transient, $val, true );
		}
		set_post_transient( $this->get_id(), $transient, $this->get_meta( $transient ), $time );
	}


	public function get_transient( $transient ) {
		$val = get_post_transient( $this->get_id(), $transient );
		if ( $val !== false ) {
			$this->add_meta_data( $transient, $val, true );
		}

		return $this->get_meta( $transient );
	}

	public function delete_transient( $transient ) {
		delete_post_transient( $this->get_id(), $transient );
	}


	/**
	 * @return static
	 */
	public function duplicate() {
		$new = clone $this;

		return $new;
	}

	/**
	 * @param \WP_User $user
	 */
	public function reassign( $user ) {
		if ( $user->exists() ) {
			$post_author = $this->post_author;
			$this->reassign_recursive( $this->post, $user->ID );
			do_action( 'wpp_reassigned_' . ( $this->post_content ?: $this->post_title ), $this, $user->ID, $post_author );
			$this->post_author = $user->ID;
		}
	}

	/**
	 * @param \WP_Post $post
	 * @param int      $new_author
	 */
	public function reassign_recursive( $post, $new_author ) {
		$post_author = $post->post_author;
		$posts       = get_posts( array(
			'post_parent'    => $post->ID,
			'post_author'    => $post_author,
			'posts_per_page' => 1000
		) );
		foreach ( $posts as $post ) {
			$this->reassign_recursive( $post, $new_author );
		}
		wp_update_post( array( 'ID' => $post->ID, 'post_author' => $new_author ) );

	}
}