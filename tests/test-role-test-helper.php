<?php
/**
 * Class RoleTestHelperTest
 *
 * @package Role_Test_Helper
 */

/**
 * Sample test case.
 */
class RoleTestHelperTest extends WP_UnitTestCase {

	/**
	 * Test that the plugin is loaded.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'ROLE_TEST_HELPER_VERSION' ) );
		$this->assertTrue( defined( 'ROLE_TEST_HELPER_PATH' ) );
		$this->assertTrue( defined( 'ROLE_TEST_HELPER_URL' ) );
	}

	/**
	 * Test that the admin menu is added.
	 */
	public function test_admin_menu() {
		global $admin_page_hooks;

		// Create an instance of the plugin.
		$plugin = new Role_Test_Helper();
		
		// Call the admin menu hook.
		do_action( 'admin_menu' );
		
		// Check if the admin page was registered.
		$this->assertArrayHasKey( 'role-test-helper', $admin_page_hooks );
	}
}