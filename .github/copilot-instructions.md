# Copilot Instructions for wp-abilities-api-demo

## Repository Overview

**Type**: WordPress Plugin  
**Language**: PHP 8.3.6, JavaScript (ES6+)  
**Framework**: WordPress Abilities API  
**Size**: ~14 files - 7 PHP, 2 JS, 2 CSS (excluding vendor dependencies)  
**Dependencies**: WordPress Coding Standards (WPCS) 3.0 via Composer

This is a demonstration WordPress plugin that implements the WordPress Abilities API with various example abilities including site info retrieval, plugin listing, debug log management, post creation, and security checking using the Plugin Check plugin. The plugin includes an admin page with a JavaScript client interface for testing abilities.

## Project Structure

### Root Files
- `wp-abilities-api-demo.php` - Main plugin file with plugin header and includes
- `composer.json` - Dependency management (WPCS for linting)
- `composer.lock` - Locked dependency versions
- `.gitignore` - Excludes vendor directory

### Directory Structure
```
/
├── includes/                           # Ability implementations
│   ├── category-abilities-api-demo.php # Registers 'abilities-api-demo' category
│   ├── ability-site-info.php           # Site information retrieval ability
│   ├── ability-get-plugins.php         # Plugin listing ability
│   ├── ability-debug-log.php           # Debug log read/clear abilities
│   ├── ability-create-post.php         # Post creation ability
│   └── ability-check-security.php      # Security checking ability (uses Plugin Check)
├── admin/                              # Admin interface
│   └── abilities-demo-page.php         # Admin page for testing abilities
├── assets/                             # Frontend assets
│   ├── css/
│   │   └── admin.css                   # Admin page styles
│   └── js/
│       └── admin.js                    # Admin page JavaScript (uses wp-abilities client)
└── .github/
    ├── agents/
    │   └── wordpress-plugin-developer.md # Custom WordPress plugin developer agent
    └── copilot-instructions.md         # This file
```

### Key Architectural Patterns
- Each ability is in a separate file in `includes/`
- Abilities register using WordPress hooks: `wp_abilities_api_init` and `abilities_api_categories_init` (note: changed from `abilities_api_init` to `wp_abilities_api_init`)
- Main file includes all ability files and admin page using `require_once`
- Conditional debug logging controlled by `WP_ABILITIES_API_DEMO_DEBUG` constant
- Permission callbacks use WordPress capabilities (`manage_options`, `publish_posts`)
- All abilities include `show_in_rest` meta to enable REST API access
- Admin page uses WordPress Abilities API JavaScript client for executing abilities
- Assets are enqueued with `filemtime()` for cache busting

## Build and Validation Commands

### Initial Setup

**ALWAYS run composer install before any other commands**:
```bash
composer install --no-interaction
```
This installs WPCS and dependencies. Expected time: 10-30 seconds. May show GitHub authentication warnings but will fall back to cloning from source successfully.

### Linting

**Run PHPCS to check code against WordPress standards**:
```bash
vendor/bin/phpcs --standard=WordPress --extensions=php wp-abilities-api-demo.php includes/ admin/
```
Expected time: ~1 second. Returns exit code 2 if violations found, 0 if clean.

**Auto-fix PHPCS violations where possible**:
```bash
vendor/bin/phpcbf --standard=WordPress --extensions=php wp-abilities-api-demo.php includes/ admin/
```
This will automatically fix many formatting issues. Not all violations can be auto-fixed.

**Check available coding standards**:
```bash
vendor/bin/phpcs -i
```
Available: MySource, PEAR, PSR1, PSR2, PSR12, Squiz, Zend, Modernize, NormalizedArrays, Universal, PHPCSUtils, WordPress, WordPress-Core, WordPress-Docs, WordPress-Extra

### Testing

**No automated tests exist** in this repository. This is a demonstration plugin without test infrastructure.

### Build

**No build step required** for PHP files. JavaScript and CSS files are served directly without compilation.

**Asset Management**: The plugin uses `filemtime()` for cache busting on CSS and JS files. No minification or bundling is performed.

## Known Issues and Workarounds

### Composer Install Authentication
**Issue**: Composer may display "Could not authenticate against github.com" warnings during install.  
**Expected Behavior**: This is normal. Composer automatically falls back to cloning from source (git) and completes successfully.  
**No action required**.

### PHPCS Violations
**Issue**: The codebase has existing PHPCS violations (errors and warnings).  
**Current State**: Pre-existing violations in ability files and admin files.

**Common Violations**:
- Missing/incorrect file comments and @package tags
- Inline comments not ending with punctuation
- Debug code (error_log) in production (acceptable for demo plugin)
- Missing trailing commas in multi-line arrays
- Non-strict comparisons
- Yoda conditions not used
- Trailing whitespace

**When making changes**: Focus on not introducing NEW violations. You are NOT responsible for fixing pre-existing violations unless they are in code you modify.

### Debug Logging
**Feature**: The plugin includes extensive debug logging controlled by the `WP_ABILITIES_API_DEMO_DEBUG` constant (default: false).  
**Note**: PHPCS will warn about `error_log()` and `print_r()` usage. This is expected and acceptable for a demo plugin.

## Making Code Changes

### Adding a New Ability

