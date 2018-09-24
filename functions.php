<?php
/**
 * Copyright (c) Adrien Foulon - 2018.
 * Licensed under the Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 */

//namespace Tofandel;

if ( ! function_exists( 'wpp_array_insert_after' ) ) {
	/**
	 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
	 * to the end of the array.
	 *
	 * @param array $array
	 * @param string $key
	 * @param array $new
	 *
	 * @return array
	 */
	function wpp_array_insert_after( array $array, $key, array $new ) {
		$keys  = array_keys( $array );
		$index = array_search( $key, $keys );
		$pos   = false === $index ? count( $array ) : $index + 1;

		return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
	}
}
if ( ! function_exists( 'wpp_admin_notice' ) ) {
	/**
	 * @param $text
	 * @param string $type
	 */
	function wpp_admin_notice( $text, $type = 'success' ) {
		add_action( 'admin_notices', function () use ( $text, $type ) {
			echo "<div class='notice notice-$type is-dismissible'>
			<p>$text</p>
		</div>";
		} );
	}
}

if ( ! function_exists( 'wpp_remove_domain_from_url' ) ) {
	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	function wpp_remove_domain_from_url( $url ) {
		if ( preg_match( '#(?:https?:\/\/)?[^\/]*?(\/.*)#', $url, $matches ) ) {
			return $matches[1];
		}

		return $url;
	}
}

if ( ! function_exists( 'wpp_get_domain_from_url' ) ) {
	/**
	 * @param $url
	 * @param bool $scheme
	 *
	 * @return string
	 */
	function wpp_get_domain_from_url( $url, $scheme = false ) {
		if ( preg_match( '#(https?:\/\/)?([^\/]*?)\/.*#', $url, $matches ) ) {
			return $scheme ? $matches[1] . $matches[2] : $matches[2];
		}

		return $url;
	}
}

if ( ! function_exists( 'wpp_has_shortcode' ) ) {
	/**
	 * @param string $string
	 * @param string|array $shortcode
	 *
	 * @return bool
	 */
	function wpp_has_shortcode( $string, $shortcode ) {
		global $shortcode_tags;
		$old = $shortcode_tags;
		if ( is_array( $shortcode ) ) {
			$shortcode_tags = array_flip( $shortcode );
			foreach ( $shortcode as $sh ) {
				if ( has_shortcode( $string, $sh ) ) {
					$shortcode_tags = $old;

					return true;
				}
			}
		} else {
			$shortcode_tags = array( $shortcode => '' );
			if ( has_shortcode( $string, $shortcode ) ) {
				$shortcode_tags = $old;

				return true;
			}
		}

		$shortcode_tags = $old;

		return false;
	}
}

if ( ! function_exists( 'wpp_unique_slug' ) ) {
	/**
	 * @param string $string The string to slugifiy
	 * @param bool $prepend Can be (and should) be used as a vendor name to separate slugs
	 *
	 * @param array $other_slugs
	 *
	 * @return string
	 */
	function wpp_unique_slug( $string, $prepend = false, $other_slugs = array() ) {
		static $slugs = array();

		$slugs = array_unique( array_merge( $slugs, $other_slugs ) );

		$string = ( $prepend ? wpp_slugify( $prepend ) . '-' : '' ) . wpp_slugify( $string );
		if ( ! in_array( $string, $slugs ) ) {
			$slugs[] = $string;

			return $string;
		}
		/** @noinspection PhpStatementHasEmptyBodyInspection */
		for ( $i = 2; in_array( $string . $i, $slugs ); $i ++ ) {
			;
		}
		$slugs[] = $string . $i;

		return $string . $i;
	}
}

if ( ! function_exists( 'wpp_get_editable_users' ) ) {
	/**
	 * @param array $args
	 *
	 * @return array|mixed
	 */
	function wpp_get_editable_users( $args = array() ) {
		static $users;
		if ( ! isset( $users ) ) {
			$roles = get_editable_roles();

			$args = array_merge( $args, array( 'role__in' => array_keys( $roles ) ) );

			$args = wpp_apply_filters( 'wpp_get_editable_users_args', $args );
			if ( empty( $args['role__in'] ) ) {
				$args['include'] = array_merge( wp_get_users_with_no_role(), array( get_current_user_id() ) );
			}

			$users_t = get_users( $args );

			$users = array();
			foreach ( $users_t as $user ) {
				$users[ $user->ID ] = $user;
			}
			$user  = wp_get_current_user();
			$users = array_replace( array( $user->ID => $user ), $users );

			$users = wpp_apply_filters( 'wpp_get_editable_users', $users );
		}

		return $users;
	}
}

