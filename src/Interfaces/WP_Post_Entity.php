<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
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
	public function postType();
}