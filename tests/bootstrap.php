<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Role_Test_Helper
 */

$role_test_helper_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $role_test_helper_tests_dir ) {
	$role_test_helper_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $role_test_helper_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $role_test_helper_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $role_test_helper_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function role_test_helper_manually_load_plugin() {
	require dirname( __DIR__ ) . '/role-test-helper.php';
}
tests_add_filter( 'muplugins_loaded', 'role_test_helper_manually_load_plugin' );

// Start up the WP testing environment.
require $role_test_helper_tests_dir . '/includes/bootstrap.php';
