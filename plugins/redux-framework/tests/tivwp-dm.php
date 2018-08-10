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
 * Plugin Name: TIVWP-DM Development Manager
 * Plugin URI: https://github.com/TIVWP/tivwp-dm
 * Description: Install and manage development plugins. (Single-site only, no Network Activation).
 * Text Domain: tivwp-dm
 * Domain Path: /languages/
 * Version: 14.03.25
 * Author: TIV.NET
 * Author URI: http://www.tiv.net
 * Network: false
 * License: GPL2
 */


return;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * There is nothing in this plugin for WP AJAX calls,
 * so we cut this off right away, even before loading our classes.
 */
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	return;
}

/**
 * Disable network activation on multisite.
 *
 * @param bool $network_wide
 */
function tivwp_dm_disable_network_activation( $network_wide ) {
	if ( $network_wide ) {
		$silent = true;
		deactivate_plugins( plugin_basename( __FILE__ ), $silent, $network_wide );
		wp_redirect( network_admin_url( 'plugins.php?deactivate=true' ) );
		exit;
	}
}

/**
 * There should be no reason to use this plugin network-wide.
 * However, if anyone wants that, there is a constant that allows:
 * <code>
 * define( 'TIVWP_DM_NETWORK_ACTIVATION_ALLOWED', true );
 * </code>
 */
if ( ! ( defined( 'TIVWP_DM_NETWORK_ACTIVATION_ALLOWED' ) && TIVWP_DM_NETWORK_ACTIVATION_ALLOWED ) ) {
	register_activation_hook( __FILE__, 'tivwp_dm_disable_network_activation' );
}


/**
 * Launch the Controller only after plugins_loaded, so we can do necessary validation
 * @see TIVWP_DM_Controller::construct
 */
require_once dirname( __FILE__ ) . '/includes/class-tivwp-dm-controller.php';
add_action( 'plugins_loaded', array(
		'TIVWP_DM_Controller',
		'construct'
	)
);

# --- EOF
