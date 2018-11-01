<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Redux_WordPress_Data', false)) {

    class Redux_WordPress_Data extends Redux_Class {

        private $wp_data = null;

        /**
         * Redux_WordPress_Data constructor.
         * @param ReduxFramework|string $parent
         */
        public function __construct($parent = null) {
            if (is_string($parent)) {
                $this->opt_name = $parent;
            } else {
                parent::__construct($parent);
            }
        }

        private function maybe_translate(&$value, $post_type) {
            if (has_filter('wpml_object_id')) {
                if (Redux_Helpers::is_integer($value)) {
                    $value = apply_filters('wpml_object_id', $value, $post_type, true);
                } elseif (is_array($value)) {
                    $value = array_map(function ($val) use ($post_type) {
                        return apply_filters('wpml_object_id', $val, $post_type, true);
                    }, $value);
                }
            }
        }

        /**
         * @param bool   $type
         * @param array  $args
         * @param string|int $current_value
         * @return array|mixed|string
         */
        public function get($type = false, $args = array(), $current_value = "") {

            $data = array();

            $opt_name = $this->opt_name;

            /**
             * filter 'redux/options/{opt_name}/data/{type}'
             *
             * @param string $data
             */
            $data = apply_filters("redux/options/{$opt_name}/data/$type", $data);

            //We add the current selected post type in the data so that it's always in the options (else it can be lost)
            if (!empty($current_value) && (is_array($current_value) || Redux_Helpers::is_integer($current_value))) {
                switch ($type) {
                    case 'pages':
                    case 'page':
                        $this->maybe_translate($current_value, 'page');
                        $pages = get_pages(array('include' => $current_value));
                        if (!empty($pages)) {
                            foreach ($pages as $page) {
                                $data[$page->ID] = $page->post_title;
                            }
                        }
                    break;

                    case 'categories':
                    case 'category':
                        $this->maybe_translate($current_value, 'category');
                        $terms = get_categories(array('object_ids' => $current_value));
                        if (!empty($terms)) {
                            foreach ($terms as $term) {
                                $data[$term->term_id] = $term->name;
                            }
                        }
                    break;

                    case 'terms':
                    case 'term':
                        $this->maybe_translate($current_value, isset($args['taxonomy']) ? $args['taxonomy'] : '');
                        $terms = get_terms(array('object_ids' => $current_value, 'taxonomy' => isset($args['taxonomy']) ? $args['taxonomy'] : ''));
                        if (!empty($terms) && !is_a($terms, 'WP_Error')) {
                            foreach ($terms as $term) {
                                $data[$term->term_id] = $term->name;
                            }
                        }
                    break;

                    case 'tags':
                    case 'tag':
                        $this->maybe_translate($current_value, 'post_tag');
                        $terms = get_tags(array('object_ids' => $current_value));
                        if (!empty($terms)) {
                            foreach ($terms as $term) {
                                $data[$term->term_id] = $term->name;
                            }
                        }
                    break;

                    case 'menus':
                    case 'menu':
                        $this->maybe_translate($current_value, 'nav_menu');
                        $menus = wp_get_nav_menus(array('object_ids' => $current_value));
                        if (!empty($menus)) {
                            foreach ($menus as $item) {
                                $data[$item->term_id] = $item->name;
                            }
                        }
                    break;

                    case 'post':
                    case 'posts':
                        $this->maybe_translate($current_value, 'post');
                        $posts = get_posts(array('post__in' => $current_value));
                        if (!empty($posts)) {
                            foreach ($posts as $post) {
                                $data[$post->ID] = $post->post_title;
                            }
                        }
                    break;

                    case 'users':
                    case 'user':
                        $users = get_users(array('include' => $current_value));
                        if (!empty($users)) {
                            foreach ($users as $user) {
                                $data[$user->ID] = $user->display_name;
                            }
                        }
                    break;
                }
            }
            $argsKey = md5( serialize( $args ) );

            if (isset($this->wp_data[$type . $argsKey])) {
                $data += $this->wp_data[$type . $argsKey];
                $data = array_unique($data);
            } elseif (!empty($type)) {

                /**
                 * Use data from Wordpress to populate options array
                 * */
                if (!empty($type) && empty($data)) {
                    if (empty($args)) {
                        $args = array();
                    }

                    $data = array();
                    $args = wp_parse_args($args, array());

                    switch ($type) {
                        case 'categories':
                        case 'category':
                            $cats = get_categories($args);
                            if (!empty($cats)) {
                                foreach ($cats as $cat) {
                                    $data[$cat->term_id] = $cat->name;
                                }
                            }
                        break;

                        case 'pages':
                        case 'page':
                            if (!isset($args['posts_per_page'])) {
                                $args['posts_per_page'] = 20;
                            }
                            $pages = get_pages($args);
                            if (!empty($pages)) {
                                foreach ($pages as $page) {
                                    $data[$page->ID] = $page->post_title;
                                }
                            }
                        break;

                        case 'terms':
                        case 'term':
                            $terms = get_terms($args);
                            if (!empty($terms) && !is_a($terms, 'WP_Error')) {
                                foreach ($terms as $term) {
                                    $data[$term->term_id] = $term->name;
                                }
                            }
                        break;

                        case 'taxonomies':
                        case 'taxonomy':
                        case 'tax':
                            $taxonomies = get_taxonomies($args);
                            if (!empty($taxonomies)) {
                                foreach ($taxonomies as $key => $taxonomy) {
                                    $data[$key] = $taxonomy;
                                }
                            }
                        break;
                        case 'post':
                        case 'posts':
                            $posts = get_posts($args);
                            if (!empty($posts)) {
                                foreach ($posts as $post) {
                                    $data[$post->ID] = $post->post_title;
                                }
                            }
                        break;

                        case 'post_type':
                        case 'post_types':
                            global $wp_post_types;

                            $defaults = array(
                                'public' => true,
                                'exclude_from_search' => false,
                            );
                            $args = wp_parse_args($args, $defaults);
                            $output = 'names';
                            $operator = 'and';
                            $post_types = get_post_types($args, $output, $operator);

                            ksort($post_types);

                            foreach ($post_types as $name => $title) {
                                if (isset($wp_post_types[$name]->labels->menu_name)) {
                                    $data[$name] = $wp_post_types[$name]->labels->menu_name;
                                } else {
                                    $data[$name] = ucfirst($name);
                                }
                            }
                        break;

                        case 'tags':
                        case 'tag':
                            $tags = get_tags($args);
                            if (!empty($tags)) {
                                foreach ($tags as $tag) {
                                    $data[$tag->term_id] = $tag->name;
                                }
                            }
                        break;

                        case 'menus':
                        case 'menu':
                            $menus = wp_get_nav_menus($args);
                            if (!empty($menus)) {
                                foreach ($menus as $item) {
                                    $data[$item->term_id] = $item->name;
                                }
                            }
                        break;

                        case 'menu_locations':
                        case 'menu_location':
                            global $_wp_registered_nav_menus;

                            foreach ($_wp_registered_nav_menus as $k => $v) {
                                $data[$k] = $v;
                            }
                        break;

                        case 'image_size':
                        case 'image_sizes':
                            global $_wp_additional_image_sizes;

                            foreach ($_wp_additional_image_sizes as $size_name => $size_attrs) {
                                $data[$size_name] = $size_name . ' - ' . $size_attrs['width'] . ' x ' . $size_attrs['height'];
                            }
                        break;

                        case 'elusive-icons':
                        case 'elusive-icon':
                        case 'elusive':
                        case 'icons':
                        case 'font-icon':
                        case 'font-icons':
                            /**
                             * filter 'redux/font-icons'
                             *
                             * @deprecated
                             *
                             * @param array $font_icons array of elusive icon classes
                             */
                            $font_icons = apply_filters('redux/font-icons', array());

                            /**
                             * filter 'redux/{opt_name}/field/font/icons'
                             *
                             * @deprecated
                             *
                             * @param array $font_icons array of elusive icon classes
                             */
                            $font_icons = apply_filters("redux/{$opt_name}/field/font/icons", $font_icons);

                            foreach ($font_icons as $k) {
                                $data[$k] = $k;
                            }
                        break;

                        case 'roles':
                        case 'role':
                            /** @global WP_Roles $wp_roles */
                            global $wp_roles;

                            $data = $wp_roles->get_names();
                        break;

                        case 'sidebars':
                        case 'sidebar':
                            /** @global array $wp_registered_sidebars */
                            global $wp_registered_sidebars;

                            foreach ($wp_registered_sidebars as $key => $value) {
                                $data[$key] = $value['name'];
                            }
                        break;
                        case 'capabilities':
                        case 'capability':
                            /** @global WP_Roles $wp_roles */
                            global $wp_roles;

                            foreach ($wp_roles->roles as $role) {
                                foreach ($role['capabilities'] as $key => $cap) {
                                    $data[$key] = ucwords(str_replace('_', ' ', $key));
                                }
                            }
                        break;

                        case 'users':
                        case 'user':
                            $users = get_users($args);
                            if (!empty($users)) {
                                foreach ($users as $user) {
                                    $data[$user->ID] = $user->display_name;
                                }
                            }
                        break;

                        case 'callback':
                            if (!is_array($args)) {
                                $args = array($args);
                            }
                            $data = call_user_func($args[0], $current_value);
                        break;
                    }
                }

                $this->wp_data[$type . $argsKey] = $data;
            }

            return $data;
        }

    }

}