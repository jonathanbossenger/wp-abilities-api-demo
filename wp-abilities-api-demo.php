<?php
/**
 * Plugin Name: WP Abilities API Demo
 * Description: Demonstrates the WordPress Abilities API with various example abilities.
 * Version: 1.0.2
 * Requires Plugins: plugin-check
 *
 * @package WPAbilitiesAPIDemo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    // Composer dependencies are missing.
    add_action(
        'admin_notices',
        function () {
            ?>
            <div class="notice notice-error">
                <p><?php esc_html_e( 'WP Abilities API Demo plugin requires Composer dependencies. Please run "composer install" in the plugin directory.', 'wp-abilities-api-demo' ); ?></p>
            </div>
            <?php
        }
    );
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

// Define debug constant for conditional logging.
if ( ! defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) ) {
	define( 'WP_ABILITIES_API_DEMO_DEBUG', false );
}

// Include logger.
require_once __DIR__ . '/includes/logger.php';

// Include ability files.
require_once __DIR__ . '/includes/category-abilities-api-demo.php';
require_once __DIR__ . '/includes/ability-site-info.php';
require_once __DIR__ . '/includes/ability-plugins.php';
require_once __DIR__ . '/includes/ability-themes.php';
require_once __DIR__ . '/includes/ability-debug-log.php';
require_once __DIR__ . '/includes/ability-create-post.php';
require_once __DIR__ . '/includes/ability-check-security.php';

// Include admin page.
require_once __DIR__ . '/admin/abilities-demo-page.php';