if ( ! function_exists( 'wpp_apply_filter' ) ) {
	/**
	 * @param $filter
	 * @param $value
	 *
	 * @return mixed
	 */
	function wpp_apply_filters( $filter, $value ) {
		if ( ! has_filter( $filter ) ) {
			return $value;
		} else {
			$args = func_get_args();

			return call_user_func_array( 'apply_filters', $args );
		}
	}
}

if ( ! function_exists( 'get_post_transient' ) ) {
	/**
	 * @param int $post_id
	 * @param string $transient
	 *
	 * @return mixed
	 */
	function get_post_transient( $post_id, $transient ) {
		return get_object_transient( 'post', $post_id, $transient );
	}
}


if ( ! function_exists( 'set_post_transient' ) ) {
	/**
	 * @param int $post_id
	 * @param string $transient
	 * @param mixed $value
	 * @param int $expiration
	 *
	 * @return bool
	 */
	function set_post_transient( $post_id, $transient, $value, $expiration = 0 ) {
		return set_object_transient( 'post', $post_id, $transient, $value, $expiration );
	}
}


if ( ! function_exists( 'delete_post_transient' ) ) {
	/**
	 * @param int $post_id
	 * @param string $transient
	 *
	 * @return bool
	 */
	function delete_post_transient( $post_id, $transient ) {
		return delete_object_transient( 'post', $post_id, $transient );
	}
}


if ( ! function_exists( 'delete_expired_post_transients' ) ) {
	/**
	 * @param bool $force_db
	 */
	function delete_expired_post_transients( $force_db = true ) {
		delete_expired_object_transients( 'post', $force_db );
	}
}


if ( ! function_exists( 'get_user_transient' ) ) {
	/**
	 * @param int $user_id
	 * @param string $transient
	 *
	 * @return mixed
	 */
	function get_user_transient( $user_id, $transient ) {
		return get_object_transient( 'user', $user_id, $transient );
	}
}

if ( ! function_exists( 'set_user_transient' ) ) {
	/**
	 * @param int $user_id
	 * @param string $transient
	 * @param mixed $value
	 * @param int $expiration
	 *
	 * @return bool
	 */
	function set_user_transient( $user_id, $transient, $value, $expiration = 0 ) {
		return set_object_transient( 'user', $user_id, $transient, $value, $expiration );
	}
}

if ( ! function_exists( 'delete_user_transient' ) ) {
	/**
	 * @param int $user_id
	 * @param string $transient
	 *
	 * @return bool
	 */
	function delete_user_transient( $user_id, $transient ) {
		return delete_object_transient( 'user', $user_id, $transient );
	}
}


if ( ! function_exists( 'delete_expired_user_transients' ) ) {
	/**
	 * @param bool $force_db
	 */
	function delete_expired_user_transients( $force_db = true ) {
		delete_expired_object_transients( 'user', $force_db );
	}
}