1. Create a new file in `includes/` following the naming pattern: `ability-{name}.php`
2. Include the file in `wp-abilities-api-demo.php` using `require_once`
3. Use the pattern from existing abilities:
   - Security check at top: `if ( ! defined( 'ABSPATH' ) ) { exit; }`
   - Register using `add_action( 'wp_abilities_api_init', function() { ... } )` (note: use `wp_abilities_api_init` not `abilities_api_init`)
   - Use `wp_register_ability()` with proper schema
   - Include permission_callback checking WordPress capabilities
   - Add `'show_in_rest' => true` in meta array to enable REST API access
4. Follow WordPress coding standards (use PHPCS to validate)
5. Add conditional debug logging using `WP_ABILITIES_API_DEMO_DEBUG` constant
6. Add PHPDoc block above callback function

### Modifying Existing Abilities

- Each ability file is self-contained
- Hook registration must be above the callback function definition
- Main registration is in the `add_action( 'wp_abilities_api_init', ... )` callback
- Execute callback functions are defined below the registration with PHPDoc blocks
- Schema defines input/output structure using JSON Schema format
- All abilities should include `'show_in_rest' => true` in meta for REST API access

### Code Style Requirements

- Use WordPress Coding Standards
- Always include ABSPATH security check
- Use WordPress naming conventions (snake_case for functions)
- Prefix functions with `wp_abilities_demo_`
- Sanitize all user input
- Use WordPress i18n functions for translatable strings
- Add proper PHPDoc blocks

## Dependencies

### Required Runtime Dependencies (WordPress)
- WordPress core (version not specified in plugin header)
- WordPress Abilities API (assumed to be available)
- Plugin Check plugin (required for security check ability only)

### JavaScript Dependencies
- `wp-abilities` - WordPress Abilities API JavaScript client (enqueued as dependency)
- No external JavaScript libraries required (uses vanilla JS)

### Development Dependencies (Composer)
- `wp-coding-standards/wpcs` ^3.0 - WordPress Coding Standards for PHPCS
- `squizlabs/php_codesniffer` (auto-installed as WPCS dependency)

### Installing New Dependencies

**Before adding any composer dependencies**:
```bash
composer require <package> --dev
```
Then run `composer install` to update lock file.

## Validation Checklist

Before finalizing changes:

1. ✅ Run `composer install` if composer.json changed
2. ✅ Run PHPCS: `vendor/bin/phpcs --standard=WordPress --extensions=php wp-abilities-api-demo.php includes/ admin/`
3. ✅ Address any NEW violations in your changed code (you can ignore pre-existing ones)
4. ✅ Verify plugin header syntax if modified (Plugin Name, Description, Version, Requires Plugins)
5. ✅ Ensure all new functions have security checks (`! defined( 'ABSPATH' )`)
6. ✅ Verify new includes are added to main plugin file
7. ✅ Check that input sanitization is used for user-provided data
8. ✅ For new abilities, ensure `wp_abilities_api_init` hook is used (not `abilities_api_init`)
9. ✅ For new abilities, ensure `show_in_rest` is set to true in meta array

## File Manifest

**Root directory** (4 files excluding vendor):
- `wp-abilities-api-demo.php` - Main plugin file
- `composer.json` - Dependency configuration
- `composer.lock` - Locked dependency versions
- `.gitignore` - Git ignore rules

**includes/ directory** (6 files):
- `category-abilities-api-demo.php` - Category registration
- `ability-site-info.php` - Site info ability
- `ability-get-plugins.php` - Plugin list ability
- `ability-debug-log.php` - Debug log abilities
- `ability-create-post.php` - Post creation ability
- `ability-check-security.php` - Security check ability

**admin/ directory** (1 file):
- `abilities-demo-page.php` - Admin page with ability testing interface

**assets/ directory** (2 files):
- `css/admin.css` - Admin page styles
- `js/admin.js` - Admin page JavaScript with Abilities API client integration

**.github/ directory** (2 files):
- `agents/wordpress-plugin-developer.md` - Custom WordPress plugin developer agent for Copilot
- `copilot-instructions.md` - This file

## Additional Notes

### Admin Page
The plugin includes an admin page accessible via **Tools → Abilities API Demo** that provides a user interface for testing all registered abilities. The page uses the WordPress Abilities API JavaScript client to execute abilities and display results.

### Custom Agents
A custom WordPress plugin developer agent is available at `.github/agents/wordpress-plugin-developer.md` for advanced WordPress plugin development tasks. This agent has specialized knowledge of WordPress best practices and coding standards.

### WordPress Plugin Header
The main plugin file includes required WordPress headers that define the plugin. Do NOT modify these unless specifically required:
```php
/**
 * Plugin Name: WP Abilities API Demo
 * Description: Demonstrates the WordPress Abilities API with various example abilities.
 * Version: 1.0.1
 * Requires Plugins: plugin-check
 */
```

### No CI/CD Pipeline
This repository does NOT have GitHub Actions, workflows, or automated CI/CD. All validation must be done locally.

### No README
This repository does not have a README.md file. All documentation is in this file.

### Trust These Instructions
The information in this file has been validated by running commands and inspecting the codebase. Trust this as the source of truth and only perform additional exploration if you find information missing or incorrect.
