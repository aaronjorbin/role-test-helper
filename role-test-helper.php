<?php
/**
 * Plugin Name: Role Test Helper
 * Plugin URI: https://github.com/aaronjorbin/role-test-helper
 * Description: A plugin to help test WordPress user roles and capabilities.
 * Version: 0.1.0
 * Author: Aaron Jorbin
 * Author URI: https://aaron.jorb.in
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: role-test-helper
 * Domain Path: /languages
 *
 * @package Role_Test_Helper
 */

namespace RoleTestHelper;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'ROLE_TEST_HELPER_VERSION', '0.1.0' );
define( 'ROLE_TEST_HELPER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ROLE_TEST_HELPER_URL', plugin_dir_url( __FILE__ ) );

// Include the core functionality.
require_once ROLE_TEST_HELPER_PATH . 'includes/core.php';

// Initialize the plugin.
setup_hooks();