if ( ! function_exists( 'get_object_transient' ) ) {
	/**
	 * Get the value of a transient.
	 *
	 * If the transient does not exist, does not have a value, or has expired,
	 * then the return value will be false.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object
	 * @param int $object_id
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 *
	 * @return mixed Value of transient.
	 */
	function get_object_transient( $object = null, $object_id, $transient ) {
		if ( ! isset( $object ) ) {
			return get_transient( $transient );
		}
		/**
		 * Filters the value of an existing transient.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * Passing a truthy value to the filter will effectively short-circuit retrieval
		 * of the transient, returning the passed value instead.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 The `$transient` parameter was added
		 *
		 * @param mixed $pre_transient The default value to return if the transient does not exist.
		 *                              Any value other than false will short-circuit the retrieval
		 *                              of the transient, and return the returned value.
		 * @param string $transient Transient name.
		 */
		$pre = wpp_apply_filters( "pre_{$object}_transient_{$transient}", false, $transient, $object_id );
		if ( false !== $pre ) {
			return $pre;
		}

		if ( wp_using_ext_object_cache() ) {
			$value = wp_cache_get( $object_id . '_' . $transient, "{$object}_transient" );
		} else {
			$transient_option  = '_transient_' . $transient;
			$transient_timeout = '_transient_timeout_' . $transient;
			$timeout           = get_metadata( $object, $object_id, $transient_timeout, true );
			if ( ! empty( $timeout ) && $timeout < time() ) {
				delete_metadata( $object, $object_id, $transient_option );
				delete_metadata( $object, $object_id, $transient_timeout );
				$value = false;
			}

			if ( ! isset( $value ) ) {
				$value = get_metadata( $object, $object_id, $transient_option, true );
			}
		}

		/**
		 * Filters an existing transient's value.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 The `$transient` parameter was added
		 *
		 * @param mixed $value Value of transient.
		 * @param string $transient Transient name.
		 */
		return wpp_apply_filters( "{$object}_transient_{$transient}", $value, $transient );
	}
}

if ( ! function_exists( 'set_object_transient' ) ) {
	/**
	 * Set/update the value of a transient.
	 *
	 * You do not need to serialize values. If the value needs to be serialized, then
	 * it will be serialized before it is set.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object
	 * @param int $object_id
	 * @param string $transient Transient name. Expected to not be SQL-escaped. Must be
	 *                           172 characters or fewer in length.
	 * @param mixed $value Transient value. Must be serializable if non-scalar.
	 *                           Expected to not be SQL-escaped.
	 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
	 *
	 * @return bool False if value was not set and true if value was set.
	 */
	function set_object_transient( $object = null, $object_id, $transient, $value, $expiration = 0 ) {
		if ( ! isset( $object ) ) {
			set_transient( $transient, $value, $expiration );
		}
		$expiration = (int) $expiration;

		/**
		 * Filters a specific transient before its value is set.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * @since 3.0.0
		 * @since 4.2.0 The `$expiration` parameter was added.
		 * @since 4.4.0 The `$transient` parameter was added.
		 *
		 * @param mixed $value New value of transient.
		 * @param int $expiration Time until expiration in seconds.
		 * @param string $transient Transient name.
		 */
		$value = wpp_apply_filters( "pre_set_{$object}_transient_{$transient}", $value, $expiration, $transient );

		/**
		 * Filters the expiration for a transient before its value is set.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * @since 4.4.0
		 *
		 * @param int $expiration Time until expiration in seconds. Use 0 for no expiration.
		 * @param mixed $value New value of transient.
		 * @param string $transient Transient name.
		 */
		$expiration = wpp_apply_filters( "expiration_of_{$object}_transient_{$transient}", $expiration, $value, $transient );

		if ( wp_using_ext_object_cache() ) {
			$result = wp_cache_set( $object_id . '_' . $transient, $value, "{$object}_transient", $expiration );
		} else {
			$transient_timeout = '_transient_timeout_' . $transient;
			$transient_option  = '_transient_' . $transient;
			if ( false === get_metadata( $object, $object_id, $transient_option, true ) ) {
				if ( $expiration ) {
					add_metadata( $object, $object_id, $transient_timeout, time() + $expiration, true );
				}
				$result = add_metadata( $object, $object_id, $transient_option, $value, true );
			} else {
				// If expiration is requested, but the transient has no timeout option,
				// delete, then re-create transient rather than update.
				$update = true;
				if ( $expiration ) {
					if ( false === get_metadata( $object, $object_id, $transient_timeout, true ) ) {
						delete_metadata( $object, $object_id, $transient_option );
						add_metadata( $object, $object_id, $transient_timeout, time() + $expiration, true );
						$result = add_metadata( $object, $object_id, $transient_option, $value, true );
						$update = false;
					} else {
						update_metadata( $object, $object_id, $transient_timeout, time() + $expiration );
					}
				}
				if ( $update ) {
					$result = update_metadata( $object, $object_id, $transient_option, $value );
				}
			}
		}

		if ( isset( $result ) && $result ) {

			/**
			 * Fires after the value for a specific transient has been set.
			 *
			 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
			 *
			 * @since 3.0.0
			 * @since 3.6.0 The `$value` and `$expiration` parameters were added.
			 * @since 4.4.0 The `$transient` parameter was added.
			 *
			 * @param mixed $value Transient value.
			 * @param int $expiration Time until expiration in seconds.
			 * @param string $transient The name of the transient.
			 */
			do_action( "set_{$object}_transient_{$transient}", $value, $expiration, $transient );

			/**
			 * Fires after the value for a transient has been set.
			 *
			 * @since 3.0.0
			 * @since 3.6.0 The `$value` and `$expiration` parameters were added.
			 *
			 * @param string $transient The name of the transient.
			 * @param mixed $value Transient value.
			 * @param int $expiration Time until expiration in seconds.
			 */
			do_action( "setted_{$object}_transient", $transient, $value, $expiration );

			return $result;
		}

		return false;
	}
}


