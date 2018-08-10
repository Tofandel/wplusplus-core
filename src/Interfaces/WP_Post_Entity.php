<?php
/**
 * Copyright (c) Adrien Foulon - 2018. All rights reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 22/05/2018
 * Time: 16:30
 */

namespace Tofandel\Core\Interfaces;


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
interface WP_Post_Entity {
	/**
	 * @param mixed $post_or_slug
	 * @param bool $create
	 *
	 * @throws \Exception
	 */
	public function setPost( $post_or_slug = false, $create = false );

	public function postType();

	/**
	 * @param int $ID
	 */
	public function setID( $ID );

	public function setOverride( $name, $val );

	/**
	 * @return bool
	 */
	public function isCreated();

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get( $name );

	/**
	 * @param $name
	 * @param $value
	 *
	 */
	public function set( $name, $value );

	public function save();

	public function delete( $force = true );

	/**
	 * @return static
	 */
	public function duplicate();

	/**
	 * @param \WP_User $user
	 */
	public function reassign( $user );

	/**
	 * @param \WP_Post $post
	 * @param int $new_author
	 */
	public function reassign_recursive( $post, $new_author );
}