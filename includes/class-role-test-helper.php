<?php
/**
 * The main plugin class.
 *
 * @package Role_Test_Helper
 */

/**
 * Class Role_Test_Helper
 */
class Role_Test_Helper {

	/**
	 * Whether the plugin is active for the current environment.
	 *
	 * @var bool
	 */
	private $is_active = false;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->check_environment();
		$this->init();
	}

	/**
	 * Check if the plugin should be active in the current environment.
	 *
	 * @return void
	 */
	private function check_environment() {
		$is_allowed = true;

		// Check environment type.
		$environment_type = wp_get_environment_type();
		if ( 'production' === $environment_type ) {
			$is_allowed = false;
		}

		// Check if it's a .local or localhost URL.
		$site_url = get_site_url();
		if ( false !== strpos( $site_url, '.local' ) || false !== strpos( $site_url, 'localhost' ) ) {
			$is_allowed = true; // Allow .local and localhost domains.
		}

		/**
		 * Filter whether the Role Test Helper plugin should be active.
		 *
		 * @param bool $is_allowed Whether the plugin should be active.
		 * @param string $environment_type The current environment type.
		 * @param string $site_url The current site URL.
		 */
		$this->is_active = apply_filters( 'role_test_helper_is_active', $is_allowed, $environment_type, $site_url );
	}

	/**
	 * Initialize the plugin hooks.
	 *
	 * @return void
	 */
	private function init() {
		if ( ! $this->is_active ) {
			add_action( 'admin_notices', array( $this, 'display_inactive_notice' ) );
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_filter( 'authenticate', array( $this, 'authenticate_role_login' ), 20, 2 );
	}

	/**
	 * Display an admin notice when the plugin is inactive.
	 *
	 * @return void
	 */
	public function display_inactive_notice() {
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
	 * @param string $username The username to check.
	 * @return false|string False if not a role, role name if it is.
	 */
	public function get_role_from_username( $username ) {
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
	 * @param WP_User|WP_Error|null $user The user object, WP_Error, or null.
	 * @param string                $username The username.
	 * @return WP_User|WP_Error|null The user object, WP_Error, or null.
	 */
	public function authenticate_role_login( $user, $username ) {
		if ( ! $this->is_active || $user instanceof WP_User ) {
			return $user;
		}

		$role = $this->get_role_from_username( $username );
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
		$user_obj = new WP_User( $user_id );
		$user_obj->set_role( $role );

		// Send a notice about the created user.
		add_action(
			'admin_notices',
			function () use ( $username, $role ) {
				?>
				<div class="notice notice-success">
					<p>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: 1: username, 2: role */
								__( 'User <strong>%1$s</strong> created with role <strong>%2$s</strong>.', 'role-test-helper' ),
								esc_html( $username ),
								esc_html( $role )
							),
							array( 'strong' => array() )
						);
						?>
					</p>
				</div>
				<?php
			}
		);

		return $user_obj;
	}

	/**
	 * Add the admin menu.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'Role Test Helper', 'role-test-helper' ),
			__( 'Role Test Helper', 'role-test-helper' ),
			'manage_options',
			'role-test-helper',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
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
			</div>
		</div>
		<?php
	}

	/**
	 * Check if the plugin is active for the current environment.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->is_active;
	}
}

// Initialize the class.
new Role_Test_Helper();