if ( ! function_exists( 'delete_object_transient' ) ) {
	/**
	 * Delete a transient.
	 *
	 * @since 2.8.0
	 *
	 * @param string $object
	 * @param int $object_id
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 *
	 * @return bool true if successful, false otherwise
	 */
	function delete_object_transient( $object = null, $object_id, $transient ) {
		if ( ! isset( $object ) ) {
			delete_transient( $transient );
		}
		/**
		 * Fires immediately before a specific transient is deleted.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * @since 3.0.0
		 *
		 * @param string $transient Transient name.
		 */
		do_action( "delete_{$object}_transient_{$transient}", $transient, $object_id );

		if ( wp_using_ext_object_cache() ) {
			$result = wp_cache_delete( $transient, "{$object}_transient" );
		} else {
			$option_timeout = '_transient_timeout_' . $transient;
			$option         = '_transient_' . $transient;
			$result         = delete_metadata( $object, $object_id, $option );
			if ( $result ) {
				delete_metadata( $object, $object_id, $option_timeout );
			}
		}

		if ( $result ) {

			/**
			 * Fires after a transient is deleted.
			 *
			 * @since 3.0.0
			 *
			 * @param string $transient Deleted transient name.
			 */
			do_action( "deleted_{$object}_transient", $transient );
		}

		return $result;
	}
}


if ( ! function_exists( 'delete_expired_object_transients' ) ) {
	/**
	 * Deletes all expired transients.
	 *
	 * The multi-table delete syntax is used to delete the transient record
	 * from table a, and the corresponding transient_timeout record from table b.
	 *
	 * @since 4.9.0
	 *
	 * @param string $object
	 * @param bool $force_db Optional. Force cleanup to run against the database even when an external object cache is used.
	 */
	function delete_expired_object_transients( $object = 'user', $force_db = false ) {
		global $wpdb;

		if ( ! $force_db && wp_using_ext_object_cache() ) {
			return;
		}

		$wpdb->query( $wpdb->prepare(
			"DELETE a, b FROM {$wpdb->{$object.'meta'}} a, {$wpdb->{$object.'meta'}} b
			WHERE a.meta_key LIKE %s
			AND a.meta_key NOT LIKE %s
			AND b.meta_key = CONCAT( '_transient_timeout_', SUBSTRING( a.meta_key, 12 ) )
			AND b.meta_value < %d",
			$wpdb->esc_like( '_transient_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_' ) . '%',
			time()
		) );

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_' ) . '%'
		) );
	}
}

