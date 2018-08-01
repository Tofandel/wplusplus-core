<?php

namespace Tofandel\Core\Traits;

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
		get as parentGet;
		set as parentSet;
		setOverride as parentSetOverride;
		__isset as ParentIsset;
	}
	use Initializable;

	public static $capability = null;
	static public $instances = array();
	private static $post_vars;
	static private $_retrieved_metas = array();
	/**
	 * @var \WP_Post
	 */
	public $post = null;
	/**
	 * @var bool
	 */
	protected $is_created = false;
	private $_post_type;

	/**
	 * WP_Post_Entity constructor.
	 *
	 * @param string|bool $slug
	 *
	 * @throws \Exception
	 */
	public function __construct( $slug = false ) {
		if ( $slug ) {
			$this->setPost( $slug, true );
		}
	}

	/**
	 * @param $post_id_or_slug
	 *
	 * @throws \Exception
	 */
	public function retrieve( $post_id_or_slug ) {
		$this->setPost( $post_id_or_slug );
	}

	/**
	 * @param mixed $post_or_slug
	 * @param bool $create
	 *
	 * @throws \Exception
	 */
	public function setPost( $post_or_slug = false, $create = false ) {
		if ( is_a( $post_or_slug, static::class ) ) {
			/**
			 * @var self $post_or_slug
			 */
			if ( $post_or_slug->post->post_type == $this->postType() ) {
				$this->setID( wpp_apply_filters( 'wpml_object_id', $post_or_slug->post->ID, $this->postType(), true ) );
			} else {
				throw new \Exception( "Invalid post type" );
			}
		} elseif ( wpp_is_integer( $post_or_slug ) ) {
			$this->setID( $post_or_slug );
		} else {
			$slug = wpp_slugify( $post_or_slug );
			if ( isset( self::$instances[ $slug ] ) ) {
				$this->setID( wpp_apply_filters( 'wpml_object_id', self::$instances[ $slug ]->ID, $this->postType(), true ) );
			} elseif ( $post = get_page_by_path( $slug, OBJECT, $this->postType() ) ) {
				$this->setID( wpp_apply_filters( 'wpml_object_id', $post->ID, $this->postType(), true ) );
			} elseif ( $create ) {
				$post_id = wp_insert_post( array(
					'post_title'   => $post_or_slug,
					'post_content' => $slug,
					'post_name'    => $slug,
					'post_type'    => $this->postType(),
					'post_status'  => 'publish'
				) );
				if ( is_wp_error( $post_id ) ) {
					throw new \Exception( "Post $slug could not be created" );
				}
				$this->is_created = true;
				$this->setID( $post_id );
			}
		}
	}

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
	 * @param int $ID
	 */
	public function setID( $ID ) {
		$p = get_post( $ID );
		if ( ! $p ) {
			return;
		}
		$this->post = new \WP_Post( $p );
		if ( ! isset( $this->post ) || $this->post->post_type != $this->postType() ) {
			$this->post = null;

			return;
		}
		$this->setOverride( 'ID', $ID );
		self::$instances[ $this->post->post_name ] = &$this;
	}

	public function setOverride( $name, $val ) {
		self::$_retrieved_metas[ $this->ID ][ $name ] = $val;
		$this->parentSetOverride( $name, $val );
	}

	public static function __init__() {
		$post_type = static::StaticPostType();
		if ( ! isset( static::$capability ) ) {
			static::$capability = $post_type;
		}
		$class = static::class;
		add_action( 'init', function () use ( $post_type, $class ) {
			register_post_type( $post_type, call_user_func( array( $class, 'post_type_options' ) ) );
		} );
	}

	abstract public static function post_type_options();

	/**
	 * @param array $where
	 * @param self $ent
	 *
	 * @throws \Exception
	 *
	 * @return self
	 */
	public static function getOneWith( $where, &$ent = null ) {
		if ( isset( $where['ID'] ) ) {
			$post     = new \stdClass();
			$post->ID = $where['ID'];
		} else {
			$posts = get_posts( array_merge( $where,
				array( 'posts_per_page' => 1 ) ) );
			if ( empty( $posts ) ) {
				return null;
			}
			$post = $posts[0];
		}
		if ( ! isset( $ent ) ) {
			$ref = new \ReflectionClass( static::class );
			$ent = $ref->newInstanceWithoutConstructor();
		}
		$ent->setID( $post->ID );

		return $ent;
	}

	/**
	 * @param $where
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public static function getAllWith( $where ) {
		$posts = get_posts( $where );
		$ents  = array();

		$ref = new \ReflectionClass( static::class );
		foreach ( $posts as $post ) {
			$ent = $ref->newInstanceWithoutConstructor();
			$ent->setID( $post->ID );
			$ents[] = $ent;
		}

		return $ents;
	}

	/**
	 * @param array $posts
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public static function postsToInstances( $posts ) {
		$ents = array();

		$ref = new \ReflectionClass( static::class );
		foreach ( $posts as $post ) {
			/**
			 * @var self $ent
			 */
			$ent = $ref->newInstanceWithoutConstructor();
			$ent->setID( $post->ID );
			$ents[] = $ent;
		}

		return $ents;
	}

	/**
	 * @return bool
	 */
	public function isCreated() {
		return $this->is_created;
	}

	public function __destruct() {
		//$this->save();
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		if ( ! isset( self::$post_vars ) ) {
			self::$post_vars = get_class_vars( 'WP_Post' );
		}
		if ( array_key_exists( $name, self::$post_vars ) ) {
			return $this->parentGet( 'post', $name );
		}

		if ( isset( $this->_meta[ $name ] ) ) {
			return $this->_meta[ $name ];
		}

		if ( ! isset( $_retrieved_metas[ $this->ID ][ $name ] ) ) {
			return $_retrieved_metas[ $this->ID ][ $name ] = get_post_meta( $this->ID, $name, true );
			//$this->{$name}                          = $_retrieved_metas[ $this->ID ][ $name ];
		}

		return isset( $this->{$name} ) ? $this->{$name} : null;
	}

	public function __isset( $name ) {
		if ( ! isset( self::$post_vars ) ) {
			self::$post_vars = get_class_vars( 'WP_Post' );
		}
		if ( array_key_exists( $name, self::$post_vars ) ) {
			return isset( $this->post->$name );
		}

		return $this->ParentIsset( $name );
	}

	public function set_transient( $transient, $val = null, $time = 3600 ) {
		if ( isset( $val ) ) {
			$this->setOverride( $transient, $val );
		}
		set_post_transient( $this->ID, $transient, $this->get( $transient ), $time );
	}


	public function get_transient( $transient ) {
		$val = get_post_transient( $this->ID, $transient );
		if ( $val !== false ) {
			$this->setOverride( $transient, $val );
		}

		return $this->get( $transient );
	}

	public function delete_transient( $transient ) {
		delete_post_transient( $this->ID, $transient );
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 */
	public function set( $name, $value ) {
		if ( ! isset( self::$post_vars ) ) {
			self::$post_vars = get_class_vars( 'WP_Post' );
		}
		if ( array_key_exists( $name, self::$post_vars ) ) {
			$this->parentSet( 'post', $name, $value );
		} else {
			call_user_func_array( [ $this, 'parentSet' ], func_get_args() );
		}
	}

	public function save() {
		if ( $this->isNew() && isset( $this->post ) ) {
			$p                = $this->post->to_array();
			$p['post_author'] = get_current_user_id();
			$id               = wp_insert_post( $p );

			if ( ! is_wp_error( $id ) ) {
				$this->setOverride( 'ID', $id );
			}
		} elseif ( $this->isModified() && isset( $this->post ) ) {
			wp_update_post( $this->post );
			foreach ( $this->getNewData() as $key => $val ) {
				if ( $key == 'post' ) {
					continue;
				}
				update_post_meta( $this->ID, $key, $val );
			}
			$this->applyModifications();
		}
	}

	public function delete( $force = true ) {
		if ( ! $this->isNew() && isset( $this->post ) ) {
			wp_delete_post( $this->ID, $force );
			do_action( 'wpp_deleted_' . ( $this->post_content ?: $this->post_title ), $this, $force );
			unset( $this->post );
			unset( $this->ID );
		}
	}

	/**
	 * @throws \Exception
	 */
	public function __clone() {
		$post = clone $this->post;
		if ( isset( $this->post ) ) {
			unset( $this->post->ID );
			$id = wp_insert_post( $post->to_array() );
			if ( ! is_wp_error( $id ) ) {
				$this->post->ID = $id;
				do_action( 'wpp_duplicated_' . ( $this->post_content ?: $this->post_title ), $this );
			}
		}
		throw new \Exception( 'Could not clone Post Entity' );
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
	 * @param int $new_author
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