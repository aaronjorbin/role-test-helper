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
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin hooks.
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
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
		</div>
		<?php
	}
}

// Initialize the class.
new Role_Test_Helper();