if ( ! function_exists( 'wpp_edit_user' ) ) {
	/**
	 * Edit user settings based on contents of $_POST
	 *
	 * Used on user-edit.php and profile.php to manage and process user options, passwords etc.
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id Optional. User ID.
	 *
	 * @return int|WP_Error user id of the updated user
	 */
	function wpp_edit_user( $user_id = 0 ) {
		$wp_roles = wp_roles();
		$user     = new stdClass;
		if ( $user_id ) {
			$update           = true;
			$user->ID         = (int) $user_id;
			$userdata         = get_userdata( $user_id );
			$user->user_login = wp_slash( $userdata->user_login );
		} else {
			$update = false;
		}

		if ( ! $update && isset( $_POST['user_login'] ) ) {
			$user->user_login = sanitize_user( $_POST['user_login'], true );
		}

		$pass1 = $pass2 = '';
		if ( isset( $_POST['pass1'] ) ) {
			$pass1 = $_POST['pass1'];
		}
		if ( isset( $_POST['pass2'] ) ) {
			$pass2 = $_POST['pass2'];
		}

		$errors = new WP_Error();
		if ( isset( $_POST['role'] ) ) {
			$new_role       = sanitize_text_field( $_POST['role'] );
			$potential_role = isset( $wp_roles->role_objects[ $new_role ] ) ? $wp_roles->role_objects[ $new_role ] : false;
			// Don't let anyone with 'edit_users' (admins) edit their own role to something without it.
			// Multisite super admins can freely edit their blog roles -- they possess all caps.
			if ( ( is_multisite() && current_user_can( 'manage_sites' ) ) || $user_id != get_current_user_id() || ( $potential_role && $potential_role->has_cap( 'edit_users' ) ) ) {
				$user->role = $new_role;
			}

			// If the new role isn't editable by the logged-in user die with error
			$editable_roles = get_editable_roles();
			if ( ! empty( $new_role ) && empty( $editable_roles[ $new_role ] ) ) {
				$errors->add( 'role', __( 'Sorry, you are not allowed to give users that role.' ) );

				return $errors;
			}
		}

		if ( isset( $_POST['email'] ) ) {
			$user->user_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
		}
		if ( isset( $_POST['url'] ) ) {
			if ( empty ( $_POST['url'] ) || $_POST['url'] == 'http://' ) {
				$user->user_url = '';
			} else {
				$user->user_url = esc_url_raw( $_POST['url'] );
				$protocols      = implode( '|', array_map( 'preg_quote', wp_allowed_protocols() ) );
				$user->user_url = preg_match( '/^(' . $protocols . '):/is', $user->user_url ) ? $user->user_url : 'http://' . $user->user_url;
			}
		}
		if ( isset( $_POST['first_name'] ) ) {
			$user->first_name = sanitize_text_field( $_POST['first_name'] );
		}
		if ( isset( $_POST['last_name'] ) ) {
			$user->last_name = sanitize_text_field( $_POST['last_name'] );
		}
		if ( isset( $_POST['nickname'] ) ) {
			$user->nickname = sanitize_text_field( $_POST['nickname'] );
		}
		if ( isset( $_POST['display_name'] ) ) {
			$user->display_name = sanitize_text_field( $_POST['display_name'] );
		}

		if ( isset( $_POST['description'] ) ) {
			$user->description = trim( $_POST['description'] );
		}

		foreach ( wp_get_user_contact_methods( $user ) as $method => $name ) {
			if ( isset( $_POST[ $method ] ) ) {
				$user->$method = sanitize_text_field( $_POST[ $method ] );
			}
		}

		if ( $update ) {
			$user->rich_editing         = isset( $_POST['rich_editing'] ) && 'false' === $_POST['rich_editing'] ? 'false' : 'true';
			$user->syntax_highlighting  = isset( $_POST['syntax_highlighting'] ) && 'false' === $_POST['syntax_highlighting'] ? 'false' : 'true';
			$user->admin_color          = isset( $_POST['admin_color'] ) ? sanitize_text_field( $_POST['admin_color'] ) : 'fresh';
			$user->show_admin_bar_front = isset( $_POST['admin_bar_front'] ) ? 'true' : 'false';
			$user->locale               = '';

			if ( isset( $_POST['locale'] ) ) {
				$locale = sanitize_text_field( $_POST['locale'] );
				if ( 'site-default' === $locale ) {
					$locale = '';
				} elseif ( '' === $locale ) {
					$locale = 'en_US';
				} elseif ( ! in_array( $locale, get_available_languages(), true ) ) {
					$locale = '';
				}

				$user->locale = $locale;
			}
		}

		$user->comment_shortcuts = isset( $_POST['comment_shortcuts'] ) && 'true' == $_POST['comment_shortcuts'] ? 'true' : '';

		$user->use_ssl = 0;
		if ( ! empty( $_POST['use_ssl'] ) ) {
			$user->use_ssl = 1;
		}


		/* checking that username has been typed */
		if ( $user->user_login == '' ) {
			$errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.' ) );
		}

		/* checking that nickname has been typed */
		if ( $update && empty( $user->nickname ) ) {
			$errors->add( 'nickname', __( '<strong>ERROR</strong>: Please enter a nickname.' ) );
		}

		/**
		 * Fires before the password and confirm password fields are checked for congruity.
		 *
		 * @since 1.5.1
		 *
		 * @param string $user_login The username.
		 * @param string $pass1 The password (passed by reference).
		 * @param string $pass2 The confirmed password (passed by reference).
		 */
		do_action_ref_array( 'check_passwords', array( $user->user_login, &$pass1, &$pass2 ) );

		// Check for blank password when adding a user.
		if ( ! $update && empty( $pass1 ) ) {
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter a password.' ), array( 'form-field' => 'pass1' ) );
		}

		// Check for "\" in password.
		if ( false !== strpos( wp_unslash( $pass1 ), "\\" ) ) {
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".' ), array( 'form-field' => 'pass1' ) );
		}

		// Checking the password has been typed twice the same.
		if ( ( $update || ! empty( $pass1 ) ) && $pass1 != $pass2 ) {
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in both password fields.' ), array( 'form-field' => 'pass1' ) );
		}

		if ( ! empty( $pass1 ) ) {
			$user->user_pass = $pass1;
		}

		if ( ! $update && isset( $_POST['user_login'] ) && ! validate_username( $_POST['user_login'] ) ) {
			$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
		}

		if ( ! $update && username_exists( $user->user_login ) ) {
			$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ) );
		}

		/** This filter is documented in wp-includes/user.php */
		$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

		if ( in_array( strtolower( $user->user_login ), array_map( 'strtolower', $illegal_logins ) ) ) {
			$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: Sorry, that username is not allowed.' ) );
		}

		/* checking email address */
		if ( empty( $user->user_email ) ) {
			$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an email address.' ), array( 'form-field' => 'email' ) );
		} elseif ( ! is_email( $user->user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.' ), array( 'form-field' => 'email' ) );
		} elseif ( ( $owner_id = email_exists( $user->user_email ) ) && ( ! $update || ( $owner_id != $user->ID ) ) ) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ), array( 'form-field' => 'email' ) );
		}

		/**
		 * Fires before user profile update errors are returned.
		 *
		 * @since 2.8.0
		 *
		 * @param WP_Error $errors WP_Error object (passed by reference).
		 * @param bool $update Whether this is a user update.
		 * @param stdClass $user User object (passed by reference).
		 */
		do_action_ref_array( 'user_profile_update_errors', array( &$errors, $update, &$user ) );

		if ( $errors->get_error_codes() ) {
			return $errors;
		}

		if ( $update ) {
			$user_id = wp_update_user( $user );
		} else {
			$user_id = wp_insert_user( $user );
			$notify  = isset( $_POST['send_user_notification'] ) ? 'user' : '';

			if ( ! empty( $notify ) ) {
				/**
				 * Fires after a new user has been created.
				 *
				 * @since 4.4.0
				 *
				 * @param int $user_id ID of the newly created user.
				 * @param string $notify Type of notification that should happen. See wp_send_new_user_notifications()
				 *                        for more information on possible values.
				 */
				do_action( 'edit_user_created_user', $user_id, $notify );
			}
		}

		return $user_id;
	}
}


