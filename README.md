# Role Test Helper

A WordPress plugin to help test user roles and capabilities.

## Description

Role Test Helper provides tools for WordPress developers and administrators to test and debug user roles and capabilities.

## Installation

### From Source

1. Clone this repository
2. Run `composer install` to install PHP dependencies
3. Activate the plugin through the WordPress admin interface

### Local Development with wp-env

1. Make sure you have Docker installed
2. Run `npm install -g @wordpress/env` to install wp-env
3. Run `wp-env start` in the plugin directory to start a local WordPress environment
4. The site will be available at http://localhost:15734 (admin: http://localhost:15734/wp-admin/)
   - Username: admin
   - Password: password

## Development

### Testing

Run PHPUnit tests:

```bash
composer phpunit
```

Run PHP CodeSniffer:

```bash
composer phpcs
```

Run PHPStan:

```bash
composer phpstan
```

## License

GPL-2.0-or-later