<?php
/**
 * Class RoleTestHelperTest
 *
 * @package Role_Test_Helper
 */

/**
 * Role Test Helper plugin tests.
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
	 * Test the environment detection logic.
	 *
	 * @dataProvider environment_provider
	 *
	 * @param string $env_type     The environment type.
	 * @param string $site_url     The site URL.
	 * @param bool   $expected     The expected result.
	 * @param bool   $filter_value The value to return from the filter (null to not apply).
	 */
	public function test_environment_detection( $env_type, $site_url, $expected, $filter_value = null ) {
		// Set the environment type.
		add_filter(
			'role_test_helper_environment_type',
			function () use ( $env_type ) {
				return $env_type;
			}
		);

		// Set the site URL.
		add_filter(
			'site_url',
			function () use ( $site_url ) {
				return $site_url;
			},
			100
		);

		// Optional: Apply the filter.
		if ( null !== $filter_value ) {
			add_filter(
				'role_test_helper_is_active',
				function () use ( $filter_value ) {
					return $filter_value;
				},
				10,
				3
			);
		}

		$plugin = new Role_Test_Helper();
		$this->assertSame( $expected, $plugin->is_active() );
	}

	/**
	 * Data provider for environment detection tests.
	 *
	 * @return array
	 */
	public function environment_provider() {
		return array(
			'production environment'   => array( 'production', 'https://example.com', false ),
			'local environment'        => array( 'local', 'https://example.com', true ),
			'development environment'  => array( 'development', 'https://example.com', true ),
			'staging environment'      => array( 'staging', 'https://example.com', true ),
			'localhost URL'            => array( 'production', 'http://localhost/wordpress', true ),
			'local domain'             => array( 'production', 'http://example.local', true ),
			'filter override to true'  => array( 'production', 'https://example.com', true, true ),
			'filter override to false' => array( 'local', 'http://localhost/wordpress', false, false ),
		);
	}

	/**
	 * Test that the admin menu is added when the plugin is active.
	 */
	public function test_admin_menu_when_active() {
		global $_registered_pages;

		// create an admin user and login.
		$admin_user = self::factory()->user->create_and_get( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_user->ID );

		// Force the plugin to be active.
		add_filter( 'role_test_helper_is_active', '__return_true' );

		// Create an instance of the plugin.
		$plugin = new Role_Test_Helper();

		// Call the admin menu hook.
		do_action( 'admin_menu' );

		// Check if the admin page was registered.
		$this->assertArrayHasKey( 'admin_page_role-test-helper', $_registered_pages );
	}

	/**
	 * Test getting role from username.
	 */
	public function test_get_role_from_username() {
		$plugin = new Role_Test_Helper();

		// Test with valid role name.
		$this->assertEquals( 'administrator', $plugin->get_role_from_username( 'administrator' ) );
		$this->assertEquals( 'editor', $plugin->get_role_from_username( 'editor' ) );
		$this->assertEquals( 'subscriber', $plugin->get_role_from_username( 'subscriber' ) );

		// Test with invalid role name.
		$this->assertFalse( $plugin->get_role_from_username( 'nonexistent_role' ) );
		$this->assertFalse( $plugin->get_role_from_username( '' ) );
	}

	/**
	 * Test role-based authentication.
	 */
	public function test_authenticate_role_login() {
		// Force the plugin to be active.
		add_filter( 'role_test_helper_is_active', '__return_true' );

		$plugin = new Role_Test_Helper();

		// Test with non-role username.
		$result = $plugin->authenticate_role_login( null, 'nonexistent_role' );
		$this->assertNull( $result );

		// Test with role username.
		$result = $plugin->authenticate_role_login( null, 'editor' );
		$this->assertInstanceOf( WP_User::class, $result );
		$this->assertEquals( 'editor', $result->user_login );
		$this->assertTrue( in_array( 'editor', $result->roles, true ) );

		// Cleanup - delete the user.
		if ( $result instanceof WP_User ) {
			wp_delete_user( $result->ID );
		}
	}

	/**
	 * Test that existing user is returned for role login.
	 */
	public function test_authenticate_existing_role_user() {
		// Force the plugin to be active.
		add_filter( 'role_test_helper_is_active', '__return_true' );

		// Create a user with the role name.
		$user_id = wp_create_user( 'author', 'password', 'author@example.com' );
		$user    = new WP_User( $user_id );
		$user->set_role( 'author' );

		$plugin = new Role_Test_Helper();

		// Test with existing role user.
		$result = $plugin->authenticate_role_login( null, 'author' );
		$this->assertInstanceOf( WP_User::class, $result );
		$this->assertEquals( 'author', $result->user_login );
		$this->assertEquals( $user_id, $result->ID );

		// Cleanup - delete the user.
		wp_delete_user( $user_id );
	}

	/**
	 * Test that the plugin does not interfere when it's inactive.
	 */
	public function test_authentication_when_inactive() {
		// Force the plugin to be inactive.
		add_filter( 'role_test_helper_is_active', '__return_false' );

		$plugin = new Role_Test_Helper();

		// Test with role username but plugin inactive.
		$result = $plugin->authenticate_role_login( null, 'editor' );
		$this->assertNull( $result );
	}

	/**
	 * Test that the plugin respects existing authentication.
	 */
	public function test_respect_existing_authentication() {
		// Force the plugin to be active.
		add_filter( 'role_test_helper_is_active', '__return_true' );

		$plugin = new Role_Test_Helper();

		// Create a test user.
		$user_id = wp_create_user( 'testuser', 'password', 'test@example.com' );
		$user    = new WP_User( $user_id );

		// Test that the plugin doesn't override an already authenticated user.
		$result = $plugin->authenticate_role_login( $user, 'editor' );
		$this->assertSame( $user, $result );

		// Cleanup - delete the user.
		wp_delete_user( $user_id );
	}
}