if ( ! function_exists( 'wpp_is_float' ) ) {
	function wpp_is_float( $val ) {
		if ( ! is_scalar( $val ) ) {
			return false;
		}

		return is_float( $val + 0 );
	}
}

if ( ! function_exists( 'wpp_is_integer' ) ) {
	function wpp_is_integer( $val ) {
		if ( ! is_scalar( $val ) || is_bool( $val ) ) {
			return false;
		}

		return is_float( $val ) ? false : preg_match( '~^((?:\+|-)?[0-9]+)$~', $val );
	}
}


if ( ! function_exists( 'wpp_is_plugin_active' ) ) {
	function wpp_is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || wpp_is_plugin_active_for_network( $plugin );
	}
}

if ( ! function_exists( 'wpp_is_plugin_active_for_network' ) ) {
	/**
	 * Check whether the plugin is active for the entire network.
	 *
	 * Only plugins installed in the plugins/ folder can be active.
	 *
	 * Plugins in the mu-plugins/ folder can't be "activated," so this function will
	 * return false for those plugins.
	 *
	 * @since 3.0.0
	 *
	 * @param string $plugin Path to the main plugin file from plugins directory.
	 *
	 * @return bool True, if active for the network, otherwise false.
	 */
	function wpp_is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'wpp_slugify' ) ) {
	/**
	 * @param $string
	 * @param bool $slashes
	 *
	 * @return mixed|null|string|string[]
	 */
	function wpp_slugify( $string, $slashes = true ) {
		//Lower case everything
		$string         = mb_strtolower( $string );
		$normalizeChars = array(
			'š' => 's',
			'ž' => 'z',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ã' => 'a',
			'ä' => 'a',
			'å' => 'a',
			'æ' => 'ae',
			'ç' => 'c',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ð' => 'o',
			'ñ' => 'n',
			'ń' => 'n',
			'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o',
			'õ' => 'o',
			'ö' => 'o',
			'ø' => 'o',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ü' => 'u',
			'ý' => 'y',
			'þ' => 'b',
			'ÿ' => 'y',
			'ƒ' => 'f',
			'ă' => 'a',
			'ș' => 's',
			'ț' => 't',
			'œ' => 'oe',
			'+' => 'plus',
			' ' => '-',
		);
		if ( $slashes ) {
			$normalizeChars['/'] = '-';
		}
		$string = strtr( $string, $normalizeChars );
		//Make alphanumeric (removes all other characters)
		$string = preg_replace( "/[^a-z0-9_" . ( $slashes ? '' : '\/' ) . "-]/", "", $string );
		//Clean up multiple dashes or whitespaces
		$string = preg_replace( "/[\s-]+/", "-", $string );
		//Convert whitespaces and underscore to dash
		//$string = preg_replace( "/[\s_]/", "-", $string );

		return $string;
	}
}

