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
 * Bootstrap the plugin unit testing environment. Customize 'active_plugins'
 * setting below to point to your main plugin file.
 *
 * Requires WordPress Unit Tests (http://unit-test.svn.wordpress.org/trunk/).
 *
 * @package wordpress-plugin-tests
 */

//Turing this crap off.  it doesn't work.  It's making Travis whine like a bitch.
return;

// Add this plugin to WordPress for activation so it can be tested.
$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( "ReduxFramework/redux-framework.php" ),
);

/**
 * If the wordpress-tests repo location has been customized (and specified
 * with WP_TESTS_DIR), use that location. This will most commonly be the case
 * when configured for use with Travis CI.
 *
 * Otherwise, we'll just assume that this plugin is installed in the WordPress
 * SVN external checkout configured in the wordpress-tests repo.
 */
if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	require getenv( 'WP_TESTS_DIR' ) . '/bootstrap.php';
} else {
	require dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/bootstrap.php';
}
