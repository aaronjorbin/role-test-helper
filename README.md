# Role Test Helper

A WordPress plugin to help developers test user roles and capabilities in non-production environments.

## Description

Role Test Helper is designed to simplify WordPress role testing by providing:

1. **Role-Based Login**: Log in with any WordPress role name as the username (e.g., "administrator", "editor", "author") and any password. The plugin will create users with appropriate permissions if they don't exist.

2. **Safe Environment Detection**: The plugin automatically disables itself in production environments to prevent security risks, only functioning in development, staging, or local environments.

## Features

- **Easy Role Testing**: Log in as any WordPress role without creating test users manually
- **Production Safety**: Automatically disabled in production environments using `wp_get_environment_type()`
- **Local Development Support**: Always enabled on localhost and .local domains
- **Extensibility**: Provides filter hooks for customizing plugin behavior

## Installation

### From Source

1. Clone this repository
2. Run `composer install` to install PHP dependencies
3. Activate the plugin through the WordPress admin interface

### Using Composer

```bash
composer require jorbin/role-test-helper
```

### Local Development with wp-env

1. Make sure you have Docker installed
2. Install Node.js dependencies with `npm install`
3. Run `npm run start` to start a local WordPress environment using wp-env
4. The site will be available at http://localhost:15734 (admin: http://localhost:15734/wp-admin/)
   - Username: admin
   - Password: password

## Usage

### Role-Based Login

Once activated, you can log in with any WordPress role name as the username:

1. Go to your WordPress login page (wp-login.php)
2. Enter a role name (e.g., "editor", "author", "contributor") as the username
3. Enter any password
4. Click "Log In"

The plugin will either:
- Log you in as an existing user with that username, or
- Create a new user with the appropriate role and log you in

### Available Roles

The default WordPress roles you can use are:
- administrator
- editor
- author
- contributor
- subscriber

Plus any custom roles defined by themes or plugins.

### Admin Page

The plugin adds an admin page under "Tools > Role Test Helper" that shows:
- Current plugin status
- Available roles for testing
- Usage instructions

### Environment Control

By default, the plugin is:
- Disabled in production environments
- Enabled in development, staging, and local environments
- Always enabled on localhost and .local domains

### Filter Hooks

#### `role_test_helper_is_active`

Control whether the plugin should be active:

```php
// Force the plugin to be inactive
add_filter( 'role_test_helper_is_active', '__return_false' );

// Custom logic for determining if the plugin should be active
add_filter( 'role_test_helper_is_active', function( $is_allowed, $environment_type, $site_url ) {
    // Your custom logic here
    return $is_allowed;
}, 10, 3 );
```

## Development

### Setup

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Testing

```bash
# Run PHPUnit tests
composer phpunit

# Run PHP CodeSniffer
composer phpcs

# Run PHP Code Beautifier
composer phpcbf

# Run PHPStan static analysis
composer phpstan

# Start the development environment
npm run start
```

### Coding Standards

This plugin follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) and uses PHP_CodeSniffer to enforce them.

### Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-new-feature`
3. Make your changes and commit them: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Submit a pull request

Before submitting, please:
- Ensure your code follows WordPress Coding Standards (`composer phpcs`)
- Add unit tests for any new functionality
- Update documentation as needed

## Security

**IMPORTANT**: This plugin is intended for development and testing environments only. It will automatically disable itself in production environments to prevent security risks.

## License

GPL-2.0-or-later