if ( ! function_exists( 'wpp_order_by' ) ) {
	/**
	 * Order a multidimensional array from a subelement set specified as a path "foo.bar.orderValue"
	 *
	 * @param array $array
	 * @param string $path
	 * @param bool $keep_keys
	 *
	 * @return array
	 * @throws Exception
	 */
	function wpp_order_by( $array, $path, $keep_keys = false ) {
		if ( empty( $array ) ) {
			return array();
		}
		if ( ! is_array( $array ) ) {
			throw new \Exception( 'The "orderBy" filter can only be used on array (' . gettype( $array ) . ' given)' );
		}
		$path = explode( '.', $path );
		$c    = count( $path );
		// Sort the multidimensional array
		( $keep_keys ? 'uasort' : 'usort' )( $array, function ( $a, $b ) use ( $path, $c ) {
			$v1 = $a;
			for ( $i = 0; $i < $c; $i ++ ) {
				$k = $path[ $i ];
				if ( isset( $v1[ $k ] ) ) {
					$v1 = $v1[ $k ];
				} else {
					$v1 = '0';
				}
			}
			$v2 = $b;
			for ( $i = 0; $i < $c; $i ++ ) {
				$k = $path[ $i ];
				if ( isset( $v2[ $k ] ) ) {
					$v2 = $v2[ $k ];
				} else {
					$v2 = '0';
				}
			}

			return $v1 > $v2;
		} );

		return $array;
	}
}

if ( ! function_exists( 'wpp_group_by' ) ) {
	/**
	 * Groups a multidimensional array from a subelement set specified as a path "foo.bar.groupValue"
	 *
	 * If multiple elements exist with the same value they will be grouped and merged recursively (the scalar elements will become indexed array)
	 *
	 * @param array $array
	 * @param string $path
	 *
	 * @return array
	 * @throws Exception
	 */
	function wpp_group_by( $array, $path ) {
		if ( empty( $array ) ) {
			return array();
		}
		if ( ! is_array( $array ) ) {
			throw new \Exception( 'The "groupBy" filter can only be used on array (' . gettype( $array ) . ' given)' );
		}
		$new_array = array();
		$path      = explode( '.', $path );
		$c         = count( $path );
		foreach ( $array as $a ) {
			$v = $a;
			for ( $i = 0; $i < $c; $i ++ ) {
				$k = $path[ $i ];
				if ( isset( $v[ $k ] ) ) {
					$v = $v[ $k ];
				} else {
					$v = '0';
				}
			}
			if ( ! is_scalar( $v ) ) {
				throw new \Exception( 'The path for "groupBy" must be final and so return a scalar (eg: string, int..)' );
			}
			if ( ! empty( $new_array[ $v ] ) ) {
				$new_array[ $v ] = array_merge_recursive( $new_array[ $v ], $a );
			} else {
				$new_array[ $v ] = $a;
			}
		}

		return $new_array;
	}
}

