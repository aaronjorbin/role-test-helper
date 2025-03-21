<?php
/**
 * Core functionality for the Role Test Helper plugin.
 *
 * @package Role_Test_Helper
 */

namespace RoleTestHelper;

/**
 * Get the current environment type.
 *
 * Returns the current environment type, filtered through the `role_test_helper_environment_type` filter.
 * By default, returns the value from `wp_get_environment_type()`.
 *
 * @since 0.1.0
 *
 * @return string The current environment type. One of: 'production', 'staging', 'development', or 'local'.
 */
function get_environment_type() {
	/**
	 * Filter the environment type for the Role Test Helper plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $environment_type The current environment type. One of: 'production', 'staging', 'development', or 'local'.
	 */
	return \apply_filters( 'role_test_helper_environment_type', \wp_get_environment_type() );
}

/**
 * Check if the plugin should be active in the current environment.
 *
 * @since 0.1.0
 *
 * @param bool $force Whether to force the check.
 * @return bool Whether the plugin should be active.
 */
function is_active( $force = false ) {
	static $is_active = null;

	if ( null !== $is_active && ! $force ) {
		return $is_active;
	}

	$is_allowed = true;

	// Check environment type.
	$environment_type = get_environment_type();
	if ( 'production' === $environment_type ) {
		$is_allowed = false;
	}

	// Check if it's a .local or localhost URL.
	$site_url = \get_site_url();
	if ( str_contains( $site_url, '.local' ) || str_contains( $site_url, 'localhost' ) ) {
		$is_allowed = true; // Allow .local and localhost domains.
	}

	/**
	 * Filter whether the Role Test Helper plugin should be active.
	 *
	 * @since 0.1.0
	 *
	 * @param bool   $is_allowed      Whether the plugin should be active.
	 * @param string $environment_type The current environment type.
	 * @param string $site_url        The current site URL.
	 */
	$is_active = \apply_filters( 'role_test_helper_is_active', $is_allowed, $environment_type, $site_url );

	return $is_active;
}

/**
 * Initialize the plugin hooks.
 *
 * @since 0.1.0
 *
 * @return void
 */
function setup_hooks() {
	if ( ! is_active() ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\display_inactive_notice' );
		return;
	}

	add_action( 'admin_menu', __NAMESPACE__ . '\add_admin_menu' );
	add_filter( 'authenticate', __NAMESPACE__ . '\authenticate_role_login', 20, 2 );
}

/**
 * Display an admin notice when the plugin is inactive.
 *
 * @since 0.1.0
 *
 * @return void
 */
function display_inactive_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="notice notice-warning">
		<p>
			<?php
			echo wp_kses(
				__( '<strong>Role Test Helper</strong> is inactive because this appears to be a production environment. The plugin only works in non-production environments.', 'role-test-helper' ),
				array( 'strong' => array() )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Check if a username matches a WordPress role.
 *
 * @since 0.1.0
 *
 * @param string $username The username to check.
 * @return false|string False if not a role, role name if it is.
 */
function get_role_from_username( $username ) {
	if ( empty( $username ) ) {
		return false;
	}

	$wp_roles = wp_roles();
	$roles    = array_keys( $wp_roles->roles );

	return in_array( $username, $roles, true ) ? $username : false;
}

/**
 * Custom authentication handler for role-based logins.
 *
 * @since 0.1.0
 *
 * @param \WP_User|\WP_Error|null $user     The user object, WP_Error, or null.
 * @param string                  $username The username.
 * @return \WP_User|\WP_Error|null The user object, WP_Error, or null.
 */
function authenticate_role_login( $user, $username ) {
	if ( ! is_active() || $user instanceof \WP_User ) {
		return $user;
	}

	$role = get_role_from_username( $username );
	if ( ! $role ) {
		return $user;
	}

	// Check if user exists with this username.
	$existing_user = get_user_by( 'login', $username );

	if ( $existing_user ) {
		// User exists, bypass password check.
		return $existing_user;
	}

	// Create a new user with this role.
	$random_email = $username . '_' . wp_generate_password( 6, false ) . '@example.com';
	$user_id      = wp_create_user( $username, wp_generate_password(), $random_email );

	if ( is_wp_error( $user_id ) ) {
		return $user;
	}

	// Assign the role to the user.
	$user_obj = new \WP_User( $user_id );
	$user_obj->set_role( $role );

	return $user_obj;
}

/**
 * Add the admin menu.
 *
 * @since 0.1.0
 *
 * @return void
 */
function add_admin_menu() {
	add_management_page(
		__( 'Role Test Helper', 'role-test-helper' ),
		__( 'Role Test Helper', 'role-test-helper' ),
		'manage_options',
		'role-test-helper',
		__NAMESPACE__ . '\render_admin_page'
	);
}

/**
 * Render the admin page.
 *
 * @since 0.1.0
 *
 * @return void
 */
function render_admin_page() {
	$is_active = is_active();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Role Test Helper', 'role-test-helper' ); ?></h1>
		<p><?php echo esc_html__( 'This plugin helps test WordPress user roles and capabilities.', 'role-test-helper' ); ?></p>

		<div class="card">
			<h2><?php echo esc_html__( 'Role Login', 'role-test-helper' ); ?></h2>
			<p>
				<?php
				echo esc_html__(
					'You can log in using any WordPress role name as the username and any password.',
					'role-test-helper'
				);
				?>
			</p>
			<?php if ( $is_active ) : ?>
				<p>
					<?php
					$wp_roles  = wp_roles();
					$role_list = implode( ', ', array_keys( $wp_roles->roles ) );
					printf(
						/* translators: %s: comma-separated list of roles */
						esc_html__( 'Available roles: %s', 'role-test-helper' ),
						'<code>' . esc_html( $role_list ) . '</code>'
					);
					?>
				</p>
			<?php else : ?>
				<p>
					<?php esc_html_e( 'The plugin is currently inactive. It is only active in non-production environments or when the site URL contains .local or localhost.', 'role-test-helper' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