if ( ! function_exists( 'wpp_remove_empty_elements' ) ) {
	/**
	 * Recursive remove empty elements from array
	 *
	 * @param array
	 *
	 * @return array
	 */
	function wpp_remove_empty_elements( $array ) {
		foreach ( (array) $array as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( empty( $value ) ) {
					unset( $array[ $key ] );
				} else {
					$array[ $key ] = wpp_remove_empty_elements( $value );
				}
			} else {
				if ( empty( $value ) ) {
					unset( $array[ $key ] );
				}
			}
		}

		return $array;
	}
}

if ( ! function_exists( 'wpp_timezone_offset' ) ) {
	/**
	 * Get timezone offset in seconds.
	 *
	 * @return float
	 */
	function wpp_timezone_offset() {
		$timezone = get_option( 'timezone_string' );

		if ( $timezone ) {
			$timezone_object = new DateTimeZone( $timezone );

			return $timezone_object->getOffset( new DateTime( 'now' ) );
		} else {
			return floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
		}
	}
}


if ( ! function_exists( 'wpp_string_to_timestamp' ) ) {
	/**
	 * Convert mysql datetime to PHP timestamp, forcing UTC. Wrapper for strtotime.
	 *
	 * @param  string $time_string Time string.
	 * @param  int|null $from_timestamp Timestamp to convert from.
	 *
	 * @return int
	 */
	function wpp_string_to_timestamp( $time_string, $from_timestamp = null ) {
		$original_timezone = date_default_timezone_get();

		// @codingStandardsIgnoreStart
		date_default_timezone_set( 'UTC' );

		if ( null === $from_timestamp ) {
			$next_timestamp = strtotime( $time_string );
		} else {
			$next_timestamp = strtotime( $time_string, $from_timestamp );
		}

		date_default_timezone_set( $original_timezone );

		// @codingStandardsIgnoreEnd

		return $next_timestamp;
	}
}

if ( ! function_exists( 'wp_timezone_string' ) ) {

	/**
	 * Helper to retrieve the timezone string for a site until.
	 * a WP core method exists (see https://core.trac.wordpress.org/ticket/24730).
	 *
	 * Adapted from https://secure.php.net/manual/en/function.timezone-name-from-abbr.php#89155.
	 *
	 * @return string PHP timezone string for the site
	 */
	function wp_timezone_string() {
		// If site timezone string exists, return it.
		$timezone = get_option( 'timezone_string' );
		if ( $timezone ) {
			return $timezone;
		}

		// Get UTC offset, if it isn't set then return UTC.
		$utc_offset = intval( get_option( 'gmt_offset', 0 ) );
		if ( 0 === $utc_offset ) {
			return 'UTC';
		}

		// Adjust UTC offset from hours to seconds.
		$utc_offset *= 3600;

		// Attempt to guess the timezone string from the UTC offset.
		$timezone = timezone_name_from_abbr( '', $utc_offset );
		if ( $timezone ) {
			return $timezone;
		}

		// Last try, guess timezone string manually.
		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( (bool) date( 'I' ) === (bool) $city['dst'] && $city['timezone_id'] && intval( $city['offset'] ) === $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}

		// Fallback to UTC.
		return 'UTC';
	}
}


if ( ! function_exists( 'wpp_doing_it_wrong' ) ) {
	/**
	 * Wrapper for wpp_doing_it_wrong.
	 *
	 * @param string $function Function used.
	 * @param string $message Message to log.
	 * @param string $version Version the message was added in.
	 */
	function wpp_doing_it_wrong( $function, $message, $version ) {
		// @codingStandardsIgnoreStart
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		if ( wp_doing_ajax() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// @codingStandardsIgnoreEnd
	}
}